@extends('client.layout', ['title' => __('app.rental.charge_rules.edit.page_title')])

@section('content')
    <div class="card border-0 shadow-sm my-4">
        <div class="card-body p-4 p-lg-5">
            <h1 class="h2 mb-2">{{ __('app.rental.charge_rules.edit.heading') }}</h1>
            <p class="text-body-secondary mb-4">{{ $lease->tenantProfile->full_name }} · {{ $lease->propertyUnit->name }}</p>

            <form method="POST" action="{{ route('client.charge-rules.update', [$lease, $chargeRule]) }}" class="row g-3">
                @csrf
                @method('PUT')
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
                        @foreach ($frequencies as $frequency)
                            <option value="{{ $frequency->value }}" @selected(old('frequency', $chargeRule->frequency->value) === $frequency->value)>{{ $frequency->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('app.rental.charge_rules.fields.interval_count') }}</label>
                    <input name="interval_count" type="number" min="1" value="{{ old('interval_count', $chargeRule->interval_count) }}" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('app.rental.charge_rules.fields.interval_unit') }}</label>
                    <select name="interval_unit" class="form-select">
                        <option value="">{{ __('app.rental.common.not_applicable') }}</option>
                        @foreach ($intervalUnits as $intervalUnit)
                            <option value="{{ $intervalUnit->value }}" @selected(old('interval_unit', $chargeRule->interval_unit?->value) === $intervalUnit->value)>{{ $intervalUnit->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('app.rental.charge_rules.fields.effective_from') }}</label>
                    <input name="effective_from" type="date" value="{{ old('effective_from', $chargeRule->effective_from->format('Y-m-d')) }}" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('app.rental.charge_rules.fields.effective_to') }}</label>
                    <input name="effective_to" type="date" value="{{ old('effective_to', $chargeRule->effective_to?->format('Y-m-d')) }}" class="form-control">
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input id="charge-rule-auto-invoice" name="auto_invoice_enabled" type="checkbox" class="form-check-input" value="1" @checked(old('auto_invoice_enabled', $chargeRule->auto_invoice_enabled))>
                        <label for="charge-rule-auto-invoice" class="form-check-label">{{ __('app.rental.charge_rules.fields.auto_invoice_enabled') }}</label>
                    </div>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">{{ __('app.rental.common.update') }}</button>
                    <a href="{{ route('client.leases.show', $lease) }}" class="btn btn-outline-secondary">{{ __('app.rental.common.cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
@endsection
