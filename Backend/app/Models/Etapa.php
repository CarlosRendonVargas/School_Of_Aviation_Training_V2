<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Etapa extends Model
{
    protected $table = 'etapas';

    protected $fillable = [
        'programa_id', 'numero', 'nombre',
        'horas_tierra', 'horas_vuelo', 'descripcion', 'prereq_etapa_id',
    ];

    protected $casts = [
        'horas_tierra' => 'decimal:1',
        'horas_vuelo'  => 'decimal:1',
    ];

    /* ─── Relaciones ─── */

    public function programa(): BelongsTo
    {
        return $this->belongsTo(Programa::class, 'programa_id');
    }

    public function materias(): HasMany
    {
        return $this->hasMany(Materia::class, 'etapa_id');
    }

    public function prerequisito(): BelongsTo
    {
        return $this->belongsTo(Etapa::class, 'prereq_etapa_id');
    }

    public function estudiantesEnEtapa(): HasMany
    {
        return $this->hasMany(Estudiante::class, 'etapa_actual_id');
    }
}
