<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanClase extends Model
{
    protected $table = 'planes_clase';

    protected $fillable = [
        'instructor_id', 'materia_id', 'fecha',
        'duracion_min', 'objetivos', 'contenido',
        'recursos', 'estado', 'observaciones',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class, 'instructor_id');
    }

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class, 'materia_id');
    }

    public function scopePorEstado($query, string $estado)
    {
        return $query->where('estado', $estado);
    }
}
