@extends('client.layout', ['title' => __('app.rental.exports.index.page_title')])

@section('content')
    <div class="vstack gap-4 py-4">
        <div>
            <h1 class="h2 mb-1">{{ __('app.rental.exports.index.heading') }}</h1>
            <p class="text-body-secondary mb-0">{{ __('app.rental.exports.index.description') }}</p>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 p-lg-5">
                <form method="POST" action="{{ route('client.exports.download') }}" class="row g-3">
                    @csrf

                    <div class="col-lg-7">
                        <label for="dataset" class="form-label">{{ __('app.rental.exports.fields.dataset') }}</label>
                        <select id="dataset" name="dataset" class="form-select @error('dataset') is-invalid @enderror" required>
                            <option value="">{{ __('app.rental.exports.index.select_placeholder') }}</option>
                            @foreach ($datasets as $key => $dataset)
                                <option value="{{ $key }}" @selected(old('dataset') === $key)>
                                    {{ $dataset['label'] }} ({{ $dataset['count'] }})
                                </option>
                            @endforeach
                        </select>
                        @error('dataset')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-lg-5">
                        <label for="format" class="form-label">{{ __('app.rental.exports.fields.format') }}</label>
                        <select id="format" name="format" class="form-select @error('format') is-invalid @enderror" required>
                            <option value="csv" @selected(old('format') === 'csv')>{{ __('app.rental.exports.formats.csv') }}</option>
                            <option value="xlsx" @selected(old('format', 'xlsx') === 'xlsx')>{{ __('app.rental.exports.formats.xlsx') }}</option>
                        </select>
                        @error('format')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">{{ __('app.rental.exports.actions.download') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-4">
            @foreach ($datasets as $dataset)
                <div class="col-md-6 col-xl-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between gap-3 align-items-start mb-3">
                                <h2 class="h5 mb-0">{{ $dataset['label'] }}</h2>
                                <span class="badge text-bg-light">{{ $dataset['count'] }}</span>
                            </div>
                            <p class="text-body-secondary mb-0">{{ $dataset['description'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
