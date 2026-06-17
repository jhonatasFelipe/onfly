<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Shared\ValueObjects;

use App\Domain\Shared\ValueObjects\Uuid;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class UuidTest extends TestCase
{
    public function test_generate_produces_valid_uuid(): void
    {
        $uuid = Uuid::generate();

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $uuid->value(),
        );
    }

    public function test_from_string_accepts_valid_uuid(): void
    {
        $uuid = Uuid::fromString('550e8400-e29b-41d4-a716-446655440000');

        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $uuid->value());
    }

    public function test_from_string_rejects_invalid_uuid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid UUID: not-a-uuid');

        Uuid::fromString('not-a-uuid');
    }

    public function test_equals_returns_true_for_same_value(): void
    {
        $a = Uuid::fromString('550e8400-e29b-41d4-a716-446655440000');
        $b = Uuid::fromString('550e8400-e29b-41d4-a716-446655440000');

        $this->assertTrue($a->equals($b));
    }

    public function test_equals_returns_false_for_different_values(): void
    {
        $a = Uuid::fromString('550e8400-e29b-41d4-a716-446655440000');
        $b = Uuid::fromString('6ba7b810-9dad-11d1-80b4-00c04fd430c8');

        $this->assertFalse($a->equals($b));
    }
}
