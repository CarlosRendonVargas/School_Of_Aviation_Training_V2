<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotaAcademica extends Model
{
    protected $table = 'notas_academicas';

    protected $fillable = [
        'estudiante_id', 'materia_id', 'instructor_id',
        'nota', 'aprobado', 'intento_num',
        'fecha_evaluacion', 'observaciones', 'pagado',
    ];

    protected $casts = [
        'nota'             => 'decimal:2',
        'aprobado'         => 'boolean',
        'fecha_evaluacion' => 'date',
    ];

    /* ─── Relaciones ─── */

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id');
    }

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class, 'materia_id');
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class, 'instructor_id');
    }

    /* ─── Scopes ─── */

    public function scopeAprobadas($query)
    {
        return $query->where('aprobado', true);
    }

    public function scopeReprobadas($query)
    {
        return $query->where('aprobado', false);
    }

    /* ─── Hooks ─── */

    protected static function booted(): void
    {
        // El cálculo de aprobado se maneja manualmente en el controlador para mayor precisión
        /*
        static::saving(function (NotaAcademica $n) {
            if ($n->materia) {
                $n->aprobado = $n->nota >= $n->materia->nota_minima;
            }
        });
        */
    }
}
