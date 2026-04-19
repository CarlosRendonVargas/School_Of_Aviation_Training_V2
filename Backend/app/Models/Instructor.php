<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Instructor extends Model
{
    protected $table = 'instructores';

    protected $fillable = [
        'persona_id', 'num_licencia', 'tipo_licencia', 'venc_licencia',
        'habilitaciones', 'horas_totales_pic', 'horas_instruccion', 'activo',
    ];

    protected $casts = [
        'venc_licencia'     => 'date',
        'habilitaciones'    => 'array',       // JSON array auto-cast
        'activo'            => 'boolean',
        'horas_totales_pic' => 'decimal:1',
        'horas_instruccion' => 'decimal:1',
    ];

    /* ─── Relaciones ─── */

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    public function certificaciones(): HasMany
    {
        return $this->hasMany(CertInstructor::class, 'instructor_id');
    }

    public function bitacoras(): HasMany
    {
        return $this->hasMany(BitacoraVuelo::class, 'instructor_id');
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class, 'instructor_id');
    }

    public function planesClase(): HasMany
    {
        return $this->hasMany(PlanClase::class, 'instructor_id');
    }

    /* ─── Scopes ─── */

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeConLicenciaVigente($query)
    {
        return $query->where('venc_licencia', '>=', now()->toDateString());
    }

    /* ─── Helpers ─── */

    public function licenciaVigente(): bool
    {
        return $this->venc_licencia >= now()->toDate();
    }

    public function diasParaVencerLicencia(): int
    {
        return (int) now()->diffInDays($this->venc_licencia, false);
    }

    public function tieneHabilitacion(string $tipo): bool
    {
        if (! $this->habilitaciones) return false;
        foreach ($this->habilitaciones as $h) {
            if (($h['tipo'] ?? '') === $tipo && ($h['venc'] ?? '') >= now()->toDateString()) {
                return true;
            }
        }
        return false;
    }

    public function getNombreCompletoAttribute(): string
    {
        return $this->persona?->nombre_completo ?? "Instructor #{$this->id}";
    }
}
