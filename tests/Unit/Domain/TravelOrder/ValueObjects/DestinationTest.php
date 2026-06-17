<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\TravelOrder\ValueObjects;

use App\Domain\TravelOrder\ValueObjects\Destination;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class DestinationTest extends TestCase
{
    public function test_from_string_trims_and_accepts_valid_value(): void
    {
        $destination = Destination::fromString('  Tokyo  ');

        $this->assertSame('Tokyo', $destination->value());
    }

    public function test_from_string_rejects_empty_value(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Destination cannot be empty.');

        Destination::fromString('   ');
    }

    public function test_from_string_rejects_value_exceeding_255_characters(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Destination cannot exceed 255 characters.');

        Destination::fromString(str_repeat('a', 256));
    }

    public function test_equals_returns_true_for_same_value(): void
    {
        $a = Destination::fromString('Paris');
        $b = Destination::fromString('Paris');

        $this->assertTrue($a->equals($b));
    }

    public function test_equals_returns_false_for_different_values(): void
    {
        $a = Destination::fromString('Paris');
        $b = Destination::fromString('London');

        $this->assertFalse($a->equals($b));
    }
}
