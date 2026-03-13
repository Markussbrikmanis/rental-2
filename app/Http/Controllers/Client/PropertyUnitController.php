<?php

namespace App\Http\Controllers\Client;

use App\Enums\PropertyUnitStatus;
use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyUnit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PropertyUnitController extends Controller
{
    public function index(Request $request): View
    {
        return view('client.units.index', [
            'units' => PropertyUnit::query()
                ->whereHas('property', fn ($query) => $query->where('user_id', $request->user()->id))
                ->with('property')
                ->latest()
                ->get(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('client.units.create', [
            'properties' => $this->ownerProperties($request),
            'statuses' => PropertyUnitStatus::cases(),
            'unit' => new PropertyUnit([
                'is_active' => true,
                'status' => PropertyUnitStatus::Vacant,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $unit = PropertyUnit::query()->create($this->validatedData($request));

        return redirect()
            ->route('client.units.index')
            ->with('status', __('app.rental.units.messages.created', ['name' => $unit->name]));
    }

    public function edit(Request $request, PropertyUnit $unit): View
    {
        $this->ensureOwnedUnit($request, $unit);

        return view('client.units.edit', [
            'properties' => $this->ownerProperties($request),
            'statuses' => PropertyUnitStatus::cases(),
            'unit' => $unit,
        ]);
    }

    public function update(Request $request, PropertyUnit $unit): RedirectResponse
    {
        $this->ensureOwnedUnit($request, $unit);

        $unit->update($this->validatedData($request));

        return redirect()
            ->route('client.units.index')
            ->with('status', __('app.rental.units.messages.updated', ['name' => $unit->name]));
    }

    public function destroy(Request $request, PropertyUnit $unit): RedirectResponse
    {
        $this->ensureOwnedUnit($request, $unit);

        $name = $unit->name;
        $unit->delete();

        return redirect()
            ->route('client.units.index')
            ->with('status', __('app.rental.units.messages.deleted', ['name' => $name]));
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request): array
    {
        $validated = $request->validate(
            [
                'property_id' => ['required', Rule::exists('properties', 'id')->where('user_id', $request->user()->id)],
                'name' => ['required', 'string', 'max:255'],
                'code' => ['nullable', 'string', 'max:255'],
                'notes' => ['nullable', 'string'],
                'status' => ['required', Rule::in(PropertyUnitStatus::values())],
                'area' => ['nullable', 'numeric', 'min:0'],
                'unit_type' => ['nullable', 'string', 'max:255'],
                'is_active' => ['nullable', 'boolean'],
            ],
            trans('app.validation.messages'),
            trans('app.validation.attributes'),
        );

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }

    private function ensureOwnedUnit(Request $request, PropertyUnit $unit): void
    {
        abort_unless($unit->property->user_id === $request->user()->id, 404);
    }

    private function ownerProperties(Request $request)
    {
        return Property::query()
            ->where('user_id', $request->user()->id)
            ->orderBy('name')
            ->get();
    }
}
