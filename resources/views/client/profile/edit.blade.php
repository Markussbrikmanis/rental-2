@extends('client.layout', ['title' => __('app.client.profile.page_title')])

@section('content')
    <div class="vstack gap-4 py-4">
        <div>
            <h1 class="h2 mb-1">{{ __('app.client.profile.heading') }}</h1>
            <p class="text-body-secondary mb-0">{{ __('app.client.profile.description') }}</p>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 p-lg-5">
                <h2 class="h4 mb-3">{{ __('app.client.profile.sections.account') }}</h2>

                <form method="POST" action="{{ route('client.profile.update') }}" class="row g-3">
                    @csrf
                    @method('PATCH')

                    <div class="col-md-6">
                        <label for="name" class="form-label">{{ __('app.client.common.full_name') }}</label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name', $user->name) }}"
                            class="form-control @error('name') is-invalid @enderror"
                            required
                        >
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label">{{ __('app.client.common.email') }}</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email', $user->email) }}"
                            class="form-control @error('email') is-invalid @enderror"
                            required
                        >
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">{{ __('app.client.common.account_type') }}</label>
                        <input type="text" value="{{ $user->role->label() }}" class="form-control" disabled>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">{{ __('app.client.profile.actions.save_account') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 p-lg-5">
                <h2 class="h4 mb-3">{{ __('app.client.profile.sections.password') }}</h2>

                <form method="POST" action="{{ route('client.profile.password') }}" class="row g-3">
                    @csrf
                    @method('PUT')

                    <div class="col-md-4">
                        <label for="current_password" class="form-label">{{ __('app.client.profile.fields.current_password') }}</label>
                        <input
                            id="current_password"
                            name="current_password"
                            type="password"
                            class="form-control @error('current_password') is-invalid @enderror"
                            required
                        >
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="password" class="form-label">{{ __('app.client.common.password') }}</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            class="form-control @error('password') is-invalid @enderror"
                            required
                        >
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="password_confirmation" class="form-label">{{ __('app.client.common.confirm_password') }}</label>
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            class="form-control"
                            required
                        >
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">{{ __('app.client.profile.actions.save_password') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm border-danger-subtle">
            <div class="card-body p-4 p-lg-5">
                <h2 class="h4 mb-3 text-danger">{{ __('app.client.profile.sections.delete') }}</h2>
                <p class="text-body-secondary mb-4">{{ __('app.client.profile.delete_description') }}</p>

                <form method="POST" action="{{ route('client.profile.destroy') }}" class="row g-3">
                    @csrf
                    @method('DELETE')

                    <div class="col-md-6">
                        <label for="delete_current_password" class="form-label">{{ __('app.client.profile.fields.current_password') }}</label>
                        <input
                            id="delete_current_password"
                            name="current_password"
                            type="password"
                            class="form-control @error('current_password') is-invalid @enderror"
                            required
                        >
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-danger">{{ __('app.client.profile.actions.delete_account') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
