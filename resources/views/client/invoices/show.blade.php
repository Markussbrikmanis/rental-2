@extends('client.layout', ['title' => $invoice->number])

@section('content')
    <div class="vstack gap-4 py-4">
        <div class="d-flex justify-content-between gap-3 flex-wrap">
            <div>
                <h1 class="h2 mb-1">{{ $invoice->number }}</h1>
                <p class="text-body-secondary mb-0">
                    {{ $invoice->lease->tenantProfile->full_name }} · {{ $invoice->lease->propertyUnit->property->name }} / {{ $invoice->lease->propertyUnit->name }}
                </p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('client.invoices.download', $invoice) }}" class="btn btn-outline-secondary">{{ __('app.rental.documents.actions.download_pdf') }}</a>
                <a href="{{ route('client.invoices.print', $invoice) }}" class="btn btn-outline-secondary" target="_blank" rel="noopener">{{ __('app.rental.documents.actions.print') }}</a>
                <form method="POST" action="{{ route('client.invoices.issue', $invoice) }}">@csrf<button type="submit" class="btn btn-outline-primary">{{ __('app.rental.invoices.actions.issue') }}</button></form>
                <form method="POST" action="{{ route('client.invoices.send', $invoice) }}">@csrf<button type="submit" class="btn btn-outline-primary">{{ __('app.rental.invoices.actions.send') }}</button></form>
                <form method="POST" action="{{ route('client.invoices.remind', $invoice) }}">@csrf<button type="submit" class="btn btn-outline-warning">{{ __('app.rental.invoices.actions.remind') }}</button></form>
                <form method="POST" action="{{ route('client.invoices.cancel', $invoice) }}">@csrf<button type="submit" class="btn btn-outline-danger">{{ __('app.rental.invoices.actions.cancel') }}</button></form>
                <form method="POST" action="{{ route('client.invoices.destroy', $invoice) }}">@csrf @method('DELETE')<button type="submit" class="btn btn-danger">{{ __('app.rental.invoices.actions.delete') }}</button></form>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h2 class="h5 mb-3">{{ __('app.rental.invoices.edit.heading') }}</h2>
                        <form method="POST" action="{{ route('client.invoices.update', $invoice) }}" class="row g-3">
                            @csrf
                            @method('PUT')
                            <div class="col-md-6">
                                <label class="form-label">{{ __('app.rental.invoices.fields.number') }}</label>
                                <input name="number" type="text" value="{{ old('number', $invoice->number) }}" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('app.rental.invoices.fields.kind') }}</label>
                                <input type="text" value="{{ $invoice->kind->label() }}" class="form-control" disabled>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ __('app.rental.invoices.fields.issue_date') }}</label>
                                <input name="issue_date" type="date" value="{{ old('issue_date', $invoice->issue_date->format('Y-m-d')) }}" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ __('app.rental.invoices.fields.due_date') }}</label>
                                <input name="due_date" type="date" value="{{ old('due_date', $invoice->due_date->format('Y-m-d')) }}" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ __('app.rental.invoices.fields.status') }}</label>
                                <select name="status" class="form-select" required>
                                    @foreach (\App\Enums\InvoiceStatus::cases() as $status)
                                        <option value="{{ $status->value }}" @selected(old('status', $invoice->status->value) === $status->value)>{{ $status->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('app.rental.invoices.fields.period_from') }}</label>
                                <input name="period_from" type="date" value="{{ old('period_from', $invoice->period_from->format('Y-m-d')) }}" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('app.rental.invoices.fields.period_to') }}</label>
                                <input name="period_to" type="date" value="{{ old('period_to', $invoice->period_to->format('Y-m-d')) }}" class="form-control" required>
                            </div>
                            <div class="col-12 d-flex justify-content-between align-items-center">
                                <div class="text-body-secondary small">
                                    {{ __('app.rental.invoices.fields.total') }}: {{ number_format((float) $invoice->total, 2, ',', ' ') }} EUR
                                </div>
                                <button type="submit" class="btn btn-primary">{{ __('app.rental.invoices.actions.update') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h2 class="h5 mb-3">{{ __('app.rental.payments.index.heading') }}</h2>
                        <form method="POST" action="{{ route('client.payments.store', $invoice) }}" class="row g-3">
                            @csrf
                            <div class="col-md-4">
                                <label class="form-label">{{ __('app.rental.payments.fields.paid_at') }}</label>
                                <input name="paid_at" type="date" value="{{ now()->format('Y-m-d') }}" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ __('app.rental.payments.fields.amount') }}</label>
                                <input name="amount" type="number" min="0.01" step="0.01" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ __('app.rental.payments.fields.method') }}</label>
                                <select name="method" class="form-select" required>
                                    @foreach (\App\Enums\PaymentMethod::cases() as $method)
                                        <option value="{{ $method->value }}">{{ $method->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('app.rental.payments.fields.reference') }}</label>
                                <input name="reference" type="text" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('app.properties.fields.notes') }}</label>
                                <input name="notes" type="text" class="form-control">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">{{ __('app.rental.payments.actions.record') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h2 class="h5 mb-3">{{ __('app.rental.invoices.fields.lines') }}</h2>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('app.rental.invoices.fields.description') }}</th>
                                <th>{{ __('app.rental.invoices.fields.quantity') }}</th>
                                <th>{{ __('app.rental.invoices.fields.unit_price') }}</th>
                                <th>{{ __('app.rental.invoices.fields.tax') }}</th>
                                <th>{{ __('app.rental.invoices.fields.line_total') }}</th>
                                <th>{{ __('app.rental.invoices.fields.line_source') }}</th>
                                <th class="text-end">{{ __('app.rental.common.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invoice->lines as $line)
                                <tr>
                                    <td>
                                        <form id="invoice-line-update-{{ $line->id }}" method="POST" action="{{ route('client.invoice-lines.update', [$invoice, $line]) }}">
                                            @csrf
                                            @method('PUT')
                                        </form>
                                        <input form="invoice-line-update-{{ $line->id }}" name="description" type="text" value="{{ $line->description }}" class="form-control" required>
                                    </td>
                                    <td>
                                        <input form="invoice-line-update-{{ $line->id }}" name="quantity" type="number" min="0.01" step="0.01" value="{{ $line->quantity }}" class="form-control" required>
                                    </td>
                                    <td>
                                        <input form="invoice-line-update-{{ $line->id }}" name="unit_price" type="number" min="0" step="0.01" value="{{ $line->unit_price }}" class="form-control" required>
                                    </td>
                                    <td>
                                        <input form="invoice-line-update-{{ $line->id }}" name="tax" type="number" min="0" step="0.01" value="{{ $line->tax }}" class="form-control">
                                    </td>
                                    <td>{{ number_format((float) $line->line_total, 2, ',', ' ') }}</td>
                                    <td class="small">
                                        @if ($line->source_type === null)
                                            {{ __('app.rental.invoice_lines.sources.custom') }}
                                        @elseif ($line->is_manual_override)
                                            {{ __('app.rental.invoice_lines.sources.manual_override') }}
                                        @else
                                            {{ __('app.rental.invoice_lines.sources.generated') }}
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-2">
                                            <button form="invoice-line-update-{{ $line->id }}" type="submit" class="btn btn-sm btn-outline-primary">{{ __('app.rental.common.update') }}</button>
                                            @if ($line->source_type === null)
                                                <form method="POST" action="{{ route('client.invoice-lines.destroy', [$invoice, $line]) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('app.rental.common.delete') }}</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h2 class="h5 mb-3">{{ __('app.rental.invoice_lines.create.heading') }}</h2>
                <form method="POST" action="{{ route('client.invoice-lines.store', $invoice) }}" class="row g-3">
                    @csrf
                    <div class="col-md-4">
                        <label class="form-label">{{ __('app.rental.invoices.fields.description') }}</label>
                        <input name="description" type="text" value="{{ old('description', $customLine->description) }}" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ __('app.rental.invoices.fields.quantity') }}</label>
                        <input name="quantity" type="number" min="0.01" step="0.01" value="{{ old('quantity', $customLine->quantity) }}" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ __('app.rental.invoices.fields.unit_price') }}</label>
                        <input name="unit_price" type="number" min="0" step="0.01" value="{{ old('unit_price', $customLine->unit_price) }}" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ __('app.rental.invoices.fields.tax') }}</label>
                        <input name="tax" type="number" min="0" step="0.01" value="{{ old('tax', $customLine->tax) }}" class="form-control">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">{{ __('app.rental.invoice_lines.actions.create') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h2 class="h5 mb-3">{{ __('app.rental.payments.index.heading') }}</h2>
                        @if ($invoice->payments->isEmpty())
                            <p class="text-body-secondary mb-0">{{ __('app.rental.payments.empty.description') }}</p>
                        @else
                            <ul class="list-group list-group-flush">
                                @foreach ($invoice->payments as $payment)
                                    <li class="list-group-item px-0">
                                        {{ $payment->paid_at->format('d.m.Y') }} · {{ number_format((float) $payment->amount, 2, ',', ' ') }} EUR · {{ $payment->method->label() }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h2 class="h5 mb-3">{{ __('app.rental.reminders.index.heading') }}</h2>
                        @if ($invoice->reminders->isEmpty())
                            <p class="text-body-secondary mb-0">{{ __('app.rental.reminders.empty.description') }}</p>
                        @else
                            <ul class="list-group list-group-flush">
                                @foreach ($invoice->reminders as $reminder)
                                    <li class="list-group-item px-0">
                                        {{ $reminder->kind }} · {{ $reminder->status }} · {{ $reminder->recipient ?: '—' }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
