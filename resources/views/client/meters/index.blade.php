@extends('client.layout', ['title' => __('app.rental.meters.index.page_title')])

@section('content')
    <div class="vstack gap-4 py-4">
        <div class="d-flex justify-content-between gap-3 flex-wrap">
            <div>
                <h1 class="h2 mb-1">{{ __('app.rental.meters.index.heading') }}</h1>
                <p class="text-body-secondary mb-0">{{ __('app.rental.meters.index.description') }}</p>
            </div>
            <a href="{{ route('client.meters.create') }}" class="btn btn-primary">{{ __('app.rental.meters.actions.create') }}</a>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                @if ($meters->isEmpty())
                    <div class="p-4 text-body-secondary">{{ __('app.rental.meters.empty.description') }}</div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle mb-0" data-datatable data-page-length="20">
                            <thead>
                                <tr>
                                    <th>{{ __('app.rental.meters.fields.name') }}</th>
                                    <th>{{ __('app.rental.meters.fields.type') }}</th>
                                    <th>{{ __('app.rental.meters.fields.unit') }}</th>
                                    <th>{{ __('app.rental.meters.fields.measurement_unit') }}</th>
                                    <th>{{ __('app.rental.meters.fields.utility_billing_mode') }}</th>
                                    <th>{{ __('app.rental.meters.fields.rate_per_unit') }}</th>
                                    <th class="text-end">{{ __('app.rental.common.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($meters as $meter)
                                    <tr>
                                        <td>{{ $meter->name }}</td>
                                        <td>{{ $meter->type->label() }}</td>
                                        <td>{{ $meter->propertyUnit->property->name }} / {{ $meter->propertyUnit->name }}</td>
                                        <td>{{ $meter->unit }}</td>
                                        <td>{{ $meter->utility_billing_mode->label() }}</td>
                                        <td>{{ $meter->rate_per_unit !== null ? number_format((float) $meter->rate_per_unit, 4, ',', ' ') : '—' }}</td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-2">
                                                <a href="{{ route('client.meters.show', $meter) }}" class="btn btn-sm btn-outline-secondary">{{ __('app.rental.common.view') }}</a>
                                                <a href="{{ route('client.meters.edit', $meter) }}" class="btn btn-sm btn-outline-primary">{{ __('app.rental.common.edit') }}</a>
                                                <form method="POST" action="{{ route('client.meters.destroy', $meter) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('app.rental.common.delete') }}</button>
                                                </form>
                                            </div>
                                        </td>
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
