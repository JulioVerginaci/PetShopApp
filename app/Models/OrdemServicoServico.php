<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class OrdemServicoServico extends Model
{
    protected $table    = 'ordem_servico_servicos';
    protected $fillable = ['ordem_servico_id','servico_id','valor_unitario','quantidade'];
    protected $casts    = ['valor_unitario'=>'decimal:2'];

    public function servico()     { return $this->belongsTo(Servico::class, 'servico_id'); }
    public function getSubtotalAttribute(): float { return $this->valor_unitario * $this->quantidade; }
}