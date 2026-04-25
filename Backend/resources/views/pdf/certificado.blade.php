<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
  @page { margin: 30px 40px; }
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', sans-serif; background: #fff; color: #1a1a2e; font-size: 13px; }
  .border-cert { border: 6px double #a10b13; border-radius: 8px; padding: 30px 40px; min-height: 92vh; display: flex; flex-direction: column; }
  .header { text-align: center; border-bottom: 2px solid #a10b13; padding-bottom: 16px; margin-bottom: 24px; }
  .logo-title { font-size: 22px; font-weight: bold; letter-spacing: 2px; color: #a10b13; text-transform: uppercase; }
  .subtitle { font-size: 11px; color: #666; letter-spacing: 3px; margin-top: 4px; }
  .cert-title { font-size: 28px; font-weight: bold; text-align: center; margin: 20px 0 10px; color: #1a1a2e; letter-spacing: 1px; text-transform: uppercase; }
  .cert-sub { text-align: center; font-size: 14px; color: #555; margin-bottom: 30px; }
  .body-text { text-align: center; font-size: 14px; line-height: 1.9; color: #333; flex: 1; }
  .nombre-est { font-size: 26px; font-weight: bold; color: #a10b13; display: block; margin: 12px 0; letter-spacing: 1px; }
  .detalle { background: #f8f9fa; border-left: 4px solid #a10b13; padding: 12px 20px; margin: 20px auto; max-width: 500px; text-align: left; border-radius: 4px; }
  .detalle p { margin: 4px 0; font-size: 12px; color: #444; }
  .footer { display: flex; justify-content: space-between; align-items: flex-end; margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; }
  .firma-bloque { text-align: center; min-width: 180px; }
  .firma-linea { border-top: 1px solid #333; margin-top: 40px; padding-top: 6px; font-size: 11px; color: #555; }
  .cert-numero { font-family: monospace; font-size: 11px; color: #999; }
  .sello { text-align: center; font-size: 10px; color: #888; }
</style>
</head>
<body>
<div class="border-cert">

  <div class="header">
    <div class="logo-title">School of Aviation Training</div>
    <div class="subtitle">RAC 141 · UAEAC · Colombia</div>
  </div>

  @php
    $titulos = [
      'finalizacion_etapa'    => 'Certificado de Finalización de Etapa',
      'finalizacion_programa' => 'Certificado de Graduación',
      'constancia_horas'      => 'Constancia de Horas de Vuelo',
      'constancia_matricula'  => 'Constancia de Matrícula',
      'aprobacion_examen'     => 'Certificado de Aprobación de Examen',
      'endorsement'           => 'Endorsement de Instrucción de Vuelo',
    ];
    $titulo = $titulos[$cert->tipo] ?? 'Certificado';
  @endphp

  <div class="cert-title">{{ $titulo }}</div>
  <div class="cert-sub">La Escuela de Aviación certifica que</div>

  <div class="body-text">
    <span class="nombre-est">
      {{ $cert->estudiante->persona->nombres }} {{ $cert->estudiante->persona->apellidos }}
    </span>
    C.C. {{ $cert->estudiante->persona->num_documento }} —
    Expediente N.º <strong>{{ $cert->estudiante->num_expediente }}</strong>

    <br><br>
    {{ $cert->descripcion ?? 'ha cumplido satisfactoriamente con los requisitos establecidos.' }}

    <div class="detalle">
      <p><strong>Tipo:</strong> {{ str_replace('_', ' ', ucfirst($cert->tipo)) }}</p>
      @if($cert->etapa)
        <p><strong>Etapa:</strong> {{ $cert->etapa->nombre }}</p>
      @endif
      @if($cert->programa)
        <p><strong>Programa:</strong> {{ $cert->programa->nombre }}</p>
      @endif
      <p><strong>Fecha de emisión:</strong> {{ $cert->fecha_emision->format('d/m/Y') }}</p>
      <p><strong>N.º Certificado:</strong> {{ $cert->numero_certificado }}</p>
    </div>
  </div>

  <div class="footer">
    <div class="firma-bloque">
      <div class="firma-linea">
        {{ $cert->emisor->persona->nombres ?? '' }} {{ $cert->emisor->persona->apellidos ?? '' }}<br>
        {{ ucfirst(str_replace('_', ' ', $cert->emisor->rol?->nombre ?? '')) }}<br>
        School of Aviation Training
      </div>
    </div>
    <div class="sello">
      <div class="cert-numero">{{ $cert->numero_certificado }}</div>
      Emitido el {{ $cert->fecha_emision->format('d \d\e F \d\e Y') }}<br>
      Sujeto a verificación institucional<br>
      RAC 141.77 — Registros conservados 5 años
    </div>
    <div class="firma-bloque">
      <div class="firma-linea">
        Director de Operaciones<br>
        School of Aviation Training<br>
        RAC 141
      </div>
    </div>
  </div>

</div>
</body>
</html>
