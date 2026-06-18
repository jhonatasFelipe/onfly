<?php

declare(strict_types=1);

namespace App\Domain\Shared\ValueObjects;

use InvalidArgumentException;

/**
 * Parâmetros de paginação — value object imutável sem dependência de framework.
 */
final readonly class Pagination
{
    public function __construct(
        public int $page,
        public int $perPage,
    ) {
        if ($page < 1) {
            throw new InvalidArgumentException('Page must be at least 1.');
        }

        if ($perPage < 1) {
            throw new InvalidArgumentException('Per page must be at least 1.');
        }
    }

    public function offset(): int
    {
        return ($this->page - 1) * $this->perPage;
    }
}
