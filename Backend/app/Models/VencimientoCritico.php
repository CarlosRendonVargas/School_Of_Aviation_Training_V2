<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VencimientoCritico extends Model
{
    protected $table = 'vencimientos_criticos';

    protected $fillable = [
        'tipo_entidad', 'entidad_id', 'descripcion',
        'fecha_vencimiento', 'dias_alerta', 'nivel_critico',
        'ultima_notificacion', 'activo',
    ];

    protected $casts = [
        'fecha_vencimiento'   => 'date',
        'ultima_notificacion' => 'date',
        'activo'              => 'boolean',
    ];

    protected $appends = ['nivel_calculado', 'dias_restantes'];

    public function getNivelCalculadoAttribute(): string
    {
        return $this->nivel_critico ?? 'info';
    }

    public function toArray()
    {
        $array = parent::toArray();
        $array['nivel_calculado'] = $this->nivel_calculado;
        $array['dias_restantes'] = $this->dias_restantes;
        return $array;
    }

    public function getDiasRestantesAttribute(): int
    {
        return $this->diasRestantes();
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeProximosAVencer($query)
    {
        return $query->whereRaw('fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL dias_alerta DAY)')
                     ->where('activo', true)
                     ->orderBy('fecha_vencimiento');
    }

    public function scopeCriticos($query)
    {
        return $query->where('nivel_critico', 'critico')->where('activo', true);
    }

    public function diasRestantes(): int
    {
        return (int) now()->diffInDays($this->fecha_vencimiento, false);
    }

    public function estaVencido(): bool
    {
        return $this->fecha_vencimiento < now()->toDate();
    }

    /** Fábrica: registrar un vencimiento crítico desde cualquier parte del sistema */
    public static function registrar(
        string $tipoEntidad,
        int    $entidadId,
        string $descripcion,
        string $fechaVencimiento,
        int    $diasAlerta = 30,
        string $nivel = 'advertencia'
    ): self {
        return static::updateOrCreate(
            ['tipo_entidad' => $tipoEntidad, 'entidad_id' => $entidadId, 'descripcion' => $descripcion],
            compact('fechaVencimiento', 'diasAlerta', 'nivel', 'activo') + ['activo' => true]
        );
    }
}
