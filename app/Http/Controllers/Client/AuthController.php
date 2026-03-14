<?php

namespace App\Http\Controllers\Client;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\TenantProfile;
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

        $this->syncTenantProfile(Auth::user());

        return redirect()->route('client.panel');
    }

    public function showRegister(): View
    {
        return view('client.auth.register', [
            'roles' => UserRole::selfRegistrationValues(),
            'plans' => SubscriptionPlan::query()
                ->where('is_active', true)
                ->where('is_public', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        $isOwner = $request->input('role') === UserRole::Owner->value;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(UserRole::selfRegistrationValues())],
            'subscription_plan_id' => [
                'nullable',
                Rule::requiredIf($isOwner),
                Rule::exists('subscription_plans', 'id')->where(static function ($query): void {
                    $query->where('is_active', true)->where('is_public', true);
                }),
            ],
            'password' => ['required', 'confirmed', 'min:8'],
        ], trans('app.validation.messages'), trans('app.validation.attributes'));

        if (! $isOwner) {
            unset($validated['subscription_plan_id']);
        }

        if ($isOwner) {
            /** @var SubscriptionPlan|null $plan */
            $plan = SubscriptionPlan::query()->find($validated['subscription_plan_id']);

            if ($plan?->trial_enabled && $plan->trial_days) {
                $validated['owner_trial_ends_at'] = now()->addDays($plan->trial_days);
            }
        }

        $user = User::create($validated);
        $this->syncTenantProfile($user);

        Auth::login($user);
        $request->session()->regenerate();

        if ($user->isOwner()) {
            return redirect()
                ->route('client.billing.index')
                ->with('status', __('app.subscription.messages.registration_completed'));
        }

        return redirect()->route('client.panel');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('client.login');
    }

    private function syncTenantProfile(?User $user): void
    {
        if ($user === null || ! $user->isTenant()) {
            return;
        }

        if ($user->tenantProfile()->exists()) {
            return;
        }

        TenantProfile::query()
            ->whereNull('user_id')
            ->where('email', $user->email)
            ->oldest('id')
            ->first()
            ?->update([
                'user_id' => $user->id,
            ]);
    }
}
