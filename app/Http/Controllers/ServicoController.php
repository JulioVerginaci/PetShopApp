<?php
namespace App\Http\Controllers;

use App\Models\Servico;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ServicoController extends Controller
{
    private function usuario(): Usuario
    {
        /** @var Usuario $u */
        $u = Auth::user();
        return $u;
    }

    private function apenasFunc(): void
    {
        abort_unless($this->usuario()->isFuncionario(), 403);
    }

    public function index()
    {
        $this->apenasFunc();
        $rows     = DB::table('servicos')->orderBy('nome')->paginate(15);
        $servicos = $rows;
        return view('servicos.index', compact('servicos'));
    }

    public function create()
    {
        $this->apenasFunc();
        return view('servicos.create');
    }

    public function store(Request $request)
    {
        $this->apenasFunc();
        $data = $request->validate([
            'nome'            => ['required', 'string', 'max:255'],
            'descricao'       => ['nullable', 'string'],
            'valor'           => ['required', 'numeric', 'min:0'],
            'duracao_minutos' => ['nullable', 'integer', 'min:1'],
        ]);
        $data['ativo'] = $request->boolean('ativo', true);
        Servico::create($data);
        return redirect()->route('servicos.index')->with('sucesso', 'Serviço cadastrado!');
    }

    public function edit(Servico $servico)
    {
        $this->apenasFunc();
        return view('servicos.edit', compact('servico'));
    }

    public function update(Request $request, Servico $servico)
    {
        $this->apenasFunc();
        $data = $request->validate([
            'nome'            => ['required', 'string', 'max:255'],
            'descricao'       => ['nullable', 'string'],
            'valor'           => ['required', 'numeric', 'min:0'],
            'duracao_minutos' => ['nullable', 'integer', 'min:1'],
        ]);
        $data['ativo'] = $request->boolean('ativo');
        $servico->update($data);
        return redirect()->route('servicos.index')->with('sucesso', 'Serviço atualizado!');
    }

    public function toggleAtivo(Servico $servico)
    {
        $this->apenasFunc();
        $servico->update(['ativo' => !$servico->ativo]);
        return back()->with('sucesso', 'Status alterado.');
    }
}