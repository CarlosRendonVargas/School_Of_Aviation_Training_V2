<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NormatividadDocumento extends Model
{
    protected $table = 'normatividad_documentos';

    protected $fillable = [
        'titulo',
        'descripcion',
        'url_pdf',
        'categoria',
        'orden',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden'  => 'integer',
    ];

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeOrdenados($query)
    {
        return $query->orderBy('categoria')->orderBy('orden')->orderBy('titulo');
    }
}
