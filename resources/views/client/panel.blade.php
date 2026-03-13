@extends('client.layout', ['title' => __('app.client.panel.page_title')])

@section('content')
    <div class="vstack gap-4 py-4">
        <div>
            <span class="badge bg-primary text-uppercase mb-2">{{ $user->role->label() }}</span>
            <h1 class="h2 mb-1">{{ __('app.client.panel.heading') }}</h1>
            <p class="text-body-secondary mb-0">
                {{ __('app.client.panel.signed_in_as', ['name' => $user->name, 'email' => $user->email]) }}
            </p>
        </div>

        @if ($user->isOwner())
            @php
                $money = static fn ($value) => number_format((float) $value, 2, ',', ' ') . ' EUR';
                $percent = static fn ($value) => number_format((float) $value, 2, ',', ' ') . '%';
            @endphp

            <div class="row g-4">
                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="report-card__label">{{ __('app.client.panel.owner.kpis.properties') }}</div>
                            <div class="report-card__value">{{ $ownerDashboard['counts']['properties'] }}</div>
                            <div class="text-body-secondary small">{{ __('app.client.panel.owner.kpis.units', ['count' => $ownerDashboard['counts']['units']]) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="report-card__label">{{ __('app.client.panel.owner.kpis.tenants') }}</div>
                            <div class="report-card__value">{{ $ownerDashboard['counts']['tenants'] }}</div>
                            <div class="text-body-secondary small">{{ __('app.client.panel.owner.kpis.active_leases', ['count' => $ownerDashboard['counts']['active_leases']]) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="report-card__label">{{ __('app.client.panel.owner.kpis.paid_invoices') }}</div>
                            <div class="report-card__value">{{ $ownerDashboard['counts']['paid_invoices'] }}</div>
                            <div class="text-body-secondary small">{{ __('app.client.panel.owner.kpis.unpaid_invoices', ['count' => $ownerDashboard['counts']['unpaid_invoices']]) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="report-card__label">{{ __('app.client.panel.owner.kpis.monthly_collected') }}</div>
                            <div class="report-card__value">{{ $money($ownerDashboard['monthly']['period_collected']) }}</div>
                            <div class="text-body-secondary small">{{ __('app.client.panel.owner.kpis.collection_rate') }}: {{ $percent($ownerDashboard['monthly']['collection_rate']) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                    <div>
                        <h2 class="h5 mb-2">{{ __('app.properties.owner_panel.title') }}</h2>
                        <p class="text-body-secondary mb-0">{{ __('app.properties.owner_panel.description') }}</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('client.properties.create') }}" class="btn btn-outline-secondary">
                            {{ __('app.properties.actions.create') }}
                        </a>
                        <a href="{{ route('client.units.create') }}" class="btn btn-outline-secondary">
                            {{ __('app.rental.units.actions.create') }}
                        </a>
                        <a href="{{ route('client.tenants.create') }}" class="btn btn-outline-secondary">
                            {{ __('app.rental.tenants.actions.create') }}
                        </a>
                        <a href="{{ route('client.leases.index') }}" class="btn btn-outline-secondary">
                            {{ __('app.client.panel.owner.actions.generate_invoice') }}
                        </a>
                        <a href="{{ route('client.properties.index') }}" class="btn btn-primary">
                            {{ __('app.properties.actions.open_list') }}
                        </a>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-xl-5">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h2 class="h5 mb-3">{{ __('app.client.panel.owner.reports.heading') }}</h2>
                            <div class="owner-dashboard__stat-grid">
                                <div class="owner-dashboard__stat">
                                    <span>{{ __('app.client.panel.owner.reports.monthly_invoiced') }}</span>
                                    <strong>{{ $money($ownerDashboard['monthly']['period_invoiced']) }}</strong>
                                </div>
                                <div class="owner-dashboard__stat">
                                    <span>{{ __('app.client.panel.owner.reports.monthly_outstanding') }}</span>
                                    <strong>{{ $money($ownerDashboard['monthly']['period_outstanding']) }}</strong>
                                </div>
                                <div class="owner-dashboard__stat">
                                    <span>{{ __('app.client.panel.owner.reports.yearly_collected') }}</span>
                                    <strong>{{ $money($ownerDashboard['yearly']['period_collected']) }}</strong>
                                </div>
                                <div class="owner-dashboard__stat">
                                    <span>{{ __('app.client.panel.owner.reports.portfolio_payback') }}</span>
                                    <strong>{{ $percent($ownerDashboard['yearly']['portfolio_payback_rate']) }}</strong>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap gap-2 mt-3">
                                <a href="{{ route('client.reports.index') }}" class="btn btn-outline-primary">{{ __('app.client.panel.owner.actions.open_reports') }}</a>
                                <a href="{{ route('client.exports.index') }}" class="btn btn-outline-secondary">{{ __('app.client.panel.owner.actions.open_exports') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-7">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h2 class="h5 mb-3">{{ __('app.client.panel.owner.portfolio.heading') }}</h2>
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>{{ __('app.client.navigation.properties') }}</th>
                                            <th>{{ __('app.client.panel.owner.portfolio.all_collected') }}</th>
                                            <th>{{ __('app.client.panel.owner.portfolio.open_balance') }}</th>
                                            <th>{{ __('app.client.panel.owner.portfolio.payback_rate') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($ownerDashboard['top_properties'] as $row)
                                            <tr>
                                                <td>{{ $row['property']->name }}</td>
                                                <td>{{ $money($row['all_collected']) }}</td>
                                                <td>{{ $money($row['open_balance']) }}</td>
                                                <td>{{ $percent($row['payback_rate']) }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-body-secondary">{{ __('app.client.panel.owner.empty') }}</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-xl-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between gap-3 align-items-center mb-3">
                                <h2 class="h5 mb-0">{{ __('app.client.panel.owner.invoices.recent_heading') }}</h2>
                                <a href="{{ route('client.invoices.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('app.client.panel.owner.actions.open_invoices') }}</a>
                            </div>
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>{{ __('app.rental.invoices.fields.number') }}</th>
                                            <th>{{ __('app.client.navigation.properties') }}</th>
                                            <th>{{ __('app.client.navigation.tenants') }}</th>
                                            <th>{{ __('app.client.panel.owner.invoices.outstanding') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($ownerDashboard['recent_invoices'] as $invoice)
                                            <tr>
                                                <td>
                                                    {{ $invoice['number'] }}
                                                    <div class="small text-body-secondary">{{ $invoice['status'] }}</div>
                                                </td>
                                                <td>{{ $invoice['property'] }} / {{ $invoice['unit'] }}</td>
                                                <td>{{ $invoice['tenant'] }}</td>
                                                <td>{{ $money($invoice['outstanding']) }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-body-secondary">{{ __('app.client.panel.owner.empty') }}</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between gap-3 align-items-center mb-3">
                                <h2 class="h5 mb-0">{{ __('app.client.panel.owner.invoices.overdue_heading') }}</h2>
                                <a href="{{ route('client.reports.index', ['view' => 'table']) }}" class="btn btn-sm btn-outline-secondary">{{ __('app.client.panel.owner.actions.open_reports') }}</a>
                            </div>
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>{{ __('app.rental.invoices.fields.number') }}</th>
                                            <th>{{ __('app.client.navigation.properties') }}</th>
                                            <th>{{ __('app.rental.invoices.fields.due_date') }}</th>
                                            <th>{{ __('app.client.panel.owner.invoices.outstanding') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($ownerDashboard['overdue_invoices'] as $invoice)
                                            <tr>
                                                <td>
                                                    {{ $invoice['number'] }}
                                                    <div class="small text-body-secondary">{{ $invoice['tenant'] }}</div>
                                                </td>
                                                <td>{{ $invoice['property'] }}</td>
                                                <td>{{ $invoice['due_date']->format('d.m.Y') }}</td>
                                                <td>{{ $money($invoice['outstanding']) }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-body-secondary">{{ __('app.client.panel.owner.no_overdue') }}</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h2 class="h5 mb-3">{{ __('app.client.panel.seeded_accounts') }}</h2>
                <ul class="mb-0 ps-3">
                    <li><strong>admin@example.com</strong> / <code>password</code></li>
                    <li><strong>owner@example.com</strong> / <code>password</code></li>
                    <li><strong>tenant@example.com</strong> / <code>password</code></li>
                </ul>
            </div>
        </div>
    </div>
@endsection
