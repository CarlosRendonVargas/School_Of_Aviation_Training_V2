<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Aeronave extends Model
{
    protected $table = 'aeronaves';

    protected $fillable = [
        'matricula', 'modelo', 'fabricante', 'serie', 'anio',
        'categoria', 'clase', 'horas_celula_total', 'horas_motor_total',
        'horas_desde_oh', 'cert_airworthiness', 'venc_airworthiness',
        'venc_seguro', 'estado',
    ];

    protected $casts = [
        'venc_airworthiness'  => 'date',
        'venc_seguro'         => 'date',
        'horas_celula_total'  => 'decimal:1',
        'horas_motor_total'   => 'decimal:1',
        'horas_desde_oh'      => 'decimal:1',
    ];

    /* ─── Relaciones ─── */

    public function bitacoras(): HasMany
    {
        return $this->hasMany(BitacoraVuelo::class, 'aeronave_id');
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class, 'aeronave_id');
    }

    public function mantenimientos(): HasMany
    {
        return $this->hasMany(RegistroMantenimiento::class, 'aeronave_id')->orderByDesc('fecha_realizado');
    }

    public function melItems(): HasMany
    {
        return $this->hasMany(MelItem::class, 'aeronave_id');
    }

    public function melAbiertos(): HasMany
    {
        return $this->hasMany(MelItem::class, 'aeronave_id')->where('estado', 'abierto');
    }

    public function airworthinessDirectives(): HasMany
    {
        return $this->hasMany(AirworthinessDirective::class, 'aeronave_id');
    }

    public function adsPendientes(): HasMany
    {
        return $this->hasMany(AirworthinessDirective::class, 'aeronave_id')->where('estado', 'pendiente');
    }

    /* ─── Scopes ─── */

    public function scopeDisponibles($query)
    {
        return $query->where('estado', 'disponible');
    }

    public function scopeConAirworthinessVigente($query)
    {
        return $query->where('venc_airworthiness', '>=', now()->toDateString());
    }

    /* ─── Helpers ─── */

    public function estaDisponible(): bool
    {
        return $this->estado === 'disponible';
    }

    public function airworthinessVigente(): bool
    {
        return $this->venc_airworthiness >= now()->toDate();
    }

    public function seguroVigente(): bool
    {
        return $this->venc_seguro >= now()->toDate();
    }

    public function aptoParaVolar(): bool
    {
        return $this->estaDisponible()
            && $this->airworthinessVigente()
            && $this->seguroVigente()
            && $this->melAbiertos()->where('categoria', 'A')->doesntExist();
    }

    public function diasParaVencerAirworthiness(): int
    {
        return (int) now()->diffInDays($this->venc_airworthiness, false);
    }

    /** Suma las horas de vuelo registradas en bitácoras y actualiza el TTAE */
    public function actualizarHorasCelula(): void
    {
        $this->update([
            'horas_celula_total' => $this->bitacoras()->sum('horas_totales'),
        ]);
    }
}
