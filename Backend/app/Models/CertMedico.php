<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertMedico extends Model
{
    protected $table = 'cert_medicos';

    protected $fillable = [
        'estudiante_id', 'tipo', 'numero_certificado',
        'fecha_emision', 'fecha_vencimiento',
        'centro_aeromedico', 'restricciones', 'archivo_url',
    ];

    protected $casts = [
        'fecha_emision'     => 'date',
        'fecha_vencimiento' => 'date',
    ];

    /* ─── Relaciones ─── */

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id');
    }

    /* ─── Scopes ─── */

    public function scopeVigentes($query)
    {
        return $query->where('fecha_vencimiento', '>=', now()->toDateString());
    }

    public function scopeProximosAVencer($query, int $dias = 60)
    {
        return $query->whereBetween('fecha_vencimiento', [
            now()->toDateString(),
            now()->addDays($dias)->toDateString(),
        ]);
    }

    /* ─── Helpers ─── */

    public function estaVigente(): bool
    {
        return $this->fecha_vencimiento >= now()->toDate();
    }

    public function diasParaVencer(): int
    {
        return (int) now()->diffInDays($this->fecha_vencimiento, false);
    }
}
