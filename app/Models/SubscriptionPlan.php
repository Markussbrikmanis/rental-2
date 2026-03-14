<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'stripe_price_id',
        'display_price',
        'currency',
        'billing_interval',
        'property_limit',
        'trial_enabled',
        'trial_days',
        'is_active',
        'is_public',
        'is_unlimited',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'property_limit' => 'integer',
            'trial_enabled' => 'boolean',
            'trial_days' => 'integer',
            'is_active' => 'boolean',
            'is_public' => 'boolean',
            'is_unlimited' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function requiresStripeCheckout(): bool
    {
        return ! $this->is_unlimited && filled($this->stripe_price_id);
    }

    public function propertyLimitLabel(): string
    {
        if ($this->is_unlimited || $this->property_limit === null) {
            return __('app.subscription.unlimited');
        }

        return __('app.subscription.property_limit', ['count' => $this->property_limit]);
    }
}
