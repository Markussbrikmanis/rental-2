<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            ],
            trans('app.validation.messages'),
            trans('app.validation.attributes'),
        );

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
}
