@extends('client.layout', ['title' => __('app.subscription.owner.index.page_title')])

@section('content')
    <div class="vstack gap-4 py-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div>
                <h1 class="h2 mb-1">{{ __('app.subscription.owner.index.heading') }}</h1>
                <p class="text-body-secondary mb-0">{{ __('app.subscription.owner.index.description') }}</p>
            </div>

            @if ($user->hasStripeId())
                <a href="{{ route('client.billing.portal') }}" class="btn btn-outline-secondary">
                    {{ __('app.subscription.actions.open_portal') }}
                </a>
            @endif
        </div>

        <div class="row g-4">
            <div class="col-12 col-xl-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="text-body-secondary small mb-2">{{ __('app.subscription.owner.current_plan') }}</div>
                        <div class="h3 mb-1">{{ $user->ownerPlan()?->name ?? __('app.subscription.no_plan') }}</div>
                        <div class="text-body-secondary mb-3">{{ $user->ownerPlan()?->display_price }}</div>

                        <dl class="row mb-0">
                            <dt class="col-6 text-body-secondary">{{ __('app.subscription.owner.status') }}</dt>
                            <dd class="col-6">{{ $user->ownerHasUnlimitedPlan() ? __('app.subscription.statuses.unlimited') : ($user->subscribed('default') ? __('app.subscription.statuses.active') : ($user->ownerTrialActive() ? __('app.subscription.statuses.trial') : __('app.subscription.statuses.inactive'))) }}</dd>

                            <dt class="col-6 text-body-secondary">{{ __('app.subscription.fields.property_limit') }}</dt>
                            <dd class="col-6">{{ $user->ownerPlan()?->propertyLimitLabel() ?? '-' }}</dd>

                            <dt class="col-6 text-body-secondary">{{ __('app.subscription.fields.trial_ends_at') }}</dt>
                            <dd class="col-6">{{ $user->owner_trial_ends_at?->format('d.m.Y') ?? '-' }}</dd>
                        </dl>

                        @if ($user->ownerHasBillingAccess())
                            <div class="mt-3">
                                <a href="{{ route('client.panel') }}" class="btn btn-primary">
                                    {{ __('app.subscription.actions.open_panel') }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-8">
                <div class="row g-4">
                    @foreach ($plans as $plan)
                        <div class="col-12 col-lg-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body p-4 d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                        <div>
                                            <h2 class="h4 mb-1">{{ $plan->name }}</h2>
                                            <div class="text-body-secondary">{{ $plan->display_price }}</div>
                                        </div>

                                        @if ($user->subscription_plan_id === $plan->id)
                                            <span class="badge text-bg-primary">{{ __('app.subscription.owner.selected') }}</span>
                                        @endif
                                    </div>

                                    @if ($plan->description)
                                        <p class="text-body-secondary">{{ $plan->description }}</p>
                                    @endif

                                    <div class="small text-body-secondary mb-4">
                                        {{ $plan->propertyLimitLabel() }}
                                        @if ($plan->trial_enabled && $plan->trial_days)
                                            · {{ __('app.subscription.owner.trial_days', ['count' => $plan->trial_days]) }}
                                        @endif
                                    </div>

                                    <div class="mt-auto">
                                        @php($route = $user->subscribed('default') ? 'client.billing.swap' : 'client.billing.checkout')
                                        <form method="POST" action="{{ route($route) }}">
                                            @csrf
                                            <input type="hidden" name="subscription_plan_id" value="{{ $plan->id }}">
                                            <button type="submit" class="btn {{ $user->subscription_plan_id === $plan->id ? 'btn-outline-secondary' : 'btn-primary' }}">
                                                {{ $user->subscribed('default') ? __('app.subscription.actions.change_plan') : __('app.subscription.actions.choose_plan') }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
