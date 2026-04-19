<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BitacoraVuelo extends Model
{
    protected $table = 'bitacoras_vuelo';

    protected $fillable = [
        'estudiante_id', 'instructor_id', 'aeronave_id', 'reserva_id',
        'fecha', 'hora_salida', 'hora_llegada',
        'origen_icao', 'destino_icao',
        'horas_totales', 'horas_dual', 'horas_solo',
        'horas_noche', 'horas_ifr', 'horas_simulador',
        'tipo_vuelo', 'condiciones_vmc', 'aterrizajes',
        'firma_instructor', 'firma_estudiante', 'observaciones',
    ];

    protected $casts = [
        'fecha'           => 'date',
        'hora_salida'     => 'datetime:H:i',
        'hora_llegada'    => 'datetime:H:i',
        'horas_totales'   => 'decimal:1',
        'horas_dual'      => 'decimal:1',
        'horas_solo'      => 'decimal:1',
        'horas_noche'     => 'decimal:1',
        'horas_ifr'       => 'decimal:1',
        'horas_simulador' => 'decimal:1',
        'condiciones_vmc' => 'boolean',
    ];

    /* ─── Relaciones ─── */

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id');
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class, 'instructor_id');
    }

    public function aeronave(): BelongsTo
    {
        return $this->belongsTo(Aeronave::class, 'aeronave_id');
    }

    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }

    /* ─── Scopes ─── */

    public function scopePorEstudiante($query, int $estudianteId)
    {
        return $query->where('estudiante_id', $estudianteId);
    }

    public function scopeEntreFechas($query, string $desde, string $hasta)
    {
        return $query->whereBetween('fecha', [$desde, $hasta]);
    }

    public function scopeVueloReal($query)
    {
        return $query->where('tipo_vuelo', '!=', 'sim');
    }

    /* ─── Accessors ─── */

    public function getFirmadaAttribute(): bool
    {
        return ! empty($this->firma_estudiante);
    }

    public function getFirmadaPorInstructorAttribute(): bool
    {
        return ! empty($this->firma_instructor);
    }

    /* ─── Hooks ─── */

    protected static function booted(): void
    {
        // Al guardar una bitácora, actualizar TTAE de la aeronave
        static::saved(function (BitacoraVuelo $b) {
            $b->aeronave?->actualizarHorasCelula();
        });
    }
}
