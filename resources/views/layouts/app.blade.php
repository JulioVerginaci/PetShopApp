<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pet Shop — @yield('titulo', 'Sistema')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background:#f5f5f5; }
        .sidebar { min-height:calc(100vh - 56px); background:#fff; border-right:1px solid #dee2e6; padding:12px 8px; }
        .sidebar .nav-link { color:#495057; border-radius:6px; margin-bottom:2px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background:#e8f5e9; color:#2e7d32; }
        .navbar { background:#1b5e20 !important; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="{{ route('agendamentos.index') }}">
            <i class="bi bi-scissors"></i> Pet Shop
        </a>
        @auth
        <div class="d-flex align-items-center gap-3">
            <span class="text-white-50 small">
                <i class="bi bi-person-circle"></i>
                {{ Auth::user()->nome }}
                <span class="badge bg-light text-dark ms-1">{{ Auth::user()->tipo_usuario }}</span>
            </span>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="btn btn-sm btn-outline-light">
                    <i class="bi bi-box-arrow-right"></i> Sair
                </button>
            </form>
        </div>
        @endauth
    </div>
</nav>

<div class="container-fluid">
    <div class="row">

        @auth
        <nav class="col-md-2 d-none d-md-block sidebar">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('agendamentos.*') ? 'active' : '' }}"
                       href="{{ route('agendamentos.index') }}">
                        <i class="bi bi-calendar-check"></i> Agendamentos
                    </a>
                </li>
                @if(Auth::user()->tipo_usuario === 'cliente')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('pets.*') ? 'active' : '' }}"
                       href="{{ route('pets.index') }}">
                        <i class="bi bi-heart"></i> Meus Pets
                    </a>
                </li>
                @endif
                @if(Auth::user()->tipo_usuario === 'funcionario')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('ordens.*') ? 'active' : '' }}"
                       href="{{ route('ordens.index') }}">
                        <i class="bi bi-receipt"></i> Ordens de Serviço
                    </a>
                </li>
                <li class="nav-item mt-3">
                    <small class="text-muted px-2 fw-bold" style="font-size:.7rem">GESTÃO</small>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('servicos.*') ? 'active' : '' }}"
                       href="{{ route('servicos.index') }}">
                        <i class="bi bi-tools"></i> Serviços
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('produtos.*') ? 'active' : '' }}"
                       href="{{ route('produtos.index') }}">
                        <i class="bi bi-box-seam"></i> Produtos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('financeiro.*') ? 'active' : '' }}"
                       href="{{ route('financeiro.index') }}">
                        <i class="bi bi-bar-chart"></i> Financeiro
                    </a>
                </li>
                                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('funcionarios.*') ? 'active' : '' }}"
                       href="{{ route('funcionarios.create') }}">
                        <i class="bi bi-person-plus"></i> Novo Funcionário
                    </a>
                </li>
                @endif
            </ul>
        </nav>
        @endauth

        <main class="{{ Auth::check() ? 'col-md-10' : 'col-12' }} py-4 px-4">
            @if(session('sucesso'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle"></i> {{ session('sucesso') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('erro'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle"></i> {{ session('erro') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @yield('conteudo')
        </main>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>