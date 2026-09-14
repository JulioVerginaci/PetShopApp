<?php
namespace App\Http\Controllers;

use App\Models\Agendamento;
use App\Models\OrdemServico;
use App\Models\OrdemServicoServico;
use App\Models\OrdemServicoProduto;
use App\Models\Servico;
use App\Models\Produto;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrdemServicoController extends Controller
{
    private function usuario(): Usuario
    {
        /** @var Usuario $u */
        $u = Auth::user();
        return $u;
    }

    public function create(Agendamento $agendamento)
    {
        abort_unless($this->usuario()->isFuncionario(), 403);

        /** @var OrdemServico|null $ordemExistente */
        $ordemExistente = OrdemServico::query()
            ->where('agendamento_id', $agendamento->id)
            ->first();

        if ($ordemExistente !== null) {
            return redirect()->route('ordens.show', $ordemExistente);
        }

        $servicos = Servico::query()->where('ativo', true)->orderBy('nome')->get();
        $produtos = Produto::query()->where('ativo', true)->orderBy('nome')->get();

        return view('ordens.create', compact('agendamento', 'servicos', 'produtos'));
    }

    public function store(Request $request, Agendamento $agendamento)
    {
        abort_unless($this->usuario()->isFuncionario(), 403);

        /** @var OrdemServico|null $ordemExistente */
        $ordemExistente = OrdemServico::query()
            ->where('agendamento_id', $agendamento->id)
            ->first();

        if ($ordemExistente !== null) {
            return redirect()->route('ordens.show', $ordemExistente);
        }

        $data = $request->validate([
            'observacoes'           => ['nullable', 'string'],
            'servicos'              => ['nullable', 'array'],
            'servicos.*.servico_id' => ['required', 'exists:servicos,id'],
            'servicos.*.quantidade' => ['required', 'integer', 'min:1'],
            'produtos'              => ['nullable', 'array'],
            'produtos.*.produto_id' => ['required', 'exists:produtos,id'],
            'produtos.*.quantidade' => ['required', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($data, $agendamento) {
            /** @var OrdemServico $ordem */
            $ordem = OrdemServico::query()->create([
                'agendamento_id' => $agendamento->id,
                'observacoes'    => $data['observacoes'] ?? null,
                'status'         => OrdemServico::STATUS_PENDENTE,
                'valor_total'    => 0,
            ]);

            foreach ($data['servicos'] ?? [] as $item) {
                /** @var Servico $s */
                $s = Servico::query()->findOrFail($item['servico_id']);
                OrdemServicoServico::query()->create([
                    'ordem_servico_id' => $ordem->id,
                    'servico_id'       => $s->id,
                    'valor_unitario'   => $s->valor,
                    'quantidade'       => $item['quantidade'],
                ]);
            }

            foreach ($data['produtos'] ?? [] as $item) {
                /** @var Produto $p */
                $p = Produto::query()->findOrFail($item['produto_id']);
                OrdemServicoProduto::query()->create([
                    'ordem_servico_id' => $ordem->id,
                    'produto_id'       => $p->id,
                    'valor_unitario'   => $p->valor,
                    'quantidade'       => $item['quantidade'],
                ]);
            }

            $ordem->load('itensServico', 'itensProduto');
            $ordem->recalcularTotal();

            $agendamento->update(['status' => Agendamento::STATUS_CONCLUIDO]);
        });

        return redirect()->route('agendamentos.index')->with('sucesso', 'Ordem gerada!');
    }

    public function show(OrdemServico $ordem)
    {
        $usuario = $this->usuario();
        $ordem->load([
            'agendamento.usuario',
            'agendamento.pet',
            'agendamento.servico',
            'itensServico.servico',
            'itensProduto.produto',
        ]);

        if ($usuario->isCliente() && $ordem->agendamento->usuario_id !== $usuario->id) {
            abort(403);
        }

        return view('ordens.show', compact('ordem'));
    }

    /**
     * Grade fixa de horários do petshop (mesma lista usada em AgendamentoController).
     */
    private function horariosPermitidos(): array
    {
        return ['08:00','08:30','09:00','09:30','10:00','10:30','11:00','11:30',
            '12:00','13:30','14:00','14:30','15:00','15:30','16:00','16:30','17:00','17:30','18:00'];
    }

    /**
     * Aba "Ordem de Serviço": lista as ordens do dia selecionado (com filtro de
     * horário e de status de pagamento), além dos agendamentos do dia que ainda
     * não têm ordem gerada, para permitir gerar rapidamente no balcão.
     */
    public function index(Request $request)
    {
        abort_unless($this->usuario()->isFuncionario(), 403);

        try {
            $data = $request->filled('data')
                ? \Carbon\Carbon::parse($request->input('data'))->toDateString()
                : now()->toDateString();
        } catch (\Throwable $e) {
            $data = now()->toDateString();
        }

        $hora   = $request->input('hora', 'todos');
        $status = $request->input('status', 'todos');

        $ordens = OrdemServico::query()
            ->with(['agendamento.usuario', 'agendamento.pet', 'itensServico.servico', 'itensProduto.produto'])
            ->whereHas('agendamento', function ($q) use ($data, $hora) {
                $q->whereDate('data_agendamento', $data);
                if ($hora !== 'todos') {
                    $q->where('hora_agendamento', $hora);
                }
            })
            ->when($status !== 'todos', fn($q) => $q->where('status', $status))
            ->get()
            ->sortBy(fn($o) => $o->agendamento->hora_agendamento)
            ->values();

        $totalOrdens    = $ordens->count();
        $totalPendentes = $ordens->where('status', OrdemServico::STATUS_PENDENTE)->count();

        $agendamentosSemOrdem = Agendamento::query()
            ->with(['usuario', 'pet', 'servico'])
            ->whereDate('data_agendamento', $data)
            ->whereDoesntHave('ordemServico')
            ->where('status', '!=', Agendamento::STATUS_CANCELADO)
            ->orderBy('hora_agendamento')
            ->get();

        $horariosPermitidos = $this->horariosPermitidos();

        return view('ordens.index', compact(
            'ordens', 'data', 'hora', 'status', 'horariosPermitidos',
            'totalOrdens', 'totalPendentes', 'agendamentosSemOrdem'
        ));
    }

    /**
     * Liga/desliga o pagamento de uma ordem de serviço (mesmo padrão do
     * toggleAtivo já usado em Serviços/Produtos).
     */
    public function togglePago(OrdemServico $ordem)
    {
        abort_unless($this->usuario()->isFuncionario(), 403);

        $novoStatus = $ordem->estaPago() ? OrdemServico::STATUS_PENDENTE : OrdemServico::STATUS_PAGO;
        $ordem->update(['status' => $novoStatus]);

        return back()->with('sucesso', $novoStatus === OrdemServico::STATUS_PAGO
            ? 'Ordem marcada como paga.'
            : 'Ordem marcada como pendente.');
    }
}