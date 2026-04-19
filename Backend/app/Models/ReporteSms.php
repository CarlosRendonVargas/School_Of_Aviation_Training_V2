<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReporteSms extends Model
{
    protected $table = 'reportes_sms';

    protected $fillable = [
        'reportante_id', 'anonimo', 'tipo', 'descripcion',
        'fecha_evento', 'lugar', 'aeronave_id',
        'severidad', 'probabilidad', 'nivel_riesgo',
        'estado', 'notificado_uaeac',
    ];

    protected $casts = [
        'fecha_evento'      => 'datetime',
        'anonimo'           => 'boolean',
        'notificado_uaeac'  => 'boolean',
    ];

    /* ─── Relaciones ─── */

    public function reportante(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'reportante_id');
    }

    public function aeronave(): BelongsTo
    {
        return $this->belongsTo(Aeronave::class, 'aeronave_id');
    }

    public function acciones(): HasMany
    {
        return $this->hasMany(AccionCorrectiva::class, 'reporte_sms_id');
    }

    /* ─── Scopes ─── */

    public function scopeAbiertos($query)
    {
        return $query->whereIn('estado', ['nuevo', 'en_analisis']);
    }

    public function scopeCriticos($query)
    {
        return $query->where('nivel_riesgo', '>=', 15);  // zona roja en matriz OACI
    }

    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /* ─── Helpers ─── */

    public function calcularNivelRiesgo(): int
    {
        return $this->severidad * $this->probabilidad;
    }

    public function colorRiesgo(): string
    {
        return match(true) {
            $this->nivel_riesgo >= 15 => 'rojo',      // inaceptable
            $this->nivel_riesgo >= 8  => 'amarillo',  // tolerable con revisión
            default                   => 'verde',     // aceptable
        };
    }

    /* ─── Hooks ─── */

    protected static function booted(): void
    {
        // Calcular nivel_riesgo automáticamente antes de guardar
        static::saving(function (ReporteSms $r) {
            $r->nivel_riesgo = $r->severidad * $r->probabilidad;
        });
    }
}
