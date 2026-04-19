<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Reserva extends Model
{
    protected $table = 'reservas';

    protected $fillable = [
        'aeronave_id', 'estudiante_id', 'instructor_id',
        'fecha', 'hora_inicio', 'hora_fin',
        'tipo', 'estado', 'motivo_cancelacion',
    ];

    protected $casts = [
        'fecha'       => 'date',
        'hora_inicio' => 'datetime:H:i',
        'hora_fin'    => 'datetime:H:i',
    ];

    /* ─── Relaciones ─── */

    public function aeronave(): BelongsTo
    {
        return $this->belongsTo(Aeronave::class, 'aeronave_id');
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id');
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class, 'instructor_id');
    }

    public function bitacora(): HasOne
    {
        return $this->hasOne(BitacoraVuelo::class, 'reserva_id');
    }

    public function briefings(): HasMany
    {
        return $this->hasMany(BriefingDebriefing::class, 'reserva_id');
    }

    public function briefingPreVuelo(): HasOne
    {
        return $this->hasOne(BriefingDebriefing::class, 'reserva_id')->where('tipo', 'pre_vuelo');
    }

    public function debriefingPostVuelo(): HasOne
    {
        return $this->hasOne(BriefingDebriefing::class, 'reserva_id')->where('tipo', 'post_vuelo');
    }

    /* ─── Scopes ─── */

    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeConfirmadas($query)
    {
        return $query->where('estado', 'confirmada');
    }

    public function scopeDeHoy($query)
    {
        return $query->where('fecha', now()->toDateString());
    }

    public function scopePorFecha($query, string $fecha)
    {
        return $query->where('fecha', $fecha);
    }

    /* ─── Helpers ─── */

    public function confirmar(): void
    {
        $this->update(['estado' => 'confirmada']);
    }

    public function cancelar(string $motivo): void
    {
        $this->update(['estado' => 'cancelada', 'motivo_cancelacion' => $motivo]);
    }

    public function completar(): void
    {
        $this->update(['estado' => 'completada']);
    }
}
