<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * AuditService
 * ─────────────
 * Registra toda creación, modificación y eliminación de registros.
 * RAC 141.77 exige conservar los registros mínimo 5 años y
 * tenerlos disponibles para la UAEAC en cualquier momento.
 *
 * Uso recomendado: desde Observers de Eloquent (ver AppServiceProvider).
 */
class AuditService
{
    public function registrar(
        string  $tabla,
        string  $accion,
        int     $registroId,
        ?array  $datosAntes  = null,
        ?array  $datosDespues = null
    ): void {
        AuditLog::create([
            'usuario_id'     => Auth::id(),
            'tabla'          => $tabla,
            'accion'         => $accion,
            'registro_id'    => $registroId,
            'datos_antes'    => $datosAntes,
            'datos_despues'  => $datosDespues,
            'ip_address'     => Request::ip(),
            'user_agent'     => Request::userAgent(),
        ]);
    }

    public function insercion(Model $model): void
    {
        $this->registrar(
            tabla:        $model->getTable(),
            accion:       'INSERT',
            registroId:   $model->getKey(),
            datosDespues: $model->toArray()
        );
    }

    public function actualizacion(Model $model): void
    {
        $this->registrar(
            tabla:       $model->getTable(),
            accion:      'UPDATE',
            registroId:  $model->getKey(),
            datosAntes:  $model->getOriginal(),
            datosDespues: $model->toArray()
        );
    }

    public function eliminacion(Model $model): void
    {
        $this->registrar(
            tabla:      $model->getTable(),
            accion:     'DELETE',
            registroId: $model->getKey(),
            datosAntes: $model->toArray()
        );
    }
}
