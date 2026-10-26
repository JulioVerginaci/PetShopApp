<?php

namespace App\Http\Controllers;

use App\Models\Agendamento;
use App\Models\Pet;
use App\Models\Servico;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AgendamentoController extends Controller
{
    private function usuario(): Usuario
    {
        /** @var Usuario $usuario */
        $usuario = Auth::user();

        return $usuario;
    }

    public function index(Request $request)
    {
        $usuario = $this->usuario();

        $query = Agendamento::query()
            ->with(['usuario', 'pet', 'servico'])
            ->orderByDesc('data_agendamento')
            ->orderByDesc('hora_agendamento');

        if ($usuario->isCliente()) {
            $query->where('usuario_id', $usuario->id);
        }

        if ($request->filled('status')) {
            $request->validate([
                'status' => [
                    'in:pendente,confirmado,concluido,cancelado',
                ],
            ]);

            $query->where('status', $request->input('status'));
        }

        $agendamentos = $query->paginate(15);

        return view('agendamento.index', compact('agendamentos'));
    }

    public function create()
    {
        $usuario = $this->usuario();

        $pets = $usuario->isCliente()
            ? $usuario->pets()->orderBy('nome')->get()
            : Pet::query()->with('usuario')->orderBy('nome')->get();

        $servicos = Servico::query()
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();

        return view('agendamento.create', compact('pets', 'servicos'));
    }

    public function horariosDisponiveis(Request $request)
    {
        $dados = $request->validate([
            'data_agendamento' => [
                'required',
                'date',
                'date_equals:' . now()->toDateString(),
            ],
            'agendamento_id' => [
                'nullable',
                'integer',
                'exists:agendamentos,id',
            ],
        ], [
            'data_agendamento.date_equals' =>
                'Só é possível consultar horários para hoje.',
        ]);

        $agendamentoId = isset($dados['agendamento_id'])
            ? (int) $dados['agendamento_id']
            : null;

        if ($agendamentoId !== null) {
            $agendamento = Agendamento::query()
                ->findOrFail($agendamentoId);

            $this->autorizar($agendamento);

            abort_unless($agendamento->podeCancelar(), 403);
        }

        $ocupados = Agendamento::query()
            ->where(
                'data_agendamento',
                $dados['data_agendamento']
            )
            ->where(
                'status',
                '!=',
                Agendamento::STATUS_CANCELADO
            )
            ->when(
                $agendamentoId !== null,
                fn ($query) => $query->where(
                    'id',
                    '!=',
                    $agendamentoId
                )
            )
            ->pluck('hora_agendamento')
            ->map(fn ($hora) => substr((string) $hora, 0, 5))
            ->unique()
            ->values()
            ->all();

        $horariosPermitidos = Agendamento::horariosPermitidos();

        $disponiveis = array_values(array_diff(
            $horariosPermitidos,
            $ocupados
        ));

        return response()->json([
            'horarios_disponiveis' => $disponiveis,
            'horarios_ocupados' => array_values(array_intersect(
                $horariosPermitidos,
                $ocupados
            )),
        ]);
    }

    public function store(Request $request)
    {
        $usuario = $this->usuario();

        $dados = $request->validate([
            'pet_id' => [
                'required',
                'integer',
                'exists:pets,id',
            ],
            'servico_id' => [
                'required',
                'integer',
                'exists:servicos,id',
            ],
            'data_agendamento' => [
                'required',
                'date',
                'date_equals:' . now()->toDateString(),
            ],
            'hora_agendamento' => [
                'required',
                'date_format:H:i',
                'in:' . implode(',', Agendamento::horariosPermitidos()),
            ],
        ], [
            'required' => 'Preencha este campo obrigatório.',
            'pet_id.exists' => 'O pet selecionado é inválido.',
            'servico_id.exists' => 'O serviço selecionado é inválido.',
            'data_agendamento.date' => 'Insira uma data válida.',
            'data_agendamento.date_equals' =>
                'Os agendamentos só podem ser realizados para hoje.',
            'hora_agendamento.date_format' =>
                'Selecione um horário válido.',
            'hora_agendamento.in' =>
                'Selecione um horário dentro do expediente.',
        ]);

        $pet = Pet::query()->findOrFail($dados['pet_id']);

        if (
            $usuario->isCliente()
            && (int) $pet->usuario_id !== (int) $usuario->id
        ) {
            abort(403, 'Você só pode agendar os seus próprios pets.');
        }

        $criado = DB::transaction(function () use (
            $dados,
            $pet,
            $usuario
        ) {
            $this->bloquearEscritasDeAgendamento();

            if ($this->slotOcupado(
                $dados['data_agendamento'],
                $dados['hora_agendamento']
            )) {
                return false;
            }

            Agendamento::query()->create([
                'usuario_id' => $usuario->isCliente()
                    ? $usuario->id
                    : $pet->usuario_id,
                'pet_id' => $pet->id,
                'servico_id' => $dados['servico_id'],
                'data_agendamento' => $dados['data_agendamento'],
                'hora_agendamento' => $dados['hora_agendamento'],
                'status' => Agendamento::STATUS_PENDENTE,
            ]);

            return true;
        }, 3);

        if (!$criado) {
            return back()
                ->withErrors([
                    'hora_agendamento' =>
                        'Este horário acabou de ser reservado por outro atendimento. Escolha outro horário.',
                ])
                ->withInput();
        }

        return redirect()
            ->route('agendamentos.index')
            ->with('sucesso', 'Agendamento criado!');
    }

    public function show(Agendamento $agendamento)
    {
        $this->autorizar($agendamento);

        $agendamento->load([
            'pet',
            'servico',
            'usuario',
            'ordemServico.itensServico.servico',
            'ordemServico.itensProduto.produto',
        ]);

        return view('agendamento.show', compact('agendamento'));
    }

    public function edit(Agendamento $agendamento)
    {
        $this->autorizar($agendamento);

        abort_unless($agendamento->podeCancelar(), 403);

        $usuario = $this->usuario();

        $pets = $usuario->isCliente()
            ? $usuario->pets()->orderBy('nome')->get()
            : Pet::query()->with('usuario')->orderBy('nome')->get();

        $servicos = Servico::query()
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();

        return view(
            'agendamento.edit',
            compact('agendamento', 'pets', 'servicos')
        );
    }

    public function update(
        Request $request,
        Agendamento $agendamento
    ) {
        $this->autorizar($agendamento);

        abort_unless($agendamento->podeCancelar(), 403);

        $usuario = $this->usuario();

        $dados = $request->validate([
            'pet_id' => [
                'required',
                'integer',
                'exists:pets,id',
            ],
            'servico_id' => [
                'required',
                'integer',
                'exists:servicos,id',
            ],
            'data_agendamento' => [
                'required',
                'date',
                'date_equals:' . now()->toDateString(),
            ],
            'hora_agendamento' => [
                'required',
                'date_format:H:i',
                'in:' . implode(',', Agendamento::horariosPermitidos()),
            ],
        ], [
            'required' => 'Preencha este campo obrigatório.',
            'pet_id.exists' => 'O pet selecionado é inválido.',
            'servico_id.exists' => 'O serviço selecionado é inválido.',
            'data_agendamento.date' => 'Insira uma data válida.',
            'data_agendamento.date_equals' =>
                'Os agendamentos só podem ser realizados para hoje.',
            'hora_agendamento.date_format' =>
                'Selecione um horário válido.',
            'hora_agendamento.in' =>
                'Selecione um horário dentro do expediente.',
        ]);

        $pet = Pet::query()->findOrFail($dados['pet_id']);

        if (
            $usuario->isCliente()
            && (int) $pet->usuario_id !== (int) $usuario->id
        ) {
            abort(403, 'Você só pode agendar os seus próprios pets.');
        }

        $atualizado = DB::transaction(function () use (
            $dados,
            $pet,
            $usuario,
            $agendamento
        ) {
            $this->bloquearEscritasDeAgendamento();

            if ($this->slotOcupado(
                $dados['data_agendamento'],
                $dados['hora_agendamento'],
                (int) $agendamento->id
            )) {
                return false;
            }

            $agendamento->update([
                'usuario_id' => $usuario->isCliente()
                    ? $usuario->id
                    : $pet->usuario_id,
                'pet_id' => $pet->id,
                'servico_id' => $dados['servico_id'],
                'data_agendamento' => $dados['data_agendamento'],
                'hora_agendamento' => $dados['hora_agendamento'],
            ]);

            return true;
        }, 3);

        if (!$atualizado) {
            return back()
                ->withErrors([
                    'hora_agendamento' =>
                        'Esse horário já está reservado por outro atendimento. Escolha outro horário.',
                ])
                ->withInput();
        }

        return redirect()
            ->route('agendamentos.show', $agendamento)
            ->with('sucesso', 'Agendamento atualizado!');
    }

    public function cancelar(Agendamento $agendamento)
    {
        $this->autorizar($agendamento);

        abort_unless($agendamento->podeCancelar(), 403);

        DB::transaction(function () use ($agendamento) {
            $this->bloquearEscritasDeAgendamento();

            $agendamento->update([
                'status' => Agendamento::STATUS_CANCELADO,
            ]);
        }, 3);

        return redirect()
            ->route('agendamentos.index')
            ->with('sucesso', 'Agendamento cancelado.');
    }

    public function alterarStatus(
        Request $request,
        Agendamento $agendamento
    ) {
        abort_unless($this->usuario()->isFuncionario(), 403);

        $dados = $request->validate([
            'status' => [
                'required',
                'in:pendente,confirmado,concluido,cancelado',
            ],
        ]);

        $atualizado = DB::transaction(function () use (
            $agendamento,
            $dados
        ) {
            $this->bloquearEscritasDeAgendamento();

            if (
                $dados['status'] !== Agendamento::STATUS_CANCELADO
                && $this->slotOcupado(
                    $agendamento->data_agendamento->format('Y-m-d'),
                    substr(
                        (string) $agendamento->hora_agendamento,
                        0,
                        5
                    ),
                    (int) $agendamento->id
                )
            ) {
                return false;
            }

            $agendamento->update([
                'status' => $dados['status'],
            ]);

            return true;
        }, 3);

        if (!$atualizado) {
            return back()->withErrors([
                'status' =>
                    'Não é possível manter este agendamento ativo porque o horário já está ocupado por outro atendimento.',
            ]);
        }

        return back()->with('sucesso', 'Status atualizado!');
    }

    private function slotOcupado(
        string $data,
        string $hora,
        ?int $ignorarId = null
    ): bool {
        return Agendamento::query()
            ->where('data_agendamento', $data)
            ->where('hora_agendamento', $hora)
            ->where(
                'status',
                '!=',
                Agendamento::STATUS_CANCELADO
            )
            ->when(
                $ignorarId !== null,
                fn ($query) => $query->where('id', '!=', $ignorarId)
            )
            ->exists();
    }

    private function bloquearEscritasDeAgendamento(): void
    {
        DB::statement(
            'LOCK TABLE public.agendamentos IN EXCLUSIVE MODE'
        );
    }

    private function autorizar(Agendamento $agendamento): void
    {
        $usuario = $this->usuario();

        if (
            $usuario->isCliente()
            && (int) $agendamento->usuario_id !== (int) $usuario->id
        ) {
            abort(403);
        }
    }
}
