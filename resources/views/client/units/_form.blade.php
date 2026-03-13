@csrf

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="property_id">{{ __('app.rental.units.fields.property') }}</label>
        <select id="property_id" name="property_id" class="form-select @error('property_id') is-invalid @enderror" required>
            @foreach ($properties as $property)
                <option value="{{ $property->id }}" @selected(old('property_id', request('property_id', $unit->property_id)) == $property->id)>
                    {{ $property->name }}
                </option>
            @endforeach
        </select>
        @error('property_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="name">{{ __('app.rental.units.fields.name') }}</label>
        <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $unit->name) }}" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="code">{{ __('app.rental.units.fields.code') }}</label>
        <input id="code" name="code" type="text" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $unit->code) }}">
        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="status">{{ __('app.rental.units.fields.status') }}</label>
        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected(old('status', $unit->status?->value ?? $unit->status) === $status->value)>
                    {{ $status->label() }}
                </option>
            @endforeach
        </select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="unit_type">{{ __('app.rental.units.fields.unit_type') }}</label>
        <input id="unit_type" name="unit_type" type="text" class="form-control @error('unit_type') is-invalid @enderror" value="{{ old('unit_type', $unit->unit_type) }}">
        @error('unit_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="area">{{ __('app.rental.units.fields.area') }}</label>
        <input id="area" name="area" type="number" step="0.01" min="0" class="form-control @error('area') is-invalid @enderror" value="{{ old('area', $unit->area) }}">
        @error('area')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4 d-flex align-items-end">
        <div class="form-check">
            <input id="is_active" name="is_active" type="checkbox" class="form-check-input" value="1" @checked(old('is_active', $unit->is_active))>
            <label for="is_active" class="form-check-label">{{ __('app.rental.units.fields.is_active') }}</label>
        </div>
    </div>
    <div class="col-12">
        <label class="form-label" for="notes">{{ __('app.rental.units.fields.notes') }}</label>
        <textarea id="notes" name="notes" rows="4" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $unit->notes) }}</textarea>
        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="d-flex flex-wrap gap-2 mt-4">
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    <a href="{{ route('client.units.index') }}" class="btn btn-outline-secondary">{{ __('app.rental.common.cancel') }}</a>
</div>
