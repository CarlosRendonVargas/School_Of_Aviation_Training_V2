<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GastoOperativo extends Model
{
    protected $table = 'gastos_operativos';

    protected $fillable = [
        'tipo', 'concepto', 'valor', 'fecha', 'responsable',
        'comprobante_url', 'observaciones', 'registrado_por',
    ];

    protected $casts = ['fecha' => 'date', 'valor' => 'float'];

    public function registrador() { return $this->belongsTo(Usuario::class, 'registrado_por'); }
}
