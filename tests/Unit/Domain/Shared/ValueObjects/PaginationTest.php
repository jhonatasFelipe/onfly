<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Shared\ValueObjects;

use App\Domain\Shared\ValueObjects\Pagination;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class PaginationTest extends TestCase
{
    public function test_accepts_valid_page_and_per_page(): void
    {
        $pagination = new Pagination(page: 2, perPage: 15);

        $this->assertSame(2, $pagination->page);
        $this->assertSame(15, $pagination->perPage);
    }

    public function test_offset_calculates_from_page_and_per_page(): void
    {
        $pagination = new Pagination(page: 3, perPage: 10);

        $this->assertSame(20, $pagination->offset());
    }

    public function test_first_page_offset_is_zero(): void
    {
        $pagination = new Pagination(page: 1, perPage: 25);

        $this->assertSame(0, $pagination->offset());
    }

    public function test_rejects_page_below_one(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Page must be at least 1.');

        new Pagination(page: 0, perPage: 15);
    }

    public function test_rejects_per_page_below_one(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Per page must be at least 1.');

        new Pagination(page: 1, perPage: 0);
    }
}
