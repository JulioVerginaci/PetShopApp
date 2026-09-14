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

    public function index(Request $request)
    {
        abort_unless($this->usuario()->isFuncionario(), 403);

        $mes = $request->input('mes', now()->format('Y-m'));
        [$ano, $mesNum] = explode('-', (string) $mes);

        $ordens = OrdemServico::with(['agendamento.usuario', 'agendamento.pet'])
            ->whereHas('agendamento', function ($q) use ($ano, $mesNum) {
                /** @var \Illuminate\Database\Eloquent\Builder $q */
                $q->whereYear('data_agendamento', $ano)
                  ->whereMonth('data_agendamento', $mesNum);
            })
            ->orderByDesc('created_at')
            ->get();

        $totalMes = $ordens->sum('valor_total');

        $porStatus = DB::table('agendamentos')
            ->selectRaw('status, count(*) as total')
            ->whereYear('data_agendamento', $ano)
            ->whereMonth('data_agendamento', $mesNum)
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('financeiro.index', compact('ordens', 'totalMes', 'porStatus', 'mes'));
    }
}