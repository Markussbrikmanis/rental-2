<?php

namespace App\Models;

use App\Enums\MeterType;
use App\Enums\UtilityBillingMode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meter extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'property_unit_id',
        'name',
        'type',
        'unit',
        'utility_billing_mode',
        'rate_per_unit',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'type' => MeterType::class,
            'utility_billing_mode' => UtilityBillingMode::class,
            'rate_per_unit' => 'decimal:4',
        ];
    }

    /**
     * @return BelongsTo<PropertyUnit, $this>
     */
    public function propertyUnit(): BelongsTo
    {
        return $this->belongsTo(PropertyUnit::class);
    }

    /**
     * @return HasMany<MeterReading, $this>
     */
    public function readings(): HasMany
    {
        return $this->hasMany(MeterReading::class);
    }
}
