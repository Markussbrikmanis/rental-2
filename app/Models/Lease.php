<?php

namespace App\Models;

use App\Enums\LeaseStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lease extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'property_unit_id',
        'tenant_profile_id',
        'start_date',
        'end_date',
        'billing_start_date',
        'due_day',
        'currency',
        'status',
        'deposit',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'billing_start_date' => 'date',
            'due_day' => 'integer',
            'deposit' => 'decimal:2',
            'status' => LeaseStatus::class,
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
     * @return BelongsTo<TenantProfile, $this>
     */
    public function tenantProfile(): BelongsTo
    {
        return $this->belongsTo(TenantProfile::class);
    }

    /**
     * @return HasMany<LeaseChargeRule, $this>
     */
    public function chargeRules(): HasMany
    {
        return $this->hasMany(LeaseChargeRule::class);
    }

    /**
     * @return HasMany<Invoice, $this>
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
