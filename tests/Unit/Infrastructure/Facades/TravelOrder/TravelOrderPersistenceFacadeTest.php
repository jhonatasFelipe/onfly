<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Facades\TravelOrder;

use App\Domain\TravelOrder\Entities\TravelOrder;
use App\Infrastructure\Contracts\TravelOrderEloquentTranslatorInterface;
use App\Infrastructure\Facades\TravelOrder\TravelOrderPersistenceFacade;
use Mockery;
use Tests\Unit\Domain\TravelOrder\Support\MakesTravelOrder;
use Tests\Unit\UnitTestCase;

final class TravelOrderPersistenceFacadeTest extends UnitTestCase
{
    use MakesTravelOrder;

    public function test_to_domain_delegates_to_translator(): void
    {
        $translator = Mockery::mock(TravelOrderEloquentTranslatorInterface::class);
        $order = $this->makeTravelOrder();
        $record = ['id' => $order->id()->value()];

        $translator->shouldReceive('toDomain')
            ->once()
            ->with($record)
            ->andReturn($order);

        $facade = new TravelOrderPersistenceFacade($translator);

        $this->assertSame($order, $facade->toDomain($record));
    }

    public function test_to_persistence_array_delegates_to_translator(): void
    {
        $translator = Mockery::mock(TravelOrderEloquentTranslatorInterface::class);
        $order = $this->makeTravelOrder();
        $expected = ['id' => $order->id()->value()];

        $translator->shouldReceive('toPersistenceArray')
            ->once()
            ->with(Mockery::type(TravelOrder::class))
            ->andReturn($expected);

        $facade = new TravelOrderPersistenceFacade($translator);

        $this->assertSame($expected, $facade->toPersistenceArray($order));
    }
}
