<?php
namespace App\Http\Controllers;

use App\Models\Pet;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PetController extends Controller
{
    private function usuario(): Usuario
    {
        /** @var Usuario $u */
        $u = Auth::user();
        return $u;
    }

    public function index()
    {
        $pets = $this->usuario()->pets()->orderBy('nome')->get();
        return view('pets.index', compact('pets'));
    }

    public function create()
    {
        return view('pets.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate(['nome' => ['required', 'string', 'max:100']]);
        $this->usuario()->pets()->create($data);
        return redirect()->route('pets.index')->with('sucesso', 'Pet cadastrado!');
    }

    public function edit(Pet $pet)
    {
        abort_unless((int) $pet->usuario_id === (int) Auth::id(), 403);
        return view('pets.edit', compact('pet'));
    }

    public function update(Request $request, Pet $pet)
    {
        abort_unless((int) $pet->usuario_id === (int) Auth::id(), 403);
        $data = $request->validate(['nome' => ['required', 'string', 'max:100']]);
        $pet->update($data);
        return redirect()->route('pets.index')->with('sucesso', 'Pet atualizado!');
    }

    public function destroy(Pet $pet)
    {
        abort_unless((int) $pet->usuario_id === (int) Auth::id(), 403);
        DB::table('pets')->where('id', $pet->id)->delete();
        return redirect()->route('pets.index')->with('sucesso', 'Pet removido.');
    }
}