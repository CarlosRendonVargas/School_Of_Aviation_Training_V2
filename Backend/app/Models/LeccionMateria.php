<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeccionMateria extends Model
{
    protected $table = 'lecciones_materia';

    protected $fillable = [
        'materia_id', 'titulo', 'descripcion', 'video_url', 'documento_url', 'orden', 'activo', 'max_intentos'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer'
    ];

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class, 'materia_id');
    }

    public function preguntas(): HasMany
    {
        return $this->hasMany(BancoPregunta::class, 'leccion_id');
    }
}
