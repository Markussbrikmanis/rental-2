<?php

namespace App\Models;

use App\Enums\MeterReadingSource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeterReading extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'meter_id',
        'reading_date',
        'value',
        'source',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reading_date' => 'date',
            'value' => 'decimal:3',
            'source' => MeterReadingSource::class,
        ];
    }

    /**
     * @return BelongsTo<Meter, $this>
     */
    public function meter(): BelongsTo
    {
        return $this->belongsTo(Meter::class);
    }
}
