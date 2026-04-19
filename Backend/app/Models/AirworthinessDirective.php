<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AirworthinessDirective extends Model
{
    protected $table = 'airworthiness_directives';

    protected $fillable = [
        'aeronave_id', 'numero_ad', 'titulo',
        'fecha_emision', 'fecha_limite', 'estado',
        'metodo_cumplimiento', 'archivo_url',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'fecha_limite'  => 'date',
    ];

    public function aeronave(): BelongsTo
    {
        return $this->belongsTo(Aeronave::class, 'aeronave_id');
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeVencidos($query)
    {
        return $query->where('estado', 'pendiente')
                     ->whereNotNull('fecha_limite')
                     ->where('fecha_limite', '<', now()->toDateString());
    }

    public function cumplir(string $metodo): void
    {
        $this->update(['estado' => 'cumplido', 'metodo_cumplimiento' => $metodo]);
    }
}
