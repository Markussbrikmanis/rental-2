<?php

namespace App\Http\Controllers\Client;

use App\Enums\LeaseStatus;
use App\Http\Controllers\Controller;
use App\Models\Lease;
use App\Models\LeaseChargeRule;
use App\Models\PropertyUnit;
use App\Models\TenantProfile;
use App\Services\InvoiceGenerationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LeaseController extends Controller
{
    public function index(Request $request): View
    {
        return view('client.leases.index', [
            'leases' => Lease::query()
                ->whereHas('propertyUnit.property', fn ($query) => $query->where('user_id', $request->user()->id))
                ->with(['propertyUnit.property', 'tenantProfile'])
                ->latest()
                ->get(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('client.leases.create', [
            'lease' => new Lease([
                'status' => LeaseStatus::Draft,
                'currency' => 'EUR',
                'due_day' => 10,
            ]),
            'statuses' => LeaseStatus::cases(),
            'units' => $this->ownerUnits($request),
            'tenants' => $request->user()->tenantProfiles()->orderBy('full_name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $this->assertNoOverlap($validated);

        $lease = Lease::query()->create($validated);
        $this->syncUnitStatus($lease->propertyUnit);

        return redirect()
            ->route('client.leases.show', $lease)
            ->with('status', __('app.rental.leases.messages.created'));
    }

    public function show(Request $request, Lease $lease): View
    {
        $this->ensureOwnedLease($request, $lease);

        return view('client.leases.show', [
            'lease' => $lease->load([
                'propertyUnit.property',
                'tenantProfile',
                'chargeRules',
                'invoices',
            ]),
            'chargeRule' => new LeaseChargeRule([
                'interval_count' => 1,
                'auto_invoice_enabled' => true,
                'effective_from' => $lease->billing_start_date,
            ]),
        ]);
    }

    public function edit(Request $request, Lease $lease): View
    {
        $this->ensureOwnedLease($request, $lease);

        return view('client.leases.edit', [
            'lease' => $lease,
            'statuses' => LeaseStatus::cases(),
            'units' => $this->ownerUnits($request),
            'tenants' => $request->user()->tenantProfiles()->orderBy('full_name')->get(),
        ]);
    }

    public function update(Request $request, Lease $lease): RedirectResponse
    {
        $this->ensureOwnedLease($request, $lease);

        $validated = $this->validatedData($request, $lease);
        $this->assertNoOverlap($validated, $lease);

        $lease->update($validated);
        $this->syncUnitStatus($lease->propertyUnit);

        return redirect()
            ->route('client.leases.show', $lease)
            ->with('status', __('app.rental.leases.messages.updated'));
    }

    public function destroy(Request $request, Lease $lease): RedirectResponse
    {
        $this->ensureOwnedLease($request, $lease);

        $unit = $lease->propertyUnit;
        $lease->delete();
        $this->syncUnitStatus($unit);

        return redirect()
            ->route('client.leases.index')
            ->with('status', __('app.rental.leases.messages.deleted'));
    }

    public function generateInvoice(Request $request, Lease $lease, InvoiceGenerationService $invoiceGenerationService): RedirectResponse
    {
        $this->ensureOwnedLease($request, $lease);

        $generated = $invoiceGenerationService->generateForLease($lease->load(['chargeRules', 'tenantProfile', 'propertyUnit.property']), now());

        return redirect()
            ->route('client.leases.show', $lease)
            ->with('status', __('app.rental.invoices.messages.generated', ['count' => $generated->count()]));
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?Lease $lease = null): array
    {
        return $request->validate(
            [
                'property_unit_id' => [
                    'required',
                    Rule::exists('property_units', 'id')->where(function ($query) use ($request): void {
                        $query->whereIn('property_id', $request->user()->properties()->select('id'));
                    }),
                ],
                'tenant_profile_id' => ['required', Rule::exists('tenant_profiles', 'id')->where('owner_id', $request->user()->id)],
                'start_date' => ['required', 'date'],
                'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
                'billing_start_date' => ['required', 'date'],
                'due_day' => ['required', 'integer', 'min:1', 'max:28'],
                'currency' => ['required', 'string', 'max:10'],
                'status' => ['required', Rule::in(LeaseStatus::values())],
                'deposit' => ['nullable', 'numeric', 'min:0'],
                'notes' => ['nullable', 'string'],
            ],
            trans('app.validation.messages'),
            trans('app.validation.attributes'),
        );
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function assertNoOverlap(array $validated, ?Lease $lease = null): void
    {
        $query = Lease::query()
            ->where('property_unit_id', $validated['property_unit_id'])
            ->whereNotIn('status', [LeaseStatus::Ended, LeaseStatus::Cancelled]);

        if ($lease) {
            $query->whereKeyNot($lease->id);
        }

        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'] ?? null;

        $query->where(function ($builder) use ($startDate, $endDate): void {
            $builder->whereDate('start_date', '<=', $endDate ?? '9999-12-31')
                ->where(function ($subQuery) use ($startDate): void {
                    $subQuery->whereNull('end_date')
                        ->orWhereDate('end_date', '>=', $startDate);
                });
        });

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'property_unit_id' => __('app.rental.leases.messages.overlap'),
            ]);
        }
    }

    private function ensureOwnedLease(Request $request, Lease $lease): void
    {
        abort_unless($lease->propertyUnit->property->user_id === $request->user()->id, 404);
    }

    private function ownerUnits(Request $request)
    {
        return PropertyUnit::query()
            ->whereHas('property', fn ($query) => $query->where('user_id', $request->user()->id))
            ->with('property')
            ->orderBy('name')
            ->get();
    }

    private function syncUnitStatus(PropertyUnit $unit): void
    {
        $unit->status = $unit->leases()
            ->whereIn('status', [LeaseStatus::Active, LeaseStatus::Draft])
            ->exists()
            ? \App\Enums\PropertyUnitStatus::Occupied
            : \App\Enums\PropertyUnitStatus::Vacant;

        $unit->save();
    }
}
