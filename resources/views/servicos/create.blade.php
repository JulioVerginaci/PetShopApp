@extends('layouts.app')
@section('titulo', 'Novo Serviço')
@section('conteudo')

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('servicos.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h4 class="mb-0"><i class="bi bi-plus-circle"></i> Novo Serviço</h4>
</div>

<div class="card shadow-sm" style="max-width:500px">
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif
        <form action="{{ route('servicos.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold">Nome</label>
                <input type="text" name="nome" class="form-control" value="{{ old('nome') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Descrição</label>
                <textarea name="descricao" class="form-control" rows="2">{{ old('descricao') }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Valor (R$)</label>
                <input type="number" name="valor" class="form-control" step="0.01" min="0"
                       value="{{ old('valor') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Duração (minutos)</label>
                <input type="number" name="duracao_minutos" class="form-control" min="1" step="1"
                       value="{{ old('duracao_minutos') }}" placeholder="Ex: 60">
            </div>
            <div class="mb-3 form-check">
                <input type="hidden" name="ativo" value="0">
                <input type="checkbox" name="ativo" value="1" class="form-check-input" id="ativo" checked>
                <label class="form-check-label" for="ativo">Ativo</label>
            </div>
            <button type="submit" class="btn btn-success w-100">
                <i class="bi bi-save"></i> Cadastrar
            </button>
        </form>
    </div>
</div>

@endsection