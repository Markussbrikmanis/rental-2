<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('client.profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate(
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
                'invoice_number_format' => [
                    Rule::requiredIf($user->isOwner()),
                    'nullable',
                    'string',
                    'max:255',
                    function (string $attribute, mixed $value, \Closure $fail) use ($user): void {
                        if (! $user->isOwner() || $value === null || $value === '') {
                            return;
                        }

                        if (! str_contains($value, '{num}')) {
                            $fail(__('app.client.profile.invoice_number_format.messages.num_required'));

                            return;
                        }

                        preg_match_all('/\{[^}]+\}/', $value, $matches);

                        $allowedPlaceholders = ['{year}', '{num}', '{property_unit_code}'];

                        foreach ($matches[0] as $placeholder) {
                            if (! in_array($placeholder, $allowedPlaceholders, true)) {
                                $fail(__('app.client.profile.invoice_number_format.messages.invalid_placeholder', ['placeholder' => $placeholder]));

                                return;
                            }
                        }
                    },
                ],
                'invoice_sender_name' => ['nullable', 'string', 'max:255'],
                'invoice_sender_address' => ['nullable', 'string'],
                'invoice_sender_registration_number' => ['nullable', 'string', 'max:255'],
                'invoice_sender_vat_number' => ['nullable', 'string', 'max:255'],
                'invoice_sender_bank_name' => ['nullable', 'string', 'max:255'],
                'invoice_sender_swift_code' => ['nullable', 'string', 'max:255'],
                'invoice_sender_account_number' => ['nullable', 'string', 'max:255'],
                'invoice_payment_terms_text' => ['nullable', 'string'],
                'invoice_footer_text' => ['nullable', 'string'],
                'invoice_vat_enabled' => ['nullable', 'boolean'],
                'invoice_vat_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
                'invoice_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
                'remove_invoice_logo' => ['nullable', 'boolean'],
            ],
            trans('app.validation.messages'),
            trans('app.validation.attributes'),
        );

        if ($user->isOwner()) {
            $validated['invoice_vat_enabled'] = $request->boolean('invoice_vat_enabled');
            $validated['invoice_vat_rate'] = $validated['invoice_vat_rate'] ?? $user->invoice_vat_rate ?? 21;
        } else {
            unset($validated['invoice_number_format']);
            unset(
                $validated['invoice_sender_name'],
                $validated['invoice_sender_address'],
                $validated['invoice_sender_registration_number'],
                $validated['invoice_sender_vat_number'],
                $validated['invoice_sender_bank_name'],
                $validated['invoice_sender_swift_code'],
                $validated['invoice_sender_account_number'],
                $validated['invoice_payment_terms_text'],
                $validated['invoice_footer_text'],
                $validated['invoice_vat_enabled'],
                $validated['invoice_vat_rate'],
            );
        }

        $this->syncInvoiceLogo($request, $user, $validated);

        unset($validated['invoice_logo'], $validated['remove_invoice_logo']);

        $user->update($validated);

        return redirect()
            ->route('client.profile.edit')
            ->with('status', __('app.client.profile.messages.updated'));
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            [
                'current_password' => ['required', 'current_password'],
                'password' => ['required', 'confirmed', Password::min(8)],
            ],
            trans('app.validation.messages'),
            trans('app.validation.attributes'),
        );

        $request->user()->update([
            'password' => $validated['password'],
        ]);

        return redirect()
            ->route('client.profile.edit')
            ->with('status', __('app.client.profile.messages.password_updated'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate(
            [
                'current_password' => ['required', 'current_password'],
            ],
            trans('app.validation.messages'),
            trans('app.validation.attributes'),
        );

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('client.login')
            ->with('status', __('app.client.profile.messages.deleted'));
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function syncInvoiceLogo(Request $request, \App\Models\User $user, array &$validated): void
    {
        if (! $user->isOwner()) {
            return;
        }

        if ($request->boolean('remove_invoice_logo') && $user->invoice_logo_path) {
            Storage::disk('public')->delete($user->invoice_logo_path);
            $validated['invoice_logo_path'] = null;
        }

        /** @var UploadedFile|null $uploadedLogo */
        $uploadedLogo = $request->file('invoice_logo');

        if (! $uploadedLogo) {
            return;
        }

        if ($user->invoice_logo_path) {
            Storage::disk('public')->delete($user->invoice_logo_path);
        }

        $validated['invoice_logo_path'] = $uploadedLogo->store('invoice-logos', 'public');
    }
}
