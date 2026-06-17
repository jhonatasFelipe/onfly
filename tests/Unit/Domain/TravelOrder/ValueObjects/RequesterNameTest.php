<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\TravelOrder\ValueObjects;

use App\Domain\TravelOrder\ValueObjects\RequesterName;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class RequesterNameTest extends TestCase
{
    public function test_from_string_trims_and_accepts_valid_value(): void
    {
        $name = RequesterName::fromString('  Jane Doe  ');

        $this->assertSame('Jane Doe', $name->value());
    }

    public function test_from_string_rejects_empty_value(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Requester name cannot be empty.');

        RequesterName::fromString('');
    }

    public function test_from_string_rejects_value_exceeding_255_characters(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Requester name cannot exceed 255 characters.');

        RequesterName::fromString(str_repeat('x', 256));
    }

    public function test_equals_returns_true_for_same_value(): void
    {
        $a = RequesterName::fromString('Alice');
        $b = RequesterName::fromString('Alice');

        $this->assertTrue($a->equals($b));
    }

    public function test_equals_returns_false_for_different_values(): void
    {
        $a = RequesterName::fromString('Alice');
        $b = RequesterName::fromString('Bob');

        $this->assertFalse($a->equals($b));
    }
}
