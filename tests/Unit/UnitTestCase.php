<?php

declare(strict_types=1);

namespace Tests\Unit;

use Mockery;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class UnitTestCase extends BaseTestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}
