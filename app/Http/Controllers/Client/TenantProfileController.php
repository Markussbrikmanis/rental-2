<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\TenantProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TenantProfileController extends Controller
{
    public function index(Request $request): View
    {
        return view('client.tenants.index', [
            'tenants' => $request->user()
                ->tenantProfiles()
                ->latest()
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('client.tenants.create', [
            'tenant' => new TenantProfile(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant = $request->user()->tenantProfiles()->create($this->validatedData($request));

        return redirect()
            ->route('client.tenants.index')
            ->with('status', __('app.rental.tenants.messages.created', ['name' => $tenant->full_name]));
    }

    public function edit(Request $request, TenantProfile $tenant): View
    {
        $this->ensureOwnedTenant($request, $tenant);

        return view('client.tenants.edit', [
            'tenant' => $tenant,
        ]);
    }

    public function update(Request $request, TenantProfile $tenant): RedirectResponse
    {
        $this->ensureOwnedTenant($request, $tenant);

        $tenant->update($this->validatedData($request, $tenant));

        return redirect()
            ->route('client.tenants.index')
            ->with('status', __('app.rental.tenants.messages.updated', ['name' => $tenant->full_name]));
    }

    public function destroy(Request $request, TenantProfile $tenant): RedirectResponse
    {
        $this->ensureOwnedTenant($request, $tenant);

        $name = $tenant->full_name;
        $tenant->delete();

        return redirect()
            ->route('client.tenants.index')
            ->with('status', __('app.rental.tenants.messages.deleted', ['name' => $name]));
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?TenantProfile $tenant = null): array
    {
        return $request->validate(
            [
                'full_name' => ['required', 'string', 'max:255'],
                'company_name' => ['nullable', 'string', 'max:255'],
                'email' => ['nullable', 'email', 'max:255'],
                'phone' => ['nullable', 'string', 'max:255'],
                'personal_code' => ['nullable', 'string', 'max:255'],
                'registration_number' => ['nullable', 'string', 'max:255'],
                'billing_name' => ['nullable', 'string', 'max:255'],
                'billing_address' => ['nullable', 'string'],
                'billing_registration_number' => ['nullable', 'string', 'max:255'],
                'billing_vat_number' => ['nullable', 'string', 'max:255'],
                'billing_bank_name' => ['nullable', 'string', 'max:255'],
                'billing_swift_code' => ['nullable', 'string', 'max:255'],
                'billing_account_number' => ['nullable', 'string', 'max:255'],
                'notes' => ['nullable', 'string'],
                'user_id' => [
                    'nullable',
                    Rule::exists('users', 'id')->where('role', 'tenant'),
                    Rule::unique('tenant_profiles', 'user_id')->ignore($tenant?->id),
                ],
            ],
            trans('app.validation.messages'),
            trans('app.validation.attributes'),
        );
    }

    private function ensureOwnedTenant(Request $request, TenantProfile $tenant): void
    {
        abort_unless($tenant->owner_id === $request->user()->id, 404);
    }
}
