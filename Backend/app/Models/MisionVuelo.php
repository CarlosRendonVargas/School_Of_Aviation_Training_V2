<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MisionVuelo extends Model
{
    protected $fillable = [
        'estudiante_id',
        'instructor_id',
        'programa_id',
        'fecha',
        'matricula',
        'tipo_vuelo',
        'horas',
        'despegues',
        'aterrizajes',
        'calificacion',
        'observaciones'
    ];

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function programa()
    {
        return $this->belongsTo(Programa::class);
    }

    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }
}
