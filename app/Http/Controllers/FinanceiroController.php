<?php

namespace App\Http\Controllers;

use App\Models\Agendamento;
use App\Models\OrdemServico;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FinanceiroController extends Controller
{
    private function usuario(): Usuario
    {
        /** @var Usuario $u */
        $u = Auth::user();

        return $u;
    }

    /**
     * Exibe o financeiro do mês selecionado.
     */
    public function index(Request $request)
    {
        abort_unless($this->usuario()->isFuncionario(), 403);

        $mes = $request->input('mes', now()->format('Y-m'));

        // Evita erros caso seja informado um mês inválido.
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', (string) $mes)) {
            $mes = now()->format('Y-m');
        }

        [$ano, $mesNum] = explode('-', $mes);

        $ordens = OrdemServico::with([
                'agendamento.usuario',
                'agendamento.pet',
            ])
            ->whereHas('agendamento', function ($q) use ($ano, $mesNum) {
                $q->whereYear('data_agendamento', $ano)
                    ->whereMonth('data_agendamento', $mesNum);
            })
            ->orderByDesc('created_at')
            ->get();

        // Considera como recebido somente o valor das ordens pagas.
        $totalMes = $ordens
            ->where('status', OrdemServico::STATUS_PAGO)
            ->sum('valor_total');

        $porStatus = DB::table('agendamentos')
            ->selectRaw('status, count(*) as total')
            ->whereYear('data_agendamento', $ano)
            ->whereMonth('data_agendamento', $mesNum)
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('financeiro.index', compact(
            'ordens',
            'totalMes',
            'porStatus',
            'mes'
        ));
    }

    /**
     * Atualiza o status e a forma de pagamento de uma OS.
     */
    public function atualizarPagamento(
        Request $request,
        OrdemServico $ordem
    ) {
        abort_unless($this->usuario()->isFuncionario(), 403);

        $dados = $request->validate([
            'status' => [
                'required',
                'in:pendente,pago',
            ],

            'forma_pagamento' => [
                'nullable',
                'required_if:status,pago',
                'in:dinheiro,pix,cartao_credito,cartao_debito,transferencia',
            ],
        ]);

        $ordem->update([
            'status' => $dados['status'],
            'forma_pagamento' => $dados['forma_pagamento'] ?? null,
        ]);

        return redirect()
            ->route('financeiro.index', [
                'mes' => $request->input(
                    'mes',
                    optional($ordem->agendamento)->data_agendamento
                        ? \Carbon\Carbon::parse(
                            $ordem->agendamento->data_agendamento
                        )->format('Y-m')
                        : now()->format('Y-m')
                ),
            ])
            ->with('sucesso', 'Pagamento atualizado com sucesso!');
    }
}
