@extends('layouts.app')
@section('titulo', 'Nova Ordem de Serviço')
@section('conteudo')

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('agendamentos.show', $agendamento) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h4 class="mb-0"><i class="bi bi-receipt"></i> Nova Ordem de Serviço</h4>
</div>

<div class="alert alert-light border mb-4">
    <strong>Agendamento #{{ $agendamento->id }}</strong> —
    {{ $agendamento->pet->nome }} ({{ $agendamento->usuario->nome }}) —
    {{ $agendamento->servico->nome }} —
    {{ \Carbon\Carbon::parse($agendamento->data_agendamento)->format('d/m/Y') }}
    às {{ substr($agendamento->hora_agendamento, 0, 5) }}
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<form action="{{ route('ordens.store', $agendamento) }}" method="POST">
    @csrf

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-tools"></i> Serviços</span>
                    <button type="button" class="btn btn-sm btn-outline-success" id="addServico">
                        <i class="bi bi-plus"></i> Adicionar
                    </button>
                </div>
                <div class="card-body" id="listaServicos">
                    <div class="row g-2 mb-2 item-row">
                        <div class="col-8">
                            <select name="servicos[0][servico_id]" class="form-select form-select-sm">
                                <option value="">Selecione...</option>
                                @foreach($servicos as $s)
                                    <option value="{{ $s->id }}">
                                        {{ $s->nome }} — R$ {{ number_format($s->valor, 2, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-2">
                            <input type="number" name="servicos[0][quantidade]"
                                   class="form-control form-control-sm" value="1" min="1">
                        </div>
                        <div class="col-2">
                            <button type="button" class="btn btn-sm btn-outline-danger remover">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-box-seam"></i> Produtos</span>
                    <button type="button" class="btn btn-sm btn-outline-success" id="addProduto">
                        <i class="bi bi-plus"></i> Adicionar
                    </button>
                </div>
                <div class="card-body" id="listaProdutos">
                    <div class="row g-2 mb-2 item-row">
                        <div class="col-8">
                            <select name="produtos[0][produto_id]" class="form-select form-select-sm">
                                <option value="">Selecione...</option>
                                @foreach($produtos as $p)
                                    <option value="{{ $p->id }}">
                                        {{ $p->nome }} — R$ {{ number_format($p->valor, 2, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-2">
                            <input type="number" name="produtos[0][quantidade]"
                                   class="form-control form-control-sm" value="1" min="1">
                        </div>
                        <div class="col-2">
                            <button type="button" class="btn btn-sm btn-outline-danger remover">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <label class="form-label fw-semibold">Observações</label>
                    <textarea name="observacoes" class="form-control" rows="2"
                              placeholder="Opcional...">{{ old('observacoes') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-success btn-lg">
            <i class="bi bi-receipt"></i> Gerar Ordem
        </button>
    </div>
</form>

@push('scripts')
<script>
    @php
        $servicosOpts = $servicos->map(fn($s) => ['id'=>$s->id,'nome'=>$s->nome,'valor'=>number_format($s->valor,2,',','.')]);
        $produtosOpts = $produtos->map(fn($p) => ['id'=>$p->id,'nome'=>$p->nome,'valor'=>number_format($p->valor,2,',','.')]);
    @endphp
    const servicosOpts = @json($servicosOpts);
    const produtosOpts = @json($produtosOpts);

    let si = 1, pi = 1;

    function novaLinhaServico(idx) {
        let opts = '<option value="">Selecione...</option>';
        servicosOpts.forEach(s => opts += `<option value="${s.id}">${s.nome} — R$ ${s.valor}</option>`);
        return `<div class="row g-2 mb-2 item-row">
            <div class="col-8"><select name="servicos[${idx}][servico_id]" class="form-select form-select-sm">${opts}</select></div>
            <div class="col-2"><input type="number" name="servicos[${idx}][quantidade]" class="form-control form-control-sm" value="1" min="1"></div>
            <div class="col-2"><button type="button" class="btn btn-sm btn-outline-danger remover"><i class="bi bi-trash"></i></button></div>
        </div>`;
    }

    function novaLinhaProduto(idx) {
        let opts = '<option value="">Selecione...</option>';
        produtosOpts.forEach(p => opts += `<option value="${p.id}">${p.nome} — R$ ${p.valor}</option>`);
        return `<div class="row g-2 mb-2 item-row">
            <div class="col-8"><select name="produtos[${idx}][produto_id]" class="form-select form-select-sm">${opts}</select></div>
            <div class="col-2"><input type="number" name="produtos[${idx}][quantidade]" class="form-control form-control-sm" value="1" min="1"></div>
            <div class="col-2"><button type="button" class="btn btn-sm btn-outline-danger remover"><i class="bi bi-trash"></i></button></div>
        </div>`;
    }

    document.getElementById('addServico').addEventListener('click', () => {
        document.getElementById('listaServicos').insertAdjacentHTML('beforeend', novaLinhaServico(si++));
    });
    document.getElementById('addProduto').addEventListener('click', () => {
        document.getElementById('listaProdutos').insertAdjacentHTML('beforeend', novaLinhaProduto(pi++));
    });
    document.addEventListener('click', e => {
        if (e.target.closest('.remover')) e.target.closest('.item-row').remove();
    });
</script>
@endpush

@endsection