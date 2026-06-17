<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\TravelOrder\ValueObjects;

use App\Domain\TravelOrder\Exceptions\InvalidTravelPeriodException;
use App\Domain\TravelOrder\ValueObjects\TravelPeriod;
use PHPUnit\Framework\TestCase;

final class TravelPeriodTest extends TestCase
{
    public function test_rejects_return_before_departure(): void
    {
        $this->expectException(InvalidTravelPeriodException::class);
        $this->expectExceptionMessage('Return date must be on or after departure date.');

        TravelPeriod::fromStrings('2026-07-10', '2026-07-01');
    }

    public function test_accepts_same_day_return(): void
    {
        $period = TravelPeriod::fromStrings('2026-07-01', '2026-07-01');

        $this->assertSame('2026-07-01', $period->departure->format('Y-m-d'));
        $this->assertSame('2026-07-01', $period->return->format('Y-m-d'));
    }

    public function test_accepts_return_after_departure(): void
    {
        $period = TravelPeriod::fromStrings('2026-07-01', '2026-07-15');

        $this->assertSame('2026-07-01', $period->departure->format('Y-m-d'));
        $this->assertSame('2026-07-15', $period->return->format('Y-m-d'));
    }
}
