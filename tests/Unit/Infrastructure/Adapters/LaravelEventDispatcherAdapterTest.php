<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Adapters;

use App\Infrastructure\Adapters\LaravelEventDispatcherAdapter;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

final class LaravelEventDispatcherAdapterTest extends TestCase
{
    public function test_dispatch_delegates_to_laravel_event(): void
    {
        Event::fake();

        $event = new \stdClass;
        $adapter = new LaravelEventDispatcherAdapter;

        $adapter->dispatch($event);

        Event::assertDispatched(\stdClass::class, fn (\stdClass $dispatched) => $dispatched === $event);
    }
}
