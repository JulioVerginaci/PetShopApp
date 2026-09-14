<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pet extends Model
{
    protected $table = 'pets';

    // Apenas 'nome' e 'usuario_id' permitidos para preenchimento em massa
    protected $fillable = [
        'nome', 'usuario_id'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function agendamentos()
    {
        return $this->hasMany(Agendamento::class);
    }
}