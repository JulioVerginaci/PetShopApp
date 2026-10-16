<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdemServico extends Model
{
    protected $table = 'ordens_servico';

    protected $fillable = [
        'agendamento_id',
        'valor_total',
        'observacoes',
        'status',
        'forma_pagamento',
    ];

    protected $casts = [
        'valor_total' => 'decimal:2',
    ];

    const STATUS_PENDENTE = 'pendente';
    const STATUS_PAGO = 'pago';

    public static function statusLabels(): array
    {
        return [
            self::STATUS_PENDENTE => 'Pendente',
            self::STATUS_PAGO => 'Pago',
        ];
    }

    public static function formasPagamento(): array
    {
        return [
            'dinheiro' => 'Dinheiro',
            'pix' => 'Pix',
            'cartao_credito' => 'Cartão de crédito',
            'cartao_debito' => 'Cartão de débito',
            'transferencia' => 'Transferência',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabels()[$this->status] ?? 'Pendente';
    }

    public function getFormaPagamentoLabelAttribute(): ?string
    {
        return self::formasPagamento()[$this->forma_pagamento] ?? null;
    }

    public function estaPago(): bool
    {
        return $this->status === self::STATUS_PAGO;
    }

    public function agendamento()
    {
        return $this->belongsTo(
            Agendamento::class,
            'agendamento_id'
        );
    }

    public function itensServico()
    {
        return $this->hasMany(
            OrdemServicoServico::class,
            'ordem_servico_id'
        );
    }

    public function itensProduto()
    {
        return $this->hasMany(
            OrdemServicoProduto::class,
            'ordem_servico_id'
        );
    }

    public function recalcularTotal(): void
    {
        $s = $this->itensServico->sum(
            fn($i) => $i->valor_unitario * $i->quantidade
        );

        $p = $this->itensProduto->sum(
            fn($i) => $i->valor_unitario * $i->quantidade
        );

        $this->update([
            'valor_total' => $s + $p,
        ]);
    }
}
