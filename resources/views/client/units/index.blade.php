@extends('client.layout', ['title' => __('app.rental.units.index.page_title')])

@section('content')
    <div class="vstack gap-4 py-4">
        <div class="d-flex justify-content-between gap-3 flex-wrap">
            <div>
                <h1 class="h2 mb-1">{{ __('app.rental.units.index.heading') }}</h1>
                <p class="text-body-secondary mb-0">{{ __('app.rental.units.index.description') }}</p>
            </div>
            <a href="{{ route('client.units.create') }}" class="btn btn-primary">{{ __('app.rental.units.actions.create') }}</a>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                @if ($units->isEmpty())
                    <div class="p-4 text-body-secondary">{{ __('app.rental.units.empty.description') }}</div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle mb-0" data-datatable data-page-length="20">
                            <thead>
                                <tr>
                                    <th>{{ __('app.rental.units.fields.property') }}</th>
                                    <th>{{ __('app.rental.units.fields.name') }}</th>
                                    <th>{{ __('app.rental.units.fields.code') }}</th>
                                    <th>{{ __('app.rental.units.fields.status') }}</th>
                                    <th>{{ __('app.rental.units.fields.unit_type') }}</th>
                                    <th>{{ __('app.rental.units.fields.area') }}</th>
                                    <th class="text-end">{{ __('app.rental.common.actions') }}</th>
                                </tr>
                                <tr data-dt-order="disable">
                                    <th><input class="form-control form-control-sm" placeholder="{{ __('app.properties.index.search_placeholder') }}"></th>
                                    <th><input class="form-control form-control-sm" placeholder="{{ __('app.properties.index.search_placeholder') }}"></th>
                                    <th><input class="form-control form-control-sm" placeholder="{{ __('app.properties.index.search_placeholder') }}"></th>
                                    <th><input class="form-control form-control-sm" placeholder="{{ __('app.properties.index.search_placeholder') }}"></th>
                                    <th><input class="form-control form-control-sm" placeholder="{{ __('app.properties.index.search_placeholder') }}"></th>
                                    <th><input class="form-control form-control-sm" placeholder="{{ __('app.properties.index.search_placeholder') }}"></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($units as $unit)
                                    <tr>
                                        <td>{{ $unit->property->name }}</td>
                                        <td>{{ $unit->name }}</td>
                                        <td>{{ $unit->code ?: '—' }}</td>
                                        <td>{{ $unit->status->label() }}</td>
                                        <td>{{ $unit->unit_type ?: '—' }}</td>
                                        <td>{{ $unit->area ? number_format((float) $unit->area, 2, ',', ' ') : '—' }}</td>
                                        <td class="text-end">
                                            <div class="client-row-actions">
                                                <a href="{{ route('client.units.edit', $unit) }}" class="btn btn-sm btn-outline-primary client-icon-btn" aria-label="{{ __('app.rental.common.edit') }}" title="{{ __('app.rental.common.edit') }}">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m4 20 4.5-1 9.5-9.5-3.5-3.5L5 15.5 4 20Z"/><path d="M13.5 6 17 9.5"/></svg>
                                                    <span class="visually-hidden">{{ __('app.rental.common.edit') }}</span>
                                                </a>
                                                <form method="POST" action="{{ route('client.units.destroy', $unit) }}">
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
