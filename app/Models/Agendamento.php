<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agendamento extends Model
{
    protected $table = 'agendamentos';

    protected $fillable = [
        'usuario_id',
        'pet_id',
        'servico_id',
        'data_agendamento',
        'hora_agendamento',
        'status',
    ];

    protected $casts = [
        'data_agendamento' => 'date',
    ];

    public const STATUS_PENDENTE = 'pendente';
    public const STATUS_CONFIRMADO = 'confirmado';
    public const STATUS_CONCLUIDO = 'concluido';
    public const STATUS_CANCELADO = 'cancelado';

    public static function horariosPermitidos(): array
    {
        return [
            '08:00', '08:30',
            '09:00', '09:30',
            '10:00', '10:30',
            '11:00', '11:30',
            '12:00',
            '13:30', '14:00', '14:30',
            '15:00', '15:30',
            '16:00', '16:30',
            '17:00', '17:30',
            '18:00',
        ];
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_PENDENTE => 'Pendente',
            self::STATUS_CONFIRMADO => 'Confirmado',
            self::STATUS_CONCLUIDO => 'Concluído',
            self::STATUS_CANCELADO => 'Cancelado',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function pet()
    {
        return $this->belongsTo(Pet::class, 'pet_id');
    }

    public function servico()
    {
        return $this->belongsTo(Servico::class, 'servico_id');
    }

    public function ordemServico()
    {
        return $this->hasOne(OrdemServico::class, 'agendamento_id');
    }

    public function ocupaHorario(): bool
    {
        return $this->status !== self::STATUS_CANCELADO;
    }

    public function podeCancelar(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDENTE,
            self::STATUS_CONFIRMADO,
        ], true);
    }
}
