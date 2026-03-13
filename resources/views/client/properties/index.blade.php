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
                                            <div class="d-inline-flex flex-wrap justify-content-end gap-2">
                                                <a href="{{ route('client.properties.edit', $property) }}" class="btn btn-sm btn-outline-primary">
                                                    {{ __('app.properties.actions.edit') }}
                                                </a>
                                                <form method="POST" action="{{ route('client.properties.destroy', $property) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        {{ __('app.properties.actions.delete') }}
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
