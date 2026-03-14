@extends('client.layout', ['title' => __('app.client.password_reset.reset.page_title')])

@section('content')
    <div class="auth-layout auth-layout--single">
        <aside class="auth-panel auth-panel--intro">
            <a class="auth-panel__brand" href="{{ route('marketing.home') }}">
                <span class="auth-panel__brand-mark">N</span>
                <span>{{ config('app.name', 'Noma') }}</span>
            </a>

            <span class="auth-panel__badge">{{ __('app.client.common.badge') }}</span>
            <h1>{{ __('app.client.password_reset.reset.heading') }}</h1>
            <p>{{ __('app.client.password_reset.reset.intro') }}</p>
        </aside>

        <section class="auth-panel auth-panel--form">
            <div class="auth-card">
                <span class="auth-card__eyebrow">{{ __('app.client.common.badge') }}</span>
                <h2>{{ __('app.client.password_reset.reset.heading') }}</h2>
                <p class="auth-card__intro">{{ __('app.client.password_reset.reset.intro') }}</p>

                <form method="POST" action="{{ route('password.store') }}" class="auth-form">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="auth-field">
                        <label for="email" class="auth-label">{{ __('app.client.common.email') }}</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $email) }}" class="auth-input @error('email') is-invalid @enderror" required autofocus>
                        @error('email')<div class="auth-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="auth-field">
                        <label for="password" class="auth-label">{{ __('app.client.common.password') }}</label>
                        <input id="password" name="password" type="password" class="auth-input @error('password') is-invalid @enderror" required>
                        @error('password')<div class="auth-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="auth-field">
                        <label for="password_confirmation" class="auth-label">{{ __('app.client.common.confirm_password') }}</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" class="auth-input" required>
                    </div>

                    <button type="submit" class="button button--primary auth-button">{{ __('app.client.password_reset.actions.save_password') }}</button>
                </form>
            </div>
        </section>
    </div>
@endsection
