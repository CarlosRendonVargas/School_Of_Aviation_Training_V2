<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    protected $table = 'pagos';

    protected $fillable = [
        'factura_id', 'fecha_pago', 'valor', 'metodo',
        'referencia', 'registrado_por', 'comprobante_url', 'observaciones',
    ];

    protected $casts = [
        'fecha_pago' => 'date',
        'valor'      => 'decimal:2',
    ];

    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class, 'factura_id');
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'registrado_por');
    }

    /* Marcar factura como pagada si el saldo queda en 0 */
    protected static function booted(): void
    {
        static::saved(function (Pago $p) {
            $factura = $p->factura;
            if ($factura && $factura->saldo <= 0) {
                $factura->update(['estado' => 'pagada']);
            }
        });
    }
}
