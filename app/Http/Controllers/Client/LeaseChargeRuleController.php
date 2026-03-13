<?php

namespace App\Http\Controllers\Client;

use App\Enums\ChargeFrequency;
use App\Enums\ChargeIntervalUnit;
use App\Http\Controllers\Controller;
use App\Models\Lease;
use App\Models\LeaseChargeRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LeaseChargeRuleController extends Controller
{
    public function edit(Request $request, Lease $lease, LeaseChargeRule $chargeRule): View
    {
        $this->ensureOwnedLease($request, $lease);
        abort_unless($chargeRule->lease_id === $lease->id, 404);

        return view('client.charge-rules.edit', [
            'lease' => $lease,
            'chargeRule' => $chargeRule,
            'frequencies' => ChargeFrequency::cases(),
            'intervalUnits' => ChargeIntervalUnit::cases(),
        ]);
    }

    public function store(Request $request, Lease $lease): RedirectResponse
    {
        $this->ensureOwnedLease($request, $lease);

        $lease->chargeRules()->create($this->validatedData($request));

        return redirect()
            ->route('client.leases.show', $lease)
            ->with('status', __('app.rental.charge_rules.messages.created'));
    }

    public function update(Request $request, Lease $lease, LeaseChargeRule $chargeRule): RedirectResponse
    {
        $this->ensureOwnedLease($request, $lease);
        abort_unless($chargeRule->lease_id === $lease->id, 404);

        $chargeRule->update($this->validatedData($request));

        return redirect()
            ->route('client.leases.show', $lease)
            ->with('status', __('app.rental.charge_rules.messages.updated'));
    }

    public function destroy(Request $request, Lease $lease, LeaseChargeRule $chargeRule): RedirectResponse
    {
        $this->ensureOwnedLease($request, $lease);
        abort_unless($chargeRule->lease_id === $lease->id, 404);

        $chargeRule->delete();

        return redirect()
            ->route('client.leases.show', $lease)
            ->with('status', __('app.rental.charge_rules.messages.deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request): array
    {
        $validated = $request->validate(
            [
                'name' => ['required', 'string', 'max:255'],
                'amount' => ['required', 'numeric', 'min:0'],
                'frequency' => ['required', Rule::in(ChargeFrequency::values())],
                'interval_count' => ['required', 'integer', 'min:1'],
                'interval_unit' => ['nullable', Rule::in(ChargeIntervalUnit::values())],
                'effective_from' => ['required', 'date'],
                'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
                'auto_invoice_enabled' => ['nullable', 'boolean'],
            ],
            trans('app.validation.messages'),
            trans('app.validation.attributes'),
        );

        $validated['auto_invoice_enabled'] = $request->boolean('auto_invoice_enabled');

        return $validated;
    }

    private function ensureOwnedLease(Request $request, Lease $lease): void
    {
        abort_unless($lease->propertyUnit->property->user_id === $request->user()->id, 404);
    }
}
