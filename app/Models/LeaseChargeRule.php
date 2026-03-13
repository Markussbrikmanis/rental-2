<?php

namespace App\Models;

use App\Enums\ChargeFrequency;
use App\Enums\ChargeIntervalUnit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class LeaseChargeRule extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'lease_id',
        'name',
        'amount',
        'frequency',
        'interval_count',
        'interval_unit',
        'effective_from',
        'effective_to',
        'auto_invoice_enabled',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'effective_from' => 'date',
            'effective_to' => 'date',
            'auto_invoice_enabled' => 'boolean',
            'frequency' => ChargeFrequency::class,
            'interval_unit' => ChargeIntervalUnit::class,
        ];
    }

    /**
     * @return BelongsTo<Lease, $this>
     */
    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class);
    }

    /**
     * @return MorphMany<InvoiceLine, $this>
     */
    public function invoiceLines(): MorphMany
    {
        return $this->morphMany(InvoiceLine::class, 'source');
    }
}
