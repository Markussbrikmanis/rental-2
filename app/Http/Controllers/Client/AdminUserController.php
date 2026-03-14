<?php

namespace App\Http\Controllers\Client;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(): View
    {
        return view('client.admin.users.index', [
            'users' => User::query()
                ->with('subscriptionPlan')
                ->orderBy('name')
                ->orderBy('email')
                ->get(),
        ]);
    }

    public function edit(User $user): View
    {
        return view('client.admin.users.edit', [
            'editedUser' => $user->load('subscriptionPlan'),
            'plans' => SubscriptionPlan::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'roles' => UserRole::cases(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate(
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
                'role' => ['required', Rule::in(UserRole::values())],
                'subscription_plan_id' => [
                    'nullable',
                    Rule::requiredIf($request->input('role') === UserRole::Owner->value),
                    Rule::exists('subscription_plans', 'id'),
                ],
                'owner_trial_ends_at' => ['nullable', 'date'],
            ],
            trans('app.validation.messages'),
            trans('app.validation.attributes'),
        );

        $newRole = UserRole::from($validated['role']);

        if ($newRole !== UserRole::Owner) {
            $validated['subscription_plan_id'] = null;
            $validated['owner_trial_ends_at'] = null;
            $user->subscription('default')?->cancelNow();
        }

        $user->update($validated);

        return redirect()
            ->route('client.admin.users.index')
            ->with('status', __('app.admin.users.messages.updated', ['name' => $user->name]));
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            return redirect()
                ->route('client.admin.users.index')
                ->with('error', __('app.admin.users.messages.delete_self_blocked'));
        }

        $userName = $user->name;
        $user->subscription('default')?->cancelNow();
        $user->delete();

        return redirect()
            ->route('client.admin.users.index')
            ->with('status', __('app.admin.users.messages.deleted', ['name' => $userName]));
    }

    public function sendPasswordReset(User $user): RedirectResponse
    {
        $status = Password::sendResetLink([
            'email' => $user->email,
        ]);

        if ($status !== Password::RESET_LINK_SENT) {
            return redirect()
                ->route('client.admin.users.index')
                ->with('error', __('app.admin.users.messages.password_reset_failed', ['name' => $user->name]));
        }

        return redirect()
            ->route('client.admin.users.index')
            ->with('status', __('app.admin.users.messages.password_reset_sent', ['name' => $user->name]));
    }
}
