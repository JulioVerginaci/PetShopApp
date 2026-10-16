<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AgendamentoController;
use App\Http\Controllers\PetController;
use App\Http\Controllers\ServicoController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\OrdemServicoController;
use App\Http\Controllers\FinanceiroController;

// Públicas
Route::middleware('guest')->group(function () {
    Route::get('/login',[AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',[AuthController::class, 'login']);
    Route::get('/cadastro',[AuthController::class, 'showCadastro'])->name('cadastro');
    Route::post('/cadastro',[AuthController::class, 'cadastro']);
});

Route::post('/logout',[AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Autenticadas
Route::middleware('auth')->group(function () {

    Route::get('/', fn() => redirect()->route('agendamentos.index'));

    // Cadastro de funcionário (apenas um funcionário logado pode cadastrar outro)
    Route::get('/funcionarios/novo',[AuthController::class, 'showCadastroFuncionario'])->name('funcionarios.create');
    Route::post('/funcionarios/novo',[AuthController::class, 'cadastroFuncionario'])->name('funcionarios.store');

    // Agendamentos
    Route::get('/agendamentos',[AgendamentoController::class, 'index'])->name('agendamentos.index');
    Route::get('/agendamentos/novo',[AgendamentoController::class, 'create'])->name('agendamentos.create');
    Route::post('/agendamentos',[AgendamentoController::class, 'store'])->name('agendamentos.store');
    Route::get('/agendamentos/{agendamento}',[AgendamentoController::class, 'show'])->name('agendamentos.show');
    Route::get('/agendamentos/{agendamento}/editar',[AgendamentoController::class, 'edit'])->name('agendamentos.edit');
    Route::put('/agendamentos/{agendamento}',[AgendamentoController::class, 'update'])->name('agendamentos.update');
    Route::patch('/agendamentos/{agendamento}/cancelar',[AgendamentoController::class, 'cancelar'])->name('agendamentos.cancelar');
    Route::patch('/agendamentos/{agendamento}/status',[AgendamentoController::class, 'alterarStatus'])->name('agendamentos.status');

    // Pets
    Route::get('/pets',[PetController::class, 'index'])->name('pets.index');
    Route::get('/pets/novo',[PetController::class, 'create'])->name('pets.create');
    Route::post('/pets',[PetController::class, 'store'])->name('pets.store');
    Route::get('/pets/{pet}/editar', [PetController::class, 'edit'])->name('pets.edit');
    Route::put('/pets/{pet}',[PetController::class, 'update'])->name('pets.update');
    Route::delete('/pets/{pet}',[PetController::class, 'destroy'])->name('pets.destroy');

    // Ordens
    Route::get('/ordens',[OrdemServicoController::class, 'index'])->name('ordens.index');
    Route::get ('/agendamentos/{agendamento}/ordem/nova',[OrdemServicoController::class, 'create'])->name('ordens.create');
    Route::post('/agendamentos/{agendamento}/ordem',[OrdemServicoController::class, 'store'])->name('ordens.store');
    Route::get('/ordens/{ordem}',[OrdemServicoController::class, 'show'])->name('ordens.show');
    Route::patch('/ordens/{ordem}/pagamento',[OrdemServicoController::class, 'togglePago'])->name('ordens.toggle-pago');

    // Serviços (funcionário)
    Route::get('/servicos',[ServicoController::class, 'index'])->name('servicos.index');
    Route::get('/servicos/novo',[ServicoController::class, 'create'])->name('servicos.create');
    Route::post('/servicos',[ServicoController::class, 'store'])->name('servicos.store');
    Route::get('/servicos/{servico}/editar',[ServicoController::class, 'edit'])->name('servicos.edit');
    Route::put('/servicos/{servico}',[ServicoController::class, 'update'])->name('servicos.update');
    Route::patch('/servicos/{servico}/toggle',[ServicoController::class, 'toggleAtivo'])->name('servicos.toggle');

    // Produtos (funcionário)
    Route::get('/produtos',[ProdutoController::class, 'index'])->name('produtos.index');
    Route::get('/produtos/novo',[ProdutoController::class, 'create'])->name('produtos.create');
    Route::post('/produtos',[ProdutoController::class, 'store'])->name('produtos.store');
    Route::get('/produtos/{produto}/editar',[ProdutoController::class, 'edit'])->name('produtos.edit');
    Route::put('/produtos/{produto}',[ProdutoController::class, 'update'])->name('produtos.update');
    Route::patch('/produtos/{produto}/toggle',[ProdutoController::class, 'toggleAtivo'])->name('produtos.toggle');

    // Financeiro (funcionário)
    Route::get('/financeiro',[FinanceiroController::class, 'index'])->name('financeiro.index');
Route::patch(
    '/financeiro/ordens/{ordem}/pagamento',
    [FinanceiroController::class, 'atualizarPagamento']
)->name('financeiro.pagamento');


});