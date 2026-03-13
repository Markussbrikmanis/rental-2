<?php

namespace App\Enums;

enum UtilityBillingMode: string
{
    case None = 'none';
    case Included = 'included';
    case Separate = 'separate';

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
        return __('app.rental.utility_billing_modes.'.$this->value);
    }
}
