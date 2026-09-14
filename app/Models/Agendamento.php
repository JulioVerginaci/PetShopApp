<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Agendamento extends Model
{
    protected $table    = 'agendamentos';
    protected $fillable = ['usuario_id','pet_id','servico_id','data_agendamento','hora_agendamento','status'];
    protected $casts    = ['data_agendamento'=>'date'];

    const STATUS_PENDENTE   = 'pendente';
    const STATUS_CONFIRMADO = 'confirmado';
    const STATUS_CONCLUIDO  = 'concluido';
    const STATUS_CANCELADO  = 'cancelado';

    public static function statusLabels(): array
    {
        return [
            self::STATUS_PENDENTE   => 'Pendente',
            self::STATUS_CONFIRMADO => 'Confirmado',
            self::STATUS_CONCLUIDO  => 'Concluído',
            self::STATUS_CANCELADO  => 'Cancelado',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }

    public function usuario()     { return $this->belongsTo(Usuario::class,  'usuario_id'); }
    public function pet()         { return $this->belongsTo(Pet::class,       'pet_id'); }
    public function servico()     { return $this->belongsTo(Servico::class,   'servico_id'); }
    public function ordemServico(){ return $this->hasOne(OrdemServico::class, 'agendamento_id'); }

    public function podeCancelar(): bool
    {
        return in_array($this->status, [self::STATUS_PENDENTE, self::STATUS_CONFIRMADO]);
    }
}