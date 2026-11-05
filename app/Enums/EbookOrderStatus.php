<?php

namespace App\Enums;

enum EbookOrderStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';
    case Refunded = 'refunded';

    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::Pending => in_array($next, [self::Paid, self::Failed], true),
            self::Paid => $next === self::Refunded,
            self::Failed, self::Refunded => false,
        };
    }
}
