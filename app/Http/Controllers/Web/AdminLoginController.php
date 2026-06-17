<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Application\Auth\DTOs\WebLoginAdminInput;
use App\Application\Auth\Exceptions\AdminAccessRequiredException;
use App\Application\Auth\Exceptions\InvalidCredentialsException;
use App\Application\Auth\UseCases\WebLoginAdminUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\AdminLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Login web mínimo para administradores acessarem a documentação da API.
 */
final class AdminLoginController extends Controller
{
    public function create(): View
    {
        return view('admin.login');
    }

    public function store(AdminLoginRequest $request, WebLoginAdminUseCase $useCase): RedirectResponse
    {
        try {
            $useCase->execute(new WebLoginAdminInput(
                email: $request->validated('email'),
                password: $request->validated('password'),
            ));
        } catch (InvalidCredentialsException) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Credenciais inválidas.']);
        } catch (AdminAccessRequiredException) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Acesso restrito a administradores.']);
        }

        $request->session()->regenerate();

        return redirect()->intended('/docs/api');
    }

    public function destroy(Request $request): RedirectResponse
    {
        auth()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
