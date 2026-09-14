<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login — Pet Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background:#1b1b1b; min-height:100vh; display:flex; align-items:center; justify-content:center; }
        .card { background:#2a2a2a; color:#fff; border:1px solid #3a3a3a; border-radius:12px; width:100%; max-width:420px; }
        .form-control { background:#333; border:1px solid #444; color:#fff; }
        .form-control:focus { background:#3a3a3a; color:#fff; border-color:#2e7d32; box-shadow:none; }
        label { color:#ccc; }
        a { color:#4caf50; }
    </style>
</head>
<body>
<div class="card p-4 shadow">
    <h4 class="text-center fw-bold mb-1"><i class="bi bi-scissors"></i> Login</h4>
    <p class="text-center text-secondary small mb-4">Entre com sua conta</p>

    @if($errors->any())
        <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('login') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label">E-mail</label>
            <input type="email" name="email" class="form-control"
                   value="{{ old('email') }}" placeholder="seu@email.com" required autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label">Senha</label>
            <input type="password" name="senha" class="form-control" placeholder="••••••••" required>
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="lembrar" name="lembrar">
            <label class="form-check-label text-secondary" for="lembrar">Lembrar-me</label>
        </div>
        <button type="submit" class="btn btn-success w-100 fw-semibold">Entrar</button>
    </form>
    <p class="text-center mt-3 small">
        Não tem conta? <a href="{{ route('cadastro') }}">Cadastre-se</a>
    </p>
</div>
</body>
</html>