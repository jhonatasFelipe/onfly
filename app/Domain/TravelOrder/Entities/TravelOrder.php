<?php

declare(strict_types=1);

namespace App\Domain\TravelOrder\Entities;

use App\Domain\TravelOrder\Events\TravelOrderApproved;
use App\Domain\TravelOrder\Events\TravelOrderCancelled;
use App\Domain\TravelOrder\Exceptions\InvalidTravelOrderStateException;
use App\Domain\TravelOrder\ValueObjects\Destination;
use App\Domain\TravelOrder\ValueObjects\RequesterName;
use App\Domain\TravelOrder\ValueObjects\TravelOrderId;
use App\Domain\TravelOrder\ValueObjects\TravelOrderStatus;
use App\Domain\TravelOrder\ValueObjects\TravelPeriod;
use App\Domain\TravelOrder\ValueObjects\UserId;

/**
 * Aggregate root de pedido de viagem — encapsula estado, transições e eventos de domínio.
 */
final class TravelOrder
{
    /** @var list<TravelOrderApproved|TravelOrderCancelled> */
    private array $domainEvents = [];

    private function __construct(
        private readonly TravelOrderId $id,
        private readonly UserId $userId,
        private readonly RequesterName $requesterName,
        private readonly Destination $destination,
        private readonly TravelPeriod $period,
        private TravelOrderStatus $status,
    ) {}

    public static function create(
        UserId $userId,
        RequesterName $requesterName,
        Destination $destination,
        TravelPeriod $period,
    ): self {
        return new self(
            id: TravelOrderId::generate(),
            userId: $userId,
            requesterName: $requesterName,
            destination: $destination,
            period: $period,
            status: TravelOrderStatus::Solicitado,
        );
    }

    public static function reconstitute(
        TravelOrderId $id,
        UserId $userId,
        RequesterName $requesterName,
        Destination $destination,
        TravelPeriod $period,
        TravelOrderStatus $status,
    ): self {
        return new self($id, $userId, $requesterName, $destination, $period, $status);
    }

    /**
     * @throws InvalidTravelOrderStateException
     */
    public function approve(): void
    {
        if (! $this->status->isSolicitado()) {
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
        if (! $this->status->isSolicitado()) {
            throw new InvalidTravelOrderStateException('Only requested orders can be cancelled.');
        }

        $this->status = TravelOrderStatus::Cancelado;
        $this->record(new TravelOrderCancelled($this->id, $this->userId));
    }

    /**
     * Drena e retorna os eventos de domínio acumulados desde a última chamada.
     *
     * @return list<TravelOrderApproved|TravelOrderCancelled>
     */
    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }

    public function id(): TravelOrderId
    {
        return $this->id;
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function requesterName(): RequesterName
    {
        return $this->requesterName;
    }

    public function destination(): Destination
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

    public function belongsTo(UserId $userId): bool
    {
        return $this->userId->equals($userId);
    }

    private function record(object $event): void
    {
        $this->domainEvents[] = $event;
    }
}
