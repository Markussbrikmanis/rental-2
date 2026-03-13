<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <title>{{ $invoice->number }}</title>
        <style>
            * { box-sizing: border-box; }
            body {
                margin: 0;
                font-family: DejaVu Sans, sans-serif;
                background: #f2f2f2;
                color: #111111;
            }
            .invoice-page {
                width: 210mm;
                min-height: 297mm;
                margin: 0 auto;
                background: #ffffff;
                padding: 28mm 20mm 26mm;
            }
            .invoice-toolbar {
                width: 210mm;
                margin: 18px auto 0;
                display: flex;
                justify-content: flex-end;
                gap: 12px;
            }
            .invoice-toolbar a,
            .invoice-toolbar button {
                border: 1px solid #111111;
                background: #ffffff;
                color: #111111;
                padding: 10px 14px;
                border-radius: 999px;
                font-size: 13px;
                text-decoration: none;
                cursor: pointer;
            }
            .invoice-header {
                display: table;
                width: 100%;
                margin-bottom: 42px;
            }
            .invoice-header__left,
            .invoice-header__right {
                display: table-cell;
                vertical-align: top;
                width: 50%;
            }
            .invoice-header__right {
                text-align: right;
            }
            .invoice-title {
                font-size: 24px;
                line-height: 1.2;
                font-weight: 700;
                margin: 0 0 8px;
            }
            .invoice-date {
                font-size: 14px;
                font-weight: 700;
                margin: 0;
            }
            .invoice-logo {
                max-width: 200px;
                max-height: 90px;
            }
            .invoice-parties {
                display: table;
                width: 100%;
                margin-bottom: 38px;
            }
            .invoice-party {
                display: table-cell;
                width: 50%;
                vertical-align: top;
                padding-right: 28px;
            }
            .invoice-party:last-child {
                padding-right: 0;
            }
            .invoice-party__title {
                font-size: 14px;
                font-weight: 700;
                margin: 0 0 12px;
            }
            .invoice-party__row {
                display: table;
                width: 100%;
                margin-bottom: 7px;
            }
            .invoice-party__label,
            .invoice-party__value {
                display: table-cell;
                vertical-align: top;
                font-size: 12px;
                line-height: 1.45;
            }
            .invoice-party__label {
                width: 44%;
                padding-right: 12px;
                font-weight: 700;
            }
            .invoice-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 32px;
            }
            .invoice-table th,
            .invoice-table td {
                border: 1px solid #5d5d5d;
                padding: 7px 10px;
                font-size: 12px;
                vertical-align: top;
            }
            .invoice-table th {
                font-weight: 700;
                text-align: left;
            }
            .invoice-table th.num,
            .invoice-table td.num {
                text-align: right;
                white-space: nowrap;
            }
            .invoice-table__ghost {
                border: none !important;
                padding: 0 !important;
            }
            .invoice-table__summary-label {
                font-weight: 700;
                border-left: 1px solid #5d5d5d !important;
            }
            .invoice-table__summary-value {
                text-align: right;
                white-space: nowrap;
            }
            .invoice-meta {
                display: table;
                width: 100%;
                margin-bottom: 10px;
            }
            .invoice-meta__label,
            .invoice-meta__value {
                display: table-cell;
                vertical-align: top;
                font-size: 12px;
                line-height: 1.5;
                padding-bottom: 8px;
            }
            .invoice-meta__label {
                width: 34%;
                font-weight: 700;
                padding-right: 16px;
            }
            .invoice-meta--spaced {
                margin-bottom: 34px;
            }
            .invoice-footer {
                font-size: 12px;
                line-height: 1.6;
                max-width: 78%;
            }
            .muted {
                color: #4f4f4f;
            }
            .text-right {
                text-align: right;
            }
            @media print {
                body {
                    background: #ffffff;
                }
                .invoice-toolbar {
                    display: none;
                }
                .invoice-page {
                    width: auto;
                    min-height: auto;
                    margin: 0;
                    padding: 0;
                }
            }
        </style>
    </head>
    <body>
        @if ($render_mode === 'browser')
            <div class="invoice-toolbar">
                <a href="{{ route('client.invoices.show', $invoice) }}">{{ __('app.rental.documents.actions.back_to_invoice') }}</a>
                <a href="{{ route('client.invoices.download', $invoice) }}">{{ __('app.rental.documents.actions.download_pdf') }}</a>
                <button type="button" onclick="window.print()">{{ __('app.rental.documents.actions.print') }}</button>
            </div>
        @endif

        <div class="invoice-page">
            <div class="invoice-header">
                <div class="invoice-header__left">
                    <h1 class="invoice-title">{{ __('app.rental.documents.invoice_title', ['number' => $invoice->number]) }}</h1>
                    <p class="invoice-date">{{ $invoice->issue_date->locale(app()->getLocale())->translatedFormat('Y. \\g\\a\\d\\a j. F') }}</p>
                </div>
                <div class="invoice-header__right">
                    @if ($logo_data_uri)
                        <img src="{{ $logo_data_uri }}" alt="Logo" class="invoice-logo">
                    @endif
                </div>
            </div>

            <div class="invoice-parties">
                <div class="invoice-party">
                    <p class="invoice-party__title">{{ __('app.rental.documents.payer_title') }}</p>
                    @foreach ([
                        __('app.rental.documents.fields.name') => $recipient['name'],
                        __('app.rental.documents.fields.address') => $recipient['address'],
                        __('app.rental.documents.fields.registration_number') => $recipient['registration_number'],
                        __('app.rental.documents.fields.vat_number') => $recipient['vat_number'],
                        __('app.rental.documents.fields.bank_name') => $recipient['bank_name'],
                        __('app.rental.documents.fields.swift_code') => $recipient['swift_code'],
                        __('app.rental.documents.fields.account_number') => $recipient['account_number'],
                    ] as $label => $value)
                        @if (filled($value))
                            <div class="invoice-party__row">
                                <div class="invoice-party__label">{{ $label }}</div>
                                <div class="invoice-party__value">{!! nl2br(e($value)) !!}</div>
                            </div>
                        @endif
                    @endforeach
                </div>

                <div class="invoice-party">
                    <p class="invoice-party__title">{{ __('app.rental.documents.sender_title') }}</p>
                    @foreach ([
                        __('app.rental.documents.fields.name') => $sender['name'],
                        __('app.rental.documents.fields.address') => $sender['address'],
                        __('app.rental.documents.fields.registration_number') => $sender['registration_number'],
                        __('app.rental.documents.fields.vat_number') => $sender['vat_number'],
                        __('app.rental.documents.fields.bank_name') => $sender['bank_name'],
                        __('app.rental.documents.fields.swift_code') => $sender['swift_code'],
                        __('app.rental.documents.fields.account_number') => $sender['account_number'],
                    ] as $label => $value)
                        @if (filled($value))
                            <div class="invoice-party__row">
                                <div class="invoice-party__label">{{ $label }}</div>
                                <div class="invoice-party__value">{!! nl2br(e($value)) !!}</div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <table class="invoice-table">
                <thead>
                    <tr>
                        <th>{{ __('app.rental.documents.table.description') }}</th>
                        <th>{{ __('app.rental.documents.table.unit') }}</th>
                        <th class="num">{{ __('app.rental.documents.table.quantity') }}</th>
                        <th class="num">{{ __('app.rental.documents.table.unit_price') }}</th>
                        <th class="num">{{ __('app.rental.documents.table.line_total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoice->lines as $line)
                        <tr>
                            <td>{{ $line->description }}</td>
                            <td>
                                @if ($line->source_type === \App\Models\Meter::class)
                                    {{ $line->source?->unit ?: __('app.rental.documents.default_unit') }}
                                @else
                                    {{ __('app.rental.documents.default_unit') }}
                                @endif
                            </td>
                            <td class="num">{{ number_format((float) $line->quantity, 2, ',', ' ') }}</td>
                            <td class="num">{{ number_format((float) $line->unit_price, 2, ',', ' ') }}</td>
                            <td class="num">{{ number_format((float) $line->line_total, 2, ',', ' ') }}</td>
                        </tr>
                    @endforeach
                    @if ($vat_enabled)
                        <tr>
                            <td class="invoice-table__ghost" colspan="3"></td>
                            <td class="invoice-table__summary-label">{{ __('app.rental.documents.summary.subtotal_without_tax') }}</td>
                            <td class="invoice-table__summary-value">{{ number_format($subtotal_without_tax, 2, ',', ' ') }}</td>
                        </tr>
                        <tr>
                            <td class="invoice-table__ghost" colspan="3"></td>
                            <td class="invoice-table__summary-label">{{ __('app.rental.documents.summary.tax_total', ['rate' => rtrim(rtrim(number_format($vat_rate, 2, '.', ''), '0'), '.')]) }}</td>
                            <td class="invoice-table__summary-value">{{ number_format($tax_total, 2, ',', ' ') }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td class="invoice-table__ghost" colspan="3"></td>
                        <td class="invoice-table__summary-label">{{ __('app.rental.documents.summary.total') }}</td>
                        <td class="invoice-table__summary-value">{{ number_format((float) $invoice->total, 2, ',', ' ') }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="invoice-meta">
                <div class="invoice-meta__label">{{ __('app.rental.documents.summary.total_in_words') }}</div>
                <div class="invoice-meta__value">{{ $total_in_words }}</div>
            </div>
            <div class="invoice-meta invoice-meta--spaced">
                <div class="invoice-meta__label">{{ __('app.rental.documents.summary.payment_terms') }}</div>
                <div class="invoice-meta__value">{!! nl2br(e($payment_terms_text)) !!}</div>
            </div>

            <div class="invoice-footer">
                {!! nl2br(e($footer_text)) !!}
            </div>
        </div>
    </body>
</html>
