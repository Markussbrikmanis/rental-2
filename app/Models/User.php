<?php

namespace App\Models;

use App\Enums\UserRole;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use Billable, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'owner_trial_ends_at',
        'subscription_plan_id',
        'invoice_number_format',
        'invoice_sender_name',
        'invoice_sender_address',
        'invoice_sender_registration_number',
        'invoice_sender_vat_number',
        'invoice_sender_bank_name',
        'invoice_sender_swift_code',
        'invoice_sender_account_number',
        'invoice_payment_terms_text',
        'invoice_footer_text',
        'invoice_logo_path',
        'invoice_vat_enabled',
        'invoice_vat_rate',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'owner_trial_ends_at' => 'datetime',
            'invoice_vat_enabled' => 'boolean',
            'invoice_vat_rate' => 'decimal:2',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isOwner(): bool
    {
        return $this->role === UserRole::Owner;
    }

    public function isTenant(): bool
    {
        return $this->role === UserRole::Tenant;
    }

    public function ownerPlan(): ?SubscriptionPlan
    {
        if (! $this->isOwner()) {
            return null;
        }

        return $this->subscriptionPlan;
    }

    public function ownerPropertyLimit(): ?int
    {
        if ($this->ownerHasUnlimitedPlan()) {
            return null;
        }

        return $this->ownerPlan()?->property_limit;
    }

    public function ownerHasUnlimitedPlan(): bool
    {
        return (bool) $this->ownerPlan()?->is_unlimited;
    }

    public function ownerTrialActive(): bool
    {
        return ($this->owner_trial_ends_at !== null && $this->owner_trial_ends_at->isFuture())
            || $this->onTrial('default');
    }

    public function ownerHasBillingAccess(): bool
    {
        if (! $this->isOwner() || $this->subscription_plan_id === null) {
            return false;
        }

        return $this->ownerHasUnlimitedPlan()
            || $this->subscribed('default')
            || $this->ownerTrialActive();
    }

    public function canCreateProperty(): bool
    {
        if (! $this->isOwner()) {
            return false;
        }

        if (! $this->ownerHasBillingAccess()) {
            return false;
        }

        $propertyLimit = $this->ownerPropertyLimit();

        return $propertyLimit === null || $this->properties()->count() < $propertyLimit;
    }

    public function invoiceNumberFormat(): string
    {
        return $this->invoice_number_format ?: '{year}-{num}';
    }

    /**
     * @return HasMany<Property, $this>
     */
    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    /**
     * @return BelongsTo<SubscriptionPlan, $this>
     */
    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    /**
     * @return HasMany<TenantProfile, $this>
     */
    public function tenantProfiles(): HasMany
    {
        return $this->hasMany(TenantProfile::class, 'owner_id');
    }

    /**
     * @return HasOne<TenantProfile, $this>
     */
    public function tenantProfile(): HasOne
    {
        return $this->hasOne(TenantProfile::class);
    }
}
