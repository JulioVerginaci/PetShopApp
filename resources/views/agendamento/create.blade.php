@extends('layouts.app')
@section('titulo', 'Novo Agendamento')
@section('conteudo')

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('agendamentos.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h4 class="mb-0"><i class="bi bi-plus-circle"></i> Novo Agendamento</h4>
</div>

<div class="card shadow-sm" style="max-width:600px">
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form action="{{ route('agendamentos.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-semibold">Pet</label>
                @if($pets->isEmpty())
                    <div class="alert alert-warning py-2">
                        Você ainda não tem pets cadastrados.
                        <a href="{{ route('pets.create') }}">Cadastrar pet</a>
                    </div>
                @else
                    <select name="pet_id" class="form-select" required>
                        <option value="">Selecione o pet</option>
                        @foreach($pets as $pet)
                            <option value="{{ $pet->id }}" {{ old('pet_id') == $pet->id ? 'selected' : '' }}>
                                {{ $pet->nome }}
                                @if(Auth::user()->tipo_usuario === 'funcionario')
                                    — {{ $pet->usuario->nome }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Serviço</label>
                <select name="servico_id" class="form-select" required>
                    <option value="">Selecione o serviço</option>
                    @foreach($servicos as $s)
                        <option value="{{ $s->id }}" {{ old('servico_id') == $s->id ? 'selected' : '' }}>
                            {{ $s->nome }} — R$ {{ number_format($s->valor, 2, ',', '.') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Data</label>
                    <input type="date" name="data_agendamento" class="form-control"
                           value="{{ old('data_agendamento') }}" min="{{ date('Y-m-d') }}" oninvalid="this.setCustomValidity('A data do agendamento deve ser igual ou posterior a hoje.')"
       oninput="this.setCustomValidity('')" required>
                </div>
                <div class="col-md-6 mb-3">
    <label class="form-label fw-semibold">Hora</label>
    @php
        $horarios = [
            '08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '12:00',
            '13:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30', '17:00', '17:30', '18:00'
        ];
    @endphp
    <select name="hora_agendamento" class="form-select" required>
        <option value="">Selecione um horário</option>
        @foreach($horarios as $h)
            <option value="{{ $h }}" {{ old('hora_agendamento') == $h ? 'selected' : '' }}>
                {{ $h }}
            </option>
        @endforeach
    </select>
</div>
            </div>

            <button type="submit" class="btn btn-success w-100 fw-semibold">
                <i class="bi bi-calendar-plus"></i> Agendar
            </button>
        </form>
    </div>
</div>

@endsection