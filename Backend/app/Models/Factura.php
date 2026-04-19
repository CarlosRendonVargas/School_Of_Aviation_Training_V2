<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Factura extends Model
{
    protected $table = 'facturas';

    protected $fillable = [
        'matricula_id', 'numero_factura', 'fecha_factura',
        'fecha_vencimiento_pago', 'concepto',
        'subtotal', 'iva', 'total',
        'estado', 'cufe', 'archivo_url',
    ];

    protected $casts = [
        'fecha_factura'           => 'date',
        'fecha_vencimiento_pago'  => 'date',
        'subtotal'                => 'decimal:2',
        'iva'                     => 'decimal:2',
        'total'                   => 'decimal:2',
    ];

    public function matricula(): BelongsTo
    {
        return $this->belongsTo(Matricula::class, 'matricula_id');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'factura_id');
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeVencidas($query)
    {
        return $query->where('estado', 'vencida');
    }

    public function getTotalPagadoAttribute(): float
    {
        return (float) $this->pagos()->sum('valor');
    }

    public function getSaldoAttribute(): float
    {
        return (float) ($this->total - $this->total_pagado);
    }
}
