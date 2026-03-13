@csrf

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="full_name">{{ __('app.rental.tenants.fields.full_name') }}</label>
        <input id="full_name" name="full_name" type="text" class="form-control @error('full_name') is-invalid @enderror" value="{{ old('full_name', $tenant->full_name) }}" required>
        @error('full_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="company_name">{{ __('app.rental.tenants.fields.company_name') }}</label>
        <input id="company_name" name="company_name" type="text" class="form-control @error('company_name') is-invalid @enderror" value="{{ old('company_name', $tenant->company_name) }}">
        @error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="email">{{ __('app.client.common.email') }}</label>
        <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $tenant->email) }}">
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="phone">{{ __('app.rental.tenants.fields.phone') }}</label>
        <input id="phone" name="phone" type="text" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $tenant->phone) }}">
        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="personal_code">{{ __('app.rental.tenants.fields.personal_code') }}</label>
        <input id="personal_code" name="personal_code" type="text" class="form-control @error('personal_code') is-invalid @enderror" value="{{ old('personal_code', $tenant->personal_code) }}">
        @error('personal_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="registration_number">{{ __('app.rental.tenants.fields.registration_number') }}</label>
        <input id="registration_number" name="registration_number" type="text" class="form-control @error('registration_number') is-invalid @enderror" value="{{ old('registration_number', $tenant->registration_number) }}">
        @error('registration_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <hr class="my-1">
    </div>
    <div class="col-12">
        <h2 class="h5 mb-1">{{ __('app.rental.tenants.billing.heading') }}</h2>
        <p class="text-body-secondary mb-0">{{ __('app.rental.tenants.billing.description') }}</p>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="billing_name">{{ __('app.rental.tenants.fields.billing_name') }}</label>
        <input id="billing_name" name="billing_name" type="text" class="form-control @error('billing_name') is-invalid @enderror" value="{{ old('billing_name', $tenant->billing_name) }}">
        @error('billing_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label" for="billing_address">{{ __('app.rental.tenants.fields.billing_address') }}</label>
        <textarea id="billing_address" name="billing_address" rows="3" class="form-control @error('billing_address') is-invalid @enderror">{{ old('billing_address', $tenant->billing_address) }}</textarea>
        @error('billing_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="billing_registration_number">{{ __('app.rental.tenants.fields.billing_registration_number') }}</label>
        <input id="billing_registration_number" name="billing_registration_number" type="text" class="form-control @error('billing_registration_number') is-invalid @enderror" value="{{ old('billing_registration_number', $tenant->billing_registration_number) }}">
        @error('billing_registration_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="billing_vat_number">{{ __('app.rental.tenants.fields.billing_vat_number') }}</label>
        <input id="billing_vat_number" name="billing_vat_number" type="text" class="form-control @error('billing_vat_number') is-invalid @enderror" value="{{ old('billing_vat_number', $tenant->billing_vat_number) }}">
        @error('billing_vat_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="billing_bank_name">{{ __('app.rental.tenants.fields.billing_bank_name') }}</label>
        <input id="billing_bank_name" name="billing_bank_name" type="text" class="form-control @error('billing_bank_name') is-invalid @enderror" value="{{ old('billing_bank_name', $tenant->billing_bank_name) }}">
        @error('billing_bank_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="billing_swift_code">{{ __('app.rental.tenants.fields.billing_swift_code') }}</label>
        <input id="billing_swift_code" name="billing_swift_code" type="text" class="form-control @error('billing_swift_code') is-invalid @enderror" value="{{ old('billing_swift_code', $tenant->billing_swift_code) }}">
        @error('billing_swift_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="billing_account_number">{{ __('app.rental.tenants.fields.billing_account_number') }}</label>
        <input id="billing_account_number" name="billing_account_number" type="text" class="form-control @error('billing_account_number') is-invalid @enderror" value="{{ old('billing_account_number', $tenant->billing_account_number) }}">
        @error('billing_account_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label" for="notes">{{ __('app.properties.fields.notes') }}</label>
        <textarea id="notes" name="notes" rows="4" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $tenant->notes) }}</textarea>
        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="d-flex flex-wrap gap-2 mt-4">
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    <a href="{{ route('client.tenants.index') }}" class="btn btn-outline-secondary">{{ __('app.rental.common.cancel') }}</a>
</div>
