@extends('layouts.app')
@section('titulo', 'Novo Pet')
@section('conteudo')

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('pets.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h4 class="mb-0"><i class="bi bi-plus-circle"></i> Novo Pet</h4>
</div>

<div class="card shadow-sm" style="max-width:400px">
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
        @endif
        <form action="{{ route('pets.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold">Nome do pet</label>
                <input type="text" name="nome" class="form-control"
                       value="{{ old('nome') }}" placeholder="Ex: Rex, Luna..." required autofocus>
            </div>
            <button type="submit" class="btn btn-success w-100">
                <i class="bi bi-heart"></i> Cadastrar Pet
            </button>
        </form>
    </div>
</div>

@endsection