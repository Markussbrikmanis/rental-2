<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Meter;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\TenantProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OwnerExportService
{
    /**
     * @return array<string, array{label: string, description: string, count: int}>
     */
    public function options(User $owner): array
    {
        $datasets = $this->datasetDefinitions($owner);
        $options = [];

        foreach ($datasets as $key => $definition) {
            $options[$key] = [
                'label' => $definition['label'],
                'description' => $definition['description'],
                'count' => (clone $definition['query'])->count(),
            ];
        }

        return $options;
    }

    public function download(User $owner, string $dataset, string $format): StreamedResponse
    {
        $definition = $this->datasetDefinitions($owner)[$dataset];
        $rows = $definition['rows']($definition['query']->get());
        $fileName = $this->fileName($dataset, $format);

        return $format === 'csv'
            ? $this->csvResponse($fileName, $definition['columns'], $rows)
            : $this->xlsxResponse($fileName, $definition['label'], $definition['columns'], $rows);
    }

    /**
     * @return array<string, array{
     *     label: string,
     *     description: string,
     *     columns: array<int, string>,
     *     query: Builder,
     *     rows: \Closure(Collection<int, mixed>): array<int, array<int, scalar|null>>
     * }>
     */
    private function datasetDefinitions(User $owner): array
    {
        return [
            'properties' => [
                'label' => __('app.rental.exports.datasets.properties.label'),
                'description' => __('app.rental.exports.datasets.properties.description'),
                'columns' => [
                    __('app.rental.exports.columns.id'),
                    __('app.properties.fields.name'),
                    __('app.properties.fields.address'),
                    __('app.properties.fields.city'),
                    __('app.properties.fields.country'),
                    __('app.properties.fields.price'),
                    __('app.properties.fields.type'),
                    __('app.properties.fields.acquired_at'),
                    __('app.properties.fields.notes'),
                    __('app.rental.exports.columns.created_at'),
                ],
                'query' => Property::query()
                    ->where('user_id', $owner->id)
                    ->latest(),
                'rows' => fn (Collection $items): array => $items
                    ->map(fn (Property $property): array => [
                        $property->id,
                        $property->name,
                        $property->address,
                        $property->city,
                        $property->country,
                        $this->money($property->price),
                        $property->type->label(),
                        $this->date($property->acquired_at),
                        $property->notes,
                        $this->dateTime($property->created_at),
                    ])
                    ->all(),
            ],
            'units' => [
                'label' => __('app.rental.exports.datasets.units.label'),
                'description' => __('app.rental.exports.datasets.units.description'),
                'columns' => [
                    __('app.rental.exports.columns.id'),
                    __('app.rental.units.fields.property'),
                    __('app.rental.units.fields.name'),
                    __('app.rental.units.fields.code'),
                    __('app.rental.units.fields.status'),
                    __('app.rental.units.fields.area'),
                    __('app.rental.units.fields.unit_type'),
                    __('app.rental.units.fields.is_active'),
                    __('app.properties.fields.notes'),
                    __('app.rental.exports.columns.created_at'),
                ],
                'query' => PropertyUnit::query()
                    ->whereHas('property', fn (Builder $query) => $query->where('user_id', $owner->id))
                    ->with('property')
                    ->latest(),
                'rows' => fn (Collection $items): array => $items
                    ->map(fn (PropertyUnit $unit): array => [
                        $unit->id,
                        $unit->property->name,
                        $unit->name,
                        $unit->code,
                        $unit->status->label(),
                        $this->decimal($unit->area),
                        $unit->unit_type,
                        $unit->is_active ? __('app.rental.exports.values.yes') : __('app.rental.exports.values.no'),
                        $unit->notes,
                        $this->dateTime($unit->created_at),
                    ])
                    ->all(),
            ],
            'tenants' => [
                'label' => __('app.rental.exports.datasets.tenants.label'),
                'description' => __('app.rental.exports.datasets.tenants.description'),
                'columns' => [
                    __('app.rental.exports.columns.id'),
                    __('app.rental.tenants.fields.full_name'),
                    __('app.rental.tenants.fields.company_name'),
                    __('app.client.common.email'),
                    __('app.rental.tenants.fields.phone'),
                    __('app.rental.tenants.fields.personal_code'),
                    __('app.rental.tenants.fields.registration_number'),
                    __('app.rental.tenants.fields.billing_name'),
                    __('app.rental.tenants.fields.billing_address'),
                    __('app.rental.tenants.fields.billing_registration_number'),
                    __('app.rental.tenants.fields.billing_vat_number'),
                    __('app.properties.fields.notes'),
                    __('app.rental.exports.columns.created_at'),
                ],
                'query' => TenantProfile::query()
                    ->where('owner_id', $owner->id)
                    ->latest(),
                'rows' => fn (Collection $items): array => $items
                    ->map(fn (TenantProfile $tenant): array => [
                        $tenant->id,
                        $tenant->full_name,
                        $tenant->company_name,
                        $tenant->email,
                        $tenant->phone,
                        $tenant->personal_code,
                        $tenant->registration_number,
                        $tenant->billing_name,
                        $tenant->billing_address,
                        $tenant->billing_registration_number,
                        $tenant->billing_vat_number,
                        $tenant->notes,
                        $this->dateTime($tenant->created_at),
                    ])
                    ->all(),
            ],
            'leases' => [
                'label' => __('app.rental.exports.datasets.leases.label'),
                'description' => __('app.rental.exports.datasets.leases.description'),
                'columns' => [
                    __('app.rental.exports.columns.id'),
                    __('app.rental.units.fields.property'),
                    __('app.rental.leases.fields.unit'),
                    __('app.rental.leases.fields.tenant'),
                    __('app.rental.leases.fields.start_date'),
                    __('app.rental.leases.fields.end_date'),
                    __('app.rental.leases.fields.billing_start_date'),
                    __('app.rental.leases.fields.due_day'),
                    __('app.rental.leases.fields.currency'),
                    __('app.rental.leases.fields.status'),
                    __('app.rental.leases.fields.deposit'),
                    __('app.properties.fields.notes'),
                    __('app.rental.exports.columns.created_at'),
                ],
                'query' => Lease::query()
                    ->whereHas('propertyUnit.property', fn (Builder $query) => $query->where('user_id', $owner->id))
                    ->with(['propertyUnit.property', 'tenantProfile'])
                    ->latest(),
                'rows' => fn (Collection $items): array => $items
                    ->map(fn (Lease $lease): array => [
                        $lease->id,
                        $lease->propertyUnit->property->name,
                        $lease->propertyUnit->name,
                        $lease->tenantProfile->full_name,
                        $this->date($lease->start_date),
                        $this->date($lease->end_date),
                        $this->date($lease->billing_start_date),
                        $lease->due_day,
                        $lease->currency,
                        $lease->status->label(),
                        $this->money($lease->deposit),
                        $lease->notes,
                        $this->dateTime($lease->created_at),
                    ])
                    ->all(),
            ],
            'invoices' => [
                'label' => __('app.rental.exports.datasets.invoices.label'),
                'description' => __('app.rental.exports.datasets.invoices.description'),
                'columns' => [
                    __('app.rental.exports.columns.id'),
                    __('app.rental.invoices.fields.number'),
                    __('app.rental.invoices.fields.kind'),
                    __('app.rental.invoices.fields.status'),
                    __('app.rental.units.fields.property'),
                    __('app.rental.leases.fields.unit'),
                    __('app.rental.leases.fields.tenant'),
                    __('app.rental.invoices.fields.issue_date'),
                    __('app.rental.invoices.fields.due_date'),
                    __('app.rental.invoices.fields.period_from'),
                    __('app.rental.invoices.fields.period_to'),
                    __('app.rental.exports.columns.subtotal'),
                    __('app.rental.invoices.fields.total'),
                    __('app.rental.exports.columns.sent_at'),
                    __('app.rental.exports.columns.created_at'),
                ],
                'query' => Invoice::query()
                    ->whereHas('lease.propertyUnit.property', fn (Builder $query) => $query->where('user_id', $owner->id))
                    ->with(['lease.propertyUnit.property', 'lease.tenantProfile'])
                    ->latest(),
                'rows' => fn (Collection $items): array => $items
                    ->map(fn (Invoice $invoice): array => [
                        $invoice->id,
                        $invoice->number,
                        $invoice->kind->label(),
                        $invoice->status->label(),
                        $invoice->lease->propertyUnit->property->name,
                        $invoice->lease->propertyUnit->name,
                        $invoice->lease->tenantProfile->full_name,
                        $this->date($invoice->issue_date),
                        $this->date($invoice->due_date),
                        $this->date($invoice->period_from),
                        $this->date($invoice->period_to),
                        $this->money($invoice->subtotal),
                        $this->money($invoice->total),
                        $this->dateTime($invoice->sent_at),
                        $this->dateTime($invoice->created_at),
                    ])
                    ->all(),
            ],
            'meters' => [
                'label' => __('app.rental.exports.datasets.meters.label'),
                'description' => __('app.rental.exports.datasets.meters.description'),
                'columns' => [
                    __('app.rental.exports.columns.id'),
                    __('app.rental.units.fields.property'),
                    __('app.rental.meters.fields.unit'),
                    __('app.rental.meters.fields.name'),
                    __('app.rental.meters.fields.type'),
                    __('app.rental.meters.fields.measurement_unit'),
                    __('app.rental.meters.fields.utility_billing_mode'),
                    __('app.rental.meters.fields.rate_per_unit'),
                    __('app.rental.meters.fields.is_active'),
                    __('app.rental.exports.columns.created_at'),
                ],
                'query' => Meter::query()
                    ->whereHas('propertyUnit.property', fn (Builder $query) => $query->where('user_id', $owner->id))
                    ->with(['propertyUnit.property'])
                    ->latest(),
                'rows' => fn (Collection $items): array => $items
                    ->map(fn (Meter $meter): array => [
                        $meter->id,
                        $meter->propertyUnit->property->name,
                        $meter->propertyUnit->name,
                        $meter->name,
                        $meter->type->label(),
                        $meter->unit,
                        $meter->utility_billing_mode->label(),
                        $this->money($meter->rate_per_unit),
                        $meter->is_active ? __('app.rental.exports.values.yes') : __('app.rental.exports.values.no'),
                        $this->dateTime($meter->created_at),
                    ])
                    ->all(),
            ],
        ];
    }

    /**
     * @param  array<int, string>  $columns
     * @param  array<int, array<int, scalar|null>>  $rows
     */
    private function csvResponse(string $fileName, array $columns, array $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($columns, $rows): void {
            $handle = fopen('php://output', 'wb');

            if ($handle === false) {
                return;
            }

            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, $columns);

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @param  array<int, string>  $columns
     * @param  array<int, array<int, scalar|null>>  $rows
     */
    private function xlsxResponse(string $fileName, string $sheetTitle, array $columns, array $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($columns, $rows, $sheetTitle): void {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle(mb_substr($sheetTitle, 0, 31));
            $sheet->fromArray($columns, null, 'A1');
            $sheet->fromArray($rows, null, 'A2');
            $sheet->getStyle('A1:'.$this->lastColumn(count($columns)).'1')->getFont()->setBold(true);

            foreach (range(1, count($columns)) as $index) {
                $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($index))->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function fileName(string $dataset, string $format): string
    {
        return $dataset.'-'.now()->format('Ymd-His').'.'.$format;
    }

    private function lastColumn(int $columnCount): string
    {
        return Coordinate::stringFromColumnIndex($columnCount);
    }

    private function date(mixed $value): ?string
    {
        if (! $value instanceof Carbon) {
            return $value ? (string) $value : null;
        }

        return $value->format('Y-m-d');
    }

    private function dateTime(mixed $value): ?string
    {
        if (! $value instanceof Carbon) {
            return $value ? (string) $value : null;
        }

        return $value->format('Y-m-d H:i:s');
    }

    private function money(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return number_format((float) $value, 2, '.', '');
    }

    private function decimal(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return number_format((float) $value, 2, '.', '');
    }
}
