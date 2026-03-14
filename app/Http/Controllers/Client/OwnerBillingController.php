<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Laravel\Cashier\SubscriptionBuilder;

class OwnerBillingController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->load('subscriptionPlan');
        $subscription = $user->subscription('default');

        return view('client.billing.index', [
            'user' => $user,
            'subscription' => $subscription,
            'plans' => SubscriptionPlan::query()
                ->where('is_active', true)
                ->where('is_public', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function checkout(Request $request): mixed
    {
        $plan = $this->validatedPlan($request);
        $user = $request->user();

        $user->update([
            'subscription_plan_id' => $plan->id,
        ]);

        if (! $plan->requiresStripeCheckout()) {
            return redirect()
                ->route('client.billing.index')
                ->with('status', __('app.subscription.messages.plan_selected', ['plan' => $plan->name]));
        }

        if ($user->subscribed('default')) {
            return $this->swapToPlan($user, $plan);
        }

        return $this->newSubscriptionBuilder($user, $plan)->checkout([
            'success_url' => route('client.billing.success').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('client.billing.cancel'),
        ]);
    }

    public function swap(Request $request): RedirectResponse
    {
        $plan = $this->validatedPlan($request);
        $user = $request->user();

        if (! $user->subscribed('default')) {
            return redirect()
                ->route('client.billing.index')
                ->with('error', __('app.subscription.messages.no_active_subscription'));
        }

        $user->update([
            'subscription_plan_id' => $plan->id,
        ]);

        return $this->swapToPlan($user, $plan);
    }

    public function portal(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasStripeId()) {
            return redirect()
                ->route('client.billing.index')
                ->with('error', __('app.subscription.messages.portal_unavailable'));
        }

        return $user->redirectToBillingPortal(route('client.billing.index'));
    }

    public function success(): RedirectResponse
    {
        return redirect()
            ->route('client.billing.index')
            ->with('status', __('app.subscription.messages.checkout_success'));
    }

    public function cancel(): RedirectResponse
    {
        return redirect()
            ->route('client.billing.index')
            ->with('error', __('app.subscription.messages.checkout_cancelled'));
    }

    private function validatedPlan(Request $request): SubscriptionPlan
    {
        $validated = $request->validate(
            [
                'subscription_plan_id' => [
                    'required',
                    Rule::exists('subscription_plans', 'id')->where(static function ($query): void {
                        $query->where('is_active', true)->where('is_public', true);
                    }),
                ],
            ],
            trans('app.validation.messages'),
            trans('app.validation.attributes'),
        );

        /** @var SubscriptionPlan $plan */
        $plan = SubscriptionPlan::query()->findOrFail($validated['subscription_plan_id']);

        return $plan;
    }

    private function newSubscriptionBuilder(\App\Models\User $user, SubscriptionPlan $plan): SubscriptionBuilder
    {
        $builder = $user->newSubscription('default', $plan->stripe_price_id);

        if ($user->owner_trial_ends_at !== null && $user->owner_trial_ends_at->isFuture()) {
            return $builder->trialUntil($user->owner_trial_ends_at);
        }

        if ($plan->trial_enabled && $plan->trial_days) {
            return $builder->trialDays($plan->trial_days);
        }

        return $builder;
    }

    private function swapToPlan(\App\Models\User $user, SubscriptionPlan $plan): RedirectResponse
    {
        if (! $plan->requiresStripeCheckout()) {
            $user->subscription('default')?->cancelNow();

            return redirect()
                ->route('client.billing.index')
                ->with('status', __('app.subscription.messages.plan_selected', ['plan' => $plan->name]));
        }

        $subscription = $user->subscription('default');

        if ($subscription === null) {
            return redirect()
                ->route('client.billing.index')
                ->with('error', __('app.subscription.messages.no_active_subscription'));
        }

        if ($subscription->onGracePeriod()) {
            $subscription->resume();
        }

        $subscription->swap($plan->stripe_price_id);

        return redirect()
            ->route('client.billing.index')
            ->with('status', __('app.subscription.messages.plan_changed', ['plan' => $plan->name]));
    }
}
