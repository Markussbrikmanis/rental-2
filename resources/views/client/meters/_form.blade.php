@csrf

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="property_unit_id">{{ __('app.rental.meters.fields.unit') }}</label>
        <select id="property_unit_id" name="property_unit_id" class="form-select @error('property_unit_id') is-invalid @enderror" required>
            @foreach ($units as $unit)
                <option value="{{ $unit->id }}" @selected(old('property_unit_id', $meter->property_unit_id) == $unit->id)>
                    {{ $unit->property->name }} - {{ $unit->name }}
                </option>
            @endforeach
        </select>
        @error('property_unit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="name">{{ __('app.rental.meters.fields.name') }}</label>
        <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $meter->name) }}" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="type">{{ __('app.rental.meters.fields.type') }}</label>
        <select id="type" name="type" class="form-select @error('type') is-invalid @enderror" required>
            @foreach ($types as $type)
                <option value="{{ $type->value }}" @selected(old('type', $meter->type?->value ?? $meter->type) === $type->value)>{{ $type->label() }}</option>
            @endforeach
        </select>
        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="unit">{{ __('app.rental.meters.fields.measurement_unit') }}</label>
        <input id="unit" name="unit" type="text" class="form-control @error('unit') is-invalid @enderror" value="{{ old('unit', $meter->unit) }}" required>
        @error('unit')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="utility_billing_mode">{{ __('app.rental.meters.fields.utility_billing_mode') }}</label>
        <select id="utility_billing_mode" name="utility_billing_mode" class="form-select @error('utility_billing_mode') is-invalid @enderror" required>
            @foreach ($utilityBillingModes as $mode)
                <option value="{{ $mode->value }}" @selected(old('utility_billing_mode', $meter->utility_billing_mode?->value ?? $meter->utility_billing_mode) === $mode->value)>{{ $mode->label() }}</option>
            @endforeach
        </select>
        @error('utility_billing_mode')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="rate_per_unit">{{ __('app.rental.meters.fields.rate_per_unit') }}</label>
        <input id="rate_per_unit" name="rate_per_unit" type="number" min="0" step="0.0001" class="form-control @error('rate_per_unit') is-invalid @enderror" value="{{ old('rate_per_unit', $meter->rate_per_unit) }}">
        @error('rate_per_unit')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4 d-flex align-items-end">
        <div class="form-check">
            <input id="meter-is-active" name="is_active" type="checkbox" class="form-check-input" value="1" @checked(old('is_active', $meter->is_active))>
            <label for="meter-is-active" class="form-check-label">{{ __('app.rental.meters.fields.is_active') }}</label>
        </div>
    </div>
</div>

<div class="d-flex flex-wrap gap-2 mt-4">
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    <a href="{{ route('client.meters.index') }}" class="btn btn-outline-secondary">{{ __('app.rental.common.cancel') }}</a>
</div>
