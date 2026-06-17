<?php

declare(strict_types=1);

namespace App\Domain\TravelOrder\ValueObjects;

use InvalidArgumentException;

/**
 * Destino da viagem solicitada.
 */
final readonly class Destination
{
    private function __construct(private string $value) {}

    public static function fromString(string $value): self
    {
        $trimmed = trim($value);

        if ($trimmed === '') {
            throw new InvalidArgumentException('Destination cannot be empty.');
        }

        if (strlen($trimmed) > 255) {
            throw new InvalidArgumentException('Destination cannot exceed 255 characters.');
        }

        return new self($trimmed);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
