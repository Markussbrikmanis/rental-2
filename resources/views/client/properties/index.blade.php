@extends('client.layout', ['title' => __('app.properties.index.page_title')])

@section('content')
    <div class="vstack gap-4 py-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div>
                <h1 class="h2 mb-1">{{ __('app.properties.index.heading') }}</h1>
                <p class="text-body-secondary mb-0">{{ __('app.properties.index.description') }}</p>
            </div>

            <a href="{{ route('client.properties.create') }}" class="btn btn-primary">
                {{ __('app.properties.actions.create') }}
            </a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                @if ($properties->isEmpty())
                    <div class="p-4">
                        <h2 class="h5 mb-2">{{ __('app.properties.empty.title') }}</h2>
                        <p class="text-body-secondary mb-3">{{ __('app.properties.empty.description') }}</p>
                        <a href="{{ route('client.properties.create') }}" class="btn btn-outline-primary">
                            {{ __('app.properties.actions.create_first') }}
                        </a>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle mb-0" data-datatable data-page-length="20">
                            <thead>
                                <tr>
                                    <th>{{ __('app.properties.fields.name') }}</th>
                                    <th>{{ __('app.properties.fields.notes') }}</th>
                                    <th>{{ __('app.properties.fields.address') }}</th>
                                    <th>{{ __('app.properties.fields.city') }}</th>
                                    <th>{{ __('app.properties.fields.country') }}</th>
                                    <th>{{ __('app.properties.fields.price') }}</th>
                                    <th>{{ __('app.properties.fields.type') }}</th>
                                    <th>{{ __('app.properties.fields.acquired_at') }}</th>
                                    <th class="text-end">{{ __('app.properties.index.actions_column') }}</th>
                                </tr>
                                <tr class="align-top" data-dt-order="disable">
                                    <th>
                                        <input
                                            type="text"
                                            class="form-control form-control-sm"
                                            placeholder="{{ __('app.properties.index.search_placeholder') }}"
                                        >
                                    </th>
                                    <th>
                                        <input
                                            type="text"
                                            class="form-control form-control-sm"
                                            placeholder="{{ __('app.properties.index.search_placeholder') }}"
                                        >
                                    </th>
                                    <th>
                                        <input
                                            type="text"
                                            class="form-control form-control-sm"
                                            placeholder="{{ __('app.properties.index.search_placeholder') }}"
                                        >
                                    </th>
                                    <th>
                                        <input
                                            type="text"
                                            class="form-control form-control-sm"
                                            placeholder="{{ __('app.properties.index.search_placeholder') }}"
                                        >
                                    </th>
                                    <th>
                                        <select class="form-select form-select-sm">
                                            <option value="">{{ __('app.properties.index.all_option') }}</option>
                                            @foreach ($countries as $country)
                                                <option value="{{ $country }}">{{ $country }}</option>
                                            @endforeach
                                        </select>
                                    </th>
                                    <th>
                                        <input
                                            type="text"
                                            class="form-control form-control-sm"
                                            placeholder="0.00"
                                        >
                                    </th>
                                    <th>
                                        <select class="form-select form-select-sm">
                                            <option value="">{{ __('app.properties.index.all_option') }}</option>
                                            @foreach ($types as $type)
                                                <option value="{{ $type->label() }}">{{ $type->label() }}</option>
                                            @endforeach
                                        </select>
                                    </th>
                                    <th>
                                        <input
                                            type="text"
                                            class="form-control form-control-sm"
                                            placeholder="{{ __('app.properties.index.date_placeholder') }}"
                                        >
                                    </th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($properties as $property)
                                    <tr>
                                        <td class="fw-semibold">{{ $property->name }}</td>
                                        <td>{{ $property->notes ?: '—' }}</td>
                                        <td>{{ $property->address }}</td>
                                        <td>{{ $property->city }}</td>
                                    <td>{{ $property->country }}</td>
                                    <td>{{ number_format((float) $property->price, 2, ',', ' ') }} EUR</td>
                                    <td>{{ $property->type->label() }}</td>
                                    <td>{{ $property->acquired_at->format('d.m.Y') }}</td>
                                    <td class="text-end">
                                        <div class="client-row-actions">
                                            <a href="{{ route('client.properties.show', $property) }}" class="btn btn-sm btn-outline-secondary client-icon-btn" aria-label="{{ __('app.rental.common.view') }}" title="{{ __('app.rental.common.view') }}">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                                <span class="visually-hidden">{{ __('app.rental.common.view') }}</span>
                                            </a>
                                            <a href="{{ route('client.properties.edit', $property) }}" class="btn btn-sm btn-outline-primary client-icon-btn" aria-label="{{ __('app.properties.actions.edit') }}" title="{{ __('app.properties.actions.edit') }}">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m4 20 4.5-1 9.5-9.5-3.5-3.5L5 15.5 4 20Z"/><path d="M13.5 6 17 9.5"/></svg>
                                                <span class="visually-hidden">{{ __('app.properties.actions.edit') }}</span>
                                            </a>
                                                <form method="POST" action="{{ route('client.properties.destroy', $property) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger client-icon-btn" aria-label="{{ __('app.properties.actions.delete') }}" title="{{ __('app.properties.actions.delete') }}">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                                                        <span class="visually-hidden">{{ __('app.properties.actions.delete') }}</span>
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
