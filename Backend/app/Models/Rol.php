<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rol extends Model
{
    protected $table = 'roles';

    protected $fillable = ['nombre', 'descripcion'];

    // Constantes para evitar strings sueltos en el código
    const ESTUDIANTE    = 'estudiante';
    const INSTRUCTOR    = 'instructor';
    const ADMIN         = 'admin';
    const DIR_OPS       = 'dir_ops';
    const MANTENIMIENTO = 'mantenimiento';
    const AUDITOR_UAEAC = 'auditor_uaeac';

    /* ─── Relaciones ─── */

    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class, 'rol_id');
    }

    public function permisos(): HasMany
    {
        return $this->hasMany(Permiso::class, 'rol_id');
    }

    /* ─── Helpers ─── */

    public function puede(string $modulo, string $accion): bool
    {
        return $this->permisos()
            ->where('modulo', $modulo)
            ->where('accion', $accion)
            ->where('activo', true)
            ->exists();
    }
}
