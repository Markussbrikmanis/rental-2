<?php

namespace App\Enums;

enum MeterType: string
{
    case ColdWater = 'cold_water';
    case HotWater = 'hot_water';
    case Electricity = 'electricity';
    case Gas = 'gas';
    case Other = 'other';

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
        return __('app.rental.meter_types.'.$this->value);
    }
}
