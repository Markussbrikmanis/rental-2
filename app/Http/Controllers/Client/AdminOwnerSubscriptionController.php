<?php

namespace App\Http\Controllers\Client;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminOwnerSubscriptionController extends Controller
{
    public function index(): View
    {
        return view('client.admin.owner-subscriptions.index', [
            'owners' => User::query()
                ->where('role', UserRole::Owner)
                ->with(['subscriptionPlan', 'subscriptions'])
                ->withCount('properties')
                ->orderBy('name')
                ->get(),
            'plans' => SubscriptionPlan::query()
                ->withCount('users')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'newPlan' => new SubscriptionPlan([
                'currency' => 'EUR',
                'billing_interval' => 'month',
                'display_price' => '19,00 EUR / mēnesī',
                'is_active' => true,
                'is_public' => true,
                'sort_order' => 0,
            ]),
        ]);
    }

    public function update(Request $request, User $owner): RedirectResponse
    {
        abort_unless($owner->role === UserRole::Owner, 404);

        $validated = $request->validate(
            [
                'subscription_plan_id' => ['required', Rule::exists('subscription_plans', 'id')],
                'owner_trial_ends_at' => ['nullable', 'date'],
            ],
            trans('app.validation.messages'),
            trans('app.validation.attributes'),
        );

        /** @var SubscriptionPlan $plan */
        $plan = SubscriptionPlan::query()->findOrFail($validated['subscription_plan_id']);

        $owner->update($validated);

        if ($plan->requiresStripeCheckout() && $owner->subscribed('default')) {
            $subscription = $owner->subscription('default');

            if ($subscription?->onGracePeriod()) {
                $subscription->resume();
            }

            $subscription?->swap($plan->stripe_price_id);
        }

        if (! $plan->requiresStripeCheckout()) {
            $owner->subscription('default')?->cancelNow();
        }

        return redirect()
            ->route('client.admin.owner-subscriptions.index')
            ->with('status', __('app.subscription.messages.owner_subscription_updated', [
                'name' => $owner->name,
            ]));
    }

    public function storePlan(Request $request): RedirectResponse
    {
        $plan = SubscriptionPlan::query()->create($this->validatedPlanData($request));

        return redirect()
            ->route('client.admin.owner-subscriptions.index')
            ->with('status', __('app.subscription.messages.plan_created', ['name' => $plan->name]));
    }

    public function updatePlan(Request $request, SubscriptionPlan $plan): RedirectResponse
    {
        $plan->update($this->validatedPlanData($request, $plan));

        return redirect()
            ->route('client.admin.owner-subscriptions.index')
            ->with('status', __('app.subscription.messages.plan_updated', ['name' => $plan->name]));
    }

    public function destroyPlan(SubscriptionPlan $plan): RedirectResponse
    {
        if ($plan->users()->exists()) {
            return redirect()
                ->route('client.admin.owner-subscriptions.index')
                ->with('error', __('app.subscription.messages.plan_delete_blocked', ['name' => $plan->name]));
        }

        $planName = $plan->name;
        $plan->delete();

        return redirect()
            ->route('client.admin.owner-subscriptions.index')
            ->with('status', __('app.subscription.messages.plan_deleted', ['name' => $planName]));
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedPlanData(Request $request, ?SubscriptionPlan $plan = null): array
    {
        $validated = $request->validate(
            [
                'name' => ['required', 'string', 'max:255'],
                'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('subscription_plans', 'slug')->ignore($plan?->id)],
                'description' => ['nullable', 'string'],
                'stripe_price_id' => ['nullable', 'string', 'max:255'],
                'display_price' => ['required', 'string', 'max:255'],
                'currency' => ['required', 'string', 'size:3'],
                'billing_interval' => ['required', Rule::in(['day', 'week', 'month', 'year'])],
                'property_limit' => ['nullable', 'integer', 'min:1'],
                'trial_enabled' => ['nullable', 'boolean'],
                'trial_days' => ['nullable', 'integer', 'min:1', 'max:365'],
                'is_active' => ['nullable', 'boolean'],
                'is_public' => ['nullable', 'boolean'],
                'is_unlimited' => ['nullable', 'boolean'],
                'sort_order' => ['nullable', 'integer', 'min:0'],
            ],
            trans('app.validation.messages'),
            trans('app.validation.attributes'),
        );

        $validated['trial_enabled'] = $request->boolean('trial_enabled');
        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_public'] = $request->boolean('is_public');
        $validated['is_unlimited'] = $request->boolean('is_unlimited');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['currency'] = mb_strtoupper($validated['currency']);

        if ($validated['is_unlimited']) {
            $validated['property_limit'] = null;
            $validated['stripe_price_id'] = null;
            $validated['trial_enabled'] = false;
            $validated['trial_days'] = null;
        }

        if (! $validated['is_unlimited'] && empty($validated['property_limit'])) {
            throw ValidationException::withMessages([
                'property_limit' => __('app.subscription.messages.property_limit_required'),
            ]);
        }

        if ($validated['trial_enabled'] && empty($validated['trial_days'])) {
            throw ValidationException::withMessages([
                'trial_days' => __('app.subscription.messages.trial_days_required'),
            ]);
        }

        if ($validated['is_active'] && ! $validated['is_unlimited'] && blank($validated['stripe_price_id'])) {
            throw ValidationException::withMessages([
                'stripe_price_id' => __('app.subscription.messages.stripe_price_required'),
            ]);
        }

        if (! $validated['trial_enabled']) {
            $validated['trial_days'] = null;
        }

        return $validated;
    }
}
