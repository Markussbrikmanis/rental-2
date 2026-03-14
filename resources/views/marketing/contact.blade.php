@extends('marketing.layout')

@section('content')
    <section class="page-hero">
        <div class="section-heading">
            <span>{{ __('marketing.contact_page.eyebrow') }}</span>
            <h1>{{ __('marketing.contact_page.title') }}</h1>
            <p>{{ __('marketing.contact_page.description') }}</p>
        </div>
    </section>

    <section class="split-section split-section--top">
        <div class="split-section__copy">
            <span>{{ __('marketing.contact_page.panel.eyebrow') }}</span>
            <h2>{{ __('marketing.contact_page.panel.title') }}</h2>
            <p>{{ __('marketing.contact_page.panel.description') }}</p>

            <ul>
                @foreach (__('marketing.contact_page.panel.points') as $point)
                    <li>{{ $point }}</li>
                @endforeach
            </ul>
        </div>

        <div class="contact-card-grid">
            @foreach (__('marketing.contact_page.cards') as $card)
                <article class="feature-card">
                    <div class="feature-card__icon" aria-hidden="true">{!! $card['icon'] !!}</div>
                    <p class="feature-card__eyebrow">{{ $card['eyebrow'] }}</p>
                    <h3>{{ $card['title'] }}</h3>
                    <p>{{ $card['description'] }}</p>
                    <a class="contact-card__link" href="{{ $card['href'] }}">{{ $card['label'] }}</a>
                </article>
            @endforeach
        </div>
    </section>
@endsection
