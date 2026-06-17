<?php

declare(strict_types=1);

namespace App\Domain\TravelOrder\ValueObjects;

/**
 * Status possíveis de um pedido de viagem e regras de transição entre eles.
 */
enum TravelOrderStatus: string
{
    case Solicitado = 'solicitado';
    case Aprovado = 'aprovado';
    case Cancelado = 'cancelado';

    public function isSolicitado(): bool
    {
        return $this === self::Solicitado;
    }

    /**
     * Indica se a transição do status atual para o alvo é permitida.
     */
    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::Solicitado => in_array($target, [self::Aprovado, self::Cancelado], true),
            self::Aprovado, self::Cancelado => false,
        };
    }

    public static function fromString(string $value): self
    {
        return self::from($value);
    }
}
