<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertInstructor extends Model
{
    protected $table = 'cert_instructores';

    protected $fillable = [
        'instructor_id', 'tipo', 'descripcion', 'numero',
        'fecha_emision', 'fecha_vencimiento', 'archivo_url', 'activo',
    ];

    protected $casts = [
        'fecha_emision'     => 'date',
        'fecha_vencimiento' => 'date',
        'activo'            => 'boolean',
    ];

    /* ─── Relaciones ─── */

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class, 'instructor_id');
    }

    /* ─── Scopes ─── */

    public function scopeVigentes($query)
    {
        return $query->where('fecha_vencimiento', '>=', now()->toDateString())
                     ->where('activo', true);
    }

    public function scopeProximosAVencer($query, int $dias = 90)
    {
        return $query->whereBetween('fecha_vencimiento', [
            now()->toDateString(),
            now()->addDays($dias)->toDateString(),
        ])->where('activo', true);
    }

    /* ─── Helpers ─── */

    public function estaVigente(): bool
    {
        return $this->activo && $this->fecha_vencimiento >= now()->toDate();
    }

    public function diasParaVencer(): int
    {
        return (int) now()->diffInDays($this->fecha_vencimiento, false);
    }
}
