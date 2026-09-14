<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class OrdemServicoProduto extends Model
{
    protected $table    = 'ordem_servico_produtos';
    protected $fillable = ['ordem_servico_id','produto_id','valor_unitario','quantidade'];
    protected $casts    = ['valor_unitario'=>'decimal:2'];

    public function produto()     { return $this->belongsTo(Produto::class, 'produto_id'); }
    public function getSubtotalAttribute(): float { return $this->valor_unitario * $this->quantidade; }
}