<?php

namespace App\Http\Controllers\Client;

use App\Enums\PropertyType;
use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OwnerPropertyController extends Controller
{
    public function index(Request $request): View
    {
        return view('client.properties.index', [
            'countries' => $this->countries(),
            'properties' => $request->user()
                ->properties()
                ->latest()
                ->get(),
            'types' => PropertyType::cases(),
        ]);
    }

    public function create(): View
    {
        return view('client.properties.create', [
            'property' => new Property([
                'country' => __('app.properties.defaults.country'),
            ]),
            'countries' => $this->countries(),
            'types' => PropertyType::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $property = $request->user()->properties()->create(
            $this->validatedData($request),
        );

        return redirect()
            ->route('client.properties.index')
            ->with('status', __('app.properties.messages.created', [
                'name' => $property->name,
            ]));
    }

    public function edit(Request $request, Property $property): View
    {
        $this->ensureOwnership($request, $property);

        return view('client.properties.edit', [
            'countries' => $this->countries(),
            'property' => $property,
            'types' => PropertyType::cases(),
        ]);
    }

    public function show(Request $request, Property $property): View
    {
        $this->ensureOwnership($request, $property);

        return view('client.properties.show', [
            'property' => $property->load([
                'units.leases.tenantProfile',
                'units.meters',
            ]),
        ]);
    }

    public function update(Request $request, Property $property): RedirectResponse
    {
        $this->ensureOwnership($request, $property);

        $property->update($this->validatedData($request));

        return redirect()
            ->route('client.properties.index')
            ->with('status', __('app.properties.messages.updated', [
                'name' => $property->name,
            ]));
    }

    public function destroy(Request $request, Property $property): RedirectResponse
    {
        $this->ensureOwnership($request, $property);

        $propertyName = $property->name;
        $property->delete();

        return redirect()
            ->route('client.properties.index')
            ->with('status', __('app.properties.messages.deleted', [
                'name' => $propertyName,
            ]));
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request): array
    {
        $validated = $request->validate(
            [
                'name' => ['required', 'string', 'max:255'],
                'notes' => ['nullable', 'string'],
                'address' => ['required', 'string', 'max:255'],
                'city' => ['required', 'string', 'max:255'],
                'country' => ['required', Rule::in($this->countries())],
                'price' => ['required', 'numeric', 'min:0'],
                'type' => ['required', Rule::in(PropertyType::values())],
                'acquired_at' => ['required', 'date'],
            ],
            trans('app.validation.messages'),
            trans('app.validation.attributes'),
        );

        return $validated;
    }

    /**
     * @return list<string>
     */
    private function countries(): array
    {
        return trans('app.properties.countries');
    }

    private function ensureOwnership(Request $request, Property $property): void
    {
        abort_unless($property->user_id === $request->user()->id, 404);
    }
}
