@extends('layouts.app')
@section('titulo', 'Produtos')
@section('conteudo')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-box-seam"></i> Produtos</h4>
    <a href="{{ route('produtos.create') }}" class="btn btn-success">
        <i class="bi bi-plus-lg"></i> Novo Produto
    </a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Nome</th><th>Descrição</th><th>Valor</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($produtos as $p)
                <tr>
                    <td class="fw-semibold">{{ $p->nome }}</td>
                    <td class="text-muted small">{{ $p->descricao ?? '—' }}</td>
                    <td>R$ {{ number_format($p->valor, 2, ',', '.') }}</td>
                    <td>
                        <span class="badge {{ $p->ativo ? 'bg-success' : 'bg-secondary' }}">
                            {{ $p->ativo ? 'Ativo' : 'Inativo' }}
                        </span>
                    </td>
                    <td class="d-flex gap-1">
                        <a href="{{ route('produtos.edit', $p->id) }}"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('produtos.toggle', $p->id) }}" method="POST">
                            @csrf @method('PATCH')
                            <button class="btn btn-sm btn-outline-{{ $p->ativo ? 'warning' : 'success' }}">
                                <i class="bi bi-{{ $p->ativo ? 'pause' : 'play' }}-circle"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">Nenhum produto cadastrado.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $produtos->links() }}</div>

@endsection