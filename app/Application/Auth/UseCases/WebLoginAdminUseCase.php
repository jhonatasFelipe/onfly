<?php

declare(strict_types=1);

namespace App\Application\Auth\UseCases;

use App\Application\Auth\DTOs\WebLoginAdminInput;
use App\Application\Auth\Exceptions\AdminAccessRequiredException;
use App\Application\Ports\SessionAuthenticationPort;
use App\Application\Ports\UserAuthenticationPort;

final class WebLoginAdminUseCase
{
    public function __construct(
        private readonly UserAuthenticationPort $authentication,
        private readonly SessionAuthenticationPort $session,
    ) {}

    public function execute(WebLoginAdminInput $input): void
    {
        $user = $this->authentication->authenticate($input->email, $input->password);

        if (! $user->isAdmin) {
            $this->session->logout();

            throw new AdminAccessRequiredException();
        }

        $this->session->login($user->id);
    }
}
