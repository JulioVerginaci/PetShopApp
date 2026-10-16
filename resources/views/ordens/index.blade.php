
@extends('layouts.app')

@section('titulo', 'Ordens de Serviço')

@section('conteudo')

@php
    $diaFormatado = \Carbon\Carbon::parse($data)
        ->translatedFormat('D, d \d\e M');
@endphp

@if(session('sucesso'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('sucesso') }}
        <button type="button" class="btn-close"
            data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
@endif

@if(session('erro'))
    <div class="alert alert-danger">
        {{ session('erro') }}
    </div>
@endif

<div class="d-flex justify-content-between align-items-center mb-1 flex-wrap gap-2">
    <div>
        <h4 class="mb-0">
            <i class="bi bi-receipt"></i> Ordem de Serviço
        </h4>

        <span class="text-muted">
            {{ $totalOrdens }}
            {{ $totalOrdens === 1 ? 'ordem' : 'ordens' }}

            @if($totalPendentes > 0)
                ·
                <span class="text-warning-emphasis fw-semibold">
                    {{ $totalPendentes }}
                    pendente{{ $totalPendentes === 1 ? '' : 's' }}
                </span>
            @endif
        </span>
    </div>
</div>

<form class="row g-2 my-3" method="GET">

    <div class="col-auto">
        <label class="form-label small text-muted mb-0">
            Dia
        </label>

        <input
            type="date"
            name="data"
            value="{{ $data }}"
            class="form-control form-control-sm"
        >
    </div>

    <div class="col-auto">
        <label class="form-label small text-muted mb-0">
            Horário
        </label>

        <select name="hora" class="form-select form-select-sm">

            <option
                value="todos"
                {{ $hora === 'todos' ? 'selected' : '' }}
            >
                Todos os horários
            </option>

            @foreach($horariosPermitidos as $h)
                <option
                    value="{{ $h }}"
                    {{ $hora === $h ? 'selected' : '' }}
                >
                    {{ $h }}
                </option>
            @endforeach

        </select>
    </div>

    <div class="col-auto">
        <label class="form-label small text-muted mb-0">
            Pagamento
        </label>

        <select name="status" class="form-select form-select-sm">

            <option
                value="todos"
                {{ $status === 'todos' ? 'selected' : '' }}
            >
                Todos
            </option>

            <option
                value="pendente"
                {{ $status === 'pendente' ? 'selected' : '' }}
            >
                Pendentes
            </option>

            <option
                value="pago"
                {{ $status === 'pago' ? 'selected' : '' }}
            >
                Pagos
            </option>

        </select>
    </div>

    <div class="col-auto d-flex align-items-end gap-2">
        <button
            class="btn btn-sm btn-outline-secondary"
            type="submit"
        >
            Filtrar
        </button>

        <a
            href="{{ route('ordens.index') }}"
            class="btn btn-sm btn-link"
        >
            Hoje
        </a>
    </div>

</form>

<p class="text-muted small mb-3">
    Exibindo: {{ ucfirst($diaFormatado) }}
</p>

{{-- Botão para abrir a seleção de clientes --}}
<div class="mb-4">

    <button
        class="btn btn-success"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#painelUsuariosOS"
        aria-expanded="false"
        aria-controls="painelUsuariosOS"
    >
        <i class="bi bi-receipt"></i>
        Gerar Ordem de Serviço
    </button>

    <div class="collapse mt-3" id="painelUsuariosOS">

        <div class="card shadow-sm">

            <div class="card-header fw-semibold">
                <i class="bi bi-people"></i>
                Selecione um cliente

                <span class="text-muted small">
                    — ordem alfabética
                </span>
            </div>

            <div class="list-group list-group-flush">

                @forelse($usuariosParaOS as $usuario)

                    <div class="list-group-item">

                        <div class="fw-semibold">
                            {{ $usuario->nome }}
                        </div>

                        <div class="text-muted small">
                            Cliente #{{ $usuario->id }}
                        </div>

                        @forelse($usuario->agendamentos as $ag)

                            <div class="border-top mt-2 pt-2">

                                <div class="d-flex justify-content-between
                                    align-items-center flex-wrap gap-2">

                                    <div class="small">

                                        <div>
                                            <i class="bi bi-calendar-event"></i>

                                            {{ \Carbon\Carbon::parse(
                                                $ag->data_agendamento
                                            )->format('d/m/Y') }}

                                            às
                                            {{ substr($ag->hora_agendamento, 0, 5) }}
                                        </div>

                                        <div>
                                            <i class="bi bi-heart"></i>
                                            Pet:
                                            {{ $ag->pet->nome ?? 'Não informado' }}
                                        </div>

                                        <div>
                                            <i class="bi bi-scissors"></i>
                                            Serviço:
                                            {{ $ag->servico->nome ?? 'Não informado' }}
                                        </div>

                                    </div>

                                    <a
                                        href="{{ route('ordens.create', $ag) }}"
                                        class="btn btn-sm btn-outline-success"
                                    >
                                        <i class="bi bi-plus-circle"></i>
                                        Selecionar e gerar OS
                                    </a>

                                </div>

                            </div>

                        @empty

                            <div class="small text-muted mt-2">
                                Não há agendamento disponível para este
                                cliente na data selecionada.
                            </div>

                        @endforelse

                    </div>

                @empty

                    <div class="list-group-item text-muted">
                        Nenhum cliente cadastrado.
                    </div>

                @endforelse

            </div>

        </div>

    </div>

</div>

{{-- Ordens de serviço já geradas --}}
<div class="row g-3">

    @forelse($ordens as $ordem)

        @php
            $pago = $ordem->estaPago();
        @endphp

        <div class="col-md-6 col-xl-4">

            <div class="card shadow-sm h-100">

                <div class="card-header d-flex justify-content-between align-items-center">

                    <span class="fw-semibold">
                        OS #{{ $ordem->id }}
                    </span>

                    <span class="text-muted small">
                        {{ substr(
                            $ordem->agendamento->hora_agendamento,
                            0,
                            5
                        ) }}
                    </span>

                </div>

                <div class="card-body">

                    <div class="mb-2">
                        <div class="text-muted small">CLIENTE</div>

                        <div>
                            {{ $ordem->agendamento->usuario->nome }}
                            <span class="text-muted small">
                                (#{{ $ordem->agendamento->usuario->id }})
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted small">PET</div>

                        <div>
                            <i class="bi bi-heart-fill text-danger"></i>

                            {{ $ordem->agendamento->pet->nome }}

                            <span class="text-muted small">
                                (#{{ $ordem->agendamento->pet->id }})
                            </span>
                        </div>
                    </div>

                    <div class="text-muted small">SERVIÇOS</div>

                    @forelse($ordem->itensServico as $item)

                        <div class="small">
                            {{ $item->quantidade }}x
                            {{ $item->servico->nome }}
                        </div>

                    @empty

                        <div class="small text-muted fst-italic">
                            Nenhum
                        </div>

                    @endforelse

                    <div class="text-muted small mt-2">PRODUTOS</div>

                    @forelse($ordem->itensProduto as $item)

                        <div class="small">
                            {{ $item->quantidade }}x
                            {{ $item->produto->nome }}
                        </div>

                    @empty

                        <div class="small text-muted fst-italic">
                            Nenhum
                        </div>

                    @endforelse

                </div>

                <div class="card-footer bg-transparent">

                    <div class="d-flex justify-content-between align-items-center mb-2">

                        <span class="fw-bold text-success">
                            R$ {{ number_format(
                                $ordem->valor_total,
                                2,
                                ',',
                                '.'
                            ) }}
                        </span>

                        <span class="badge {{
                            $pago
                                ? 'bg-success'
                                : 'bg-warning text-dark'
                        }}">
                            {{ $ordem->status_label }}
                        </span>

                    </div>

                    @if($ordem->forma_pagamento)

                        <div class="small text-muted mb-2">
                            Forma de pagamento:
                            {{ $ordem->forma_pagamento_label
                                ?? 'Não informada' }}
                        </div>

                    @endif

                    <div class="d-flex gap-2">

                        <a
                            href="{{ route('ordens.show', $ordem) }}"
                            class="btn btn-sm btn-outline-secondary flex-fill"
                        >
                            Ver detalhes
                        </a>

                        <form
                            action="{{ route('ordens.toggle-pago', $ordem) }}"
                            method="POST"
                            class="flex-fill"
                        >

                            @csrf
                            @method('PATCH')

                            <button
                                type="submit"
                                class="btn btn-sm w-100 {{
                                    $pago
                                        ? 'btn-outline-warning'
                                        : 'btn-outline-success'
                                }}"
                            >
                                {{ $pago
                                    ? 'Marcar pendente'
                                    : 'Marcar como pago' }}
                            </button>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    @empty

        <div class="col-12">

            <div class="card shadow-sm">

                <div class="card-body text-center text-muted py-5">

                    <i class="bi bi-inbox fs-1"></i>

                    <p class="mb-0 mt-2">
                        Nenhuma ordem de serviço encontrada para esse filtro.
                    </p>

                </div>

            </div>

        </div>

    @endforelse

</div>

@endsection
