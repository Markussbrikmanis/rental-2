@csrf

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="property_unit_id">{{ __('app.rental.leases.fields.unit') }}</label>
        <select id="property_unit_id" name="property_unit_id" class="form-select @error('property_unit_id') is-invalid @enderror" required>
            @foreach ($units as $unit)
                <option value="{{ $unit->id }}" @selected(old('property_unit_id', $lease->property_unit_id) == $unit->id)>
                    {{ $unit->property->name }} - {{ $unit->name }}
                </option>
            @endforeach
        </select>
        @error('property_unit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="tenant_profile_id">{{ __('app.rental.leases.fields.tenant') }}</label>
        <select id="tenant_profile_id" name="tenant_profile_id" class="form-select @error('tenant_profile_id') is-invalid @enderror" required>
            @foreach ($tenants as $tenant)
                <option value="{{ $tenant->id }}" @selected(old('tenant_profile_id', $lease->tenant_profile_id) == $tenant->id)>
                    {{ $tenant->full_name }}
                </option>
            @endforeach
        </select>
        @error('tenant_profile_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="start_date">{{ __('app.rental.leases.fields.start_date') }}</label>
        <input id="start_date" name="start_date" type="date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', $lease->start_date?->format('Y-m-d') ?? $lease->start_date) }}" required>
        @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="end_date">{{ __('app.rental.leases.fields.end_date') }}</label>
        <input id="end_date" name="end_date" type="date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date', $lease->end_date?->format('Y-m-d') ?? $lease->end_date) }}">
        @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="billing_start_date">{{ __('app.rental.leases.fields.billing_start_date') }}</label>
        <input id="billing_start_date" name="billing_start_date" type="date" class="form-control @error('billing_start_date') is-invalid @enderror" value="{{ old('billing_start_date', $lease->billing_start_date?->format('Y-m-d') ?? $lease->billing_start_date) }}" required>
        @error('billing_start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label" for="due_day">{{ __('app.rental.leases.fields.due_day') }}</label>
        <input id="due_day" name="due_day" type="number" min="1" max="28" class="form-control @error('due_day') is-invalid @enderror" value="{{ old('due_day', $lease->due_day) }}" required>
        @error('due_day')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label" for="currency">{{ __('app.rental.leases.fields.currency') }}</label>
        <input id="currency" name="currency" type="text" class="form-control @error('currency') is-invalid @enderror" value="{{ old('currency', $lease->currency) }}" required>
        @error('currency')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label" for="status">{{ __('app.rental.leases.fields.status') }}</label>
        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected(old('status', $lease->status?->value ?? $lease->status) === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label" for="deposit">{{ __('app.rental.leases.fields.deposit') }}</label>
        <input id="deposit" name="deposit" type="number" min="0" step="0.01" class="form-control @error('deposit') is-invalid @enderror" value="{{ old('deposit', $lease->deposit) }}">
        @error('deposit')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label" for="notes">{{ __('app.properties.fields.notes') }}</label>
        <textarea id="notes" name="notes" rows="4" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $lease->notes) }}</textarea>
        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="d-flex flex-wrap gap-2 mt-4">
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    <a href="{{ route('client.leases.index') }}" class="btn btn-outline-secondary">{{ __('app.rental.common.cancel') }}</a>
</div>
