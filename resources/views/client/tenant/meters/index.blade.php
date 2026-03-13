@extends('client.layout', ['title' => __('app.rental.tenant_portal.meters.index.page_title')])

@section('content')
    @php
        $latestAllowedDate = now()->toDateString();
        $earliestAllowedDate = now()->subDays(3)->toDateString();
    @endphp

    <div class="vstack gap-4 py-4">
        <div>
            <h1 class="h2 mb-1">{{ __('app.rental.tenant_portal.meters.index.heading') }}</h1>
            <p class="text-body-secondary mb-0">{{ __('app.rental.tenant_portal.meters.index.description') }}</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger mb-0">
                {{ __('app.rental.tenant_portal.meters.form.validation_error') }}
            </div>
        @endif

        @if ($tenantProfile === null)
            <div class="alert alert-warning mb-0">{{ __('app.rental.tenant_portal.common.no_profile') }}</div>
        @elseif ($leases->isEmpty())
            <div class="card border-0 shadow-sm">
                <div class="card-body text-body-secondary">{{ __('app.rental.tenant_portal.meters.empty.description') }}</div>
            </div>
        @else
            @foreach ($leases as $lease)
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <h2 class="h4 mb-1">{{ $lease->propertyUnit->property->name }} / {{ $lease->propertyUnit->name }}</h2>
                            <p class="text-body-secondary mb-0">{{ $lease->propertyUnit->property->address }}, {{ $lease->propertyUnit->property->city }}</p>
                        </div>

                        @if ($lease->propertyUnit->meters->isEmpty())
                            <p class="text-body-secondary mb-0">{{ __('app.rental.tenant_portal.meters.empty.description') }}</p>
                        @else
                            <div class="row g-4">
                                @foreach ($lease->propertyUnit->meters as $meter)
                                    @php($lastReading = $meter->readings->first())

                                    <div class="col-12">
                                        <div class="border rounded-4 p-4">
                                            <div class="d-flex justify-content-between gap-3 flex-wrap mb-3">
                                                <div>
                                                    <h3 class="h5 mb-1">{{ $meter->name }}</h3>
                                                    <div class="text-body-secondary">
                                                        {{ $meter->type->label() }} · {{ $meter->unit }}
                                                    </div>
                                                </div>
                                                <div class="text-body-secondary">
                                                    {{ __('app.rental.tenant_portal.common.last_reading') }}:
                                                    <strong class="text-body">
                                                        {{ $lastReading ? number_format((float) $lastReading->value, 3, ',', ' ') . ' ' . $meter->unit . ' · ' . $lastReading->reading_date->format('d.m.Y') : __('app.rental.tenant_portal.common.no_data') }}
                                                    </strong>
                                                </div>
                                            </div>

                                            <div class="row g-4">
                                                <div class="col-lg-5">
                                                    <h4 class="h6 mb-3">{{ __('app.rental.tenant_portal.meters.form.heading') }}</h4>
                                                    <form method="POST" action="{{ route('client.tenant-meter-readings.store', $meter) }}" class="row g-3">
                                                        @csrf
                                                        <div class="col-md-6">
                                                            <label class="form-label">{{ __('app.rental.meter_readings.fields.reading_date') }}</label>
                                                            <input
                                                                name="reading_date"
                                                                type="date"
                                                                value="{{ old('reading_date', $latestAllowedDate) }}"
                                                                min="{{ $earliestAllowedDate }}"
                                                                max="{{ $latestAllowedDate }}"
                                                                class="form-control @error('reading_date') is-invalid @enderror"
                                                                required
                                                            >
                                                            @error('reading_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">{{ __('app.rental.meter_readings.fields.value') }} ({{ $meter->unit }})</label>
                                                            <input name="value" type="number" min="0" step="0.001" value="{{ old('value') }}" class="form-control @error('value') is-invalid @enderror" required>
                                                            @error('value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label">{{ __('app.rental.tenant_portal.meters.form.notes') }}</label>
                                                            <textarea name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                                                            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                        </div>
                                                        <div class="col-12">
                                                            <button type="submit" class="btn btn-primary">{{ __('app.rental.meter_readings.actions.record') }}</button>
                                                        </div>
                                                    </form>
                                                </div>
                                                <div class="col-lg-7">
                                                    <h4 class="h6 mb-3">{{ __('app.rental.tenant_portal.meters.history.heading') }}</h4>
                                                    @if ($meter->readings->isEmpty())
                                                        <p class="text-body-secondary mb-0">{{ __('app.rental.meter_readings.empty.description') }}</p>
                                                    @else
                                                        <div class="table-responsive">
                                                            <table class="table align-middle mb-0">
                                                                <thead>
                                                                    <tr>
                                                                        <th>{{ __('app.rental.meter_readings.fields.reading_date') }}</th>
                                                                        <th>{{ __('app.rental.meter_readings.fields.value') }}</th>
                                                                        <th>{{ __('app.rental.meter_readings.fields.source') }}</th>
                                                                        <th>{{ __('app.properties.fields.notes') }}</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach ($meter->readings->take(5) as $reading)
                                                                        <tr>
                                                                            <td>{{ $reading->reading_date->format('d.m.Y') }}</td>
                                                                            <td>{{ number_format((float) $reading->value, 3, ',', ' ') }} {{ $meter->unit }}</td>
                                                                            <td>{{ $reading->source->label() }}</td>
                                                                            <td>{{ $reading->notes ?: '—' }}</td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        @endif
    </div>
@endsection
