<?php

namespace App\Enums;

enum ChargeFrequency: string
{
    case Monthly = 'monthly';
    case Yearly = 'yearly';
    case CustomInterval = 'custom_interval';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $value): string => $value->value,
            self::cases(),
        );
    }

    public function label(): string
    {
        return __('app.rental.charge_frequencies.'.$this->value);
    }
}
