<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\TravelOrder\Collections;

use App\Domain\TravelOrder\Collections\TravelOrderCollection;
use App\Domain\TravelOrder\Entities\TravelOrder;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Domain\TravelOrder\Support\MakesTravelOrder;

final class TravelOrderCollectionTest extends TestCase
{
    use MakesTravelOrder;

    public function test_empty_returns_collection_with_zero_items(): void
    {
        $collection = TravelOrderCollection::empty();

        $this->assertCount(0, $collection);
        $this->assertSame([], $collection->all());
    }

    public function test_from_array_stores_orders(): void
    {
        $first = $this->createTravelOrder(destination: 'Paris');
        $second = $this->createTravelOrder(destination: 'Tokyo');

        $collection = TravelOrderCollection::fromArray($first, $second);

        $this->assertCount(2, $collection);
        $this->assertSame([$first, $second], $collection->all());
    }

    public function test_from_iterable_stores_orders(): void
    {
        $order = $this->createTravelOrder();

        $collection = TravelOrderCollection::fromIterable([$order]);

        $this->assertCount(1, $collection);
        $this->assertSame([$order], $collection->all());
    }

    public function test_get_iterator_yields_all_orders(): void
    {
        $first = $this->createTravelOrder(destination: 'Paris');
        $second = $this->createTravelOrder(destination: 'Tokyo');
        $collection = TravelOrderCollection::fromArray($first, $second);

        $items = iterator_to_array($collection);

        $this->assertCount(2, $items);
        $this->assertContainsOnlyInstancesOf(TravelOrder::class, $items);
    }
}
