<?php

namespace App\Enums;

enum LeaseStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Ended = 'ended';
    case Cancelled = 'cancelled';

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
        return __('app.rental.lease_statuses.'.$this->value);
    }
}
