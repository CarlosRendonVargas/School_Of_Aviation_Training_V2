<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanErg extends Model
{
    protected $table = 'planes_erg';

    protected $fillable = [
        'titulo', 'tipo_emergencia', 'procedimiento', 'personal_responsable',
        'contactos_emergencia', 'version', 'fecha_revision', 'proxima_revision', 'activo',
    ];

    protected $casts = [
        'activo'          => 'boolean',
        'fecha_revision'  => 'date',
        'proxima_revision' => 'date',
    ];
}
