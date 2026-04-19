<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Permiso extends Model
{
    protected $table = 'permisos';

    protected $fillable = [
        'rol_id',
        'modulo',
        'accion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class);
    }
}
