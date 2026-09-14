<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Servico extends Model
{
    protected $table    = 'servicos';
    protected $fillable = ['nome','descricao','valor','ativo','duracao_minutos'];
    protected $casts    = ['ativo'=>'boolean','valor'=>'decimal:2','duracao_minutos'=>'integer'];

public function scopeAtivo(\Illuminate\Database\Eloquent\Builder $q) { return $q->where('ativo', true); }}