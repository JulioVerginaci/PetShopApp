<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    protected $table    = 'produtos';
    protected $fillable = ['nome','descricao','valor','ativo'];
    protected $casts    = ['ativo'=>'boolean','valor'=>'decimal:2'];

public function scopeAtivo(\Illuminate\Database\Eloquent\Builder $q) { return $q->where('ativo', true); }
}