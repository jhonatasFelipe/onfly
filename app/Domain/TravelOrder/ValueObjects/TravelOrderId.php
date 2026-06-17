<?php

declare(strict_types=1);

namespace App\Domain\TravelOrder\ValueObjects;

use App\Domain\Shared\ValueObjects\Uuid;

/**
 * Identificador único de um pedido de viagem.
 */
final readonly class TravelOrderId
{
    private function __construct(private Uuid $uuid) {}

    public static function generate(): self
    {
        return new self(Uuid::generate());
    }

    public static function fromString(string $value): self
    {
        return new self(Uuid::fromString($value));
    }

    public function value(): string
    {
        return $this->uuid->value();
    }

    public function equals(self $other): bool
    {
        return $this->uuid->equals($other->uuid);
    }
}
