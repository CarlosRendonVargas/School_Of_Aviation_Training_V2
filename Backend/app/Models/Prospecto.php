<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prospecto extends Model
{
    protected $fillable = [
        'nombres', 'apellidos', 'email', 'telefono', 'ciudad',
        'programa_interes', 'estado', 'notas', 'fuente',
        'fecha_primer_contacto', 'proxima_accion', 'asignado_a',
    ];

    protected $casts = [
        'fecha_primer_contacto' => 'date',
        'proxima_accion'        => 'date',
    ];

    public function asignado() { return $this->belongsTo(Usuario::class, 'asignado_a'); }
}
