<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Admin — Onfly API Docs</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 24rem; margin: 4rem auto; padding: 0 1rem; }
        label { display: block; margin-bottom: 0.25rem; font-weight: 600; }
        input { width: 100%; padding: 0.5rem; margin-bottom: 1rem; box-sizing: border-box; }
        button { padding: 0.5rem 1rem; cursor: pointer; }
        .error { color: #b91c1c; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <h1>Login administrador</h1>
    <p>Acesso à documentação da API restrito a usuários com perfil admin.</p>

    @if ($errors->any())
        <div class="error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('admin.login.store') }}">
        @csrf
        <label for="email">E-mail</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>

        <label for="password">Senha</label>
        <input id="password" type="password" name="password" required>

        <button type="submit">Entrar</button>
    </form>
</body>
</html>
