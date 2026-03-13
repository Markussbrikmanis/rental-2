@csrf

<div class="row g-3">
    <div class="col-md-6">
        <label for="name" class="form-label">{{ __('app.properties.fields.name') }}</label>
        <input
            id="name"
            name="name"
            type="text"
            value="{{ old('name', $property->name) }}"
            class="form-control @error('name') is-invalid @enderror"
            required
        >
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="type" class="form-label">{{ __('app.properties.fields.type') }}</label>
        <select id="type" name="type" class="form-select @error('type') is-invalid @enderror" required>
            @foreach ($types as $type)
                <option
                    value="{{ $type->value }}"
                    @selected(old('type', $property->type?->value ?? $property->type) === $type->value)
                >
                    {{ $type->label() }}
                </option>
            @endforeach
        </select>
        @error('type')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label for="notes" class="form-label">{{ __('app.properties.fields.notes') }}</label>
        <textarea
            id="notes"
            name="notes"
            rows="4"
            class="form-control @error('notes') is-invalid @enderror"
        >{{ old('notes', $property->notes) }}</textarea>
        @error('notes')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="address" class="form-label">{{ __('app.properties.fields.address') }}</label>
        <input
            id="address"
            name="address"
            type="text"
            value="{{ old('address', $property->address) }}"
            class="form-control @error('address') is-invalid @enderror"
            required
        >
        @error('address')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label for="city" class="form-label">{{ __('app.properties.fields.city') }}</label>
        <input
            id="city"
            name="city"
            type="text"
            value="{{ old('city', $property->city) }}"
            class="form-control @error('city') is-invalid @enderror"
            required
        >
        @error('city')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label for="country" class="form-label">{{ __('app.properties.fields.country') }}</label>
        <select
            id="country"
            name="country"
            class="form-select @error('country') is-invalid @enderror"
            required
        >
            @foreach ($countries as $country)
                <option
                    value="{{ $country }}"
                    @selected(old('country', $property->country ?: __('app.properties.defaults.country')) === $country)
                >
                    {{ $country }}
                </option>
            @endforeach
        </select>
        @error('country')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="price" class="form-label">{{ __('app.properties.fields.price') }}</label>
        <input
            id="price"
            name="price"
            type="number"
            step="0.01"
            min="0"
            value="{{ old('price', $property->price) }}"
            class="form-control @error('price') is-invalid @enderror"
            required
        >
        @error('price')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="acquired_at" class="form-label">{{ __('app.properties.fields.acquired_at') }}</label>
        <input
            id="acquired_at"
            name="acquired_at"
            type="date"
            value="{{ old('acquired_at', $property->acquired_at?->format('Y-m-d') ?? $property->acquired_at) }}"
            class="form-control @error('acquired_at') is-invalid @enderror"
            required
        >
        @error('acquired_at')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="d-flex flex-wrap gap-2 mt-4">
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    <a href="{{ route('client.properties.index') }}" class="btn btn-outline-secondary">
        {{ __('app.properties.actions.cancel') }}
    </a>
</div>
