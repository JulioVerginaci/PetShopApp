
@extends('layouts.app')

@section('titulo', 'Editar Agendamento')

@section('conteudo')

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('agendamentos.show', $agendamento) }}"
       class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i>
    </a>

    <h4 class="mb-0">
        <i class="bi bi-pencil"></i>
        Editar Agendamento #{{ $agendamento->id }}
    </h4>
</div>

<div class="card shadow-sm" style="max-width:600px">
    <div class="card-body">

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('agendamentos.update', $agendamento) }}"
              method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="pet_id" class="form-label fw-semibold">
                    Pet
                </label>

                <select id="pet_id" name="pet_id"
                        class="form-select" required>
                    @foreach($pets as $pet)
                        <option value="{{ $pet->id }}"
                            {{ (string) old('pet_id', $agendamento->pet_id) === (string) $pet->id ? 'selected' : '' }}>
                            {{ $pet->nome }}

                            @if(Auth::user()->tipo_usuario === 'funcionario')
                                — {{ $pet->usuario->nome ?? 'Cliente não identificado' }}
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="servico_id" class="form-label fw-semibold">
                    Serviço
                </label>

                <select id="servico_id" name="servico_id"
                        class="form-select" required>
                    @foreach($servicos as $servico)
                        <option value="{{ $servico->id }}"
                            {{ (string) old('servico_id', $agendamento->servico_id) === (string) $servico->id ? 'selected' : '' }}>
                            {{ $servico->nome }} —
                            R$ {{ number_format($servico->valor, 2, ',', '.') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="data_agendamento"
                           class="form-label fw-semibold">
                        Data
                    </label>

                    <input
                        id="data_agendamento"
                        type="date"
                        name="data_agendamento"
                        class="form-control"
                        value="{{ now()->format('Y-m-d') }}"
                        min="{{ now()->format('Y-m-d') }}"
                        max="{{ now()->format('Y-m-d') }}"
                        required
                    >
                </div>

                <div class="col-md-6 mb-3">
                    <label for="hora_agendamento"
                           class="form-label fw-semibold">
                        Horário
                    </label>

                    <select id="hora_agendamento"
                            name="hora_agendamento"
                            class="form-select"
                            required
                            disabled>
                        <option value="">Carregando horários...</option>
                    </select>

                    <div id="horarios_feedback"
                         class="form-text d-none"
                         aria-live="polite"></div>
                </div>
            </div>

            <button
                type="submit"
                class="btn btn-primary w-100 fw-semibold"
                @if($pets->isEmpty() || $servicos->isEmpty()) disabled @endif
            >
                <i class="bi bi-save"></i> Salvar Alterações
            </button>
        </form>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const dataInput = document.getElementById('data_agendamento');
    const horaSelect = document.getElementById('hora_agendamento');

    const urlHorarios = @json(
        route('agendamentos.horarios-disponiveis')
    );

    const idAgendamento = @json($agendamento->id);
    let requisicaoAtual = 0;

    function definirOpcaoInicial(texto) {
        horaSelect.replaceChildren(new Option(texto, ''));
    }

    async function carregarHorarios() {
        const data = dataInput.value;
        const numeroRequisicao = ++requisicaoAtual;

        definirOpcaoInicial(
            data ? 'Carregando horários...' : 'Selecione a data de hoje'
        );

        horaSelect.disabled = true;

        if (!data) {
            return;
        }

        try {
            const parametros = new URLSearchParams({
                data_agendamento: data,
                agendamento_id: idAgendamento
            });

            const resposta = await fetch(
                `${urlHorarios}?${parametros.toString()}`,
                {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }
            );

            const resultado = await resposta.json();

            if (!resposta.ok) {
                throw new Error(
                    resultado.message || 'Falha ao consultar horários.'
                );
            }

            if (numeroRequisicao !== requisicaoAtual) {
                return;
            }

            definirOpcaoInicial('Selecione um horário');

            const horarios = resultado.horarios_disponiveis || [];

            horarios.forEach(horario => {
                horaSelect.add(new Option(horario, horario));
            });

            horaSelect.disabled = horarios.length === 0;

            if (horarios.length === 0) {
                horaSelect.options[0].text =
                    'Nenhum horário disponível';
            }
        } catch (erro) {
            if (numeroRequisicao !== requisicaoAtual) {
                return;
            }

            definirOpcaoInicial('Data Anterior a atual');
            horaSelect.disabled = true;

            console.error(erro);
        }
    }

    dataInput.addEventListener('change', carregarHorarios);
    carregarHorarios();
});
</script>

@endsection
