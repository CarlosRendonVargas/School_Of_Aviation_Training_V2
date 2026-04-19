<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $table   = 'audit_log';
    public    $timestamps = false;          // solo created_at

    protected $fillable = [
        'usuario_id', 'tabla', 'accion',
        'registro_id', 'datos_antes', 'datos_despues',
        'ip_address', 'user_agent',
    ];

    protected $casts = [
        'datos_antes'   => 'array',
        'datos_despues' => 'array',
        'created_at'    => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /** Registrar un evento de auditoría de forma estática */
    public static function registrar(
        string  $tabla,
        string  $accion,
        int     $registroId,
        ?array  $antes   = null,
        ?array  $despues = null,
        ?int    $userId  = null
    ): void {
        static::create([
            'usuario_id'     => $userId ?? auth()->id(),
            'tabla'          => $tabla,
            'accion'         => $accion,
            'registro_id'    => $registroId,
            'datos_antes'    => $antes,
            'datos_despues'  => $despues,
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent(),
        ]);
    }
}
