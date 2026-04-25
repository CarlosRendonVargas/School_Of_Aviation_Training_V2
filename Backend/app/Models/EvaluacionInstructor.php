<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluacionInstructor extends Model
{
    protected $table = 'evaluaciones_instructor';

    protected $fillable = [
        'instructor_id', 'evaluado_por', 'tipo', 'fecha',
        'puntaje', 'resultado', 'areas_evaluadas',
        'observaciones', 'acciones_requeridas', 'proxima_evaluacion',
    ];

    protected $casts = [
        'fecha'            => 'date',
        'proxima_evaluacion' => 'date',
        'areas_evaluadas'  => 'array',
        'puntaje'          => 'float',
    ];

    public function instructor() { return $this->belongsTo(Instructor::class); }
    public function evaluador()  { return $this->belongsTo(Usuario::class, 'evaluado_por'); }
}
