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

        $ordemExistente = OrdemServico::query()
            ->where('agendamento_id', $agendamento->id)
            ->first();

        if ($ordemExistente !== null) {
            return redirect()->route('ordens.show', $ordemExistente);
        }

        $servicos = Servico::query()
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();

        $produtos = Produto::query()
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();

        $agendamento->load(['usuario', 'pet', 'servico']);

        return view(
            'ordens.create',
            compact('agendamento', 'servicos', 'produtos')
        );
    }

    public function store(Request $request, Agendamento $agendamento)
    {
        abort_unless($this->usuario()->isFuncionario(), 403);

        $ordemExistente = OrdemServico::query()
            ->where('agendamento_id', $agendamento->id)
            ->first();

        if ($ordemExistente !== null) {
            return redirect()->route('ordens.show', $ordemExistente);
        }

        $data = $request->validate([
            'observacoes' => ['nullable', 'string'],

            'servicos' => ['nullable', 'array'],
            'servicos.*.servico_id' => [
                'required',
                'exists:servicos,id',
            ],
            'servicos.*.quantidade' => [
                'required',
                'integer',
                'min:1',
            ],

            'produtos' => ['nullable', 'array'],
            'produtos.*.produto_id' => [
                'required',
                'exists:produtos,id',
            ],
            'produtos.*.quantidade' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        DB::transaction(function () use ($data, $agendamento) {
            $ordem = OrdemServico::query()->create([
                'agendamento_id' => $agendamento->id,
                'observacoes' => $data['observacoes'] ?? null,
                'status' => OrdemServico::STATUS_PENDENTE,
                'valor_total' => 0,
                'forma_pagamento' => null,
            ]);

            foreach ($data['servicos'] ?? [] as $item) {
                $servico = Servico::query()
                    ->findOrFail($item['servico_id']);

                OrdemServicoServico::query()->create([
                    'ordem_servico_id' => $ordem->id,
                    'servico_id' => $servico->id,
                    'valor_unitario' => $servico->valor,
                    'quantidade' => $item['quantidade'],
                ]);
            }

            foreach ($data['produtos'] ?? [] as $item) {
                $produto = Produto::query()
                    ->findOrFail($item['produto_id']);

                OrdemServicoProduto::query()->create([
                    'ordem_servico_id' => $ordem->id,
                    'produto_id' => $produto->id,
                    'valor_unitario' => $produto->valor,
                    'quantidade' => $item['quantidade'],
                ]);
            }

            $ordem->load('itensServico', 'itensProduto');
            $ordem->recalcularTotal();

            $agendamento->update([
                'status' => Agendamento::STATUS_CONCLUIDO,
            ]);
        });

        return redirect()
            ->route('agendamentos.index')
            ->with('sucesso', 'Ordem gerada!');
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

        if (
            $usuario->isCliente()
            && $ordem->agendamento->usuario_id !== $usuario->id
        ) {
            abort(403);
        }

        return view('ordens.show', compact('ordem'));
    }

    /**
     * Horários permitidos para os agendamentos.
     */
    private function horariosPermitidos(): array
    {
        return [
            '08:00', '08:30',
            '09:00', '09:30',
            '10:00', '10:30',
            '11:00', '11:30',
            '12:00', '13:30',
            '14:00', '14:30',
            '15:00', '15:30',
            '16:00', '16:30',
            '17:00', '17:30',
            '18:00',
        ];
    }

    /**
     * Lista as ordens do dia e disponibiliza os clientes
     * em ordem alfabética para gerar novas ordens.
     */
    public function index(Request $request)
    {
        abort_unless($this->usuario()->isFuncionario(), 403);

        try {
            $data = $request->filled('data')
                ? \Carbon\Carbon::parse(
                    $request->input('data')
                )->toDateString()
                : now()->toDateString();
        } catch (\Throwable $e) {
            $data = now()->toDateString();
        }

        $hora = $request->input('hora', 'todos');
        $status = $request->input('status', 'todos');

        $ordens = OrdemServico::query()
            ->with([
                'agendamento.usuario',
                'agendamento.pet',
                'itensServico.servico',
                'itensProduto.produto',
            ])
            ->whereHas('agendamento', function ($q) use ($data, $hora) {
                $q->whereDate('data_agendamento', $data);

                if ($hora !== 'todos') {
                    $q->where('hora_agendamento', $hora);
                }
            })
            ->when(
                $status !== 'todos',
                fn($q) => $q->where('status', $status)
            )
            ->get()
            ->sortBy(
                fn($ordem) => $ordem->agendamento->hora_agendamento
            )
            ->values();

        $totalOrdens = $ordens->count();

        $totalPendentes = $ordens
            ->where('status', OrdemServico::STATUS_PENDENTE)
            ->count();

        /*
         * Carrega todos os clientes por nome.
         * Para cada cliente, carrega somente os agendamentos
         * da data escolhida que ainda não possuem uma OS.
         */
        $usuariosParaOS = Usuario::query()
            ->where('tipo_usuario', 'cliente')
            ->with([
                'agendamentos' => function ($q) use ($data) {
                    $q->with(['pet', 'servico'])
                        ->whereDate('data_agendamento', $data)
                        ->where(
                            'status',
                            '!=',
                            Agendamento::STATUS_CANCELADO
                        )
                        ->whereDoesntHave('ordemServico')
                        ->orderBy('hora_agendamento');
                },
            ])
            ->orderBy('nome')
            ->get();

        $horariosPermitidos = $this->horariosPermitidos();

        return view('ordens.index', compact(
            'ordens',
            'data',
            'hora',
            'status',
            'horariosPermitidos',
            'totalOrdens',
            'totalPendentes',
            'usuariosParaOS'
        ));
    }

    /**
     * Alterna o pagamento de uma OS.
     */
    public function togglePago(OrdemServico $ordem)
    {
        abort_unless($this->usuario()->isFuncionario(), 403);

        $vaiMarcarComoPago = !$ordem->estaPago();

        if ($vaiMarcarComoPago && !$ordem->forma_pagamento) {
            return redirect()
                ->route('financeiro.index')
                ->with(
                    'erro',
                    'Selecione a forma de pagamento no Financeiro antes de marcar a OS como paga.'
                );
        }

        $novoStatus = $vaiMarcarComoPago
            ? OrdemServico::STATUS_PAGO
            : OrdemServico::STATUS_PENDENTE;

        $ordem->update([
            'status' => $novoStatus,
        ]);

        return back()->with(
            'sucesso',
            $novoStatus === OrdemServico::STATUS_PAGO
                ? 'Ordem marcada como paga.'
                : 'Ordem marcada como pendente.'
        );
    }
}
