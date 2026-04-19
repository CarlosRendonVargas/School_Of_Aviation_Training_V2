<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Programa extends Model
{
    protected $table = 'programas';

    protected $fillable = [
        'codigo', 'nombre', 'tipo',
        'horas_tierra_min', 'horas_vuelo_min', 'horas_dual_min',
        'horas_solo_min', 'horas_ifr_min', 'horas_noche_min',
        'horas_sim_max', 'rac_referencia', 'activo',
    ];

    protected $casts = [
        'activo'          => 'boolean',
        'horas_tierra_min' => 'decimal:1',
        'horas_vuelo_min'  => 'decimal:1',
        'horas_dual_min'   => 'decimal:1',
        'horas_solo_min'   => 'decimal:1',
        'horas_ifr_min'    => 'decimal:1',
        'horas_noche_min'  => 'decimal:1',
        'horas_sim_max'    => 'decimal:1',
    ];

    /* ─── Relaciones ─── */

    public function etapas(): HasMany
    {
        return $this->hasMany(Etapa::class, 'programa_id')->orderBy('numero');
    }

    public function estudiantes(): HasMany
    {
        return $this->hasMany(Estudiante::class, 'programa_id');
    }

    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class, 'programa_id');
    }

    /* ─── Scopes ─── */

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
