<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Auth;

use App\Application\Auth\DTOs\AuthUserDto;
use App\Application\Auth\DTOs\LoginUserInput;
use App\Application\Auth\DTOs\RegisterUserInput;
use App\Application\Auth\DTOs\WebLoginAdminInput;
use App\Application\Auth\Exceptions\AdminAccessRequiredException;
use App\Application\Auth\Exceptions\InvalidCredentialsException;
use App\Application\Auth\UseCases\LoginUserUseCase;
use App\Application\Auth\UseCases\RegisterUserUseCase;
use App\Application\Auth\UseCases\WebLoginAdminUseCase;
use App\Application\Ports\ApiTokenPort;
use App\Application\Ports\SessionAuthenticationPort;
use App\Application\Ports\UserAuthenticationPort;
use App\Application\Ports\UserRegistrationPort;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\Unit\UnitTestCase;

final class AuthUseCaseTest extends UnitTestCase
{
    private UserAuthenticationPort&MockObject $authentication;

    private ApiTokenPort&MockObject $tokens;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authentication = $this->createMock(UserAuthenticationPort::class);
        $this->tokens = $this->createMock(ApiTokenPort::class);
    }

    public function test_login_returns_token_and_user_on_success(): void
    {
        $user = new AuthUserDto(1, 'Admin', 'admin@example.com', true);

        $this->authentication->expects($this->once())
            ->method('authenticate')
            ->with('admin@example.com', 'secret')
            ->willReturn($user);

        $this->tokens->expects($this->once())
            ->method('createForUser')
            ->with(1, 'api')
            ->willReturn('token-abc');

        $useCase = new LoginUserUseCase($this->authentication, $this->tokens);
        $output = $useCase->execute(new LoginUserInput('admin@example.com', 'secret'));

        $this->assertSame('token-abc', $output->token);
        $this->assertSame($user, $output->user);
    }

    public function test_login_propagates_invalid_credentials_exception(): void
    {
        $this->authentication->expects($this->once())
            ->method('authenticate')
            ->willThrowException(new InvalidCredentialsException);

        $this->tokens->expects($this->never())->method('createForUser');

        $useCase = new LoginUserUseCase($this->authentication, $this->tokens);

        $this->expectException(InvalidCredentialsException::class);

        $useCase->execute(new LoginUserInput('admin@example.com', 'wrong'));
    }

    public function test_register_returns_token_and_user(): void
    {
        $registration = $this->createMock(UserRegistrationPort::class);
        $user = new AuthUserDto(2, 'New User', 'new@example.com', false);

        $registration->expects($this->once())
            ->method('register')
            ->with('New User', 'new@example.com', 'password123')
            ->willReturn($user);

        $this->tokens->expects($this->once())
            ->method('createForUser')
            ->with(2, 'api')
            ->willReturn('token-xyz');

        $useCase = new RegisterUserUseCase($registration, $this->tokens);
        $output = $useCase->execute(new RegisterUserInput('New User', 'new@example.com', 'password123'));

        $this->assertSame('token-xyz', $output->token);
        $this->assertSame($user, $output->user);
    }

    public function test_web_login_admin_starts_session(): void
    {
        $session = $this->createMock(SessionAuthenticationPort::class);
        $user = new AuthUserDto(1, 'Admin', 'admin@example.com', true);

        $this->authentication->expects($this->once())
            ->method('authenticate')
            ->willReturn($user);

        $session->expects($this->once())->method('login')->with(1);
        $session->expects($this->never())->method('logout');

        $useCase = new WebLoginAdminUseCase($this->authentication, $session);
        $useCase->execute(new WebLoginAdminInput('admin@example.com', 'password'));
    }

    public function test_web_login_admin_rejects_non_admin(): void
    {
        $session = $this->createMock(SessionAuthenticationPort::class);
        $user = new AuthUserDto(2, 'User', 'user@example.com', false);

        $this->authentication->expects($this->once())
            ->method('authenticate')
            ->willReturn($user);

        $session->expects($this->once())->method('logout');
        $session->expects($this->never())->method('login');

        $useCase = new WebLoginAdminUseCase($this->authentication, $session);

        $this->expectException(AdminAccessRequiredException::class);

        $useCase->execute(new WebLoginAdminInput('user@example.com', 'password'));
    }
}
