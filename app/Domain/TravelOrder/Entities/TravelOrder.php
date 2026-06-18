<?php

declare(strict_types=1);

namespace App\Domain\TravelOrder\Entities;

use App\Domain\Shared\ValueObjects\Uuid;
use App\Domain\TravelOrder\Events\TravelOrderApproved;
use App\Domain\TravelOrder\Events\TravelOrderCancelled;
use App\Domain\TravelOrder\Exceptions\InvalidTravelOrderStateException;
use App\Domain\TravelOrder\ValueObjects\TravelOrderStatus;
use App\Domain\TravelOrder\ValueObjects\TravelPeriod;
use InvalidArgumentException;

/**
 * Aggregate root de pedido de viagem — encapsula estado, transições e eventos de domínio.
 */
final class TravelOrder
{
    /** @var list<TravelOrderApproved|TravelOrderCancelled> */
    private array $domainEvents = [];

    private function __construct(
        private readonly string $id,
        private readonly int $userId,
        private readonly string $requesterName,
        private readonly string $destination,
        private readonly TravelPeriod $period,
        private TravelOrderStatus $status,
    ) {}

    public static function create(
        int $userId,
        string $requesterName,
        string $destination,
        TravelPeriod $period,
    ): self {
        return new self(
            id: Uuid::generate()->value(),
            userId: self::normalizeUserId($userId),
            requesterName: self::normalizeRequesterName($requesterName),
            destination: self::normalizeDestination($destination),
            period: $period,
            status: TravelOrderStatus::Solicitado,
        );
    }

    public static function reconstitute(
        string $id,
        int $userId,
        string $requesterName,
        string $destination,
        TravelPeriod $period,
        TravelOrderStatus $status,
    ): self {
        return new self(
            id: self::normalizeId($id),
            userId: self::normalizeUserId($userId),
            requesterName: self::normalizeRequesterName($requesterName),
            destination: self::normalizeDestination($destination),
            period: $period,
            status: $status,
        );
    }

    /**
     * @throws InvalidTravelOrderStateException
     */
    public function approve(): void
    {
        if (! $this->status->canTransitionTo(TravelOrderStatus::Aprovado)) {
            throw new InvalidTravelOrderStateException('Only requested orders can be approved.');
        }

        $this->status = TravelOrderStatus::Aprovado;
        $this->record(new TravelOrderApproved($this->id, $this->userId));
    }

    /**
     * @throws InvalidTravelOrderStateException
     */
    public function cancel(): void
    {
        if (! $this->status->canTransitionTo(TravelOrderStatus::Cancelado)) {
            throw new InvalidTravelOrderStateException('Only requested orders can be cancelled.');
        }

        $this->status = TravelOrderStatus::Cancelado;
        $this->record(new TravelOrderCancelled($this->id, $this->userId));
    }

    /**
     * @return list<TravelOrderApproved|TravelOrderCancelled>
     */
    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function userId(): int
    {
        return $this->userId;
    }

    public function requesterName(): string
    {
        return $this->requesterName;
    }

    public function destination(): string
    {
        return $this->destination;
    }

    public function period(): TravelPeriod
    {
        return $this->period;
    }

    public function status(): TravelOrderStatus
    {
        return $this->status;
    }

    public function belongsTo(int $userId): bool
    {
        return $this->userId === $userId;
    }

    private static function normalizeId(string $id): string
    {
        return Uuid::fromString($id)->value();
    }

    private static function normalizeUserId(int $userId): int
    {
        if ($userId <= 0) {
            throw new InvalidArgumentException('User ID must be greater than zero.');
        }

        return $userId;
    }

    private static function normalizeRequesterName(string $requesterName): string
    {
        $trimmed = trim($requesterName);

        if ($trimmed === '') {
            throw new InvalidArgumentException('Requester name cannot be empty.');
        }

        if (strlen($trimmed) > 255) {
            throw new InvalidArgumentException('Requester name cannot exceed 255 characters.');
        }

        return $trimmed;
    }

    private static function normalizeDestination(string $destination): string
    {
        $trimmed = trim($destination);

        if ($trimmed === '') {
            throw new InvalidArgumentException('Destination cannot be empty.');
        }

        if (strlen($trimmed) > 255) {
            throw new InvalidArgumentException('Destination cannot exceed 255 characters.');
        }

        return $trimmed;
    }

    private function record(TravelOrderApproved|TravelOrderCancelled $event): void
    {
        $this->domainEvents[] = $event;
    }
}
