
@extends('layouts.app')

@section('titulo', 'Financeiro')

@section('conteudo')

@if(session('sucesso'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('sucesso') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"
            aria-label="Fechar"></button>
    </div>
@endif

@if(session('erro'))
    <div class="alert alert-danger">
        {{ session('erro') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <strong>Não foi possível salvar o pagamento:</strong>
        @foreach($errors->all() as $erro)
            <div>{{ $erro }}</div>
        @endforeach
    </div>
@endif

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h4 class="mb-0">
        <i class="bi bi-bar-chart"></i> Financeiro
    </h4>

    <form method="GET" class="d-flex gap-2">
        <input
            type="month"
            name="mes"
            class="form-control form-control-sm"
            value="{{ $mes }}"
        >

        <button class="btn btn-sm btn-outline-secondary" type="submit">
            Filtrar
        </button>
    </form>
</div>

<div class="row g-3 mb-4">

    <div class="col-md-4">
        <div class="card shadow-sm text-center h-100">
            <div class="card-body">
                <div class="fs-2 fw-bold text-success">
                    R$ {{ number_format($totalMes, 2, ',', '.') }}
                </div>

                <div class="text-muted small">
                    Total recebido no mês
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm text-center h-100">
            <div class="card-body">
                <div class="fs-2 fw-bold text-primary">
                    {{ $ordens->count() }}
                </div>

                <div class="text-muted small">
                    Ordens de serviço
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="small text-muted fw-semibold mb-2">
                    Agendamentos por status
                </div>

                @foreach([
                    'pendente' => 'warning',
                    'confirmado' => 'primary',
                    'concluido' => 'success',
                    'cancelado' => 'danger'
                ] as $st => $cor)

                    <div class="d-flex justify-content-between small mb-1">
                        <span class="badge bg-{{ $cor }}">
                            {{ ucfirst($st) }}
                        </span>

                        <span>{{ $porStatus[$st] ?? 0 }}</span>
                    </div>

                @endforeach
            </div>
        </div>
    </div>

</div>

<div class="card shadow-sm">

    <div class="card-header fw-semibold">
        Ordens do mês
    </div>

    <div class="table-responsive">

        <table class="table table-hover align-middle mb-0">

            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Cliente</th>
                    <th>Pet</th>
                    <th>Data</th>
                    <th>Pagamento atual</th>
                    <th class="text-end">Total</th>
                    <th>Atualizar pagamento</th>
                </tr>
            </thead>

            <tbody>

                @forelse($ordens as $o)

                    <tr>
                        <td class="text-muted small">
                            {{ $o->id }}
                        </td>

                        <td>
                            {{ $o->agendamento->usuario->nome ?? 'Cliente não encontrado' }}
                        </td>

                        <td>
                            {{ $o->agendamento->pet->nome ?? 'Pet não encontrado' }}
                        </td>

                        <td>
                            {{ \Carbon\Carbon::parse(
                                $o->agendamento->data_agendamento
                            )->format('d/m/Y') }}
                        </td>

                        <td>
                            <span class="badge {{
                                $o->estaPago()
                                    ? 'bg-success'
                                    : 'bg-warning text-dark'
                            }}">
                                {{ $o->status_label }}
                            </span>

                            <div class="small text-muted mt-1">
                                Forma:
                                {{ $o->forma_pagamento_label ?? 'Não informada' }}
                            </div>
                        </td>

                        <td class="text-end fw-semibold text-success">
                            R$ {{ number_format($o->valor_total, 2, ',', '.') }}
                        </td>

                        <td style="min-width: 300px;">

                            <form
                                action="{{ route('financeiro.pagamento', $o) }}"
                                method="POST"
                                class="d-flex flex-column gap-2"
                            >

                                @csrf
                                @method('PATCH')

                                <select
                                    name="status"
                                    class="form-select form-select-sm"
                                    required
                                >
                                    <option
                                        value="pendente"
                                        {{ $o->status === 'pendente' ? 'selected' : '' }}
                                    >
                                        Pendente
                                    </option>

                                    <option
                                        value="pago"
                                        {{ $o->status === 'pago' ? 'selected' : '' }}
                                    >
                                        Pago
                                    </option>
                                </select>

                                <select
                                    name="forma_pagamento"
                                    class="form-select form-select-sm"
                                >
                                    <option value="">
                                        Selecione a forma de pagamento
                                    </option>

                                    @foreach(
                                        \App\Models\OrdemServico::formasPagamento()
                                        as $valor => $rotulo
                                    )

                                        <option
                                            value="{{ $valor }}"
                                            {{ $o->forma_pagamento === $valor ? 'selected' : '' }}
                                        >
                                            {{ $rotulo }}
                                        </option>

                                    @endforeach

                                </select>

                                <button
                                    type="submit"
                                    class="btn btn-sm btn-success"
                                >
                                    <i class="bi bi-check-circle"></i>
                                    Salvar pagamento
                                </button>

                            </form>

                        </td>
                    </tr>

                @empty

                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            Nenhuma ordem neste mês.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection
