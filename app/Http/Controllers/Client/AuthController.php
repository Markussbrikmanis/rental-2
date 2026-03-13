<?php

namespace App\Http\Controllers\Client;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('client.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate(
            [
                'email' => ['required', 'email'],
                'password' => ['required', 'string'],
            ],
            trans('app.validation.messages'),
            trans('app.validation.attributes'),
        );

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            return back()
                ->withErrors([
                    'email' => __('app.client.messages.invalid_credentials'),
                ])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->route('client.panel');
    }

    public function showRegister(): View
    {
        return view('client.auth.register', [
            'roles' => UserRole::selfRegistrationValues(),
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(UserRole::selfRegistrationValues())],
            'password' => ['required', 'confirmed', 'min:8'],
        ], trans('app.validation.messages'), trans('app.validation.attributes'));

        $user = User::create($validated);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('client.panel');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('client.login');
    }
}
