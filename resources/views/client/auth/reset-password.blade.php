@extends('client.layout', ['title' => __('app.client.password_reset.reset.page_title')])

@section('content')
    <div class="row justify-content-center min-vh-100 align-items-center py-4">
        <div class="col-md-8 col-lg-5">
            <div class="card border-0 shadow-lg">
                <div class="card-body p-4 p-lg-5">
                    <span class="badge bg-primary text-uppercase mb-3">{{ __('app.client.common.badge') }}</span>
                    <h1 class="h2 mb-3">{{ __('app.client.password_reset.reset.heading') }}</h1>
                    <p class="text-body-secondary mb-4">{{ __('app.client.password_reset.reset.intro') }}</p>

                    <form method="POST" action="{{ route('password.store') }}" class="vstack gap-3">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">

                        <div>
                            <label for="email" class="form-label">{{ __('app.client.common.email') }}</label>
                            <input id="email" name="email" type="email" value="{{ old('email', $email) }}" class="form-control @error('email') is-invalid @enderror" required autofocus>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div>
                            <label for="password" class="form-label">{{ __('app.client.common.password') }}</label>
                            <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="form-label">{{ __('app.client.common.confirm_password') }}</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-primary">{{ __('app.client.password_reset.actions.save_password') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
