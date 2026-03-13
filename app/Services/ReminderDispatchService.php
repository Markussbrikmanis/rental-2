<?php

namespace App\Services;

use App\Enums\NotificationChannel;
use App\Mail\InvoiceMail;
use App\Models\Invoice;
use App\Models\InvoiceReminder;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class ReminderDispatchService
{
    public function __construct(
        private readonly InvoiceStatusService $invoiceStatusService,
        private readonly InvoiceDocumentService $invoiceDocumentService,
    ) {
    }

    /**
     * @return Collection<int, InvoiceReminder>
     */
    public function sendOverdueReminders(CarbonInterface $targetDate): Collection
    {
        $this->invoiceStatusService->refreshAll($targetDate);

        $invoices = Invoice::query()
            ->with(['lease.tenantProfile', 'payments', 'reminders'])
            ->whereIn('status', [\App\Enums\InvoiceStatus::Issued->value, \App\Enums\InvoiceStatus::Overdue->value])
            ->whereDate('due_date', '<=', $targetDate)
            ->get()
            ->filter(fn (Invoice $invoice) => (float) $invoice->payments()->sum('amount') < (float) $invoice->total);

        $sentLogs = collect();

        foreach ($invoices as $invoice) {
            $tenantEmail = $invoice->lease->tenantProfile?->email;

            if (! $tenantEmail) {
                continue;
            }

            $alreadySent = $invoice->reminders()
                ->where('kind', 'overdue')
                ->whereDate('sent_at', $targetDate)
                ->exists();

            if ($alreadySent) {
                continue;
            }

            try {
                Mail::raw(
                    __('app.rental.reminders.email_body', ['number' => $invoice->number]),
                    function ($message) use ($tenantEmail, $invoice): void {
                        $message
                            ->to($tenantEmail)
                            ->subject(__('app.rental.reminders.email_subject', ['number' => $invoice->number]));
                    },
                );

                $sentLogs->push($invoice->reminders()->create([
                    'kind' => 'overdue',
                    'channel' => NotificationChannel::Email,
                    'status' => 'sent',
                    'recipient' => $tenantEmail,
                    'sent_at' => $targetDate,
                ]));
            } catch (\Throwable $throwable) {
                $sentLogs->push($invoice->reminders()->create([
                    'kind' => 'overdue',
                    'channel' => NotificationChannel::Email,
                    'status' => 'failed',
                    'recipient' => $tenantEmail,
                    'error_message' => $throwable->getMessage(),
                ]));
            }
        }

        return $sentLogs;
    }

    public function sendInvoice(Invoice $invoice): InvoiceReminder
    {
        $tenantEmail = $invoice->lease->tenantProfile?->email;

        if (! $tenantEmail) {
            return $invoice->reminders()->create([
                'kind' => 'invoice',
                'channel' => NotificationChannel::Email,
                'status' => 'failed',
                'error_message' => __('app.rental.reminders.no_email'),
            ]);
        }

        try {
            $loadedInvoice = $invoice->loadMissing([
                'lease.propertyUnit.property.user',
                'lease.tenantProfile',
                'lines',
                'payments',
            ]);

            Mail::to($tenantEmail)->send(
                new InvoiceMail(
                    $loadedInvoice,
                    $this->invoiceDocumentService->pdfBinary($loadedInvoice),
                    $this->invoiceDocumentService->fileName($loadedInvoice),
                ),
            );

            $invoice->forceFill([
                'sent_at' => now(),
            ])->save();

            return $invoice->reminders()->create([
                'kind' => 'invoice',
                'channel' => NotificationChannel::Email,
                'status' => 'sent',
                'recipient' => $tenantEmail,
                'sent_at' => now(),
            ]);
        } catch (\Throwable $throwable) {
            return $invoice->reminders()->create([
                'kind' => 'invoice',
                'channel' => NotificationChannel::Email,
                'status' => 'failed',
                'recipient' => $tenantEmail,
                'error_message' => $throwable->getMessage(),
            ]);
        }
    }

    public function sendOverdueInvoice(Invoice $invoice): InvoiceReminder
    {
        $tenantEmail = $invoice->lease->tenantProfile?->email;

        if (! $tenantEmail) {
            return $invoice->reminders()->create([
                'kind' => 'overdue',
                'channel' => NotificationChannel::Email,
                'status' => 'failed',
                'error_message' => __('app.rental.reminders.no_email'),
            ]);
        }

        try {
            Mail::raw(
                __('app.rental.reminders.email_body', ['number' => $invoice->number]),
                function ($message) use ($tenantEmail, $invoice): void {
                    $message
                        ->to($tenantEmail)
                        ->subject(__('app.rental.reminders.email_subject', ['number' => $invoice->number]));
                },
            );

            return $invoice->reminders()->create([
                'kind' => 'overdue',
                'channel' => NotificationChannel::Email,
                'status' => 'sent',
                'recipient' => $tenantEmail,
                'sent_at' => now(),
            ]);
        } catch (\Throwable $throwable) {
            return $invoice->reminders()->create([
                'kind' => 'overdue',
                'channel' => NotificationChannel::Email,
                'status' => 'failed',
                'recipient' => $tenantEmail,
                'error_message' => $throwable->getMessage(),
            ]);
        }
    }
}
