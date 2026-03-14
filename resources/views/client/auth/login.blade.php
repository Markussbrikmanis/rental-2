@extends('client.layout', ['title' => __('app.client.login.page_title')])

@section('content')
    <div class="auth-layout auth-layout--single">
        <aside class="auth-panel auth-panel--intro">
            <a class="auth-panel__brand" href="{{ route('marketing.home') }}">
                <span class="auth-panel__brand-mark">N</span>
                <span>{{ config('app.name', 'Noma') }}</span>
            </a>

            <span class="auth-panel__badge">{{ __('app.client.common.badge') }}</span>
            <h1>{{ __('app.client.login.heading') }}</h1>
            <p>{{ __('app.client.login.intro') }}</p>

            <div class="auth-panel__highlights">
                <div>
                    <strong>01</strong>
                    <span>Īpašumi, līgumi un rēķini vienā plūsmā.</span>
                </div>
                <div>
                    <strong>02</strong>
                    <span>Īrnieku portāls ar rēķiniem un skaitītāju rādījumiem.</span>
                </div>
                <div>
                    <strong>03</strong>
                    <span>Publiskie plāni un abonementi sinhronizēti ar administrāciju.</span>
                </div>
            </div>
        </aside>

        <section class="auth-panel auth-panel--form">
            <div class="auth-card">
                <span class="auth-card__eyebrow">{{ __('app.client.common.badge') }}</span>
                <h2>{{ __('app.client.login.heading') }}</h2>
                <p class="auth-card__intro">{{ __('app.client.login.intro') }}</p>

                @if ($errors->any())
                    <div class="auth-alert auth-alert--danger">
                        {{ $errors->first() }}
                    </div>
                @endif

                @if (session('status'))
                    <div class="auth-alert auth-alert--success">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('client.login.store') }}" class="auth-form">
                    @csrf

                    <div class="auth-field">
                        <label for="email" class="auth-label">{{ __('app.client.common.email') }}</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            class="auth-input @error('email') is-invalid @enderror"
                            required
                            autofocus
                        >
                    </div>

                    <div class="auth-field">
                        <label for="password" class="auth-label">{{ __('app.client.common.password') }}</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            class="auth-input @error('password') is-invalid @enderror"
                            required
                        >
                    </div>

                    <label class="auth-check">
                        <input id="remember" name="remember" type="checkbox" value="1">
                        <span>{{ __('app.client.common.remember') }}</span>
                    </label>

                    <button type="submit" class="button button--primary auth-button">{{ __('app.client.common.login') }}</button>
                </form>

                <div class="auth-links">
                    <a href="{{ route('password.request') }}">{{ __('app.client.password_reset.actions.forgot_password') }}</a>
                </div>

                <div class="auth-card__footer">
                    <p>{{ __('app.client.messages.need_account') }}</p>
                    <a href="{{ route('client.register') }}" class="button button--secondary auth-button auth-button--secondary">{{ __('app.client.login.register_cta') }}</a>
                </div>
            </div>
        </section>
    </div>
@endsection
