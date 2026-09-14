@extends('layouts.app')
@section('titulo', 'Editar Pet')
@section('conteudo')

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('pets.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h4 class="mb-0"><i class="bi bi-pencil"></i> Editar Pet</h4>
</div>

<div class="card shadow-sm" style="max-width:400px">
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
        @endif
        <form action="{{ route('pets.update', $pet) }}" method="POST">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label fw-semibold">Nome do pet</label>
                <input type="text" name="nome" class="form-control"
                       value="{{ old('nome', $pet->nome) }}" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-save"></i> Salvar
            </button>
        </form>
    </div>
</div>

@endsection