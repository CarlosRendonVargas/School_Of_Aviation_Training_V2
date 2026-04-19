<?php

namespace App\Policies;

use App\Models\Estudiante;
use App\Models\Usuario;

class EstudiantePolicy
{
    public function viewAny(Usuario $usuario): bool
    {
        return in_array($usuario->rol?->nombre, [
            'instructor', 'dir_ops', 'admin', 'auditor_uaeac',
        ]);
    }

    public function view(Usuario $usuario, Estudiante $estudiante): bool
    {
        $rol = $usuario->rol?->nombre;

        if ($rol === 'estudiante') {
            return $usuario->persona?->estudiante?->id === $estudiante->id;
        }

        return in_array($rol, ['instructor', 'dir_ops', 'admin', 'auditor_uaeac']);
    }

    public function create(Usuario $usuario): bool
    {
        return in_array($usuario->rol?->nombre, ['admin', 'dir_ops']);
    }

    public function update(Usuario $usuario, Estudiante $estudiante): bool
    {
        return in_array($usuario->rol?->nombre, ['admin', 'dir_ops']);
    }

    public function delete(Usuario $usuario, Estudiante $estudiante): bool
    {
        return $usuario->rol?->nombre === 'dir_ops';
    }

    /** Ver el expediente completo (RAC 141.77) */
    public function verExpediente(Usuario $usuario, Estudiante $estudiante): bool
    {
        $rol = $usuario->rol?->nombre;

        if ($rol === 'estudiante') {
            return $usuario->persona?->estudiante?->id === $estudiante->id;
        }

        return in_array($rol, ['instructor', 'dir_ops', 'admin', 'auditor_uaeac']);
    }
}
