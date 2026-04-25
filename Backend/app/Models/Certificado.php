<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificado extends Model
{
    protected $table = 'certificados_emitidos';

    protected $fillable = [
        'numero_certificado', 'tipo', 'estudiante_id', 'emitido_por',
        'etapa_id', 'programa_id', 'fecha_emision', 'descripcion',
        'archivo_url', 'anulado', 'motivo_anulacion',
    ];

    protected $casts = ['anulado' => 'boolean', 'fecha_emision' => 'date'];

    public function estudiante()   { return $this->belongsTo(Estudiante::class); }
    public function emisor()       { return $this->belongsTo(Usuario::class, 'emitido_por'); }
    public function etapa()        { return $this->belongsTo(Etapa::class); }
    public function programa()     { return $this->belongsTo(Programa::class); }
}
