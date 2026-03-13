<?php

namespace App\Enums;

enum InvoiceKind: string
{
    case Standard = 'standard';
    case Utility = 'utility';

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
        return __('app.rental.invoice_kinds.'.$this->value);
    }
}
