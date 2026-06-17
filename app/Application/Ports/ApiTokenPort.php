<?php

declare(strict_types=1);

namespace App\Application\Ports;

/**
 * Porta para emissão e revogação de tokens de API (Sanctum).
 */
interface ApiTokenPort
{
    public function createForUser(int $userId, string $tokenName): string;

    public function revokeCurrent(): void;
}
