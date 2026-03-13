<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Carbon\CarbonInterface;

class InvoiceStatusService
{
    public function recalculate(Invoice $invoice, ?CarbonInterface $today = null): Invoice
    {
        $this->syncTotals($invoice);

        return $this->refresh($invoice->fresh(['payments']), $today);
    }

    public function refresh(Invoice $invoice, ?CarbonInterface $today = null): Invoice
    {
        $today ??= now();
        $this->syncTotals($invoice);

        if ($invoice->status === InvoiceStatus::Cancelled) {
            return $invoice;
        }

        $paidAmount = (float) $invoice->payments()->sum('amount');
        $total = (float) $invoice->total;

        if ($total > 0 && $paidAmount >= $total) {
            $invoice->status = InvoiceStatus::Paid;
        } elseif ($invoice->due_date->lt($today)) {
            $invoice->status = InvoiceStatus::Overdue;
        } elseif ($invoice->status !== InvoiceStatus::Draft) {
            $invoice->status = InvoiceStatus::Issued;
        }

        $invoice->save();

        return $invoice;
    }

    public function refreshAll(?CarbonInterface $today = null): void
    {
        Invoice::query()
            ->where('status', '!=', InvoiceStatus::Cancelled->value)
            ->with('payments')
            ->get()
            ->each(fn (Invoice $invoice) => $this->refresh($invoice, $today));
    }

    private function syncTotals(Invoice $invoice): void
    {
        $invoice->loadMissing('lease.propertyUnit.property.user');

        $subtotal = (float) $invoice->lines()->sum('line_total');
        $owner = $invoice->lease->propertyUnit->property->user;
        $vatRate = $owner->invoice_vat_enabled ? (float) $owner->invoice_vat_rate : 0.0;
        $vatTotal = round($subtotal * ($vatRate / 100), 2);

        $invoice->forceFill([
            'subtotal' => round($subtotal, 2),
            'total' => round($subtotal + $vatTotal, 2),
        ])->save();
    }
}
