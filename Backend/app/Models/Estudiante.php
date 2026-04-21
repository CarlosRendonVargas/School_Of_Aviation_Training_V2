<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Estudiante extends Model
{
    protected $table = 'estudiantes';

    protected $fillable = [
        'persona_id', 'num_expediente', 'programa_id',
        'fecha_ingreso', 'estado', 'etapa_actual_id', 'observaciones',
    ];

    protected $casts = [
        'fecha_ingreso' => 'date',
    ];

    /* ─── Relaciones ─── */

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    public function programa(): BelongsTo
    {
        return $this->belongsTo(Programa::class, 'programa_id');
    }

    public function etapaActual(): BelongsTo
    {
        return $this->belongsTo(Etapa::class, 'etapa_actual_id');
    }

    public function bitacoras(): HasMany
    {
        return $this->hasMany(BitacoraVuelo::class, 'estudiante_id');
    }

    public function notas(): HasMany
    {
        return $this->hasMany(NotaAcademica::class, 'estudiante_id');
    }

    public function certMedicos(): HasMany
    {
        return $this->hasMany(CertMedico::class, 'estudiante_id')->orderByDesc('fecha_emision');
    }

    public function certMedicoVigente(): HasOne
    {
        return $this->hasOne(CertMedico::class, 'estudiante_id')
            ->where('fecha_vencimiento', '>=', now()->toDateString())
            ->latestOfMany('fecha_vencimiento');
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class, 'estudiante_id');
    }

    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class, 'estudiante_id');
    }

    public function reintentosAutorizados(): HasMany
    {
        return $this->hasMany(ReintentoAutorizado::class, 'estudiante_id');
    }

    /* ─── Scopes ─── */

    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopePorPrograma($query, int $programaId)
    {
        return $query->where('programa_id', $programaId);
    }

    /* ─── Helpers RAC 61 ─── */

    /** Suma de horas de vuelo por tipo */
    public function totalHoras(string $campo = 'horas_totales'): float
    {
        return (float) $this->bitacoras()->sum($campo);
    }

    public function horasDual(): float   { return $this->totalHoras('horas_dual'); }
    public function horasSolo(): float   { return $this->totalHoras('horas_solo'); }
    public function horasNoche(): float  { return $this->totalHoras('horas_noche'); }
    public function horasIFR(): float    { return $this->totalHoras('horas_ifr'); }
    public function horasSim(): float    { return $this->totalHoras('horas_simulador'); }

    /**
     * Devuelve un array comparando horas acumuladas vs mínimos RAC 61
     * para mostrar el progreso del estudiante.
     */
    public function progresoHoras(): array
    {
        $p = $this->programa;
        return [
            'vuelo_total' => [
                'acumulado' => $this->totalHoras(),
                'requerido' => $p->horas_vuelo_min,
            ],
            'dual' => [
                'acumulado' => $this->horasDual(),
                'requerido' => $p->horas_dual_min,
            ],
            'solo' => [
                'acumulado' => $this->horasSolo(),
                'requerido' => $p->horas_solo_min,
            ],
            'noche' => [
                'acumulado' => $this->horasNoche(),
                'requerido' => $p->horas_noche_min,
            ],
            'ifr' => [
                'acumulado' => $this->horasIFR(),
                'requerido' => $p->horas_ifr_min,
            ],
            'simulador' => [
                'acumulado' => $this->horasSim(),
                'limite_max' => $p->horas_sim_max,
            ],
        ];
    }

    public function tieneMedicoVigente(): bool
    {
        return $this->certMedicoVigente()->exists();
    }

    public function getNombreCompletoAttribute(): string
    {
        return $this->persona?->nombre_completo ?? "Estudiante #{$this->id}";
    }
}
