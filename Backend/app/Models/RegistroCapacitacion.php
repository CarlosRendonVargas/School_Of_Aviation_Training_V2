<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistroCapacitacion extends Model
{
    protected $table = 'registros_capacitacion';

    protected $fillable = ['capacitacion_id', 'usuario_id', 'asistio', 'nota', 'aprobado'];

    protected $casts = ['asistio' => 'boolean', 'aprobado' => 'boolean', 'nota' => 'float'];

    public function capacitacion() { return $this->belongsTo(CapacitacionSms::class, 'capacitacion_id'); }
    public function usuario()      { return $this->belongsTo(Usuario::class); }
}
