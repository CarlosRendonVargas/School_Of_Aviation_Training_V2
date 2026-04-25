<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NominaItem extends Model
{
    protected $table = 'nomina_items';

    protected $fillable = [
        'periodo_id', 'usuario_id', 'cargo', 'salario_base',
        'horas_extra', 'valor_hora_extra', 'bonificaciones', 'deducciones',
        'salud', 'pension', 'neto_pagar', 'estado',
    ];

    protected $casts = [
        'salario_base' => 'float', 'horas_extra' => 'float',
        'neto_pagar'   => 'float',
    ];

    public function periodo()  { return $this->belongsTo(NominaPeriodo::class); }
    public function usuario()  { return $this->belongsTo(Usuario::class); }
}
