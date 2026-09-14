<?php
namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProdutoController extends Controller
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
        $produtos = DB::table('produtos')->orderBy('nome')->paginate(15);
        return view('produtos.index', compact('produtos'));
    }

    public function create()
    {
        $this->apenasFunc();
        return view('produtos.create');
    }

    public function store(Request $request)
    {
        $this->apenasFunc();
        $data = $request->validate([
            'nome'      => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string'],
            'valor'     => ['required', 'numeric', 'min:0'],
        ]);
        $data['ativo'] = $request->boolean('ativo', true);
        Produto::create($data);
        return redirect()->route('produtos.index')->with('sucesso', 'Produto cadastrado!');
    }

    public function edit(Produto $produto)
    {
        $this->apenasFunc();
        return view('produtos.edit', compact('produto'));
    }

    public function update(Request $request, Produto $produto)
    {
        $this->apenasFunc();
        $data = $request->validate([
            'nome'      => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string'],
            'valor'     => ['required', 'numeric', 'min:0'],
        ]);
        $data['ativo'] = $request->boolean('ativo');
        $produto->update($data);
        return redirect()->route('produtos.index')->with('sucesso', 'Produto atualizado!');
    }

    public function toggleAtivo(Produto $produto)
    {
        $this->apenasFunc();
        $produto->update(['ativo' => !$produto->ativo]);
        return back()->with('sucesso', 'Status alterado.');
    }
}