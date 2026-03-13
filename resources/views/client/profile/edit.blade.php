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

                <form method="POST" action="{{ route('client.profile.update') }}" class="row g-3" enctype="multipart/form-data">
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

                    @if ($user->isOwner())
                        <div class="col-12">
                            <hr class="my-1">
                        </div>

                        <div class="col-12">
                            <h3 class="h5 mb-2">{{ __('app.client.profile.invoice_number_format.heading') }}</h3>
                            <p class="text-body-secondary mb-0">{{ __('app.client.profile.invoice_number_format.description') }}</p>
                        </div>

                        <div class="col-lg-8">
                            <label for="invoice_number_format" class="form-label">{{ __('app.client.profile.invoice_number_format.label') }}</label>
                            <input
                                id="invoice_number_format"
                                name="invoice_number_format"
                                type="text"
                                value="{{ old('invoice_number_format', $user->invoiceNumberFormat()) }}"
                                class="form-control @error('invoice_number_format') is-invalid @enderror"
                                required
                            >
                            @error('invoice_number_format')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-4">
                            <label class="form-label">{{ __('app.client.profile.invoice_number_format.available_title') }}</label>
                            <div class="form-control h-auto">
                                <div><code>{year}</code></div>
                                <div><code>{num}</code></div>
                                <div><code>{property_unit_code}</code></div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="small text-body-secondary">
                                {{ __('app.client.profile.invoice_number_format.examples') }}
                            </div>
                        </div>

                        <div class="col-12">
                            <hr class="my-1">
                        </div>

                        <div class="col-12">
                            <h3 class="h5 mb-2">{{ __('app.client.profile.invoice_template.heading') }}</h3>
                            <p class="text-body-secondary mb-0">{{ __('app.client.profile.invoice_template.description') }}</p>
                        </div>

                        <div class="col-md-6">
                            <label for="invoice_sender_name" class="form-label">{{ __('app.client.profile.invoice_template.fields.sender_name') }}</label>
                            <input
                                id="invoice_sender_name"
                                name="invoice_sender_name"
                                type="text"
                                value="{{ old('invoice_sender_name', $user->invoice_sender_name ?: $user->name) }}"
                                class="form-control @error('invoice_sender_name') is-invalid @enderror"
                            >
                            @error('invoice_sender_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="invoice_logo" class="form-label">{{ __('app.client.profile.invoice_template.fields.logo') }}</label>
                            <input
                                id="invoice_logo"
                                name="invoice_logo"
                                type="file"
                                accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                                class="form-control @error('invoice_logo') is-invalid @enderror"
                            >
                            @error('invoice_logo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            @if ($user->invoice_logo_path)
                                <div class="form-check mt-2">
                                    <input id="remove_invoice_logo" name="remove_invoice_logo" type="checkbox" value="1" class="form-check-input">
                                    <label class="form-check-label" for="remove_invoice_logo">{{ __('app.client.profile.invoice_template.actions.remove_logo') }}</label>
                                </div>
                            @endif
                        </div>

                        <div class="col-12">
                            <label for="invoice_sender_address" class="form-label">{{ __('app.client.profile.invoice_template.fields.sender_address') }}</label>
                            <textarea
                                id="invoice_sender_address"
                                name="invoice_sender_address"
                                rows="3"
                                class="form-control @error('invoice_sender_address') is-invalid @enderror"
                            >{{ old('invoice_sender_address', $user->invoice_sender_address) }}</textarea>
                            @error('invoice_sender_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="invoice_sender_registration_number" class="form-label">{{ __('app.client.profile.invoice_template.fields.sender_registration_number') }}</label>
                            <input
                                id="invoice_sender_registration_number"
                                name="invoice_sender_registration_number"
                                type="text"
                                value="{{ old('invoice_sender_registration_number', $user->invoice_sender_registration_number) }}"
                                class="form-control @error('invoice_sender_registration_number') is-invalid @enderror"
                            >
                            @error('invoice_sender_registration_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="invoice_sender_vat_number" class="form-label">{{ __('app.client.profile.invoice_template.fields.sender_vat_number') }}</label>
                            <input
                                id="invoice_sender_vat_number"
                                name="invoice_sender_vat_number"
                                type="text"
                                value="{{ old('invoice_sender_vat_number', $user->invoice_sender_vat_number) }}"
                                class="form-control @error('invoice_sender_vat_number') is-invalid @enderror"
                            >
                            @error('invoice_sender_vat_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="invoice_sender_bank_name" class="form-label">{{ __('app.client.profile.invoice_template.fields.sender_bank_name') }}</label>
                            <input
                                id="invoice_sender_bank_name"
                                name="invoice_sender_bank_name"
                                type="text"
                                value="{{ old('invoice_sender_bank_name', $user->invoice_sender_bank_name) }}"
                                class="form-control @error('invoice_sender_bank_name') is-invalid @enderror"
                            >
                            @error('invoice_sender_bank_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="invoice_sender_swift_code" class="form-label">{{ __('app.client.profile.invoice_template.fields.sender_swift_code') }}</label>
                            <input
                                id="invoice_sender_swift_code"
                                name="invoice_sender_swift_code"
                                type="text"
                                value="{{ old('invoice_sender_swift_code', $user->invoice_sender_swift_code) }}"
                                class="form-control @error('invoice_sender_swift_code') is-invalid @enderror"
                            >
                            @error('invoice_sender_swift_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="invoice_sender_account_number" class="form-label">{{ __('app.client.profile.invoice_template.fields.sender_account_number') }}</label>
                            <input
                                id="invoice_sender_account_number"
                                name="invoice_sender_account_number"
                                type="text"
                                value="{{ old('invoice_sender_account_number', $user->invoice_sender_account_number) }}"
                                class="form-control @error('invoice_sender_account_number') is-invalid @enderror"
                            >
                            @error('invoice_sender_account_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="invoice_payment_terms_text" class="form-label">{{ __('app.client.profile.invoice_template.fields.payment_terms') }}</label>
                            <textarea
                                id="invoice_payment_terms_text"
                                name="invoice_payment_terms_text"
                                rows="2"
                                class="form-control @error('invoice_payment_terms_text') is-invalid @enderror"
                            >{{ old('invoice_payment_terms_text', $user->invoice_payment_terms_text) }}</textarea>
                            @error('invoice_payment_terms_text')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="invoice_footer_text" class="form-label">{{ __('app.client.profile.invoice_template.fields.footer_text') }}</label>
                            <textarea
                                id="invoice_footer_text"
                                name="invoice_footer_text"
                                rows="3"
                                class="form-control @error('invoice_footer_text') is-invalid @enderror"
                            >{{ old('invoice_footer_text', $user->invoice_footer_text) }}</textarea>
                            @error('invoice_footer_text')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <div class="form-check mt-md-4 pt-md-2">
                                <input
                                    id="invoice_vat_enabled"
                                    name="invoice_vat_enabled"
                                    type="checkbox"
                                    value="1"
                                    class="form-check-input @error('invoice_vat_enabled') is-invalid @enderror"
                                    @checked(old('invoice_vat_enabled', $user->invoice_vat_enabled))
                                >
                                <label class="form-check-label" for="invoice_vat_enabled">{{ __('app.client.profile.invoice_template.fields.vat_enabled') }}</label>
                                @error('invoice_vat_enabled')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="invoice_vat_rate" class="form-label">{{ __('app.client.profile.invoice_template.fields.vat_rate') }}</label>
                            <input
                                id="invoice_vat_rate"
                                name="invoice_vat_rate"
                                type="number"
                                min="0"
                                max="100"
                                step="0.01"
                                value="{{ old('invoice_vat_rate', $user->invoice_vat_rate ?? 21) }}"
                                class="form-control @error('invoice_vat_rate') is-invalid @enderror"
                            >
                            @error('invoice_vat_rate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

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
