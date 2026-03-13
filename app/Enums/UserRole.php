<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Owner = 'owner';
    case Tenant = 'tenant';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $role): string => $role->value,
            self::cases(),
        );
    }

    /**
     * @return list<string>
     */
    public static function selfRegistrationValues(): array
    {
        return [
            self::Owner->value,
            self::Tenant->value,
        ];
    }

    public function label(): string
    {
        return __('app.roles.'.$this->value);
    }
}
