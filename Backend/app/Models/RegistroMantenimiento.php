<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistroMantenimiento extends Model
{
    protected $table = 'registros_mantenimiento';

    protected $fillable = [
        'aeronave_id', 'tipo', 'descripcion', 'fecha_realizado',
        'horas_aeronave', 'realizado_por', 'licencia_tecnico',
        'proxima_fecha', 'proximas_horas', 'costo', 'archivo_url',
    ];

    protected $casts = [
        'fecha_realizado' => 'date',
        'proxima_fecha'   => 'date',
        'horas_aeronave'  => 'decimal:1',
        'proximas_horas'  => 'decimal:1',
        'costo'           => 'decimal:2',
    ];

    public function aeronave(): BelongsTo
    {
        return $this->belongsTo(Aeronave::class, 'aeronave_id');
    }

    public function scopeProximos($query, int $dias = 30)
    {
        return $query->where('proxima_fecha', '<=', now()->addDays($dias)->toDateString())
                     ->whereNotNull('proxima_fecha');
    }
}
