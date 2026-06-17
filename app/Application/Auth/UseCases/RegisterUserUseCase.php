<?php

declare(strict_types=1);

namespace App\Application\Auth\UseCases;

use App\Application\Auth\DTOs\RegisterUserInput;
use App\Application\Auth\DTOs\RegisterUserOutput;
use App\Application\Ports\ApiTokenPort;
use App\Application\Ports\UserRegistrationPort;

final class RegisterUserUseCase
{
    public function __construct(
        private readonly UserRegistrationPort $registration,
        private readonly ApiTokenPort $tokens,
    ) {}

    public function execute(RegisterUserInput $input): RegisterUserOutput
    {
        $user = $this->registration->register($input->name, $input->email, $input->password);
        $token = $this->tokens->createForUser($user->id, 'api');

        return new RegisterUserOutput($token, $user);
    }
}
