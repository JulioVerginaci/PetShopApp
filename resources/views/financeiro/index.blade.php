@extends('layouts.app')
@section('titulo', 'Financeiro')
@section('conteudo')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-bar-chart"></i> Financeiro</h4>
    <form method="GET" class="d-flex gap-2">
        <input type="month" name="mes" class="form-control form-control-sm" value="{{ $mes }}">
        <button class="btn btn-sm btn-outline-secondary" type="submit">Filtrar</button>
    </form>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm text-center">
            <div class="card-body">
                <div class="fs-2 fw-bold text-success">
                    R$ {{ number_format($totalMes, 2, ',', '.') }}
                </div>
                <div class="text-muted small">Total faturado no mês</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm text-center">
            <div class="card-body">
                <div class="fs-2 fw-bold text-primary">{{ $ordens->count() }}</div>
                <div class="text-muted small">Ordens de serviço</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="small text-muted fw-semibold mb-2">Agendamentos por status</div>
                @foreach(['pendente'=>'warning','confirmado'=>'primary','concluido'=>'success','cancelado'=>'danger'] as $st => $cor)
                <div class="d-flex justify-content-between small">
                    <span class="badge bg-{{ $cor }}">{{ ucfirst($st) }}</span>
                    <span>{{ $porStatus[$st] ?? 0 }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header fw-semibold">Ordens do mês</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>#</th><th>Cliente</th><th>Pet</th><th>Data</th><th>Pagamento</th><th class="text-end">Total</th></tr>
            </thead>
            <tbody>
                @forelse($ordens as $o)
                <tr>
                    <td class="text-muted small">{{ $o->id }}</td>
                    <td>{{ $o->agendamento->usuario->nome }}</td>
                    <td>{{ $o->agendamento->pet->nome }}</td>
                    <td>{{ \Carbon\Carbon::parse($o->agendamento->data_agendamento)->format('d/m/Y') }}</td>
                    <td>
                        <span class="badge {{ $o->estaPago() ? 'bg-success' : 'bg-warning text-dark' }}">
                            {{ $o->status_label }}
                        </span>
                    </td>
                    <td class="text-end fw-semibold text-success">
                        R$ {{ number_format($o->valor_total, 2, ',', '.') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        Nenhuma ordem neste mês.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection