<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnmiendaDocumento extends Model
{
    protected $table = 'enmiendas_documentos';

    protected $fillable = [
        'documento_id', 'numero_enmienda', 'descripcion', 'contenido_cambio',
        'estado', 'fecha_envio', 'fecha_respuesta', 'respuesta_uaeac', 'elaborado_por',
    ];

    protected $casts = [
        'fecha_envio'     => 'date',
        'fecha_respuesta' => 'date',
    ];

    public function documento()  { return $this->belongsTo(DocumentoRac::class, 'documento_id'); }
    public function elaborador() { return $this->belongsTo(Usuario::class, 'elaborado_por'); }
}
