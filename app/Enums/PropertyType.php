<?php

namespace App\Enums;

enum PropertyType: string
{
    case Garage = 'garage';
    case Office = 'office';
    case Warehouse = 'warehouse';
    case Apartment = 'apartment';
    case House = 'house';
    case Land = 'land';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $type): string => $type->value,
            self::cases(),
        );
    }

    public function label(): string
    {
        return __('app.properties.types.'.$this->value);
    }
}
