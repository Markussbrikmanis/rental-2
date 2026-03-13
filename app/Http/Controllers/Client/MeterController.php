<?php

namespace App\Http\Controllers\Client;

use App\Enums\MeterType;
use App\Enums\UtilityBillingMode;
use App\Http\Controllers\Controller;
use App\Models\Meter;
use App\Models\PropertyUnit;
use App\Services\MeterConsumptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MeterController extends Controller
{
    public function index(Request $request): View
    {
        return view('client.meters.index', [
            'meters' => Meter::query()
                ->whereHas('propertyUnit.property', fn ($query) => $query->where('user_id', $request->user()->id))
                ->with('propertyUnit.property')
                ->latest()
                ->get(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('client.meters.create', [
            'meter' => new Meter([
                'is_active' => true,
                'utility_billing_mode' => UtilityBillingMode::None,
            ]),
            'types' => MeterType::cases(),
            'utilityBillingModes' => UtilityBillingMode::cases(),
            'units' => $this->ownerUnits($request),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $meter = Meter::query()->create($this->validatedData($request));

        return redirect()
            ->route('client.meters.show', $meter)
            ->with('status', __('app.rental.meters.messages.created', ['name' => $meter->name]));
    }

    public function show(Request $request, Meter $meter, MeterConsumptionService $meterConsumptionService): View
    {
        $this->ensureOwnedMeter($request, $meter);

        return view('client.meters.show', [
            'meter' => $meter->load(['propertyUnit.property', 'readings']),
            'readingDeltas' => $meterConsumptionService->readingDeltas($meter),
        ]);
    }

    public function edit(Request $request, Meter $meter): View
    {
        $this->ensureOwnedMeter($request, $meter);

        return view('client.meters.edit', [
            'meter' => $meter,
            'types' => MeterType::cases(),
            'utilityBillingModes' => UtilityBillingMode::cases(),
            'units' => $this->ownerUnits($request),
        ]);
    }

    public function update(Request $request, Meter $meter): RedirectResponse
    {
        $this->ensureOwnedMeter($request, $meter);

        $meter->update($this->validatedData($request));

        return redirect()
            ->route('client.meters.show', $meter)
            ->with('status', __('app.rental.meters.messages.updated', ['name' => $meter->name]));
    }

    public function destroy(Request $request, Meter $meter): RedirectResponse
    {
        $this->ensureOwnedMeter($request, $meter);

        $name = $meter->name;
        $meter->delete();

        return redirect()
            ->route('client.meters.index')
            ->with('status', __('app.rental.meters.messages.deleted', ['name' => $name]));
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request): array
    {
        $validated = $request->validate(
            [
                'property_unit_id' => [
                    'required',
                    Rule::exists('property_units', 'id')->where(function ($query) use ($request): void {
                        $query->whereIn('property_id', $request->user()->properties()->select('id'));
                    }),
                ],
                'name' => ['required', 'string', 'max:255'],
                'type' => ['required', Rule::in(MeterType::values())],
                'unit' => ['required', 'string', 'max:50'],
                'utility_billing_mode' => ['required', Rule::in(UtilityBillingMode::values())],
                'rate_per_unit' => [
                    Rule::requiredIf(function () use ($request): bool {
                        return in_array($request->input('utility_billing_mode'), [
                            UtilityBillingMode::Included->value,
                            UtilityBillingMode::Separate->value,
                        ], true);
                    }),
                    'nullable',
                    'numeric',
                    'min:0',
                ],
                'is_active' => ['nullable', 'boolean'],
            ],
            trans('app.validation.messages'),
            trans('app.validation.attributes'),
        );

        if (($validated['utility_billing_mode'] ?? UtilityBillingMode::None->value) === UtilityBillingMode::None->value) {
            $validated['rate_per_unit'] = null;
        }

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }

    private function ensureOwnedMeter(Request $request, Meter $meter): void
    {
        abort_unless($meter->propertyUnit->property->user_id === $request->user()->id, 404);
    }

    private function ownerUnits(Request $request)
    {
        return PropertyUnit::query()
            ->whereHas('property', fn ($query) => $query->where('user_id', $request->user()->id))
            ->with('property')
            ->orderBy('name')
            ->get();
    }
}
