<?php

declare(strict_types=1);

namespace App\Domain\TravelOrder\ValueObjects;

use App\Domain\TravelOrder\Exceptions\InvalidTravelPeriodException;
use DateTimeImmutable;

/**
 * Período de viagem composto por datas de ida e volta.
 */
final readonly class TravelPeriod
{
    /**
     * @throws InvalidTravelPeriodException
     */
    public function __construct(
        public DateTimeImmutable $departure,
        public DateTimeImmutable $return,
    ) {
        if ($this->return < $this->departure) {
            throw new InvalidTravelPeriodException('Return date must be on or after departure date.');
        }
    }

    /**
     * @throws InvalidTravelPeriodException
     */
    public static function fromStrings(string $departure, string $return): self
    {
        return new self(
            new DateTimeImmutable($departure),
            new DateTimeImmutable($return),
        );
    }
}
