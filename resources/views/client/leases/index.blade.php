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
                                            <div class="client-row-actions">
                                                <a href="{{ route('client.leases.show', $lease) }}" class="btn btn-sm btn-outline-secondary client-icon-btn" aria-label="{{ __('app.rental.common.view') }}" title="{{ __('app.rental.common.view') }}">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                                    <span class="visually-hidden">{{ __('app.rental.common.view') }}</span>
                                                </a>
                                                <a href="{{ route('client.leases.edit', $lease) }}" class="btn btn-sm btn-outline-primary client-icon-btn" aria-label="{{ __('app.rental.common.edit') }}" title="{{ __('app.rental.common.edit') }}">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m4 20 4.5-1 9.5-9.5-3.5-3.5L5 15.5 4 20Z"/><path d="M13.5 6 17 9.5"/></svg>
                                                    <span class="visually-hidden">{{ __('app.rental.common.edit') }}</span>
                                                </a>
                                                <form method="POST" action="{{ route('client.leases.destroy', $lease) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger client-icon-btn" aria-label="{{ __('app.rental.common.delete') }}" title="{{ __('app.rental.common.delete') }}">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                                                        <span class="visually-hidden">{{ __('app.rental.common.delete') }}</span>
                                                    </button>
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
