@extends('client.layout', ['title' => $property->name])

@section('content')
    <div class="vstack gap-4 py-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
            <div>
                <h1 class="h2 mb-1">{{ $property->name }}</h1>
                <p class="text-body-secondary mb-0">
                    {{ $property->address }}, {{ $property->city }}, {{ $property->country }}
                </p>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('client.units.create', ['property_id' => $property->id]) }}" class="btn btn-primary">{{ __('app.rental.units.actions.create') }}</a>
                <a href="{{ route('client.properties.edit', $property) }}" class="btn btn-outline-secondary">{{ __('app.properties.actions.edit') }}</a>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h2 class="h5">{{ __('app.properties.fields.type') }}</h2>
                        <p class="text-body-secondary">{{ $property->type->label() }}</p>
                        <h2 class="h5">{{ __('app.properties.fields.price') }}</h2>
                        <p class="text-body-secondary">{{ number_format((float) $property->price, 2, ',', ' ') }} EUR</p>
                        <h2 class="h5">{{ __('app.properties.fields.notes') }}</h2>
                        <p class="text-body-secondary mb-0">{{ $property->notes ?: '—' }}</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h2 class="h5 mb-3">{{ __('app.rental.properties.units_title') }}</h2>

                        @if ($property->units->isEmpty())
                            <p class="text-body-secondary mb-0">{{ __('app.rental.units.empty.description') }}</p>
                        @else
                            <div class="table-responsive">
                                <table class="table align-middle mb-0" data-datatable data-page-length="20">
                                    <thead>
                                        <tr>
                                            <th>{{ __('app.rental.units.fields.name') }}</th>
                                            <th>{{ __('app.rental.units.fields.code') }}</th>
                                            <th>{{ __('app.rental.units.fields.status') }}</th>
                                            <th>{{ __('app.rental.leases.index.heading') }}</th>
                                            <th>{{ __('app.client.navigation.meters') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($property->units as $unit)
                                            <tr>
                                                <td>{{ $unit->name }}</td>
                                                <td>{{ $unit->code ?: '—' }}</td>
                                                <td>{{ $unit->status->label() }}</td>
                                                <td>{{ $unit->leases->count() }}</td>
                                                <td>{{ $unit->meters->count() }}</td>
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
    </div>
@endsection
