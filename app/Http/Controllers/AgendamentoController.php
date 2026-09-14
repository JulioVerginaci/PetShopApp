<?php
namespace App\Http\Controllers;

use App\Models\Agendamento;
use App\Models\Pet;
use App\Models\Servico;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgendamentoController extends Controller
{
    private function usuario(): Usuario
    {
        /** @var Usuario $u */
        $u = Auth::user();
        return $u;
    }

    public function index(Request $request)
    {
        $usuario = $this->usuario();

        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = Agendamento::query()->with(['usuario', 'pet', 'servico'])
            ->orderByDesc('data_agendamento')
            ->orderByDesc('hora_agendamento');

        if ($usuario->isCliente()) {
            $query->where('usuario_id', $usuario->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $agendamentos = $query->paginate(15);
        return view('agendamento.index', compact('agendamentos'));
    }

    public function create()
    {
        $usuario  = $this->usuario();
        $pets     = $usuario->isCliente()
            ? $usuario->pets()->orderBy('nome')->get()
            : Pet::query()->with('usuario')->orderBy('nome')->get();
        $servicos = Servico::query()->where('ativo', true)->orderBy('nome')->get();

        return view('agendamento.create', compact('pets', 'servicos'));
    }

    public function store(Request $request)
    {
        $usuario = $this->usuario();

        // Lista de horários permitidos e mensagens traduzidas
        $horariosPermitidos = '08:00,08:30,09:00,09:30,10:00,10:30,11:00,11:30,12:00,13:30,14:00,14:30,15:00,15:30,16:00,16:30,17:00,17:30,18:00';
        
        $mensagens = [
            'required' => 'Preencha este campo obrigatório.',
            'pet_id.exists' => 'O pet selecionado é inválido.',
            'servico_id.exists' => 'O serviço selecionado é inválido.',
            'data_agendamento.date' => 'Insira uma data válida.',
            'data_agendamento.after_or_equal' => 'A data do agendamento deve ser de hoje em diante.',
            'hora_agendamento.in' => 'Por favor, selecione um horário válido dentro do nosso expediente.',
        ];

        $data = $request->validate([
            'pet_id'           => ['required', 'exists:pets,id'],
            'servico_id'       => ['required', 'exists:servicos,id'],
            'data_agendamento' => ['required', 'date', 'after_or_equal:today'],
            'hora_agendamento' => ['required', 'date_format:H:i', 'in:' . $horariosPermitidos],
        ], $mensagens);

        if ($usuario->isCliente()) {
            /** @var Pet $pet */
            $pet = Pet::query()->where('id', $data['pet_id'])
                ->where('usuario_id', $usuario->id)
                ->first();
            abort_unless($pet !== null, 403);
        }

        $conflito = Agendamento::query()
            ->where('pet_id', $data['pet_id'])
            ->where('data_agendamento', $data['data_agendamento'])
            ->where('hora_agendamento', $data['hora_agendamento'])
            ->where('status', '!=', Agendamento::STATUS_CANCELADO)
            ->exists();

        if ($conflito) {
            return back()
                ->withErrors(['hora_agendamento' => 'Já existe agendamento para este pet neste horário.'])
                ->withInput();
        }

        Agendamento::query()->create([
            'usuario_id'       => $usuario->id,
            'pet_id'           => $data['pet_id'],
            'servico_id'       => $data['servico_id'],
            'data_agendamento' => $data['data_agendamento'],
            'hora_agendamento' => $data['hora_agendamento'],
            'status'           => Agendamento::STATUS_PENDENTE,
        ]);

        return redirect()->route('agendamentos.index')->with('sucesso', 'Agendamento criado!');
    }

    public function show(Agendamento $agendamento)
    {
        $this->autorizar($agendamento);
        $agendamento->load([
            'pet', 'servico', 'usuario',
            'ordemServico.itensServico.servico',
            'ordemServico.itensProduto.produto',
        ]);
        return view('agendamento.show', compact('agendamento'));
    }

    public function edit(Agendamento $agendamento)
    {
        $this->autorizar($agendamento);
        abort_unless($agendamento->podeCancelar(), 403);

        $usuario  = $this->usuario();
        $pets     = $usuario->isCliente()
            ? $usuario->pets()->orderBy('nome')->get()
            : Pet::query()->with('usuario')->orderBy('nome')->get();
        $servicos = Servico::query()->where('ativo', true)->orderBy('nome')->get();

        return view('agendamento.edit', compact('agendamento', 'pets', 'servicos'));
    }

    public function update(Request $request, Agendamento $agendamento)
    {
        $this->autorizar($agendamento);
        abort_unless($agendamento->podeCancelar(), 403);

        $horariosPermitidos = '08:00,08:30,09:00,09:30,10:00,10:30,11:00,11:30,12:00,13:30,14:00,14:30,15:00,15:30,16:00,16:30,17:00,17:30,18:00';
        
        $mensagens = [
            'required' => 'Preencha este campo obrigatório.',
            'pet_id.exists' => 'O pet selecionado é inválido.',
            'servico_id.exists' => 'O serviço selecionado é inválido.',
            'data_agendamento.date' => 'Insira uma data válida.',
            'data_agendamento.after_or_equal' => 'A data do agendamento deve ser de hoje em diante.',
            'hora_agendamento.in' => 'Por favor, selecione um horário válido dentro do nosso expediente.',
        ];

        $data = $request->validate([
            'pet_id'           => ['required', 'exists:pets,id'],
            'servico_id'       => ['required', 'exists:servicos,id'],
            'data_agendamento' => ['required', 'date', 'after_or_equal:today'],
            'hora_agendamento' => ['required', 'date_format:H:i', 'in:' . $horariosPermitidos],
        ], $mensagens);

        $conflito = Agendamento::query()
            ->where('pet_id', $data['pet_id'])
            ->where('data_agendamento', $data['data_agendamento'])
            ->where('hora_agendamento', $data['hora_agendamento'])
            ->where('status', '!=', Agendamento::STATUS_CANCELADO)
            ->where('id', '!=', $agendamento->id)
            ->exists();

        if ($conflito) {
            return back()->withErrors(['hora_agendamento' => 'Conflito de horário.'])->withInput();
        }

        $agendamento->update($data);
        return redirect()->route('agendamentos.show', $agendamento)->with('sucesso', 'Atualizado!');
    }

    public function cancelar(Agendamento $agendamento)
    {
        $this->autorizar($agendamento);
        abort_unless($agendamento->podeCancelar(), 403);
        $agendamento->update(['status' => Agendamento::STATUS_CANCELADO]);
        return redirect()->route('agendamentos.index')->with('sucesso', 'Agendamento cancelado.');
    }

    public function alterarStatus(Request $request, Agendamento $agendamento)
    {
        abort_unless($this->usuario()->isFuncionario(), 403);
        $data = $request->validate([
            'status' => ['required', 'in:pendente,confirmado,concluido,cancelado'],
        ]);
        $agendamento->update($data);
        return back()->with('sucesso', 'Status atualizado!');
    }

    private function autorizar(Agendamento $agendamento): void
    {
        $usuario = $this->usuario();
        if ($usuario->isCliente() && $agendamento->usuario_id !== $usuario->id) {
            abort(403);
        }
    }
}