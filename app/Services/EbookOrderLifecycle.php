<?php

namespace App\Services;

use App\Enums\EbookOrderStatus;
use App\Models\EbookOrder;
use Illuminate\Support\Facades\DB;
use LogicException;

class EbookOrderLifecycle
{
    public function transition(EbookOrder $order, EbookOrderStatus $next): EbookOrder
    {
        return DB::transaction(function () use ($order, $next): EbookOrder {
            $lockedOrder = EbookOrder::query()->whereKey($order->getKey())->lockForUpdate()->firstOrFail();
            $current = $lockedOrder->status;

            if ($current === $next) {
                return $lockedOrder;
            }

            if (! $current->canTransitionTo($next)) {
                throw new LogicException("Invalid eBook order status transition: {$current->value} to {$next->value}.");
            }

            $lockedOrder->status = $next;
            if ($next === EbookOrderStatus::Paid) {
                $lockedOrder->paid_at ??= now();
            }
            $lockedOrder->save();

            return $lockedOrder->refresh();
        });
    }
}
