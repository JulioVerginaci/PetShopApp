@extends('layouts.app')
@section('titulo', 'Detalhe do Agendamento')
@section('conteudo')

@php
    $cores  = ['pendente'=>'warning','confirmado'=>'primary','concluido'=>'success','cancelado'=>'danger'];
    $labels = ['pendente'=>'Pendente','confirmado'=>'Confirmado','concluido'=>'Concluído','cancelado'=>'Cancelado'];
@endphp

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('agendamentos.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h4 class="mb-0"><i class="bi bi-calendar-event"></i> Agendamento #{{ $agendamento->id }}</h4>
    <span class="badge bg-{{ $cores[$agendamento->status] ?? 'secondary' }} fs-6">
        {{ $labels[$agendamento->status] ?? $agendamento->status }}
    </span>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header fw-semibold">Dados</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5 text-muted">Cliente</dt>
                    <dd class="col-7">{{ $agendamento->usuario->nome }}</dd>
                    <dt class="col-5 text-muted">Pet</dt>
                    <dd class="col-7"><i class="bi bi-heart-fill text-danger"></i> {{ $agendamento->pet->nome }}</dd>
                    <dt class="col-5 text-muted">Serviço</dt>
                    <dd class="col-7">{{ $agendamento->servico->nome }}</dd>
                    <dt class="col-5 text-muted">Data</dt>
                    <dd class="col-7">{{ \Carbon\Carbon::parse($agendamento->data_agendamento)->format('d/m/Y') }}</dd>
                    <dt class="col-5 text-muted">Hora</dt>
                    <dd class="col-7">{{ substr($agendamento->hora_agendamento, 0, 5) }}</dd>
                </dl>
            </div>
            <div class="card-footer bg-transparent d-flex gap-2 flex-wrap">
                @if(in_array($agendamento->status, ['pendente','confirmado']))
                    <a href="{{ route('agendamentos.edit', $agendamento) }}"
                       class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil"></i> Editar
                    </a>
                    <form action="{{ route('agendamentos.cancelar', $agendamento) }}" method="POST"
                          onsubmit="return confirm('Confirmar cancelamento?')">
                        @csrf @method('PATCH')
                        <button class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    @if(Auth::user()->tipo_usuario === 'funcionario')
    <div class="col-md-6">
        <div class="card shadow-sm mb-3">
            <div class="card-header fw-semibold">Alterar Status</div>
            <div class="card-body">
                <form action="{{ route('agendamentos.status', $agendamento) }}" method="POST"
                      class="d-flex gap-2">
                    @csrf @method('PATCH')
                    <select name="status" class="form-select">
                        @foreach(['pendente'=>'Pendente','confirmado'=>'Confirmado','concluido'=>'Concluído','cancelado'=>'Cancelado'] as $val => $label)
                            <option value="{{ $val }}" {{ $agendamento->status === $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <button class="btn btn-primary" type="submit">Salvar</button>
                </form>
            </div>
        </div>

        @if(!$agendamento->ordemServico)
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <p class="text-muted mb-2">Nenhuma ordem de serviço gerada.</p>
                <a href="{{ route('ordens.create', $agendamento) }}" class="btn btn-success">
                    <i class="bi bi-receipt"></i> Gerar Ordem de Serviço
                </a>
            </div>
        </div>
        @endif
    </div>
    @endif
</div>

@if($agendamento->ordemServico)
<div class="card shadow-sm mt-4">
    <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
        <span><i class="bi bi-receipt"></i> Ordem de Serviço #{{ $agendamento->ordemServico->id }}</span>
        <span class="badge {{ $agendamento->ordemServico->estaPago() ? 'bg-success' : 'bg-warning text-dark' }}">
            {{ $agendamento->ordemServico->status_label }}
        </span>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-muted">Serviços</h6>
                <table class="table table-sm">
                    <thead><tr><th>Nome</th><th>Qtd</th><th>Total</th></tr></thead>
                    <tbody>
                        @foreach($agendamento->ordemServico->itensServico as $item)
                        <tr>
                            <td>{{ $item->servico->nome }}</td>
                            <td>{{ $item->quantidade }}</td>
                            <td>R$ {{ number_format($item->valor_unitario * $item->quantidade, 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-muted">Produtos</h6>
                <table class="table table-sm">
                    <thead><tr><th>Nome</th><th>Qtd</th><th>Total</th></tr></thead>
                    <tbody>
                        @forelse($agendamento->ordemServico->itensProduto as $item)
                        <tr>
                            <td>{{ $item->produto->nome }}</td>
                            <td>{{ $item->quantidade }}</td>
                            <td>R$ {{ number_format($item->valor_unitario * $item->quantidade, 2, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-muted">Nenhum produto.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($agendamento->ordemServico->observacoes)
            <p class="text-muted"><strong>Obs:</strong> {{ $agendamento->ordemServico->observacoes }}</p>
        @endif
        <div class="text-end">
            <span class="fs-5 fw-bold text-success">
                Total: R$ {{ number_format($agendamento->ordemServico->valor_total, 2, ',', '.') }}
            </span>
        </div>
    </div>
</div>
@endif

@endsection