<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\TravelOrder\ValueObjects;

use App\Domain\TravelOrder\ValueObjects\UserId;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class UserIdTest extends TestCase
{
    public function test_from_int_accepts_positive_value(): void
    {
        $userId = UserId::fromInt(42);

        $this->assertSame(42, $userId->value());
    }

    public function test_from_int_rejects_zero(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('User ID must be greater than zero.');

        UserId::fromInt(0);
    }

    public function test_from_int_rejects_negative_value(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('User ID must be greater than zero.');

        UserId::fromInt(-1);
    }

    public function test_equals_returns_true_for_same_value(): void
    {
        $a = UserId::fromInt(1);
        $b = UserId::fromInt(1);

        $this->assertTrue($a->equals($b));
    }

    public function test_equals_returns_false_for_different_values(): void
    {
        $a = UserId::fromInt(1);
        $b = UserId::fromInt(2);

        $this->assertFalse($a->equals($b));
    }
}
