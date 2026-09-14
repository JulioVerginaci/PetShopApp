<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Novo Funcionário — Pet Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width:500px">
    <div class="card shadow-sm">
        <div class="card-header fw-bold">Cadastrar Novo Funcionário</div>
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif
            <form action="{{ route('funcionarios.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Nome</label>
                    <input type="text" name="nome" class="form-control" value="{{ old('nome') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">E-mail</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Telefone</label>
                    <input type="text" name="telefone" class="form-control" value="{{ old('telefone') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Senha</label>
                    <input type="password" name="senha" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirmar Senha</label>
                    <input type="password" name="senha_confirmation" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success w-100">Cadastrar Funcionário</button>
            </form>
        </div>
        <div class="card-footer">
            <a href="{{ route('agendamentos.index') }}">← Voltar</a>
        </div>
    </div>
</div>
</body>
</html>