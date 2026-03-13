@extends('client.layout', ['title' => __('app.rental.leases.index.page_title')])

@section('content')
    <div class="vstack gap-4 py-4">
        <div class="d-flex justify-content-between gap-3 flex-wrap">
            <div>
                <h1 class="h2 mb-1">{{ __('app.rental.leases.index.heading') }}</h1>
                <p class="text-body-secondary mb-0">{{ __('app.rental.leases.index.description') }}</p>
            </div>
            <a href="{{ route('client.leases.create') }}" class="btn btn-primary">{{ __('app.rental.leases.actions.create') }}</a>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                @if ($leases->isEmpty())
                    <div class="p-4 text-body-secondary">{{ __('app.rental.leases.empty.description') }}</div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle mb-0" data-datatable data-page-length="20">
                            <thead>
                                <tr>
                                    <th>{{ __('app.rental.leases.fields.unit') }}</th>
                                    <th>{{ __('app.rental.leases.fields.tenant') }}</th>
                                    <th>{{ __('app.rental.leases.fields.start_date') }}</th>
                                    <th>{{ __('app.rental.leases.fields.end_date') }}</th>
                                    <th>{{ __('app.rental.leases.fields.status') }}</th>
                                    <th class="text-end">{{ __('app.rental.common.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($leases as $lease)
                                    <tr>
                                        <td>{{ $lease->propertyUnit->property->name }} / {{ $lease->propertyUnit->name }}</td>
                                        <td>{{ $lease->tenantProfile->full_name }}</td>
                                        <td>{{ $lease->start_date->format('d.m.Y') }}</td>
                                        <td>{{ $lease->end_date?->format('d.m.Y') ?? '—' }}</td>
                                        <td>{{ $lease->status->label() }}</td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-2">
                                                <a href="{{ route('client.leases.show', $lease) }}" class="btn btn-sm btn-outline-secondary">{{ __('app.rental.common.view') }}</a>
                                                <a href="{{ route('client.leases.edit', $lease) }}" class="btn btn-sm btn-outline-primary">{{ __('app.rental.common.edit') }}</a>
                                                <form method="POST" action="{{ route('client.leases.destroy', $lease) }}">
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
