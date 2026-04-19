<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Factura;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FacturaController extends Controller
{
    public function index(): JsonResponse
    {
        $facturas = Factura::with(['matricula.estudiante.persona', 'pagos'])->orderByDesc('fecha_factura')->get();
        // Add dynamic calculated fields
        $res = $facturas->map(function ($f) {
            $f->total_pagado = $f->total_pagado;
            $f->saldo = $f->saldo;
            return $f;
        });
        return response()->json(['ok' => true, 'data' => $res]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'matricula_id'           => 'required|exists:matriculas,id',
            'numero_factura'         => 'nullable|string|unique:facturas',
            'fecha_factura'          => 'required|date',
            'fecha_vencimiento_pago' => 'required|date',
            'concepto'               => 'required|string',
            'subtotal'               => 'required|numeric',
            'iva'                    => 'required|numeric',
            'total'                  => 'required|numeric',
        ]);

        if (empty($data['numero_factura'])) {
            $data['numero_factura'] = $this->generarNumeroFactura();
        }

        $data['estado'] = 'pendiente';
        $factura = Factura::create($data);
        return response()->json(['ok' => true, 'data' => $factura->load('matricula.estudiante.persona')]);
    }

    public function proximoNumero(): JsonResponse
    {
        return response()->json(['ok' => true, 'data' => $this->generarNumeroFactura()]);
    }

    private function generarNumeroFactura(): string
    {
        $anio = now()->year;
        $ultimo = Factura::where('numero_factura', 'like', "FE-{$anio}-%")
                        ->count() + 1;
        
        $numero = "FE-{$anio}-" . str_pad($ultimo, 4, '0', STR_PAD_LEFT);

        // Validar que no exista (por si acaso hubo borrados o saltos)
        while (Factura::where('numero_factura', $numero)->exists()) {
            $ultimo++;
            $numero = "FE-{$anio}-" . str_pad($ultimo, 4, '0', STR_PAD_LEFT);
        }

        return $numero;
    }

    public function show($id): JsonResponse
    {
        $factura = Factura::with(['matricula.estudiante.persona', 'pagos.registrador'])->findOrFail($id);
        $factura->total_pagado = $factura->total_pagado;
        $factura->saldo = $factura->saldo;
        return response()->json(['ok' => true, 'data' => $factura]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $factura = Factura::findOrFail($id);
        $data = $request->validate([
            'fecha_vencimiento_pago' => 'nullable|date',
            'estado' => 'nullable|string',
            'concepto' => 'nullable|string',
        ]);
        
        $factura->update($data);
        return response()->json(['ok' => true, 'data' => $factura]);
    }

    public function pdf($id)
    {
        $factura = Factura::with(['matricula.estudiante.persona', 'pagos'])->findOrFail($id);
        
        // Simulación de respuesta PDF (en una app real usaría mPDF o dompdf)
        // Por ahora devolvemos un JSON que indica que el PDF está "generado"
        return response()->json([
            'ok' => true, 
            'mensaje' => 'Generando PDF para Factura ' . $factura->numero_factura,
            'url' => url("/api/v1/facturas/{$id}") // Placeholder corrigiendo la ruta no definida
        ]);
    }

    public function storePago(Request $request, $id): JsonResponse
    {
        $request->validate([
            'fecha_pago' => 'required|date',
            'valor'      => 'required|numeric|min:1',
            'metodo'     => 'required|string',
            'referencia' => 'required|string',
        ]);

        $factura = Factura::findOrFail($id);
        if ($factura->estado === 'pagada') {
            return response()->json(['ok'=>false, 'mensaje'=>'Factura ya pagada'], 422);
        }

        $pago = Pago::create([
            'factura_id'     => $factura->id,
            'fecha_pago'     => $request->fecha_pago,
            'valor'          => $request->valor,
            'metodo'         => $request->metodo,
            'referencia'     => $request->referencia,
            'registrado_por' => $request->user()->id,
            'observaciones'  => $request->observaciones ?? '',
        ]);

        // Recalcular estado de la factura
        if ($factura->saldo <= 0) {
            $factura->update(['estado' => 'pagada']);
        } else {
            $factura->update(['estado' => 'parcial']);
        }

        return response()->json(['ok' => true, 'data' => $pago]);
    }

    public function pagos($id): JsonResponse
    {
        $pagos = Pago::where('factura_id', $id)->with('registrador')->get();
        return response()->json(['ok' => true, 'data' => $pagos]);
    }

    public function carteraVencida(): JsonResponse
    {
        $hoy = now()->format('Y-m-d');
        $facturas = Factura::with(['matricula.estudiante.persona'])
            ->where('estado', '!=', 'pagada')
            ->where('fecha_vencimiento_pago', '<', $hoy)
            ->get()
            ->map(function ($f) {
                $f->total_pagado = $f->total_pagado;
                $f->saldo = $f->saldo;
                return $f;
            });
            
        return response()->json(['ok' => true, 'data' => $facturas]);
    }
}
