<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MelItem extends Model
{
    protected $table = 'mel_items';

    protected $fillable = [
        'aeronave_id', 'item_ata', 'descripcion', 'categoria',
        'fecha_apertura', 'fecha_limite', 'estado',
        'procedimiento_o', 'cerrado_por', 'fecha_cierre',
    ];

    protected $casts = [
        'fecha_apertura' => 'date',
        'fecha_limite'   => 'date',
        'fecha_cierre'   => 'date',
    ];

    // Días por categoría según MEL estándar
    const DIAS_POR_CATEGORIA = ['A' => 3, 'B' => 10, 'C' => 30, 'D' => 120];

    public function aeronave(): BelongsTo
    {
        return $this->belongsTo(Aeronave::class, 'aeronave_id');
    }

    public function scopeAbiertos($query)
    {
        return $query->where('estado', 'abierto');
    }

    public function scopeVencidos($query)
    {
        return $query->where('estado', 'abierto')
                     ->where('fecha_limite', '<', now()->toDateString());
    }

    public function estaVencido(): bool
    {
        return $this->estado === 'abierto' && $this->fecha_limite < now()->toDate();
    }

    public function cerrar(string $cerradoPor): void
    {
        $this->update([
            'estado'      => 'cerrado',
            'cerrado_por' => $cerradoPor,
            'fecha_cierre' => now()->toDateString(),
        ]);
    }
}
