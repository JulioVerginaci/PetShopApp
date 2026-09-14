<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Conta padrão de funcionário, para o primeiro acesso ao sistema.
        Usuario::query()->updateOrCreate(
            ['email' => 'admin@petshop.com'],
            [
                'nome'         => 'Administrador',
                'senha'        => Hash::make('123456'),
                'tipo_usuario' => 'funcionario',
                'telefone'     => null,
            ]
        );
    }
}
