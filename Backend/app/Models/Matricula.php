<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Matricula extends Model
{
    protected $table = 'matriculas';

    protected $fillable = [
        'estudiante_id', 'programa_id', 'fecha_matricula',
        'valor_total', 'descuento', 'forma_pago',
        'num_cuotas', 'estado', 'contrato_url', 'observaciones',
    ];

    protected $casts = [
        'fecha_matricula' => 'date',
        'valor_total'     => 'decimal:2',
        'descuento'       => 'decimal:2',
    ];

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id');
    }

    public function programa(): BelongsTo
    {
        return $this->belongsTo(Programa::class, 'programa_id');
    }

    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class, 'matricula_id');
    }

    public function getValorNetoAttribute(): float
    {
        return (float) ($this->valor_total - $this->descuento);
    }

    public function getTotalPagadoAttribute(): float
    {
        return (float) $this->facturas()
            ->whereHas('pagos')
            ->with('pagos')
            ->get()
            ->flatMap->pagos
            ->sum('valor');
    }

    public function getSaldoPendienteAttribute(): float
    {
        return $this->valor_neto - $this->total_pagado;
    }
}
