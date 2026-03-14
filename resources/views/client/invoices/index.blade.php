@extends('client.layout', ['title' => __('app.rental.invoices.index.page_title')])

@section('content')
    <div class="vstack gap-4 py-4">
        <div>
            <h1 class="h2 mb-1">{{ __('app.rental.invoices.index.heading') }}</h1>
            <p class="text-body-secondary mb-0">{{ __('app.rental.invoices.index.description') }}</p>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                @if ($invoices->isEmpty())
                    <div class="p-4 text-body-secondary">{{ __('app.rental.invoices.empty.description') }}</div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle mb-0" data-datatable data-page-length="20">
                            <thead>
                                <tr>
                                    <th>{{ __('app.rental.invoices.fields.number') }}</th>
                                    <th>{{ __('app.rental.invoices.fields.kind') }}</th>
                                    <th>{{ __('app.rental.leases.fields.tenant') }}</th>
                                    <th>{{ __('app.rental.leases.fields.unit') }}</th>
                                    <th>{{ __('app.rental.invoices.fields.due_date') }}</th>
                                    <th>{{ __('app.rental.invoices.fields.status') }}</th>
                                    <th>{{ __('app.rental.invoices.fields.total') }}</th>
                                    <th class="text-end">{{ __('app.rental.common.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($invoices as $invoice)
                                    <tr>
                                        <td>{{ $invoice->number }}</td>
                                        <td>{{ $invoice->kind->label() }}</td>
                                        <td>{{ $invoice->lease->tenantProfile->full_name }}</td>
                                        <td>{{ $invoice->lease->propertyUnit->property->name }} / {{ $invoice->lease->propertyUnit->name }}</td>
                                        <td>{{ $invoice->due_date->format('d.m.Y') }}</td>
                                        <td>{{ $invoice->status->label() }}</td>
                                        <td>{{ number_format((float) $invoice->total, 2, ',', ' ') }} EUR</td>
                                        <td class="text-end">
                                            <div class="client-row-actions">
                                                <a href="{{ route('client.invoices.show', $invoice) }}" class="btn btn-sm btn-outline-primary client-icon-btn" aria-label="{{ __('app.rental.common.view') }}" title="{{ __('app.rental.common.view') }}">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                                    <span class="visually-hidden">{{ __('app.rental.common.view') }}</span>
                                                </a>
                                                <form method="POST" action="{{ route('client.invoices.destroy', $invoice) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger client-icon-btn" aria-label="{{ __('app.rental.invoices.actions.delete') }}" title="{{ __('app.rental.invoices.actions.delete') }}">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                                                        <span class="visually-hidden">{{ __('app.rental.invoices.actions.delete') }}</span>
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
