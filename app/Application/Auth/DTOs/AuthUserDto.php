<?php

declare(strict_types=1);

namespace App\Application\Auth\DTOs;

/**
 * Representação do usuário autenticado para respostas da API.
 */
final readonly class AuthUserDto
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public bool $isAdmin,
    ) {}
}
