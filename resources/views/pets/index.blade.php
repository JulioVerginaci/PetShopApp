@extends('layouts.app')
@section('titulo', 'Meus Pets')
@section('conteudo')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-heart"></i> Meus Pets</h4>
    <a href="{{ route('pets.create') }}" class="btn btn-success">
        <i class="bi bi-plus-lg"></i> Novo Pet
    </a>
</div>

<div class="row g-3">
    @forelse($pets as $pet)
    <div class="col-sm-6 col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-danger bg-opacity-10 p-3">
                    <i class="bi bi-heart-fill text-danger fs-4"></i>
                </div>
                <div>
                    <h5 class="mb-0">{{ $pet->nome }}</h5>
                    <small class="text-muted">Desde {{ \Carbon\Carbon::parse($pet->created_at)->format('d/m/Y') }}</small>
                </div>
            </div>
            <div class="card-footer bg-transparent d-flex gap-2">
                <a href="{{ route('pets.edit', $pet) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-pencil"></i> Editar
                </a>
                <form action="{{ route('pets.destroy', $pet) }}" method="POST"
                      onsubmit="return confirm('Remover este pet?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Nenhum pet cadastrado.
            <a href="{{ route('pets.create') }}">Cadastrar agora</a>
        </div>
    </div>
    @endforelse
</div>

@endsection