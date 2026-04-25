<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Endorsement extends Model
{
    protected $fillable = [
        'estudiante_id', 'instructor_id', 'tipo', 'fecha',
        'aeronave_matricula', 'aeropuerto_icao', 'observaciones', 'firma_instructor',
    ];

    protected $casts = ['fecha' => 'date'];

    public function estudiante() { return $this->belongsTo(Estudiante::class); }
    public function instructor() { return $this->belongsTo(Instructor::class); }
}
