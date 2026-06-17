<?php

declare(strict_types=1);

namespace App\Domain\TravelOrder\Collections;

use App\Domain\TravelOrder\Entities\TravelOrder;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Coleção imutável de pedidos de viagem.
 *
 * @implements IteratorAggregate<int, TravelOrder>
 */
final readonly class TravelOrderCollection implements Countable, IteratorAggregate
{
    /** @param list<TravelOrder> $items */
    private function __construct(private array $items) {}

    /**
     * Retorna uma coleção vazia.
     */
    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * Cria uma coleção a partir de pedidos variádicos.
     */
    public static function fromArray(TravelOrder ...$orders): self
    {
        return new self(array_values($orders));
    }

    /**
     * @param  iterable<TravelOrder>  $orders
     */
    public static function fromIterable(iterable $orders): self
    {
        $items = [];

        foreach ($orders as $order) {
            $items[] = $order;
        }

        return new self($items);
    }

    /** @return list<TravelOrder> */
    public function all(): array
    {
        return $this->items;
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @return Traversable<int, TravelOrder>
     */
    public function getIterator(): Traversable
    {
        yield from $this->items;
    }
}
