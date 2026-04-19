<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentoRac extends Model
{
    protected $table = 'documentos_rac';

    protected $fillable = [
        'tipo', 'numero', 'version', 'titulo',
        'fecha_documento', 'aprobado_uaeac', 'fecha_aprobacion',
        'archivo_url', 'vigente', 'observaciones',
    ];

    protected $casts = [
        'fecha_documento'  => 'date',
        'fecha_aprobacion' => 'date',
        'aprobado_uaeac'   => 'boolean',
        'vigente'          => 'boolean',
    ];

    public function scopeVigentes($query)
    {
        return $query->where('vigente', true);
    }

    public function scopeAprobados($query)
    {
        return $query->where('aprobado_uaeac', true);
    }
}
