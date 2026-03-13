<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <title>{{ __('app.rental.reports.index.page_title') }}</title>
        <style>
            body { font-family: DejaVu Sans, sans-serif; color: #111; font-size: 12px; }
            h1, h2 { margin: 0 0 10px; }
            .muted { color: #555; margin-bottom: 14px; }
            .grid { width: 100%; margin-bottom: 22px; }
            .metric { display: inline-block; width: 24%; vertical-align: top; margin-bottom: 12px; }
            .metric strong { display: block; font-size: 16px; margin-top: 4px; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
            th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; vertical-align: top; }
            th { background: #f3f5f8; }
        </style>
    </head>
    <body>
        @php
            $money = static fn ($value) => number_format((float) $value, 2, ',', ' ') . ' EUR';
            $percent = static fn ($value) => number_format((float) $value, 2, ',', ' ') . '%';
        @endphp

        <h1>{{ __('app.rental.reports.index.heading') }}</h1>
        <div class="muted">{{ __('app.rental.reports.index.period_label') }}: {{ $report['filters']['label'] }}</div>

        <div class="grid">
            <div class="metric">{{ __('app.rental.reports.cards.period_invoiced') }}<strong>{{ $money($report['overview']['period_invoiced']) }}</strong></div>
            <div class="metric">{{ __('app.rental.reports.cards.period_collected') }}<strong>{{ $money($report['overview']['period_collected']) }}</strong></div>
            <div class="metric">{{ __('app.rental.reports.cards.period_outstanding') }}<strong>{{ $money($report['overview']['period_outstanding']) }}</strong></div>
            <div class="metric">{{ __('app.rental.reports.cards.occupancy') }}<strong>{{ $percent($report['overview']['occupancy_rate']) }}</strong></div>
        </div>

        <h2>{{ __('app.rental.reports.trend.heading') }}</h2>
        <table>
            <thead>
                <tr>
                    <th>{{ __('app.rental.reports.trend.columns.period') }}</th>
                    <th>{{ __('app.rental.reports.trend.columns.invoiced') }}</th>
                    <th>{{ __('app.rental.reports.trend.columns.collected') }}</th>
                    <th>{{ __('app.rental.reports.trend.columns.open') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($report['monthly_trend'] as $row)
                    <tr>
                        <td>{{ $row['label'] }}</td>
                        <td>{{ $money($row['invoiced']) }}</td>
                        <td>{{ $money($row['collected']) }}</td>
                        <td>{{ $money($row['open']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <h2>{{ __('app.rental.reports.property_performance.heading') }}</h2>
        <table>
            <thead>
                <tr>
                    <th>{{ __('app.client.navigation.properties') }}</th>
                    <th>{{ __('app.rental.reports.property_performance.columns.purchase_price') }}</th>
                    <th>{{ __('app.rental.reports.property_performance.columns.all_collected') }}</th>
                    <th>{{ __('app.rental.reports.property_performance.columns.open_balance') }}</th>
                    <th>{{ __('app.rental.reports.property_performance.columns.remaining') }}</th>
                    <th>{{ __('app.rental.reports.property_performance.columns.payback_rate') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($report['property_performance'] as $row)
                    <tr>
                        <td>{{ $row['property']->name }}</td>
                        <td>{{ $money($row['purchase_price']) }}</td>
                        <td>{{ $money($row['all_collected']) }}</td>
                        <td>{{ $money($row['open_balance']) }}</td>
                        <td>{{ $money($row['remaining_to_recoup']) }}</td>
                        <td>{{ $percent($row['payback_rate']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </body>
</html>
