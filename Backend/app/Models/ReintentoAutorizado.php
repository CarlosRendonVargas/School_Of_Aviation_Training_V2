<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReintentoAutorizado extends Model
{
    protected $table = 'reintentos_autorizados';
    protected $fillable = ['estudiante_id', 'materia_id', 'autorizado_por', 'num_recibo', 'usado'];

    public function estudiante() { return $this->belongsTo(Estudiante::class); }
    public function materia()    { return $this->belongsTo(Materia::class); }
    public function autorizador() { return $this->belongsTo(Usuario::class, 'autorizado_por'); }
}
