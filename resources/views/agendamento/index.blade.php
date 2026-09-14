@extends('layouts.app')
@section('titulo', 'Agendamentos')
@section('conteudo')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-calendar-check"></i> Agendamentos</h4>
    <a href="{{ route('agendamentos.create') }}" class="btn btn-success">
        <i class="bi bi-plus-lg"></i> Novo Agendamento
    </a>
</div>

@if(Auth::user()->tipo_usuario === 'funcionario')
<form class="row g-2 mb-3" method="GET">
    <div class="col-auto">
        <select name="status" class="form-select form-select-sm">
            <option value="">Todos os status</option>
            <option value="pendente"   {{ request('status')==='pendente'   ? 'selected':'' }}>Pendente</option>
            <option value="confirmado" {{ request('status')==='confirmado' ? 'selected':'' }}>Confirmado</option>
            <option value="concluido"  {{ request('status')==='concluido'  ? 'selected':'' }}>Concluído</option>
            <option value="cancelado"  {{ request('status')==='cancelado'  ? 'selected':'' }}>Cancelado</option>
        </select>
    </div>
    <div class="col-auto">
        <button class="btn btn-sm btn-outline-secondary" type="submit">Filtrar</button>
        <a href="{{ route('agendamentos.index') }}" class="btn btn-sm btn-link">Limpar</a>
    </div>
</form>
@endif

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    @if(Auth::user()->tipo_usuario === 'funcionario')<th>Cliente</th>@endif
                    <th>Pet</th>
                    <th>Serviço</th>
                    <th>Data</th>
                    <th>Hora</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($agendamentos as $ag)
                <tr>
                    <td class="text-muted small">{{ $ag->id }}</td>
                    @if(Auth::user()->tipo_usuario === 'funcionario')
                        <td>{{ $ag->usuario->nome }}</td>
                    @endif
                    <td><i class="bi bi-heart-fill text-danger"></i> {{ $ag->pet->nome }}</td>
                    <td>{{ $ag->servico->nome }}</td>
                    <td>{{ \Carbon\Carbon::parse($ag->data_agendamento)->format('d/m/Y') }}</td>
                    <td>{{ substr($ag->hora_agendamento, 0, 5) }}</td>
                    <td>
                        @php
                            $cores = ['pendente'=>'warning','confirmado'=>'primary','concluido'=>'success','cancelado'=>'danger'];
                            $labels = ['pendente'=>'Pendente','confirmado'=>'Confirmado','concluido'=>'Concluído','cancelado'=>'Cancelado'];
                        @endphp
                        <span class="badge bg-{{ $cores[$ag->status] ?? 'secondary' }}">
                            {{ $labels[$ag->status] ?? $ag->status }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('agendamentos.show', $ag) }}"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        <i class="bi bi-calendar-x fs-3 d-block mb-2"></i>
                        Nenhum agendamento encontrado.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $agendamentos->withQueryString()->links() }}</div>

@endsection