<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CorrespondenciaUaeac extends Model
{
    protected $table = 'correspondencia_uaeac';

    protected $fillable = [
        'numero_radicado', 'tipo', 'categoria', 'asunto', 'contenido',
        'fecha_documento', 'fecha_vencimiento_respuesta', 'estado',
        'archivo_url', 'registrado_por', 'notas_internas',
    ];

    protected $casts = [
        'fecha_documento' => 'date',
        'fecha_vencimiento_respuesta' => 'date',
    ];

    public function registrador()
    {
        return $this->belongsTo(Usuario::class, 'registrado_por');
    }
}
