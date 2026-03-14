@extends('marketing.layout')

@section('content')
    <section class="hero-section">
        <div class="hero-copy">
            <div class="hero-copy__eyebrow">{{ __('marketing.home.hero.eyebrow') }}</div>
            <h1>{{ __('marketing.home.hero.title') }}</h1>
            <p>{{ __('marketing.home.hero.description') }}</p>

            <div class="hero-actions">
                <a class="button button--primary" href="{{ route('marketing.pricing') }}">
                    {{ __('marketing.home.hero.primary_cta') }}
                </a>
                <a class="button button--secondary" href="{{ route('login') }}">
                    {{ __('marketing.home.hero.secondary_cta') }}
                </a>
            </div>

            <div class="hero-meta">
                @foreach (__('marketing.home.hero.highlights') as $highlight)
                    <span>{{ $highlight }}</span>
                @endforeach
            </div>
        </div>

        <div class="hero-panel" aria-label="{{ __('marketing.home.hero.panel_aria') }}">
            <div class="hero-panel__window">
                <div class="hero-panel__topbar">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>

                <div class="hero-panel__body">
                    <div class="hero-panel__summary">
                        <div>
                            <p class="hero-panel__label">{{ __('marketing.home.dashboard.summary_label') }}</p>
                            <strong>{{ __('marketing.home.dashboard.summary_value') }}</strong>
                        </div>
                        <span class="hero-panel__status">{{ __('marketing.home.dashboard.summary_status') }}</span>
                    </div>

                    <div class="hero-panel__grid">
                        @foreach (__('marketing.home.dashboard.metrics') as $metric)
                            <article>
                                <p>{{ $metric['label'] }}</p>
                                <strong>{{ $metric['value'] }}</strong>
                            </article>
                        @endforeach
                    </div>

                    <div class="hero-panel__list">
                        @foreach (__('marketing.home.dashboard.flows') as $flow)
                            <div>
                                <span>{{ $flow['label'] }}</span>
                                <strong>{{ $flow['value'] }}</strong>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="proof-strip" aria-label="{{ __('marketing.home.proof_aria') }}">
        @foreach (__('marketing.home.proof_items') as $item)
            <div>
                <strong>{{ $item['title'] }}</strong>
                <span>{{ $item['description'] }}</span>
            </div>
        @endforeach
    </section>

    <section class="content-section">
        <div class="section-heading">
            <span>{{ __('marketing.home.product.eyebrow') }}</span>
            <h2>{{ __('marketing.home.product.title') }}</h2>
            <p>{{ __('marketing.home.product.description') }}</p>
        </div>

        <div class="feature-grid">
            @foreach (__('marketing.home.product.cards') as $card)
                <article class="feature-card">
                    <div class="feature-card__icon" aria-hidden="true">{!! $card['icon'] !!}</div>
                    <p class="feature-card__eyebrow">{{ $card['eyebrow'] }}</p>
                    <h3>{{ $card['title'] }}</h3>
                    <p>{{ $card['description'] }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="split-section">
        <div class="split-section__copy">
            <span>{{ __('marketing.home.sell.eyebrow') }}</span>
            <h2>{{ __('marketing.home.sell.title') }}</h2>
            <p>{{ __('marketing.home.sell.description') }}</p>

            <ul>
                @foreach (__('marketing.home.sell.bullets') as $bullet)
                    <li>{{ $bullet }}</li>
                @endforeach
            </ul>
        </div>

        <div class="split-section__panel">
            <div class="mini-dashboard">
                @foreach (__('marketing.home.sell.stats') as $stat)
                    <div class="mini-dashboard__card">
                        <span>{{ $stat['label'] }}</span>
                        <strong>{{ $stat['value'] }}</strong>
                        <small>{{ $stat['note'] }}</small>
                    </div>
                @endforeach

                <div class="mini-dashboard__timeline">
                    @foreach (__('marketing.home.sell.timeline') as $item)
                        <div>
                            <span>{{ $item['date'] }}</span>
                            <strong>{{ $item['title'] }}</strong>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="content-section content-section--compact">
        <div class="section-heading">
            <span>{{ __('marketing.home.pricing_preview.eyebrow') }}</span>
            <h2>{{ __('marketing.home.pricing_preview.title') }}</h2>
            <p>{{ __('marketing.home.pricing_preview.description') }}</p>
        </div>

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
                        <small>{{ $plan->propertyLimitLabel() }}</small>
                    </div>
                    <p>{{ $plan->description }}</p>
                    @if ($plan->trial_enabled && $plan->trial_days)
                        <div class="pricing-card__meta">{{ __('marketing.common.trial_days', ['count' => $plan->trial_days]) }}</div>
                    @endif
                    <a class="button button--secondary" href="{{ route('marketing.pricing') }}">
                        {{ __('marketing.home.pricing_preview.cta') }}
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

    <section class="contact-band">
        <div>
            <span>{{ __('marketing.home.cta.eyebrow') }}</span>
            <h2>{{ __('marketing.home.cta.title') }}</h2>
            <p>{{ __('marketing.home.cta.description') }}</p>
        </div>

        <div class="contact-band__panel">
            <a href="{{ route('marketing.contact') }}">{{ __('marketing.home.cta.contact_link') }}</a>
            <a href="{{ route('login') }}">{{ __('marketing.home.cta.login_link') }}</a>
            <p>{{ __('marketing.home.cta.note') }}</p>
        </div>
    </section>
@endsection
