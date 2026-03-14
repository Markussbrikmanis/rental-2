<?php

use App\Services\InvoiceGenerationService;
use App\Services\DatabaseCopyService;
use App\Services\ReminderDispatchService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Symfony\Component\Console\Command\Command;

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

Artisan::command('db:copy-sqlite-to-mysql {--source=sqlite} {--target=mysql} {--batch=500} {--truncate}', function (DatabaseCopyService $databaseCopyService) {
    $source = (string) $this->option('source');
    $target = (string) $this->option('target');
    $batch = max((int) $this->option('batch'), 1);
    $truncate = (bool) $this->option('truncate');

    $targetDriver = config("database.connections.{$target}.driver");

    if (! in_array($targetDriver, ['mysql', 'mariadb'], true)) {
        $this->error(sprintf('Target connection [%s] must use mysql or mariadb.', $target));

        return Command::FAILURE;
    }

    $this->components->info(sprintf(
        'Copying data from [%s] to [%s] with batch size %d%s.',
        $source,
        $target,
        $batch,
        $truncate ? ' and target truncation enabled' : ''
    ));

    try {
        $result = $databaseCopyService->copy(
            sourceConnection: $source,
            targetConnection: $target,
            truncate: $truncate,
            batchSize: $batch,
        );
    } catch (\Throwable $exception) {
        $this->error($exception->getMessage());

        return Command::FAILURE;
    }

    $this->table(
        ['Table', 'Rows copied'],
        collect($result['per_table'])
            ->map(fn (int $rows, string $table): array => [$table, $rows])
            ->values()
            ->all(),
    );

    $this->components->info(sprintf(
        'Finished copying %d rows across %d tables.',
        $result['rows'],
        $result['tables'],
    ));

    return Command::SUCCESS;
})->purpose('Copy the current SQLite application data into a migrated MySQL or MariaDB database');

Schedule::command('billing:generate-invoices')->dailyAt('06:00');
Schedule::command('billing:send-reminders')->dailyAt('08:00');
