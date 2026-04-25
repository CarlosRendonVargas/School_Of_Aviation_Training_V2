<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NominaPeriodo extends Model
{
    protected $table = 'nomina_periodos';

    protected $fillable = ['periodo', 'fecha_inicio', 'fecha_fin', 'estado', 'total_pagado'];

    protected $casts = ['fecha_inicio' => 'date', 'fecha_fin' => 'date', 'total_pagado' => 'float'];

    public function items() { return $this->hasMany(NominaItem::class, 'periodo_id'); }
}
