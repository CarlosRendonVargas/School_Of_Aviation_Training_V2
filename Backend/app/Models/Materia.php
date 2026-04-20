<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Materia extends Model
{
    protected $table = 'materias';

    protected $fillable = [
        'etapa_id', 'codigo', 'nombre', 'horas',
        'tipo', 'nota_minima', 'rac_referencia',
        'link_meet', 'documento_url', 'video_url', 'temario',
        'max_intentos', 'costo_reintento', 'duracion_minutos',
    ];

    protected $casts = [
        'horas'       => 'decimal:1',
        'nota_minima' => 'decimal:2',
    ];

    public function etapa(): BelongsTo
    {
        return $this->belongsTo(Etapa::class, 'etapa_id');
    }

    public function notas(): HasMany
    {
        return $this->hasMany(NotaAcademica::class, 'materia_id');
    }

    public function preguntas(): HasMany
    {
        return $this->hasMany(BancoPregunta::class, 'materia_id')->where('activo', true);
    }

    public function planesClase(): HasMany
    {
        return $this->hasMany(PlanClase::class, 'materia_id');
    }

    public function lecciones(): HasMany
    {
        return $this->hasMany(LeccionMateria::class, 'materia_id')->orderBy('orden');
    }
}
