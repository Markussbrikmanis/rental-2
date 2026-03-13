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
        @elseif ($user->isTenant())
            @php($money = static fn ($value) => number_format((float) $value, 2, ',', ' ') . ' EUR')

            @if ($tenantDashboard['tenant_profile'] === null)
                <div class="alert alert-warning mb-0">
                    {{ __('app.client.panel.tenant.no_profile') }}
                </div>
            @else
                <div class="row g-4">
                    <div class="col-md-6 col-xl-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="report-card__label">{{ __('app.client.panel.tenant.kpis.active_contracts') }}</div>
                                <div class="report-card__value">{{ $tenantDashboard['counts']['active_leases'] }}</div>
                                <div class="text-body-secondary small">{{ __('app.client.panel.tenant.kpis.properties', ['count' => $tenantDashboard['counts']['properties']]) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="report-card__label">{{ __('app.client.panel.tenant.kpis.unpaid_invoices') }}</div>
                                <div class="report-card__value">{{ $tenantDashboard['counts']['unpaid_invoices'] }}</div>
                                <div class="text-body-secondary small">{{ __('app.client.panel.tenant.kpis.paid_invoices', ['count' => $tenantDashboard['counts']['paid_invoices']]) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="report-card__label">{{ __('app.client.panel.tenant.kpis.outstanding_total') }}</div>
                                <div class="report-card__value">{{ $money($tenantDashboard['outstanding_total']) }}</div>
                                <div class="text-body-secondary small">{{ __('app.client.panel.tenant.kpis.meters', ['count' => $tenantDashboard['counts']['meters']]) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="report-card__label">{{ __('app.client.panel.tenant.kpis.billing_name') }}</div>
                                <div class="report-card__value fs-5">{{ $tenantDashboard['tenant_profile']->billing_name ?: $tenantDashboard['tenant_profile']->full_name }}</div>
                                <div class="text-body-secondary small">{{ $tenantDashboard['tenant_profile']->email ?: $user->email }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                        <div>
                            <h2 class="h5 mb-2">{{ __('app.client.panel.tenant.quick_actions.heading') }}</h2>
                            <p class="text-body-secondary mb-0">{{ __('app.client.panel.tenant.quick_actions.description') }}</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('client.tenant-leases.index') }}" class="btn btn-outline-secondary">{{ __('app.client.navigation.my_contracts') }}</a>
                            <a href="{{ route('client.tenant-invoices.index') }}" class="btn btn-outline-secondary">{{ __('app.client.navigation.my_invoices') }}</a>
                            <a href="{{ route('client.tenant-meters.index') }}" class="btn btn-primary">{{ __('app.client.navigation.utility_readings') }}</a>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-xl-5">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h2 class="h5 mb-3">{{ __('app.client.panel.tenant.next_invoice.heading') }}</h2>
                                @if ($tenantDashboard['next_invoice'])
                                    <div class="owner-dashboard__stat-grid">
                                        <div class="owner-dashboard__stat">
                                            <span>{{ __('app.rental.invoices.fields.number') }}</span>
                                            <strong>{{ $tenantDashboard['next_invoice']['number'] }}</strong>
                                        </div>
                                        <div class="owner-dashboard__stat">
                                            <span>{{ __('app.rental.invoices.fields.due_date') }}</span>
                                            <strong>{{ $tenantDashboard['next_invoice']['due_date']->format('d.m.Y') }}</strong>
                                        </div>
                                        <div class="owner-dashboard__stat">
                                            <span>{{ __('app.rental.leases.fields.unit') }}</span>
                                            <strong>{{ $tenantDashboard['next_invoice']['property'] }} / {{ $tenantDashboard['next_invoice']['unit'] }}</strong>
                                        </div>
                                        <div class="owner-dashboard__stat">
                                            <span>{{ __('app.client.panel.tenant.next_invoice.outstanding') }}</span>
                                            <strong>{{ $money($tenantDashboard['next_invoice']['outstanding']) }}</strong>
                                        </div>
                                    </div>
                                    <a href="{{ route('client.tenant-invoices.show', $tenantDashboard['next_invoice']['id']) }}" class="btn btn-outline-primary mt-3">
                                        {{ __('app.client.panel.tenant.next_invoice.open') }}
                                    </a>
                                @else
                                    <p class="text-body-secondary mb-0">{{ __('app.client.panel.tenant.next_invoice.empty') }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-7">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h2 class="h5 mb-3">{{ __('app.client.panel.tenant.contracts.heading') }}</h2>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>{{ __('app.client.navigation.properties') }}</th>
                                                <th>{{ __('app.rental.leases.fields.start_date') }}</th>
                                                <th>{{ __('app.rental.leases.fields.status') }}</th>
                                                <th>{{ __('app.rental.leases.fields.deposit') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($tenantDashboard['leases'] as $lease)
                                                <tr>
                                                    <td>{{ $lease->propertyUnit->property->name }} / {{ $lease->propertyUnit->name }}</td>
                                                    <td>{{ $lease->start_date->format('d.m.Y') }}</td>
                                                    <td>{{ $lease->status->label() }}</td>
                                                    <td>{{ number_format((float) $lease->deposit, 2, ',', ' ') }} EUR</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="4" class="text-body-secondary">{{ __('app.client.panel.tenant.contracts.empty') }}</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between gap-3 align-items-center mb-3">
                            <h2 class="h5 mb-0">{{ __('app.client.panel.tenant.invoices.heading') }}</h2>
                            <a href="{{ route('client.tenant-invoices.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('app.client.navigation.my_invoices') }}</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ __('app.rental.invoices.fields.number') }}</th>
                                        <th>{{ __('app.rental.leases.fields.unit') }}</th>
                                        <th>{{ __('app.rental.invoices.fields.due_date') }}</th>
                                        <th>{{ __('app.rental.invoices.fields.status') }}</th>
                                        <th>{{ __('app.client.panel.tenant.invoices.outstanding') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($tenantDashboard['recent_invoices'] as $invoice)
                                        <tr>
                                            <td>{{ $invoice['number'] }}</td>
                                            <td>{{ $invoice['property'] }} / {{ $invoice['unit'] }}</td>
                                            <td>{{ $invoice['due_date']->format('d.m.Y') }}</td>
                                            <td>{{ $invoice['status'] }}</td>
                                            <td>{{ $money($invoice['outstanding']) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-body-secondary">{{ __('app.client.panel.tenant.invoices.empty') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        @endif

        @if ($user->isAdmin())
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
        @endif
    </div>
@endsection
