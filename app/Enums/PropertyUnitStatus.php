<?php

namespace App\Enums;

enum PropertyUnitStatus: string
{
    case Vacant = 'vacant';
    case Occupied = 'occupied';
    case Maintenance = 'maintenance';
    case Inactive = 'inactive';

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
        return __('app.rental.unit_statuses.'.$this->value);
    }
}
