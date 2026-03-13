@extends('client.layout', ['title' => $meter->name])

@section('content')
    <div class="vstack gap-4 py-4">
        <div class="d-flex justify-content-between gap-3 flex-wrap">
            <div>
                <h1 class="h2 mb-1">{{ $meter->name }}</h1>
                <p class="text-body-secondary mb-0">
                    {{ $meter->propertyUnit->property->name }} / {{ $meter->propertyUnit->name }} · {{ $meter->utility_billing_mode->label() }}
                    @if ($meter->rate_per_unit !== null)
                        · {{ number_format((float) $meter->rate_per_unit, 4, ',', ' ') }} / {{ $meter->unit }}
                    @endif
                </p>
            </div>
            <a href="{{ route('client.meters.edit', $meter) }}" class="btn btn-outline-secondary">{{ __('app.rental.common.edit') }}</a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('client.meter-readings.store', $meter) }}" class="row g-3">
                    @csrf
                    <div class="col-md-3">
                        <label class="form-label">{{ __('app.rental.meter_readings.fields.reading_date') }}</label>
                        <input name="reading_date" type="date" value="{{ now()->format('Y-m-d') }}" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ __('app.rental.meter_readings.fields.value') }}</label>
                        <input name="value" type="number" min="0" step="0.001" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ __('app.rental.meter_readings.fields.source') }}</label>
                        <select name="source" class="form-select" required>
                            @foreach (\App\Enums\MeterReadingSource::cases() as $source)
                                <option value="{{ $source->value }}">{{ $source->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ __('app.properties.fields.notes') }}</label>
                        <input name="notes" type="text" class="form-control">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">{{ __('app.rental.meter_readings.actions.record') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                @if ($readingDeltas->isEmpty())
                    <div class="p-4 text-body-secondary">{{ __('app.rental.meter_readings.empty.description') }}</div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('app.rental.meter_readings.fields.reading_date') }}</th>
                                    <th>{{ __('app.rental.meter_readings.fields.value') }}</th>
                                    <th>{{ __('app.rental.meter_readings.fields.source') }}</th>
                                    <th>{{ __('app.rental.meter_readings.fields.consumption') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($readingDeltas as $row)
                                    <tr>
                                        <td>{{ $row['reading']->reading_date->format('d.m.Y') }}</td>
                                        <td>{{ number_format((float) $row['reading']->value, 3, ',', ' ') }} {{ $meter->unit }}</td>
                                        <td>{{ $row['reading']->source->label() }}</td>
                                        <td>{{ $row['consumption'] !== null ? number_format((float) $row['consumption'], 3, ',', ' ') . ' ' . $meter->unit : '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
