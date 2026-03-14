@extends('marketing.layout')

@section('content')
    <section class="page-hero">
        <div class="section-heading">
            <span>{{ __('marketing.pricing_page.eyebrow') }}</span>
            <h1>{{ __('marketing.pricing_page.title') }}</h1>
            <p>{{ __('marketing.pricing_page.description') }}</p>
        </div>
    </section>

    <section class="content-section content-section--compact">
        <div class="pricing-grid">
            @forelse ($plans as $plan)
                <article class="pricing-card{{ $loop->first ? ' is-featured' : '' }}">
                    <div class="pricing-card__header">
                        <h3>{{ $plan->name }}</h3>
                        @if ($loop->first)
                            <span>{{ __('marketing.common.most_selected') }}</span>
                        @endif
                    </div>
                    <div class="pricing-card__price">
                        <strong>{{ $plan->display_price }}</strong>
                        <small>{{ __('marketing.common.interval.'.strtolower($plan->billing_interval)) }}</small>
                    </div>
                    <p>{{ $plan->description }}</p>

                    <ul>
                        <li>{{ $plan->propertyLimitLabel() }}</li>
                        <li>{{ __('marketing.pricing_page.currency', ['currency' => $plan->currency]) }}</li>
                        <li>
                            {{ $plan->trial_enabled && $plan->trial_days
                                ? __('marketing.common.trial_days', ['count' => $plan->trial_days])
                                : __('marketing.pricing_page.no_trial') }}
                        </li>
                    </ul>

                    <a class="button {{ $loop->first ? 'button--primary' : 'button--secondary' }}" href="{{ route('login') }}">
                        {{ __('marketing.pricing_page.cta') }}
                    </a>
                </article>
            @empty
                <article class="pricing-card">
                    <div class="pricing-card__header">
                        <h3>{{ __('marketing.common.plan_placeholder.title') }}</h3>
                    </div>
                    <p>{{ __('marketing.common.plan_placeholder.description') }}</p>
                </article>
            @endforelse
        </div>
    </section>

    <section class="content-section content-section--compact">
        <div class="feature-grid">
            @foreach (__('marketing.pricing_page.reasons') as $reason)
                <article class="feature-card">
                    <div class="feature-card__icon" aria-hidden="true">{!! $reason['icon'] !!}</div>
                    <p class="feature-card__eyebrow">{{ $reason['eyebrow'] }}</p>
                    <h3>{{ $reason['title'] }}</h3>
                    <p>{{ $reason['description'] }}</p>
                </article>
            @endforeach
        </div>
    </section>
@endsection
