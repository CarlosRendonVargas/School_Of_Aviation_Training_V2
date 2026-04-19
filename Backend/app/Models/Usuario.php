<?php

namespace App\Models;

// 1. Cambiar Model por Authenticatable
use Illuminate\Foundation\Auth\User as Authenticatable; 
use Illuminate\Notifications\Notifiable;
// 2. Importar HasApiTokens de Sanctum
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

// 3. Extender de Authenticatable
class Usuario extends Authenticatable
{
    // 4. Usar los traits necesarios
    use HasApiTokens, Notifiable;

    protected $table = 'usuarios';

    // El resto de tu código (fillable, relations, scopes) se mantiene igual...
    protected $fillable = [
        'email', 'password_hash', 'rol_id',
        'activo', 'ultimo_acceso', 'token_reset', 'token_reset_expira',
    ];

    protected $hidden = ['password_hash', 'token_reset'];

    protected $casts = [
        'activo'              => 'boolean',
        'ultimo_acceso'       => 'datetime',
        'token_reset_expira'  => 'datetime',
    ];

    /* ─── Relaciones ─── */

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function persona(): HasOne
    {
        return $this->hasOne(Persona::class, 'usuario_id');
    }

    /* ─── Scopes ─── */

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorRol($query, string $rol)
    {
        return $query->whereHas('rol', fn($q) => $q->where('nombre', $rol));
    }

    /* ─── Helpers ─── */

    public function esRol(string $rol): bool
    {
        return $this->rol?->nombre === $rol;
    }

    public function puede(string $modulo, string $accion): bool
    {
        return $this->rol?->puede($modulo, $accion) ?? false;
    }

    public function getNombreCompletoAttribute(): string
    {
        return $this->persona
            ? "{$this->persona->nombres} {$this->persona->apellidos}"
            : $this->email;
    }
}
