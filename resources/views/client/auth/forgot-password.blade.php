@extends('client.layout', ['title' => __('app.client.password_reset.request.page_title')])

@section('content')
    <div class="auth-layout auth-layout--single">
        <aside class="auth-panel auth-panel--intro">
            <a class="auth-panel__brand" href="{{ route('marketing.home') }}">
                <span class="auth-panel__brand-mark">N</span>
                <span>{{ config('app.name', 'Noma') }}</span>
            </a>

            <span class="auth-panel__badge">{{ __('app.client.common.badge') }}</span>
            <h1>{{ __('app.client.password_reset.request.heading') }}</h1>
            <p>{{ __('app.client.password_reset.request.intro') }}</p>
        </aside>

        <section class="auth-panel auth-panel--form">
            <div class="auth-card">
                <span class="auth-card__eyebrow">{{ __('app.client.common.badge') }}</span>
                <h2>{{ __('app.client.password_reset.request.heading') }}</h2>
                <p class="auth-card__intro">{{ __('app.client.password_reset.request.intro') }}</p>

                @if (session('status'))
                    <div class="auth-alert auth-alert--success">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" class="auth-form">
                    @csrf

                    <div class="auth-field">
                        <label for="email" class="auth-label">{{ __('app.client.common.email') }}</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" class="auth-input @error('email') is-invalid @enderror" required autofocus>
                        @error('email')<div class="auth-error">{{ $message }}</div>@enderror
                    </div>

                    <button type="submit" class="button button--primary auth-button">{{ __('app.client.password_reset.actions.send_link') }}</button>
                    <a href="{{ route('client.login') }}" class="button button--secondary auth-button auth-button--secondary">{{ __('app.client.common.back_to_login') }}</a>
                </form>
            </div>
        </section>
    </div>
@endsection
