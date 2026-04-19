<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BancoPregunta extends Model
{
    protected $table = 'banco_preguntas';

    protected $fillable = [
        'materia_id', 'pregunta', 'tipo', 'opciones',
        'respuesta_correcta', 'nivel_dificultad', 'rac_referencia', 'activo',
    ];

    protected $casts = [
        'opciones' => 'array',
        'activo'   => 'boolean',
    ];

    protected $hidden = ['respuesta_correcta'];

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class, 'materia_id');
    }

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorDificultad($query, int $nivel)
    {
        return $query->where('nivel_dificultad', $nivel);
    }

    /** Genera un examen aleatorio de N preguntas para una materia */
    public static function generarExamen(int $materiaId, int $cantidad = 20): \Illuminate\Support\Collection
    {
        return static::where('materia_id', $materiaId)
            ->where('activo', true)
            ->inRandomOrder()
            ->limit($cantidad)
            ->get()
            ->makeHidden(['respuesta_correcta']);
    }
}
