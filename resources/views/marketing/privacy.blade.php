@extends('marketing.layout')

@section('content')
    <section class="page-hero">
        <div class="section-heading">
            <span>{{ __('marketing.privacy_page.eyebrow') }}</span>
            <h1>{{ __('marketing.privacy_page.title') }}</h1>
            <p>{{ __('marketing.privacy_page.description') }}</p>
        </div>
    </section>

    <section class="content-section content-section--compact">
        <div class="privacy-card">
            @foreach (__('marketing.privacy_page.sections') as $section)
                <div class="privacy-card__item privacy-card__item--stacked">
                    <span></span>
                    <div>
                        <strong>{{ $section['title'] }}</strong>
                        <p>{{ $section['description'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endsection
