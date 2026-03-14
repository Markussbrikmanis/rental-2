<?php

namespace App\Enums;

enum OwnerPlan: string
{
    case Starter = 'starter';
    case Growth = 'growth';
    case Scale = 'scale';
    case AdminUnlimited = 'admin_unlimited';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $plan): string => $plan->value,
            self::cases(),
        );
    }

    /**
     * @return list<string>
     */
    public static function publicValues(): array
    {
        return [
            self::Starter->value,
            self::Growth->value,
            self::Scale->value,
        ];
    }

    public function label(): string
    {
        return __('app.subscription.plans.'.$this->value.'.label');
    }

    public function description(): string
    {
        return __('app.subscription.plans.'.$this->value.'.description');
    }

    public function propertyLimit(): ?int
    {
        return match ($this) {
            self::Starter => (int) config('billing.owner_plans.starter.property_limit'),
            self::Growth => (int) config('billing.owner_plans.growth.property_limit'),
            self::Scale => (int) config('billing.owner_plans.scale.property_limit'),
            self::AdminUnlimited => null,
        };
    }

    public function isPublic(): bool
    {
        return $this !== self::AdminUnlimited;
    }
}
