@extends('layouts.app')
@section('titulo', 'Editar Agendamento')
@section('conteudo')

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('agendamentos.show', $agendamento) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h4 class="mb-0"><i class="bi bi-pencil"></i> Editar Agendamento #{{ $agendamento->id }}</h4>
</div>

<div class="card shadow-sm" style="max-width:600px">
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form action="{{ route('agendamentos.update', $agendamento) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label fw-semibold">Pet</label>
                <select name="pet_id" class="form-select" required>
                    @foreach($pets as $pet)
                        <option value="{{ $pet->id }}"
                            {{ old('pet_id', $agendamento->pet_id) == $pet->id ? 'selected' : '' }}>
                            {{ $pet->nome }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Serviço</label>
                <select name="servico_id" class="form-select" required>
                    @foreach($servicos as $s)
                        <option value="{{ $s->id }}"
                            {{ old('servico_id', $agendamento->servico_id) == $s->id ? 'selected' : '' }}>
                            {{ $s->nome }} — R$ {{ number_format($s->valor, 2, ',', '.') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Data</label>
                    <input type="date" name="data_agendamento" class="form-control"
                           value="{{ old('data_agendamento', \Carbon\Carbon::parse($agendamento->data_agendamento)->format('Y-m-d')) }}"
                           min="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-6 mb-3">
    <label class="form-label fw-semibold">Hora</label>
    @php
        $horarios = [
            '08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30',
            '13:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30', '17:00', '17:30'
        ];
        
        $horaAtual = old('hora_agendamento', substr($agendamento->hora_agendamento, 0, 5));
    @endphp
    <select name="hora_agendamento" class="form-select" required>
        <option value="">Selecione um horário</option>
        @foreach($horarios as $h)
            <option value="{{ $h }}" {{ $horaAtual == $h ? 'selected' : '' }}>
                {{ $h }}
            </option>
        @endforeach
    </select>
</div>
            </div>

            <button type="submit" class="btn btn-primary w-100 fw-semibold">
                <i class="bi bi-save"></i> Salvar Alterações
            </button>
        </form>
    </div>
</div>

@endsection