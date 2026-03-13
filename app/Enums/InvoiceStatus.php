<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Issued = 'issued';
    case Overdue = 'overdue';
    case Paid = 'paid';
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
        return __('app.rental.invoice_statuses.'.$this->value);
    }
}
