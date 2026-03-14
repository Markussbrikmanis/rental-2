<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('client.auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(
            [
                'email' => ['required', 'email'],
            ],
            trans('app.validation.messages'),
            trans('app.validation.attributes'),
        );

        $status = Password::sendResetLink(
            $request->only('email'),
        );

        if ($status !== Password::RESET_LINK_SENT) {
            return back()
                ->withErrors([
                    'email' => __('app.client.password_reset.messages.send_failed'),
                ])
                ->onlyInput('email');
        }

        return back()->with('status', __('app.client.password_reset.messages.link_sent'));
    }
}
