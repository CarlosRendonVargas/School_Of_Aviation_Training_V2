<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CapacitacionSms extends Model
{
    protected $table = 'capacitaciones_sms';

    protected $fillable = [
        'titulo', 'descripcion', 'tipo', 'fecha', 'duracion_horas',
        'instructor_nombre', 'obligatoria', 'estado', 'material_url',
    ];

    protected $casts = [
        'fecha'      => 'date',
        'obligatoria' => 'boolean',
    ];

    public function registros() { return $this->hasMany(RegistroCapacitacion::class, 'capacitacion_id'); }
}
