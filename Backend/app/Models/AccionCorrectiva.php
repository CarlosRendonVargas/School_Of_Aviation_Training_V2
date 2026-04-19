<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccionCorrectiva extends Model
{
    protected $table = 'acciones_correctivas';

    protected $fillable = [
        'reporte_sms_id', 'descripcion', 'responsable_id',
        'fecha_limite', 'estado', 'evidencia_url',
        'fecha_cierre', 'observaciones_cierre',
    ];

    protected $casts = [
        'fecha_limite' => 'date',
        'fecha_cierre' => 'date',
    ];

    public function reporte(): BelongsTo
    {
        return $this->belongsTo(ReporteSms::class, 'reporte_sms_id');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'responsable_id');
    }

    public function scopePendientes($query)
    {
        return $query->whereIn('estado', ['pendiente', 'en_proceso']);
    }

    public function scopeVencidas($query)
    {
        return $query->whereIn('estado', ['pendiente', 'en_proceso'])
                     ->where('fecha_limite', '<', now()->toDateString());
    }

    public function cerrar(string $observaciones, ?string $evidenciaUrl = null): void
    {
        $this->update([
            'estado'               => 'cerrada',
            'fecha_cierre'         => now()->toDateString(),
            'observaciones_cierre' => $observaciones,
            'evidencia_url'        => $evidenciaUrl ?? $this->evidencia_url,
        ]);
    }
}
