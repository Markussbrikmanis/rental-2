<?php

use App\Services\InvoiceGenerationService;
use App\Services\ReminderDispatchService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('billing:generate-invoices {--date=}', function (InvoiceGenerationService $invoiceGenerationService) {
    $date = $this->option('date') ? Carbon::parse($this->option('date')) : now();
    $generated = $invoiceGenerationService->generateForDate($date);

    $this->info(sprintf('Generated or refreshed %d invoices.', $generated->count()));
})->purpose('Generate recurring invoices for active leases');

Artisan::command('billing:send-reminders {--date=}', function (ReminderDispatchService $reminderDispatchService) {
    $date = $this->option('date') ? Carbon::parse($this->option('date')) : now();
    $sent = $reminderDispatchService->sendOverdueReminders($date);

    $this->info(sprintf('Processed %d invoice reminders.', $sent->count()));
})->purpose('Send overdue invoice reminders');

Schedule::command('billing:generate-invoices')->dailyAt('06:00');
Schedule::command('billing:send-reminders')->dailyAt('08:00');
