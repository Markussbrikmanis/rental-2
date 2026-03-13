<?php

namespace Database\Factories;

use App\Enums\InvoiceKind;
use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Lease;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $periodFrom = now()->startOfMonth();
        $periodTo = now()->endOfMonth();

        return [
            'lease_id' => Lease::factory(),
            'number' => 'INV-'.fake()->unique()->numerify('########'),
            'issue_date' => $periodFrom,
            'due_date' => $periodFrom->copy()->addDays(10),
            'period_from' => $periodFrom,
            'period_to' => $periodTo,
            'kind' => InvoiceKind::Standard,
            'status' => InvoiceStatus::Issued,
            'subtotal' => 250,
            'total' => 250,
            'sent_at' => null,
        ];
    }
}
