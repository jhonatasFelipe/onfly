<?php

declare(strict_types=1);

namespace App\Application\Auth\Exceptions;

use RuntimeException;

final class AdminAccessRequiredException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Acesso restrito a administradores.');
    }
}
