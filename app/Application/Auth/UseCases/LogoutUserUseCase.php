<?php

declare(strict_types=1);

namespace App\Application\Auth\UseCases;

use App\Application\Ports\ApiTokenPort;

final class LogoutUserUseCase
{
    public function __construct(
        private readonly ApiTokenPort $tokens,
    ) {}

    public function execute(): void
    {
        $this->tokens->revokeCurrent();
    }
}
