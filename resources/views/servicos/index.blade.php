@extends('layouts.app')
@section('titulo', 'Serviços')
@section('conteudo')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-tools"></i> Serviços</h4>
    <a href="{{ route('servicos.create') }}" class="btn btn-success">
        <i class="bi bi-plus-lg"></i> Novo Serviço
    </a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Nome</th><th>Descrição</th><th>Valor</th><th>Duração</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($servicos as $s)
                <tr>
                    <td class="fw-semibold">{{ $s->nome }}</td>
                    <td class="text-muted small">{{ $s->descricao ?? '—' }}</td>
                    <td>R$ {{ number_format($s->valor, 2, ',', '.') }}</td>
                    <td>{{ $s->duracao_minutos ? $s->duracao_minutos.' min' : '—' }}</td>
                    <td>
                        <span class="badge {{ $s->ativo ? 'bg-success' : 'bg-secondary' }}">
                            {{ $s->ativo ? 'Ativo' : 'Inativo' }}
                        </span>
                    </td>
                    <td class="d-flex gap-1">
                        <a href="{{ route('servicos.edit', $s->id) }}"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('servicos.toggle', $s->id) }}" method="POST">
                            @csrf @method('PATCH')
                            <button class="btn btn-sm btn-outline-{{ $s->ativo ? 'warning' : 'success' }}">
                                <i class="bi bi-{{ $s->ativo ? 'pause' : 'play' }}-circle"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">Nenhum serviço cadastrado.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $servicos->links() }}</div>

@endsection