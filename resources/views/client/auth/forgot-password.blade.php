@extends('client.layout', ['title' => __('app.client.password_reset.request.page_title')])

@section('content')
    <div class="row justify-content-center min-vh-100 align-items-center py-4">
        <div class="col-md-8 col-lg-5">
            <div class="card border-0 shadow-lg">
                <div class="card-body p-4 p-lg-5">
                    <span class="badge bg-primary text-uppercase mb-3">{{ __('app.client.common.badge') }}</span>
                    <h1 class="h2 mb-3">{{ __('app.client.password_reset.request.heading') }}</h1>
                    <p class="text-body-secondary mb-4">{{ __('app.client.password_reset.request.intro') }}</p>

                    <form method="POST" action="{{ route('password.email') }}" class="vstack gap-3">
                        @csrf

                        <div>
                            <label for="email" class="form-label">{{ __('app.client.common.email') }}</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autofocus>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <button type="submit" class="btn btn-primary">{{ __('app.client.password_reset.actions.send_link') }}</button>
                        <a href="{{ route('client.login') }}" class="btn btn-outline-secondary">{{ __('app.client.common.back_to_login') }}</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
