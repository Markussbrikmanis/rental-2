@extends('client.layout', ['title' => $invoice->number])

@section('content')
    @php($outstanding = max((float) $invoice->total - (float) $invoice->payments->sum('amount'), 0))

    <div class="vstack gap-4 py-4">
        <div class="d-flex justify-content-between gap-3 flex-wrap client-page-header">
            <div>
                <h1 class="h2 mb-1">{{ $invoice->number }}</h1>
                <p class="text-body-secondary mb-0">
                    {{ $invoice->lease->propertyUnit->property->name }} / {{ $invoice->lease->propertyUnit->name }}
                </p>
            </div>
            <div class="client-page-actions">
                <a href="{{ route('client.tenant-invoices.download', $invoice) }}" class="btn btn-outline-secondary">{{ __('app.rental.documents.actions.download_pdf') }}</a>
                <a href="{{ route('client.tenant-invoices.print', $invoice) }}" class="btn btn-outline-secondary" target="_blank" rel="noopener">{{ __('app.rental.documents.actions.print') }}</a>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="report-card__label">{{ __('app.rental.invoices.fields.status') }}</div>
                        <div class="report-card__value fs-5">{{ $invoice->status->label() }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="report-card__label">{{ __('app.rental.invoices.fields.total') }}</div>
                        <div class="report-card__value fs-5">{{ number_format((float) $invoice->total, 2, ',', ' ') }} EUR</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="report-card__label">{{ __('app.rental.tenant_portal.common.outstanding') }}</div>
                        <div class="report-card__value fs-5">{{ number_format($outstanding, 2, ',', ' ') }} EUR</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="report-card__label">{{ __('app.rental.tenant_portal.invoices.show.period') }}</div>
                        <div class="small">{{ $invoice->period_from->format('d.m.Y') }} - {{ $invoice->period_to->format('d.m.Y') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h2 class="h5 mb-3">{{ __('app.rental.invoices.fields.lines') }}</h2>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('app.rental.invoices.fields.description') }}</th>
                                <th>{{ __('app.rental.invoices.fields.quantity') }}</th>
                                <th>{{ __('app.rental.invoices.fields.unit_price') }}</th>
                                <th>{{ __('app.rental.invoices.fields.tax') }}</th>
                                <th>{{ __('app.rental.invoices.fields.line_total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invoice->lines as $line)
                                <tr>
                                    <td>{{ $line->description }}</td>
                                    <td>{{ number_format((float) $line->quantity, 2, ',', ' ') }}</td>
                                    <td>{{ number_format((float) $line->unit_price, 2, ',', ' ') }}</td>
                                    <td>{{ number_format((float) $line->tax, 2, ',', ' ') }}</td>
                                    <td>{{ number_format((float) $line->line_total, 2, ',', ' ') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h2 class="h5 mb-3">{{ __('app.rental.tenant_portal.invoices.show.payments') }}</h2>
                @if ($invoice->payments->isEmpty())
                    <p class="text-body-secondary mb-0">{{ __('app.rental.tenant_portal.invoices.show.empty_payments') }}</p>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('app.rental.payments.fields.paid_at') }}</th>
                                    <th>{{ __('app.rental.payments.fields.amount') }}</th>
                                    <th>{{ __('app.rental.payments.fields.method') }}</th>
                                    <th>{{ __('app.rental.payments.fields.reference') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($invoice->payments as $payment)
                                    <tr>
                                        <td>{{ $payment->paid_at->format('d.m.Y') }}</td>
                                        <td>{{ number_format((float) $payment->amount, 2, ',', ' ') }} EUR</td>
                                        <td>{{ $payment->method->label() }}</td>
                                        <td>{{ $payment->reference ?: '—' }}</td>
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
