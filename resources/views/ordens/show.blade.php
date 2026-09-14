@extends('layouts.app')
@section('titulo', 'Ordem de Serviço')
@section('conteudo')

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('agendamentos.show', $ordem->agendamento) }}"
       class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h4 class="mb-0"><i class="bi bi-receipt"></i> Ordem de Serviço #{{ $ordem->id }}</h4>
    <span class="badge {{ $ordem->estaPago() ? 'bg-success' : 'bg-warning text-dark' }} fs-6">
        {{ $ordem->status_label }}
    </span>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header fw-semibold">Dados do Agendamento</div>
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-4 text-muted">Cliente</dt>
            <dd class="col-8">{{ $ordem->agendamento->usuario->nome }}</dd>
            <dt class="col-4 text-muted">Pet</dt>
            <dd class="col-8">{{ $ordem->agendamento->pet->nome }}</dd>
            <dt class="col-4 text-muted">Serviço base</dt>
            <dd class="col-8">{{ $ordem->agendamento->servico->nome }}</dd>
            <dt class="col-4 text-muted">Data</dt>
            <dd class="col-8">{{ \Carbon\Carbon::parse($ordem->agendamento->data_agendamento)->format('d/m/Y') }}</dd>
        </dl>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header fw-semibold"><i class="bi bi-tools"></i> Serviços</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr><th>Serviço</th><th>Qtd</th><th>Total</th></tr>
                    </thead>
                    <tbody>
                        @forelse($ordem->itensServico as $item)
                        <tr>
                            <td>{{ $item->servico->nome }}</td>
                            <td>{{ $item->quantidade }}</td>
                            <td>R$ {{ number_format($item->valor_unitario * $item->quantidade, 2, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-muted text-center py-2">Nenhum serviço.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header fw-semibold"><i class="bi bi-box-seam"></i> Produtos</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr><th>Produto</th><th>Qtd</th><th>Total</th></tr>
                    </thead>
                    <tbody>
                        @forelse($ordem->itensProduto as $item)
                        <tr>
                            <td>{{ $item->produto->nome }}</td>
                            <td>{{ $item->quantidade }}</td>
                            <td>R$ {{ number_format($item->valor_unitario * $item->quantidade, 2, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-muted text-center py-2">Nenhum produto.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@if($ordem->observacoes)
<div class="card shadow-sm mt-4">
    <div class="card-body">
        <strong>Observações:</strong> {{ $ordem->observacoes }}
    </div>
</div>
@endif

<div class="card shadow-sm mt-4">
    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="fs-4 fw-bold text-success">
            Total: R$ {{ number_format($ordem->valor_total, 2, ',', '.') }}
        </span>
        @if(Auth::user()->tipo_usuario === 'funcionario')
        <form action="{{ route('ordens.toggle-pago', $ordem) }}" method="POST">
            @csrf @method('PATCH')
            <button type="submit" class="btn {{ $ordem->estaPago() ? 'btn-outline-warning' : 'btn-outline-success' }}">
                {{ $ordem->estaPago() ? 'Marcar como pendente' : 'Marcar como pago' }}
            </button>
        </form>
        @endif
    </div>
</div>

@endsection