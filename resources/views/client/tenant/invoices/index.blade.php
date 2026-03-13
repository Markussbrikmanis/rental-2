@extends('client.layout', ['title' => __('app.rental.tenant_portal.invoices.index.page_title')])

@section('content')
    @php($money = static fn ($value) => number_format((float) $value, 2, ',', ' ') . ' EUR')

    <div class="vstack gap-4 py-4">
        <div>
            <h1 class="h2 mb-1">{{ __('app.rental.tenant_portal.invoices.index.heading') }}</h1>
            <p class="text-body-secondary mb-0">{{ __('app.rental.tenant_portal.invoices.index.description') }}</p>
        </div>

        @if ($tenantProfile === null)
            <div class="alert alert-warning mb-0">{{ __('app.rental.tenant_portal.common.no_profile') }}</div>
        @elseif ($invoices->isEmpty())
            <div class="card border-0 shadow-sm">
                <div class="card-body text-body-secondary">{{ __('app.rental.tenant_portal.invoices.empty.description') }}</div>
            </div>
        @else
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0" data-datatable data-page-length="20">
                            <thead>
                                <tr>
                                    <th>{{ __('app.rental.invoices.fields.number') }}</th>
                                    <th>{{ __('app.rental.invoices.fields.kind') }}</th>
                                    <th>{{ __('app.rental.leases.fields.unit') }}</th>
                                    <th>{{ __('app.rental.invoices.fields.due_date') }}</th>
                                    <th>{{ __('app.rental.invoices.fields.status') }}</th>
                                    <th>{{ __('app.rental.invoices.fields.total') }}</th>
                                    <th>{{ __('app.rental.tenant_portal.common.outstanding') }}</th>
                                    <th class="text-end">{{ __('app.rental.common.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($invoices as $invoice)
                                    @php($outstanding = max((float) $invoice->total - (float) $invoice->payments->sum('amount'), 0))

                                    <tr>
                                        <td>{{ $invoice->number }}</td>
                                        <td>{{ $invoice->kind->label() }}</td>
                                        <td>{{ $invoice->lease->propertyUnit->property->name }} / {{ $invoice->lease->propertyUnit->name }}</td>
                                        <td>{{ $invoice->due_date->format('d.m.Y') }}</td>
                                        <td>{{ $invoice->status->label() }}</td>
                                        <td>{{ $money($invoice->total) }}</td>
                                        <td>{{ $money($outstanding) }}</td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-2">
                                                <a href="{{ route('client.tenant-invoices.show', $invoice) }}" class="btn btn-sm btn-outline-primary">{{ __('app.rental.common.view') }}</a>
                                                <a href="{{ route('client.tenant-invoices.download', $invoice) }}" class="btn btn-sm btn-outline-secondary">{{ __('app.rental.documents.actions.download_pdf') }}</a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
