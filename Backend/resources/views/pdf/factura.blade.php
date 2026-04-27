<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
  @page { margin: 30px 40px; }
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', sans-serif; background: #fff; color: #1a1a2e; font-size: 12px; }

  .page { padding: 20px; }

  /* Encabezado */
  .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid #a10b13; padding-bottom: 16px; margin-bottom: 20px; }
  .brand-name { font-size: 20px; font-weight: bold; color: #a10b13; text-transform: uppercase; letter-spacing: 2px; }
  .brand-sub  { font-size: 9px; color: #666; letter-spacing: 2px; margin-top: 3px; }
  .factura-title { text-align: right; }
  .factura-title h1 { font-size: 26px; font-weight: bold; color: #a10b13; text-transform: uppercase; letter-spacing: 1px; }
  .factura-num { font-family: monospace; font-size: 13px; color: #333; margin-top: 4px; }

  /* Datos factura + cliente */
  .meta { display: flex; justify-content: space-between; margin-bottom: 20px; gap: 20px; }
  .box { background: #f8f9fa; border-left: 4px solid #a10b13; padding: 12px 16px; flex: 1; border-radius: 4px; }
  .box-title { font-size: 9px; font-weight: bold; color: #a10b13; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 8px; }
  .box p { font-size: 11px; color: #444; margin: 3px 0; }
  .box strong { color: #1a1a2e; }

  /* Tabla de conceptos */
  table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
  thead tr { background: #a10b13; color: #fff; }
  thead th { padding: 8px 12px; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; }
  tbody tr:nth-child(even) { background: #f8f9fa; }
  tbody td { padding: 10px 12px; font-size: 11px; color: #333; border-bottom: 1px solid #eee; }
  .text-right { text-align: right; }

  /* Totales */
  .totales { margin-left: auto; width: 260px; }
  .totales table { margin-bottom: 0; }
  .totales td { padding: 5px 12px; font-size: 11px; }
  .totales .total-final td { background: #a10b13; color: #fff; font-weight: bold; font-size: 13px; }

  /* Estado */
  .estado-badge { display: inline-block; padding: 3px 12px; border-radius: 12px; font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
  .estado-pendiente { background: #fff3cd; color: #856404; border: 1px solid #ffc107; }
  .estado-pagada    { background: #d1fae5; color: #065f46; border: 1px solid #10b981; }
  .estado-vencida   { background: #fee2e2; color: #991b1b; border: 1px solid #ef4444; }
  .estado-parcial   { background: #fde8d8; color: #9a3412; border: 1px solid #f97316; }

  /* Pagos */
  .section-title { font-size: 10px; font-weight: bold; color: #a10b13; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 8px; border-bottom: 1px solid #eee; padding-bottom: 4px; }
  .pago-row { display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px dotted #eee; font-size: 11px; }
  .no-pagos { color: #999; font-size: 11px; font-style: italic; }

  /* CUFE */
  .cufe-box { background: #f8f9fa; border: 1px solid #ddd; padding: 8px 12px; border-radius: 4px; margin-top: 16px; }
  .cufe-label { font-size: 8px; color: #999; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 4px; }
  .cufe-val { font-family: monospace; font-size: 9px; color: #555; word-break: break-all; }

  /* Footer */
  .footer { margin-top: 30px; padding-top: 12px; border-top: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; }
  .footer-left { font-size: 9px; color: #999; }
  .footer-right { font-size: 9px; color: #999; text-align: right; }

  @media print { .page { padding: 0; } }
</style>
</head>
<body>
<div class="page">

  <!-- Encabezado -->
  <div class="header">
    <div>
      <div class="brand-name">School of Aviation Training</div>
      <div class="brand-sub">Escuela de Aviación · Autorizada UAEAC · RAC 141</div>
      <div class="brand-sub" style="margin-top:4px">NIT: 900.000.000-0 · Bogotá D.C., Colombia</div>
    </div>
    <div class="factura-title">
      <h1>Factura</h1>
      <div class="factura-num">{{ $factura->numero_factura }}</div>
      <div style="margin-top:6px">
        <span class="estado-badge estado-{{ $factura->estado }}">{{ strtoupper($factura->estado) }}</span>
      </div>
    </div>
  </div>

  <!-- Meta -->
  <div class="meta">
    <div class="box">
      <div class="box-title">Datos de Facturación</div>
      <p><strong>Fecha de Emisión:</strong> {{ $factura->fecha_factura->format('d/m/Y') }}</p>
      <p><strong>Fecha Vencimiento:</strong> {{ $factura->fecha_vencimiento_pago->format('d/m/Y') }}</p>
      <p><strong>Matrícula:</strong> MAT-{{ str_pad($factura->matricula->id, 5, '0', STR_PAD_LEFT) }}</p>
      <p><strong>Programa:</strong> {{ $factura->matricula->programa->nombre ?? '—' }}</p>
    </div>
    <div class="box">
      <div class="box-title">Facturado a</div>
      @php $persona = $factura->matricula->estudiante->persona ?? null; @endphp
      @if($persona)
        <p><strong>{{ $persona->nombres }} {{ $persona->apellidos }}</strong></p>
        <p>{{ $persona->tipo_documento ?? 'CC' }}: {{ $persona->num_documento ?? '—' }}</p>
        <p>{{ $persona->email ?? '—' }}</p>
        <p>{{ $persona->telefono ?? '—' }}</p>
      @else
        <p>Información no disponible</p>
      @endif
    </div>
  </div>

  <!-- Concepto -->
  <table>
    <thead>
      <tr>
        <th style="width:60%">Concepto</th>
        <th class="text-right">Subtotal</th>
        <th class="text-right">IVA</th>
        <th class="text-right">Total</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>{{ $factura->concepto }}</td>
        <td class="text-right">{{ number_format($factura->subtotal, 0, ',', '.') }}</td>
        <td class="text-right">{{ number_format($factura->iva, 0, ',', '.') }}</td>
        <td class="text-right"><strong>{{ number_format($factura->total, 0, ',', '.') }}</strong></td>
      </tr>
    </tbody>
  </table>

  <!-- Totales -->
  <div class="totales">
    <table>
      <tr>
        <td>Subtotal</td>
        <td class="text-right">$ {{ number_format($factura->subtotal, 0, ',', '.') }}</td>
      </tr>
      <tr>
        <td>IVA</td>
        <td class="text-right">$ {{ number_format($factura->iva, 0, ',', '.') }}</td>
      </tr>
      <tr>
        <td>Total Pagado</td>
        <td class="text-right" style="color:#065f46">$ {{ number_format($factura->total_pagado, 0, ',', '.') }}</td>
      </tr>
      <tr class="total-final">
        <td>SALDO PENDIENTE</td>
        <td class="text-right">$ {{ number_format(max(0, $factura->saldo), 0, ',', '.') }}</td>
      </tr>
    </table>
  </div>

  <!-- Pagos registrados -->
  @if($factura->pagos->count() > 0)
  <div style="margin-top:24px">
    <div class="section-title">Pagos Registrados</div>
    @foreach($factura->pagos as $pago)
    <div class="pago-row">
      <span>{{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') }} · {{ strtoupper($pago->metodo) }}</span>
      <span>Ref: {{ $pago->referencia ?? '—' }}</span>
      <span><strong>$ {{ number_format($pago->valor, 0, ',', '.') }}</strong></span>
    </div>
    @endforeach
  </div>
  @endif

  <!-- CUFE -->
  @if($factura->cufe)
  <div class="cufe-box">
    <div class="cufe-label">Código Único de Factura Electrónica (CUFE) · DIAN</div>
    <div class="cufe-val">{{ $factura->cufe }}</div>
  </div>
  @endif

  <!-- Footer -->
  <div class="footer">
    <div class="footer-left">
      School of Aviation Training · RAC 141 · UAEAC<br>
      Generado: {{ now()->format('d/m/Y H:i') }}
    </div>
    <div class="footer-right">
      Este documento es válido como soporte fiscal.<br>
      Conserve este comprobante.
    </div>
  </div>

</div>
</body>
</html>
