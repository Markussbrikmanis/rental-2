@extends('client.layout', ['title' => __('app.client.login.page_title')])

@section('content')
    <div class="row justify-content-center min-vh-100 align-items-center py-4">
        <div class="col-md-8 col-lg-5">
            <div class="card border-0 shadow-lg">
                <div class="card-body p-4 p-lg-5">
                    <span class="badge bg-primary text-uppercase mb-3">{{ __('app.client.common.badge') }}</span>
                    <h1 class="h2 mb-3">{{ __('app.client.login.heading') }}</h1>
                    <p class="text-body-secondary mb-4">
                        {{ __('app.client.login.intro') }}
                    </p>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('client.login.store') }}" class="vstack gap-3">
                        @csrf

                        <div>
                            <label for="email" class="form-label">{{ __('app.client.common.email') }}</label>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                value="{{ old('email') }}"
                                class="form-control @error('email') is-invalid @enderror"
                                required
                                autofocus
                            >
                        </div>

                        <div>
                            <label for="password" class="form-label">{{ __('app.client.common.password') }}</label>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                class="form-control @error('password') is-invalid @enderror"
                                required
                            >
                        </div>

                        <div class="form-check">
                            <input id="remember" name="remember" type="checkbox" class="form-check-input" value="1">
                            <label for="remember" class="form-check-label">{{ __('app.client.common.remember') }}</label>
                        </div>

                        <button type="submit" class="btn btn-primary">{{ __('app.client.common.login') }}</button>
                    </form>

                    <div class="mt-3">
                        <a href="{{ route('password.request') }}" class="link-secondary">{{ __('app.client.password_reset.actions.forgot_password') }}</a>
                    </div>

                    <div class="mt-4 pt-3 border-top">
                        <p class="mb-2 text-body-secondary">{{ __('app.client.messages.need_account') }}</p>
                        <a href="{{ route('client.register') }}" class="btn btn-outline-secondary">{{ __('app.client.login.register_cta') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
