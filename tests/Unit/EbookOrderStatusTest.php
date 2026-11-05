<?php

namespace Tests\Unit;

use App\Enums\EbookOrderStatus;
use PHPUnit\Framework\TestCase;

class EbookOrderStatusTest extends TestCase
{
    public function test_only_allowed_order_lifecycle_transitions_are_permitted(): void
    {
        self::assertTrue(EbookOrderStatus::Pending->canTransitionTo(EbookOrderStatus::Paid));
        self::assertTrue(EbookOrderStatus::Pending->canTransitionTo(EbookOrderStatus::Failed));
        self::assertTrue(EbookOrderStatus::Paid->canTransitionTo(EbookOrderStatus::Refunded));
        self::assertFalse(EbookOrderStatus::Failed->canTransitionTo(EbookOrderStatus::Paid));
        self::assertFalse(EbookOrderStatus::Refunded->canTransitionTo(EbookOrderStatus::Paid));
        self::assertFalse(EbookOrderStatus::Paid->canTransitionTo(EbookOrderStatus::Failed));
    }
}
