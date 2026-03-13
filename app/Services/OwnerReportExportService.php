<?php

namespace App\Services;

use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Arr;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OwnerReportExportService
{
    public function __construct(
        private readonly OwnerReportService $ownerReportService,
    ) {
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function download(User $owner, array $filters, string $format): Response|StreamedResponse
    {
        $report = $this->ownerReportService->build($owner, $filters);

        return match ($format) {
            'pdf' => $this->pdfResponse($report),
            'xlsx' => $this->xlsxResponse($report),
            default => $this->csvResponse($report),
        };
    }

    /**
     * @param  array<string, mixed>  $report
     */
    private function pdfResponse(array $report): Response
    {
        return Pdf::loadView('client.reports.document', ['report' => $report])
            ->setPaper('a4', 'landscape')
            ->download($this->fileName('pdf'));
    }

    /**
     * @param  array<string, mixed>  $report
     */
    private function csvResponse(array $report): StreamedResponse
    {
        return response()->streamDownload(function () use ($report): void {
            $handle = fopen('php://output', 'wb');

            if ($handle === false) {
                return;
            }

            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            foreach ($this->csvSections($report) as $section) {
                fputcsv($handle, [$section['title']]);
                fputcsv($handle, $section['headers']);

                foreach ($section['rows'] as $row) {
                    fputcsv($handle, $row);
                }

                fputcsv($handle, []);
            }

            fclose($handle);
        }, $this->fileName('csv'), [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @param  array<string, mixed>  $report
     */
    private function xlsxResponse(array $report): StreamedResponse
    {
        return response()->streamDownload(function () use ($report): void {
            $spreadsheet = new Spreadsheet();
            $sheetIndex = 0;

            foreach ($this->csvSections($report) as $section) {
                $sheet = $sheetIndex === 0
                    ? $spreadsheet->getActiveSheet()
                    : $spreadsheet->createSheet($sheetIndex);

                $sheet->setTitle(mb_substr($section['title'], 0, 31));
                $sheet->fromArray($section['headers'], null, 'A1');
                $sheet->fromArray($section['rows'], null, 'A2');
                $sheet->getStyle('1:1')->getFont()->setBold(true);
                $sheetIndex++;
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $this->fileName('xlsx'), [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * @param  array<string, mixed>  $report
     * @return array<int, array{title: string, headers: array<int, string>, rows: array<int, array<int, string>>}>
     */
    private function csvSections(array $report): array
    {
        $money = fn (mixed $value): string => number_format((float) $value, 2, '.', '');
        $percent = fn (mixed $value): string => number_format((float) $value, 2, '.', '').'%';

        return [
            [
                'title' => __('app.rental.reports.exports.sheets.overview'),
                'headers' => [
                    __('app.rental.reports.exports.columns.metric'),
                    __('app.rental.reports.exports.columns.value'),
                ],
                'rows' => [
                    [__('app.rental.reports.cards.period_invoiced'), $money($report['overview']['period_invoiced'])],
                    [__('app.rental.reports.cards.period_collected'), $money($report['overview']['period_collected'])],
                    [__('app.rental.reports.cards.period_outstanding'), $money($report['overview']['period_outstanding'])],
                    [__('app.rental.reports.cards.overdue_outstanding'), $money($report['overview']['overdue_outstanding'])],
                    [__('app.rental.reports.cards.occupancy'), $percent($report['overview']['occupancy_rate'])],
                    [__('app.rental.reports.cards.purchase_total'), $money($report['overview']['purchase_total'])],
                    [__('app.rental.reports.cards.all_collected'), $money($report['overview']['all_collected'])],
                    [__('app.rental.reports.cards.portfolio_payback'), $percent($report['overview']['portfolio_payback_rate'])],
                ],
            ],
            [
                'title' => __('app.rental.reports.exports.sheets.trend'),
                'headers' => [
                    __('app.rental.reports.trend.columns.period'),
                    __('app.rental.reports.trend.columns.invoiced'),
                    __('app.rental.reports.trend.columns.collected'),
                    __('app.rental.reports.trend.columns.open'),
                ],
                'rows' => collect($report['monthly_trend'])
                    ->map(fn (array $row): array => [
                        $row['label'],
                        $money($row['invoiced']),
                        $money($row['collected']),
                        $money($row['open']),
                    ])->all(),
            ],
            [
                'title' => __('app.rental.reports.exports.sheets.property_performance'),
                'headers' => [
                    __('app.client.navigation.properties'),
                    __('app.rental.reports.property_performance.columns.purchase_price'),
                    __('app.rental.reports.property_performance.columns.period_invoiced'),
                    __('app.rental.reports.property_performance.columns.period_collected'),
                    __('app.rental.reports.property_performance.columns.all_collected'),
                    __('app.rental.reports.property_performance.columns.open_balance'),
                    __('app.rental.reports.property_performance.columns.remaining'),
                    __('app.rental.reports.property_performance.columns.payback_rate'),
                ],
                'rows' => collect($report['property_performance'])
                    ->map(fn (array $row): array => [
                        $row['property']->name,
                        $money($row['purchase_price']),
                        $money($row['period_invoiced']),
                        $money($row['period_collected']),
                        $money($row['all_collected']),
                        $money($row['open_balance']),
                        $money($row['remaining_to_recoup']),
                        $percent($row['payback_rate']),
                    ])->all(),
            ],
            [
                'title' => __('app.rental.reports.exports.sheets.overdue'),
                'headers' => [
                    __('app.rental.invoices.fields.number'),
                    __('app.client.navigation.properties'),
                    __('app.client.navigation.tenants'),
                    __('app.rental.reports.overdue.columns.days_late'),
                    __('app.rental.reports.overdue.columns.outstanding'),
                ],
                'rows' => collect($report['overdue_invoices'])
                    ->map(fn (array $row): array => [
                        $row['invoice']->number,
                        $row['invoice']->lease->propertyUnit->property->name.' / '.$row['invoice']->lease->propertyUnit->name,
                        $row['invoice']->lease->tenantProfile->full_name,
                        (string) $row['days_late'],
                        $money($row['outstanding']),
                    ])->all(),
            ],
        ];
    }

    private function fileName(string $format): string
    {
        return 'reports-'.now()->format('Ymd-His').'.'.$format;
    }
}
