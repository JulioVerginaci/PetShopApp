<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable
{
    protected $table = 'usuarios';
    protected $fillable = ['nome','email','senha','tipo_usuario','telefone'];
    protected $hidden   = ['senha','remember_token'];

    public function getAuthPassword() { return 'senha'; }

    public function pets()         { return $this->hasMany(Pet::class, 'usuario_id'); }
    public function agendamentos() { return $this->hasMany(Agendamento::class, 'usuario_id'); }

    public function isFuncionario(): bool { return $this->tipo_usuario === 'funcionario'; }
    public function isCliente(): bool     { return $this->tipo_usuario === 'cliente'; }
}