<?php
namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'senha' => ['required'],
        ]);

        /** @var Usuario|null $usuario */
        $usuario = Usuario::query()->where('email', $data['email'])->first();

        if ($usuario === null || !Hash::check($data['senha'], $usuario->senha)) {
            return back()->withErrors(['email' => 'Credenciais inválidas.'])->onlyInput('email');
        }

        Auth::login($usuario, $request->boolean('lembrar'));
        $request->session()->regenerate();

        return redirect()->route('agendamentos.index');
    }

    public function showCadastro()
    {
        return view('auth.cadastro');
    }

    public function cadastro(Request $request)
    {
        $data = $request->validate([
            'nome'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:usuarios,email'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'senha'    => ['required', 'min:6', 'confirmed'],
        ]);

        /** @var Usuario $usuario */
        $usuario = Usuario::query()->create([
            'nome'         => $data['nome'],
            'email'        => $data['email'],
            'telefone'     => $data['telefone'] ?? null,
            'senha'        => Hash::make($data['senha']),
            'tipo_usuario' => 'cliente',
        ]);

        Auth::login($usuario);
        return redirect()->route('agendamentos.index');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function showCadastroFuncionario()
    {
        abort_unless($this->funcionarioLogado(), 403);
        return view('auth.cadastro_funcionario');
    }

    public function cadastroFuncionario(Request $request)
    {
        abort_unless($this->funcionarioLogado(), 403);

        $data = $request->validate([
            'nome'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:usuarios,email'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'senha'    => ['required', 'min:6', 'confirmed'],
        ]);

        Usuario::query()->create([
            'nome'         => $data['nome'],
            'email'        => $data['email'],
            'telefone'     => $data['telefone'] ?? null,
            'senha'        => Hash::make($data['senha']),
            'tipo_usuario' => 'funcionario',
        ]);

        return redirect()->route('agendamentos.index')->with('sucesso', 'Funcionário cadastrado!');
    }

    private function funcionarioLogado(): bool
    {
        /** @var Usuario|null $usuario */
        $usuario = Auth::user();
        return $usuario !== null && $usuario->tipo_usuario === 'funcionario';
    }
}