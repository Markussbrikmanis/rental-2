@extends('client.layout', ['title' => __('app.client.register.page_title')])

@section('content')
    <div class="auth-layout">
        <aside class="auth-panel auth-panel--intro">
            <a class="auth-panel__brand" href="{{ route('marketing.home') }}">
                <span class="auth-panel__brand-mark">N</span>
                <span>{{ config('app.name', 'Noma') }}</span>
            </a>

            <span class="auth-panel__badge">{{ __('app.client.common.badge') }}</span>
            <h1>{{ __('app.client.register.heading') }}</h1>
            <p>{{ __('app.client.register.intro') }}</p>

            <div class="auth-panel__highlights">
                <div>
                    <strong>Īpašnieks</strong>
                    <span>Izvēlieties publisko plānu un turpiniet uz abonementa aktivizēšanu.</span>
                </div>
                <div>
                    <strong>Īrnieks</strong>
                    <span>Piekļūstiet saviem līgumiem, rēķiniem un skaitītāju rādījumiem vienuviet.</span>
                </div>
            </div>
        </aside>

        <section class="auth-panel auth-panel--form">
            <div class="auth-card auth-card--wide">
                <span class="auth-card__eyebrow">{{ __('app.client.common.badge') }}</span>
                <h2>{{ __('app.client.register.heading') }}</h2>
                <p class="auth-card__intro">{{ __('app.client.register.intro') }}</p>

                @if ($errors->any())
                    <div class="auth-alert auth-alert--danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('client.register.store') }}" class="auth-form auth-form--grid">
                    @csrf

                    <div class="auth-field auth-field--full">
                        <label for="name" class="auth-label">{{ __('app.client.common.full_name') }}</label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name') }}"
                            class="auth-input"
                            required
                        >
                    </div>

                    <div class="auth-field auth-field--full">
                        <label for="email" class="auth-label">{{ __('app.client.common.email') }}</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            class="auth-input"
                            required
                        >
                    </div>

                    <div class="auth-field auth-field--full">
                        <label for="role" class="auth-label">{{ __('app.client.common.account_type') }}</label>
                        <select id="role" name="role" class="auth-select" required>
                            @foreach ($roles as $role)
                                <option value="{{ $role }}" @selected(old('role', 'tenant') === $role)>
                                    {{ __('app.roles.'.$role) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="auth-field auth-field--full" data-owner-plan-field @style(old('role', 'tenant') === 'owner' ? '' : 'display: none;')>
                        <label for="subscription_plan_id" class="auth-label">{{ __('app.subscription.fields.plan') }}</label>
                        <select id="subscription_plan_id" name="subscription_plan_id" class="auth-select">
                            <option value="">{{ __('app.subscription.select_plan') }}</option>
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->id }}" @selected((string) old('subscription_plan_id') === (string) $plan->id)>
                                    {{ $plan->name }} · {{ $plan->propertyLimitLabel() }} · {{ $plan->display_price }}
                                </option>
                            @endforeach
                        </select>
                        <div class="auth-help">
                            {{ __('app.subscription.register_hint') }}
                        </div>
                    </div>

                    <div class="auth-field">
                        <label for="password" class="auth-label">{{ __('app.client.common.password') }}</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            class="auth-input"
                            required
                        >
                    </div>

                    <div class="auth-field">
                        <label for="password_confirmation" class="auth-label">{{ __('app.client.common.confirm_password') }}</label>
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            class="auth-input"
                            required
                        >
                    </div>

                    <div class="auth-actions auth-field--full">
                        <button type="submit" class="button button--primary auth-button">{{ __('app.client.register.submit') }}</button>
                        <a href="{{ route('client.login') }}" class="button button--secondary auth-button auth-button--secondary">{{ __('app.client.common.back_to_login') }}</a>
                    </div>
                </form>
            </div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const roleField = document.getElementById('role');
            const planField = document.querySelector('[data-owner-plan-field]');
            const planSelect = document.getElementById('subscription_plan_id');

            if (!roleField || !planField || !planSelect) {
                return;
            }

            const syncPlanVisibility = () => {
                const isOwner = roleField.value === 'owner';
                planField.style.display = isOwner ? '' : 'none';

                if (!isOwner) {
                    planSelect.value = '';
                }
            };

            roleField.addEventListener('change', syncPlanVisibility);
            syncPlanVisibility();
        });
    </script>
@endsection
