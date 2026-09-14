<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cadastro — Pet Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background:#1b1b1b; min-height:100vh; display:flex; align-items:center; justify-content:center; }
        .card { background:#2a2a2a; color:#fff; border:1px solid #3a3a3a; border-radius:12px; width:100%; max-width:440px; }
        .form-control { background:#333; border:1px solid #444; color:#fff; }
        .form-control:focus { background:#3a3a3a; color:#fff; border-color:#2e7d32; box-shadow:none; }
        label { color:#ccc; }
        a { color:#4caf50; }
    </style>
</head>
<body>
<div class="card p-4 shadow">
    <h4 class="text-center fw-bold mb-1"><i class="bi bi-person-plus"></i> Criar conta</h4>
    <p class="text-center text-secondary small mb-4">Preencha os dados abaixo para se cadastrar</p>

    @if($errors->any())
        <div class="alert alert-danger py-2">
            <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('cadastro') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label"><i class="bi bi-person"></i> Nome completo</label>
            <input type="text" name="nome" class="form-control"
                   value="{{ old('nome') }}" placeholder="Seu nome" required>
        </div>
        <div class="mb-3">
            <label class="form-label"><i class="bi bi-telephone"></i> Telefone</label>
            <input type="text" name="telefone" class="form-control"
                   value="{{ old('telefone') }}" placeholder="(00) 00000-0000">
        </div>
        <div class="mb-3">
            <label class="form-label"><i class="bi bi-envelope"></i> E-mail</label>
            <input type="email" name="email" class="form-control"
                   value="{{ old('email') }}" placeholder="seu@email.com" required>
        </div>
        <div class="mb-3">
            <label class="form-label"><i class="bi bi-lock"></i> Senha</label>
            <input type="password" name="senha" class="form-control" placeholder="••••••••" required>
        </div>
        <div class="mb-3">
            <label class="form-label"><i class="bi bi-lock-fill"></i> Confirmar Senha</label>
            <input type="password" name="senha_confirmation" class="form-control" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn btn-success w-100 fw-semibold">Cadastrar</button>
    </form>
    <p class="text-center mt-3 small">
        Já tem uma conta? <a href="{{ route('login') }}">Fazer login</a>
    </p>
</div>
</body>
</html>