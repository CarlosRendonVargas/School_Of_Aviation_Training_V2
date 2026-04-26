<?php

namespace App\Policies;

use App\Models\Aeronave;
use App\Models\Usuario;

class AeronavePolicy
{
    public function viewAny(Usuario $usuario): bool
    {
        return in_array($usuario->rol?->nombre, [
            'estudiante', 'instructor', 'dir_ops', 'admin', 'mantenimiento', 'auditor_uaeac',
        ]);
    }

    public function view(Usuario $usuario, Aeronave $aeronave): bool
    {
        return in_array($usuario->rol?->nombre, [
            'estudiante', 'instructor', 'dir_ops', 'admin', 'mantenimiento', 'auditor_uaeac',
        ]);
    }

    public function create(Usuario $usuario): bool
    {
        return in_array($usuario->rol?->nombre, ['dir_ops', 'admin']);
    }

    public function update(Usuario $usuario, Aeronave $aeronave): bool
    {
        return in_array($usuario->rol?->nombre, ['dir_ops', 'admin', 'mantenimiento']);
    }

    public function delete(Usuario $usuario, Aeronave $aeronave): bool
    {
        return $usuario->rol?->nombre === 'dir_ops';
    }

    /** Registrar mantenimiento */
    public function registrarMantenimiento(Usuario $usuario, Aeronave $aeronave): bool
    {
        return in_array($usuario->rol?->nombre, ['mantenimiento', 'dir_ops']);
    }

    /** Ver bitácora técnica */
    public function verBitacoraTecnica(Usuario $usuario, Aeronave $aeronave): bool
    {
        return in_array($usuario->rol?->nombre, [
            'mantenimiento', 'dir_ops', 'auditor_uaeac',
        ]);
    }
}
