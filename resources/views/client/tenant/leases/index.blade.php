@extends('client.layout', ['title' => __('app.rental.tenant_portal.leases.index.page_title')])

@section('content')
    <div class="vstack gap-4 py-4">
        <div>
            <h1 class="h2 mb-1">{{ __('app.rental.tenant_portal.leases.index.heading') }}</h1>
            <p class="text-body-secondary mb-0">{{ __('app.rental.tenant_portal.leases.index.description') }}</p>
        </div>

        @if ($tenantProfile === null)
            <div class="alert alert-warning mb-0">{{ __('app.rental.tenant_portal.common.no_profile') }}</div>
        @elseif ($leases->isEmpty())
            <div class="card border-0 shadow-sm">
                <div class="card-body text-body-secondary">{{ __('app.rental.tenant_portal.leases.empty.description') }}</div>
            </div>
        @else
            @foreach ($leases as $lease)
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between gap-3 flex-wrap mb-4">
                            <div>
                                <h2 class="h4 mb-1">{{ $lease->propertyUnit->property->name }} / {{ $lease->propertyUnit->name }}</h2>
                                <div class="text-body-secondary">{{ $lease->status->label() }}</div>
                            </div>
                            <div class="text-body-secondary">
                                {{ __('app.rental.tenant_portal.common.property_and_unit') }}:
                                <strong class="text-body">{{ $lease->propertyUnit->property->city }}, {{ $lease->propertyUnit->property->country }}</strong>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="report-card__label">{{ __('app.rental.leases.fields.start_date') }}</div>
                                <div>{{ $lease->start_date->format('d.m.Y') }}</div>
                            </div>
                            <div class="col-md-3">
                                <div class="report-card__label">{{ __('app.rental.leases.fields.end_date') }}</div>
                                <div>{{ $lease->end_date?->format('d.m.Y') ?: '—' }}</div>
                            </div>
                            <div class="col-md-3">
                                <div class="report-card__label">{{ __('app.rental.leases.fields.due_day') }}</div>
                                <div>{{ $lease->due_day }}</div>
                            </div>
                            <div class="col-md-3">
                                <div class="report-card__label">{{ __('app.rental.leases.fields.deposit') }}</div>
                                <div>{{ number_format((float) $lease->deposit, 2, ',', ' ') }} {{ $lease->currency }}</div>
                            </div>
                        </div>

                        @if ($lease->notes)
                            <div class="mt-4">
                                <div class="report-card__label">{{ __('app.properties.fields.notes') }}</div>
                                <div>{{ $lease->notes }}</div>
                            </div>
                        @endif

                        <div class="mt-4">
                            <h3 class="h5 mb-3">{{ __('app.rental.tenant_portal.leases.sections.charges') }}</h3>
                            @if ($lease->chargeRules->isEmpty())
                                <p class="text-body-secondary mb-0">{{ __('app.client.panel.owner.empty') }}</p>
                            @else
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>{{ __('app.rental.charge_rules.fields.name') }}</th>
                                                <th>{{ __('app.rental.charge_rules.fields.frequency') }}</th>
                                                <th>{{ __('app.rental.charge_rules.fields.amount') }}</th>
                                                <th>{{ __('app.rental.charge_rules.fields.effective_from') }}</th>
                                                <th>{{ __('app.rental.charge_rules.fields.effective_to') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($lease->chargeRules as $chargeRule)
                                                <tr>
                                                    <td>{{ $chargeRule->name }}</td>
                                                    <td>{{ $chargeRule->frequency->label() }}</td>
                                                    <td>{{ number_format((float) $chargeRule->amount, 2, ',', ' ') }} EUR</td>
                                                    <td>{{ $chargeRule->effective_from->format('d.m.Y') }}</td>
                                                    <td>{{ $chargeRule->effective_to?->format('d.m.Y') ?: '—' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
@endsection
