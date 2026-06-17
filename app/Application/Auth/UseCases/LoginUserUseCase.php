<?php

declare(strict_types=1);

namespace App\Application\Auth\UseCases;

use App\Application\Auth\DTOs\LoginUserInput;
use App\Application\Auth\DTOs\LoginUserOutput;
use App\Application\Ports\ApiTokenPort;
use App\Application\Ports\UserAuthenticationPort;

final class LoginUserUseCase
{
    public function __construct(
        private readonly UserAuthenticationPort $authentication,
        private readonly ApiTokenPort $tokens,
    ) {}

    public function execute(LoginUserInput $input): LoginUserOutput
    {
        $user = $this->authentication->authenticate($input->email, $input->password);
        $token = $this->tokens->createForUser($user->id, 'api');

        return new LoginUserOutput($token, $user);
    }
}
