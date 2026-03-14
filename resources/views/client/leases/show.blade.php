@extends('client.layout', ['title' => __('app.rental.leases.show.page_title')])

@section('content')
    <div class="vstack gap-4 py-4">
        <div class="d-flex justify-content-between flex-wrap gap-3 client-page-header">
            <div>
                <h1 class="h2 mb-1">{{ __('app.rental.leases.show.heading') }}</h1>
                <p class="text-body-secondary mb-0">
                    {{ $lease->tenantProfile->full_name }} · {{ $lease->propertyUnit->property->name }} / {{ $lease->propertyUnit->name }}
                </p>
            </div>
            <div class="client-page-actions">
                <form method="POST" action="{{ route('client.leases.generate-invoice', $lease) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary">{{ __('app.rental.invoices.actions.generate') }}</button>
                </form>
                <a href="{{ route('client.leases.edit', $lease) }}" class="btn btn-outline-secondary">{{ __('app.rental.common.edit') }}</a>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-5">{{ __('app.rental.leases.fields.status') }}</dt>
                            <dd class="col-sm-7">{{ $lease->status->label() }}</dd>
                            <dt class="col-sm-5">{{ __('app.rental.leases.fields.start_date') }}</dt>
                            <dd class="col-sm-7">{{ $lease->start_date->format('d.m.Y') }}</dd>
                            <dt class="col-sm-5">{{ __('app.rental.leases.fields.end_date') }}</dt>
                            <dd class="col-sm-7">{{ $lease->end_date?->format('d.m.Y') ?? '—' }}</dd>
                            <dt class="col-sm-5">{{ __('app.rental.leases.fields.billing_start_date') }}</dt>
                            <dd class="col-sm-7">{{ $lease->billing_start_date->format('d.m.Y') }}</dd>
                            <dt class="col-sm-5">{{ __('app.rental.leases.fields.due_day') }}</dt>
                            <dd class="col-sm-7">{{ $lease->due_day }}</dd>
                            <dt class="col-sm-5">{{ __('app.rental.leases.fields.deposit') }}</dt>
                            <dd class="col-sm-7">{{ $lease->deposit ? number_format((float) $lease->deposit, 2, ',', ' ') : '—' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h2 class="h5 mb-3">{{ __('app.rental.charge_rules.index.heading') }}</h2>
                        @if ($lease->chargeRules->isEmpty())
                            <p class="text-body-secondary">{{ __('app.rental.charge_rules.empty.description') }}</p>
                        @else
                            <div class="table-responsive mb-4">
                                <table class="table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>{{ __('app.rental.charge_rules.fields.name') }}</th>
                                            <th>{{ __('app.rental.charge_rules.fields.amount') }}</th>
                                            <th>{{ __('app.rental.charge_rules.fields.frequency') }}</th>
                                            <th class="text-end">{{ __('app.rental.common.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($lease->chargeRules as $rule)
                                            <tr>
                                                <td>{{ $rule->name }}</td>
                                                <td>{{ number_format((float) $rule->amount, 2, ',', ' ') }} EUR</td>
                                                <td>{{ $rule->frequency->label() }}</td>
                                                <td class="text-end">
                                                    <div class="d-inline-flex gap-2">
                                                        <a href="{{ route('client.charge-rules.edit', [$lease, $rule]) }}" class="btn btn-sm btn-outline-primary">{{ __('app.rental.common.edit') }}</a>
                                                        <form method="POST" action="{{ route('client.charge-rules.destroy', [$lease, $rule]) }}">
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

                        <form method="POST" action="{{ route('client.charge-rules.store', $lease) }}" class="row g-3">
                            @csrf
                            <div class="col-md-6">
                                <label class="form-label">{{ __('app.rental.charge_rules.fields.name') }}</label>
                                <input name="name" type="text" value="{{ old('name', $chargeRule->name) }}" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ __('app.rental.charge_rules.fields.amount') }}</label>
                                <input name="amount" type="number" step="0.01" min="0" value="{{ old('amount', $chargeRule->amount) }}" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ __('app.rental.charge_rules.fields.frequency') }}</label>
                                <select name="frequency" class="form-select" required>
                                    @foreach (\App\Enums\ChargeFrequency::cases() as $frequency)
                                        <option value="{{ $frequency->value }}">{{ $frequency->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ __('app.rental.charge_rules.fields.interval_count') }}</label>
                                <input name="interval_count" type="number" min="1" value="{{ old('interval_count', 1) }}" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ __('app.rental.charge_rules.fields.interval_unit') }}</label>
                                <select name="interval_unit" class="form-select">
                                    <option value="">{{ __('app.rental.common.not_applicable') }}</option>
                                    @foreach (\App\Enums\ChargeIntervalUnit::cases() as $intervalUnit)
                                        <option value="{{ $intervalUnit->value }}">{{ $intervalUnit->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ __('app.rental.charge_rules.fields.effective_from') }}</label>
                                <input name="effective_from" type="date" value="{{ old('effective_from', $chargeRule->effective_from?->format('Y-m-d') ?? $lease->billing_start_date->format('Y-m-d')) }}" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ __('app.rental.charge_rules.fields.effective_to') }}</label>
                                <input name="effective_to" type="date" value="{{ old('effective_to') }}" class="form-control">
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input id="auto_invoice_enabled" name="auto_invoice_enabled" type="checkbox" class="form-check-input" value="1" checked>
                                    <label for="auto_invoice_enabled" class="form-check-label">{{ __('app.rental.charge_rules.fields.auto_invoice_enabled') }}</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">{{ __('app.rental.charge_rules.actions.create') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h2 class="h5 mb-3">{{ __('app.client.navigation.invoices') }}</h2>
                @if ($lease->invoices->isEmpty())
                    <p class="text-body-secondary mb-0">{{ __('app.rental.invoices.empty.description') }}</p>
                @else
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('app.rental.invoices.fields.number') }}</th>
                                    <th>{{ __('app.rental.invoices.fields.status') }}</th>
                                    <th>{{ __('app.rental.invoices.fields.total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($lease->invoices as $invoice)
                                    <tr>
                                        <td><a href="{{ route('client.invoices.show', $invoice) }}">{{ $invoice->number }}</a></td>
                                        <td>{{ $invoice->status->label() }}</td>
                                        <td>{{ number_format((float) $invoice->total, 2, ',', ' ') }} EUR</td>
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
