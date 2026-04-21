<?php

namespace App\Policies;

use App\Models\BitacoraVuelo;
use App\Models\Usuario;

class BitacoraVueloPolicy
{
    /**
     * Ver listado de bitácoras.
     * - Estudiante: solo las suyas
     * - Instructor, Dir. Ops, Auditor UAEAC: todas
     */
    public function viewAny(Usuario $usuario): bool
    {
        return in_array($usuario->rol?->nombre, [
            'instructor', 'dir_ops', 'admin', 'auditor_uaeac',
        ]);
    }

    /**
     * Ver una bitácora específica.
     */
    public function view(Usuario $usuario, BitacoraVuelo $bitacora): bool
    {
        $rol = $usuario->rol?->nombre;

        // El propio estudiante puede ver la suya
        if ($rol === 'estudiante') {
            return $usuario->persona?->estudiante?->id === $bitacora->estudiante_id;
        }

        // El instructor que registró la sesión
        if ($rol === 'instructor') {
            return $usuario->persona?->instructor?->id === $bitacora->instructor_id;
        }

        return in_array($rol, ['dir_ops', 'admin', 'auditor_uaeac']);
    }

    /**
     * Crear nueva entrada de bitácora.
     * Solo instructores y dir_ops pueden crear.
     */
    public function create(Usuario $usuario): bool
    {
        return in_array($usuario->rol?->nombre, ['instructor', 'dir_ops', 'admin']);
    }

    /**
     * Editar una bitácora.
     * Solo dentro de las 24h siguientes al registro, o dir_ops siempre.
     */
    public function update(Usuario $usuario, BitacoraVuelo $bitacora): bool
    {
        $rol = $usuario->rol?->nombre;

        if ($rol === 'dir_ops') return true;

        if ($rol === 'instructor') {
            $esElInstructor = $usuario->persona?->instructor?->id === $bitacora->instructor_id;
            $dentro24h = $bitacora->created_at->diffInHours(now()) <= 24;
            return $esElInstructor && $dentro24h;
        }

        return false;
    }

    /**
     * Eliminar — solo dir_ops con justificación (RAC 141.77 — no se borra, se anula).
     */
    public function delete(Usuario $usuario, BitacoraVuelo $bitacora): bool
    {
        return $usuario->rol?->nombre === 'dir_ops';
    }

    /**
     * Firmar digitalmente una bitácora.
     */
    public function firmar(Usuario $usuario, BitacoraVuelo $bitacora): bool
    {
        $rol = $usuario->rol?->nombre;
        $persona = $usuario->persona;

        // Instructor firma si es el de la sesión
        if ($rol === 'instructor') {
            return $persona?->instructor?->id === $bitacora->instructor_id;
        }

        // Estudiante firma la suya propia
        if ($rol === 'estudiante') {
            return $persona?->estudiante?->id === $bitacora->estudiante_id;
        }

        // El Admin o Director de Operaciones pueden firmar/validar cualquier bitácora
        if (in_array($rol, ['admin', 'dir_ops'])) {
            return true;
        }

        return false;
    }
}
