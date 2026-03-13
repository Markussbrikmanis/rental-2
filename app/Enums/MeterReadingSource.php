<?php

namespace App\Enums;

enum MeterReadingSource: string
{
    case Manual = 'manual';
    case Import = 'import';
    case Estimated = 'estimated';

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
        return __('app.rental.meter_reading_sources.'.$this->value);
    }
}
