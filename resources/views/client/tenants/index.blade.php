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
                                            <div class="d-inline-flex gap-2">
                                                <a href="{{ route('client.tenants.edit', $tenant) }}" class="btn btn-sm btn-outline-primary">{{ __('app.rental.common.edit') }}</a>
                                                <form method="POST" action="{{ route('client.tenants.destroy', $tenant) }}">
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
