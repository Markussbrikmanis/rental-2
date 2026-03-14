@extends('client.layout', ['title' => __('app.client.register.page_title')])

@section('content')
    <div class="row justify-content-center min-vh-100 align-items-center py-4">
        <div class="col-md-10 col-lg-6">
            <div class="card border-0 shadow-lg">
                <div class="card-body p-4 p-lg-5">
                    <span class="badge bg-info text-uppercase mb-3">{{ __('app.client.common.badge') }}</span>
                    <h1 class="h2 mb-3">{{ __('app.client.register.heading') }}</h1>
                    <p class="text-body-secondary mb-4">
                        {{ __('app.client.register.intro') }}
                    </p>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('client.register.store') }}" class="row g-3">
                        @csrf

                        <div class="col-12">
                            <label for="name" class="form-label">{{ __('app.client.common.full_name') }}</label>
                            <input
                                id="name"
                                name="name"
                                type="text"
                                value="{{ old('name') }}"
                                class="form-control"
                                required
                            >
                        </div>

                        <div class="col-12">
                            <label for="email" class="form-label">{{ __('app.client.common.email') }}</label>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                value="{{ old('email') }}"
                                class="form-control"
                                required
                            >
                        </div>

                        <div class="col-12">
                            <label for="role" class="form-label">{{ __('app.client.common.account_type') }}</label>
                            <select id="role" name="role" class="form-select" required>
                                @foreach ($roles as $role)
                                    <option value="{{ $role }}" @selected(old('role', 'tenant') === $role)>
                                        {{ __('app.roles.'.$role) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12" data-owner-plan-field @style(old('role', 'tenant') === 'owner' ? '' : 'display: none;')>
                            <label for="subscription_plan_id" class="form-label">{{ __('app.subscription.fields.plan') }}</label>
                            <select id="subscription_plan_id" name="subscription_plan_id" class="form-select">
                                <option value="">{{ __('app.subscription.select_plan') }}</option>
                                @foreach ($plans as $plan)
                                    <option value="{{ $plan->id }}" @selected((string) old('subscription_plan_id') === (string) $plan->id)>
                                        {{ $plan->name }} · {{ $plan->propertyLimitLabel() }} · {{ $plan->display_price }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">
                                {{ __('app.subscription.register_hint') }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="password" class="form-label">{{ __('app.client.common.password') }}</label>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                class="form-control"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label">{{ __('app.client.common.confirm_password') }}</label>
                            <input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                class="form-control"
                                required
                            >
                        </div>

                        <div class="col-12 d-flex flex-wrap gap-3 pt-2">
                            <button type="submit" class="btn btn-primary">{{ __('app.client.register.submit') }}</button>
                            <a href="{{ route('client.login') }}" class="btn btn-outline-secondary">{{ __('app.client.common.back_to_login') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
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
