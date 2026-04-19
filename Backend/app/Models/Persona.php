<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Persona extends Model
{
    protected $table = 'personas';

    protected $fillable = [
        'usuario_id', 'nombres', 'apellidos', 'tipo_documento',
        'num_documento', 'fecha_nacimiento', 'nacionalidad',
        'telefono', 'foto_url', 'direccion', 'ciudad',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
    ];

    /* ─── Relaciones ─── */

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function estudiante(): HasOne
    {
        return $this->hasOne(Estudiante::class, 'persona_id');
    }

    public function instructor(): HasOne
    {
        return $this->hasOne(Instructor::class, 'persona_id');
    }

    /* ─── Accessors ─── */

    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nombres} {$this->apellidos}";
    }

    public function getEdadAttribute(): int
    {
        return $this->fecha_nacimiento->age;
    }
}
