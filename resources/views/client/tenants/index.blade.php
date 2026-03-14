@extends('client.layout', ['title' => __('app.rental.tenants.index.page_title')])

@section('content')
    <div class="vstack gap-4 py-4">
        <div class="d-flex justify-content-between gap-3 flex-wrap">
            <div>
                <h1 class="h2 mb-1">{{ __('app.rental.tenants.index.heading') }}</h1>
                <p class="text-body-secondary mb-0">{{ __('app.rental.tenants.index.description') }}</p>
            </div>
            <a href="{{ route('client.tenants.create') }}" class="btn btn-primary">{{ __('app.rental.tenants.actions.create') }}</a>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                @if ($tenants->isEmpty())
                    <div class="p-4 text-body-secondary">{{ __('app.rental.tenants.empty.description') }}</div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle mb-0" data-datatable data-page-length="20">
                            <thead>
                                <tr>
                                    <th>{{ __('app.rental.tenants.fields.full_name') }}</th>
                                    <th>{{ __('app.rental.tenants.fields.company_name') }}</th>
                                    <th>{{ __('app.client.common.email') }}</th>
                                    <th>{{ __('app.rental.tenants.fields.phone') }}</th>
                                    <th class="text-end">{{ __('app.rental.common.actions') }}</th>
                                </tr>
                                <tr data-dt-order="disable">
                                    <th><input class="form-control form-control-sm" placeholder="{{ __('app.properties.index.search_placeholder') }}"></th>
                                    <th><input class="form-control form-control-sm" placeholder="{{ __('app.properties.index.search_placeholder') }}"></th>
                                    <th><input class="form-control form-control-sm" placeholder="{{ __('app.properties.index.search_placeholder') }}"></th>
                                    <th><input class="form-control form-control-sm" placeholder="{{ __('app.properties.index.search_placeholder') }}"></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tenants as $tenant)
                                    <tr>
                                        <td>{{ $tenant->full_name }}</td>
                                        <td>{{ $tenant->company_name ?: '—' }}</td>
                                        <td>{{ $tenant->email ?: '—' }}</td>
                                        <td>{{ $tenant->phone ?: '—' }}</td>
                                        <td class="text-end">
                                            <div class="client-row-actions">
                                                <a href="{{ route('client.tenants.edit', $tenant) }}" class="btn btn-sm btn-outline-primary client-icon-btn" aria-label="{{ __('app.rental.common.edit') }}" title="{{ __('app.rental.common.edit') }}">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m4 20 4.5-1 9.5-9.5-3.5-3.5L5 15.5 4 20Z"/><path d="M13.5 6 17 9.5"/></svg>
                                                    <span class="visually-hidden">{{ __('app.rental.common.edit') }}</span>
                                                </a>
                                                <form method="POST" action="{{ route('client.tenants.destroy', $tenant) }}">
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
