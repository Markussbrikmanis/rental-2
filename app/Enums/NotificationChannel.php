<?php

namespace App\Enums;

enum NotificationChannel: string
{
    case Email = 'email';

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
        return __('app.rental.notification_channels.'.$this->value);
    }
}
