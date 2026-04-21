<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotaLeccion extends Model
{
    protected $table = 'notas_lecciones';

    protected $fillable = [
        'estudiante_id', 'leccion_id', 'nota', 'aciertos', 'total', 'intentos', 'fraude_intentos'
    ];

    public function leccion(): BelongsTo
    {
        return $this->belongsTo(LeccionMateria::class, 'leccion_id');
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id');
    }
}
