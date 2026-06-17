<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\TravelOrder\ValueObjects;

use App\Domain\TravelOrder\ValueObjects\TravelOrderStatus;
use PHPUnit\Framework\TestCase;
use ValueError;

final class TravelOrderStatusTest extends TestCase
{
    public function test_is_solicitado_returns_true_only_for_solicitado(): void
    {
        $this->assertTrue(TravelOrderStatus::Solicitado->isSolicitado());
        $this->assertFalse(TravelOrderStatus::Aprovado->isSolicitado());
        $this->assertFalse(TravelOrderStatus::Cancelado->isSolicitado());
    }

    public function test_from_string_returns_matching_case(): void
    {
        $this->assertSame(TravelOrderStatus::Aprovado, TravelOrderStatus::fromString('aprovado'));
    }

    public function test_from_string_rejects_invalid_value(): void
    {
        $this->expectException(ValueError::class);

        TravelOrderStatus::fromString('invalid');
    }

    public function test_can_transition_from_solicitado_to_aprovado(): void
    {
        $this->assertTrue(
            TravelOrderStatus::Solicitado->canTransitionTo(TravelOrderStatus::Aprovado),
        );
    }

    public function test_can_transition_from_solicitado_to_cancelado(): void
    {
        $this->assertTrue(
            TravelOrderStatus::Solicitado->canTransitionTo(TravelOrderStatus::Cancelado),
        );
    }

    public function test_cannot_transition_from_solicitado_to_solicitado(): void
    {
        $this->assertFalse(
            TravelOrderStatus::Solicitado->canTransitionTo(TravelOrderStatus::Solicitado),
        );
    }

    public function test_cannot_transition_from_aprovado(): void
    {
        $this->assertFalse(
            TravelOrderStatus::Aprovado->canTransitionTo(TravelOrderStatus::Cancelado),
        );
    }

    public function test_cannot_transition_from_cancelado(): void
    {
        $this->assertFalse(
            TravelOrderStatus::Cancelado->canTransitionTo(TravelOrderStatus::Aprovado),
        );
    }
}
