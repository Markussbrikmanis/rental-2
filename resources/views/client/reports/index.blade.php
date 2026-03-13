@extends('client.layout', ['title' => __('app.rental.reports.index.page_title')])

@section('content')
    @php
        $money = static fn ($value) => number_format((float) $value, 2, ',', ' ') . ' EUR';
        $number = static fn ($value) => number_format((float) $value, 2, ',', ' ');
        $percent = static fn ($value) => number_format((float) $value, 2, ',', ' ') . '%';
        $query = request()->query();
        $occupancyFilled = (float) $report['overview']['occupancy_rate'];
        $invoiceMixTotal = (float) $report['invoice_mix']['standard']['invoiced'] + (float) $report['invoice_mix']['utility']['invoiced'];
        $standardAngle = $invoiceMixTotal > 0 ? round(((float) $report['invoice_mix']['standard']['invoiced'] / $invoiceMixTotal) * 360, 2) : 0;
        $propertyBarMax = max(1, (float) collect($report['charts']['property_collection'])->max(fn ($row) => max((float) $row['invoiced'], (float) $row['collected'])));
    @endphp

    <div class="vstack gap-4 py-4">
        <div class="d-flex flex-column flex-xl-row justify-content-between gap-3 align-items-xl-end">
            <div>
                <h1 class="h2 mb-1">{{ __('app.rental.reports.index.heading') }}</h1>
                <p class="text-body-secondary mb-0">{{ __('app.rental.reports.index.description') }}</p>
            </div>

            <form method="GET" action="{{ route('client.reports.index') }}" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label for="period" class="form-label mb-1">{{ __('app.rental.reports.filters.period') }}</label>
                    <select id="period" name="period" class="form-select">
                        @foreach ($report['filters']['period_options'] as $value => $label)
                            <option value="{{ $value }}" @selected($report['filters']['period'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="property_id" class="form-label mb-1">{{ __('app.rental.reports.filters.property') }}</label>
                    <select id="property_id" name="property_id" class="form-select">
                        <option value="">{{ __('app.rental.reports.filters.all_properties') }}</option>
                        @foreach ($report['filters']['property_options'] as $property)
                            <option value="{{ $property['id'] }}" @selected((string) $report['filters']['property_id'] === (string) $property['id'])>{{ $property['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="unit_id" class="form-label mb-1">{{ __('app.rental.reports.filters.unit') }}</label>
                    <select id="unit_id" name="unit_id" class="form-select">
                        <option value="">{{ __('app.rental.reports.filters.all_units') }}</option>
                        @foreach ($report['filters']['unit_options'] as $unit)
                            <option value="{{ $unit['id'] }}" @selected((string) $report['filters']['unit_id'] === (string) $unit['id'])>{{ $unit['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="view" class="form-label mb-1">{{ __('app.rental.reports.filters.view') }}</label>
                    <select id="view" name="view" class="form-select">
                        @foreach ($report['filters']['view_options'] as $value => $label)
                            <option value="{{ $value }}" @selected($report['filters']['view'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="from" class="form-label mb-1">{{ __('app.rental.reports.filters.from') }}</label>
                    <input id="from" name="from" type="date" value="{{ $report['filters']['from']->format('Y-m-d') }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label for="to" class="form-label mb-1">{{ __('app.rental.reports.filters.to') }}</label>
                    <input id="to" name="to" type="date" value="{{ $report['filters']['to']->format('Y-m-d') }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">{{ __('app.rental.reports.actions.apply') }}</button>
                </div>
            </form>
        </div>

        <div class="small text-body-secondary">
            {{ __('app.rental.reports.index.period_label') }}: {{ $report['filters']['label'] }}
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('client.reports.export', array_merge($query, ['format' => 'pdf'])) }}" class="btn btn-outline-secondary">{{ __('app.rental.reports.actions.export_pdf') }}</a>
            <a href="{{ route('client.reports.export', array_merge($query, ['format' => 'xlsx'])) }}" class="btn btn-outline-secondary">{{ __('app.rental.reports.actions.export_excel') }}</a>
            <a href="{{ route('client.reports.export', array_merge($query, ['format' => 'csv'])) }}" class="btn btn-outline-secondary">{{ __('app.rental.reports.actions.export_csv') }}</a>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="report-card__label">{{ __('app.rental.reports.cards.period_invoiced') }}</div>
                        <div class="report-card__value">{{ $money($report['overview']['period_invoiced']) }}</div>
                        <div class="text-body-secondary small">{{ __('app.rental.reports.cards.period_issued_count', ['count' => $report['overview']['period_issued_count']]) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="report-card__label">{{ __('app.rental.reports.cards.period_collected') }}</div>
                        <div class="report-card__value">{{ $money($report['overview']['period_collected']) }}</div>
                        <div class="text-body-secondary small">{{ __('app.rental.reports.cards.collection_rate') }}: {{ $percent($report['overview']['collection_rate']) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="report-card__label">{{ __('app.rental.reports.cards.period_outstanding') }}</div>
                        <div class="report-card__value">{{ $money($report['overview']['period_outstanding']) }}</div>
                        <div class="text-body-secondary small">{{ __('app.rental.reports.cards.overdue_outstanding') }}: {{ $money($report['overview']['overdue_outstanding']) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="report-card__label">{{ __('app.rental.reports.cards.occupancy') }}</div>
                        <div class="report-card__value">{{ $percent($report['overview']['occupancy_rate']) }}</div>
                        <div class="text-body-secondary small">{{ __('app.rental.reports.cards.occupied_units', ['occupied' => $report['overview']['occupied_units'], 'total' => $report['overview']['active_units']]) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="report-card__label">{{ __('app.rental.reports.cards.purchase_total') }}</div>
                        <div class="report-card__value">{{ $money($report['overview']['purchase_total']) }}</div>
                        <div class="text-body-secondary small">{{ __('app.rental.reports.cards.all_collected') }}: {{ $money($report['overview']['all_collected']) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="report-card__label">{{ __('app.rental.reports.cards.portfolio_payback') }}</div>
                        <div class="report-card__value">{{ $percent($report['overview']['portfolio_payback_rate']) }}</div>
                        <div class="text-body-secondary small">{{ __('app.rental.reports.cards.purchase_total') }} / {{ __('app.rental.reports.cards.all_collected') }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="report-card__label">{{ __('app.rental.reports.cards.standard_invoices') }}</div>
                        <div class="report-card__value">{{ $money($report['invoice_mix']['standard']['invoiced']) }}</div>
                        <div class="text-body-secondary small">{{ __('app.rental.reports.cards.collected') }}: {{ $money($report['invoice_mix']['standard']['collected']) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="report-card__label">{{ __('app.rental.reports.cards.utility_invoices') }}</div>
                        <div class="report-card__value">{{ $money($report['invoice_mix']['utility']['invoiced']) }}</div>
                        <div class="text-body-secondary small">{{ __('app.rental.reports.cards.collected') }}: {{ $money($report['invoice_mix']['utility']['collected']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        @if (in_array($report['filters']['view'], ['charts', 'split'], true))
            <div class="row g-4">
                <div class="col-xl-8">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h2 class="h4 mb-3">{{ __('app.rental.reports.charts.property_collection.heading') }}</h2>
                            <div class="vstack gap-3">
                                @forelse ($report['charts']['property_collection'] as $row)
                                    <div>
                                        <div class="d-flex justify-content-between gap-3 mb-1 small">
                                            <span>{{ $row['label'] }}</span>
                                            <span>{{ $money($row['collected']) }} / {{ $money($row['invoiced']) }}</span>
                                        </div>
                                        <div class="report-bars">
                                            <div class="report-bars__bar report-bars__bar--invoiced" style="width: {{ (($row['invoiced'] / $propertyBarMax) * 100) ?: 0 }}%"></div>
                                            <div class="report-bars__bar report-bars__bar--collected" style="width: {{ (($row['collected'] / $propertyBarMax) * 100) ?: 0 }}%"></div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-body-secondary">{{ __('app.rental.reports.empty') }}</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h2 class="h4 mb-3">{{ __('app.rental.reports.charts.invoice_mix.heading') }}</h2>
                            <div class="report-pie" style="--fill-angle: {{ $standardAngle }}deg;"></div>
                            <div class="report-legend mt-3">
                                <div><span class="report-legend__swatch report-legend__swatch--standard"></span>{{ __('app.rental.reports.cards.standard_invoices') }}: {{ $money($report['invoice_mix']['standard']['invoiced']) }}</div>
                                <div><span class="report-legend__swatch report-legend__swatch--utility"></span>{{ __('app.rental.reports.cards.utility_invoices') }}: {{ $money($report['invoice_mix']['utility']['invoiced']) }}</div>
                            </div>

                            <h2 class="h4 mt-4 mb-3">{{ __('app.rental.reports.charts.occupancy.heading') }}</h2>
                            <div class="report-pie report-pie--occupancy" style="--fill-angle: {{ round(($occupancyFilled / 100) * 360, 2) }}deg;"></div>
                            <div class="report-legend mt-3">
                                <div><span class="report-legend__swatch report-legend__swatch--occupied"></span>{{ __('app.rental.reports.charts.occupancy.occupied') }}: {{ $report['overview']['occupied_units'] }}</div>
                                <div><span class="report-legend__swatch report-legend__swatch--vacant"></span>{{ __('app.rental.reports.charts.occupancy.vacant') }}: {{ max($report['overview']['active_units'] - $report['overview']['occupied_units'], 0) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if (in_array($report['filters']['view'], ['table', 'split'], true))
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h2 class="h4 mb-1">{{ __('app.rental.reports.trend.heading') }}</h2>
                        <p class="text-body-secondary mb-0">{{ __('app.rental.reports.trend.description') }}</p>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('app.rental.reports.trend.columns.period') }}</th>
                                <th>{{ __('app.rental.reports.trend.columns.invoiced') }}</th>
                                <th>{{ __('app.rental.reports.trend.columns.collected') }}</th>
                                <th>{{ __('app.rental.reports.trend.columns.open') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($report['monthly_trend'] as $row)
                                <tr>
                                    <td>{{ $row['label'] }}</td>
                                    <td>{{ $money($row['invoiced']) }}</td>
                                    <td>{{ $money($row['collected']) }}</td>
                                    <td>{{ $money($row['open']) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-body-secondary">{{ __('app.rental.reports.empty') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h2 class="h4 mb-3">{{ __('app.rental.reports.property_performance.heading') }}</h2>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('app.client.navigation.properties') }}</th>
                                <th>{{ __('app.rental.reports.property_performance.columns.purchase_price') }}</th>
                                <th>{{ __('app.rental.reports.property_performance.columns.period_invoiced') }}</th>
                                <th>{{ __('app.rental.reports.property_performance.columns.period_collected') }}</th>
                                <th>{{ __('app.rental.reports.property_performance.columns.all_collected') }}</th>
                                <th>{{ __('app.rental.reports.property_performance.columns.open_balance') }}</th>
                                <th>{{ __('app.rental.reports.property_performance.columns.remaining') }}</th>
                                <th>{{ __('app.rental.reports.property_performance.columns.payback_rate') }}</th>
                                <th>{{ __('app.rental.reports.property_performance.columns.occupancy') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($report['property_performance'] as $row)
                                <tr>
                                    <td>{{ $row['property']->name }}</td>
                                    <td>{{ $money($row['purchase_price']) }}</td>
                                    <td>{{ $money($row['period_invoiced']) }}</td>
                                    <td>{{ $money($row['period_collected']) }}</td>
                                    <td>{{ $money($row['all_collected']) }}</td>
                                    <td>{{ $money($row['open_balance']) }}</td>
                                    <td>{{ $money($row['remaining_to_recoup']) }}</td>
                                    <td>{{ $percent($row['payback_rate']) }}</td>
                                    <td>{{ $percent($row['occupancy_rate']) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-body-secondary">{{ __('app.rental.reports.empty') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h2 class="h4 mb-3">{{ __('app.rental.reports.overdue.heading') }}</h2>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ __('app.rental.invoices.fields.number') }}</th>
                                        <th>{{ __('app.client.navigation.properties') }}</th>
                                        <th>{{ __('app.client.navigation.tenants') }}</th>
                                        <th>{{ __('app.rental.invoices.fields.due_date') }}</th>
                                        <th>{{ __('app.rental.reports.overdue.columns.days_late') }}</th>
                                        <th>{{ __('app.rental.reports.overdue.columns.outstanding') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($report['overdue_invoices'] as $row)
                                        <tr>
                                            <td>{{ $row['invoice']->number }}</td>
                                            <td>{{ $row['invoice']->lease->propertyUnit->property->name }} / {{ $row['invoice']->lease->propertyUnit->name }}</td>
                                            <td>{{ $row['invoice']->lease->tenantProfile->full_name }}</td>
                                            <td>{{ $row['invoice']->due_date->format('d.m.Y') }}</td>
                                            <td>{{ $row['days_late'] }}</td>
                                            <td>{{ $money($row['outstanding']) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="text-body-secondary">{{ __('app.rental.reports.empty') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h2 class="h4 mb-3">{{ __('app.rental.reports.tenants.heading') }}</h2>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ __('app.client.navigation.tenants') }}</th>
                                        <th>{{ __('app.rental.reports.tenants.columns.invoiced') }}</th>
                                        <th>{{ __('app.rental.reports.tenants.columns.paid') }}</th>
                                        <th>{{ __('app.rental.reports.tenants.columns.open') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($report['tenant_balances'] as $row)
                                        <tr>
                                            <td>
                                                {{ $row['tenant']->full_name }}
                                                <div class="small text-body-secondary">{{ __('app.rental.reports.tenants.columns.overdue_count') }}: {{ $row['overdue_count'] }}</div>
                                            </td>
                                            <td>{{ $money($row['invoiced']) }}</td>
                                            <td>{{ $money($row['paid']) }}</td>
                                            <td>{{ $money($row['open_balance']) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-body-secondary">{{ __('app.rental.reports.empty') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h2 class="h4 mb-3">{{ __('app.rental.reports.occupancy.heading') }}</h2>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ __('app.client.navigation.properties') }}</th>
                                        <th>{{ __('app.rental.reports.occupancy.columns.active_units') }}</th>
                                        <th>{{ __('app.rental.reports.occupancy.columns.occupied_units') }}</th>
                                        <th>{{ __('app.rental.reports.occupancy.columns.vacant_units') }}</th>
                                        <th>{{ __('app.rental.reports.occupancy.columns.rate') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($report['occupancy_by_property'] as $row)
                                        <tr>
                                            <td>{{ $row['property']->name }}</td>
                                            <td>{{ $row['active_units'] }}</td>
                                            <td>{{ $row['occupied_units'] }}</td>
                                            <td>{{ $row['vacant_units'] }}</td>
                                            <td>{{ $percent($row['occupancy_rate']) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-body-secondary">{{ __('app.rental.reports.empty') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h2 class="h4 mb-3">{{ __('app.rental.reports.vacant.heading') }}</h2>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ __('app.client.navigation.properties') }}</th>
                                        <th>{{ __('app.client.navigation.units') }}</th>
                                        <th>{{ __('app.rental.units.fields.code') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($report['vacant_units'] as $row)
                                        <tr>
                                            <td>{{ $row['property']->name }}</td>
                                            <td>{{ $row['unit']->name }}</td>
                                            <td>{{ $row['unit']->code }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="text-body-secondary">{{ __('app.rental.reports.empty') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
@endsection
