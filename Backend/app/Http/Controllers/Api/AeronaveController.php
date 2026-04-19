<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aeronave;
use App\Models\RegistroMantenimiento;
use App\Models\MelItem;
use App\Models\AirworthinessDirective;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class AeronaveController extends Controller
{
    /* ─── CRUD Aeronave ─── */

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Aeronave::class);

        $aeronaves = Aeronave::with(['melAbiertos', 'adsPendientes'])
            ->when($request->estado, fn($q, $v) => $q->where('estado', $v))
            ->when($request->clase,  fn($q, $v) => $q->where('clase', $v))
            ->orderBy('matricula')
            ->get();

        return response()->json(['ok' => true, 'data' => $aeronaves]);
    }

    public function show($id): JsonResponse
    {
        $aeronave = Aeronave::with([
            'melAbiertos', 'adsPendientes',
        ])->findOrFail($id);

        $this->authorize('view', $aeronave);

        return response()->json([
            'ok'   => true,
            'data' => array_merge($aeronave->toArray(), [
                'dias_venc_airworthiness' => $aeronave->diasParaVencerAirworthiness(),
                'airworthiness_vigente'   => $aeronave->airworthinessVigente(),
                'seguro_vigente'          => $aeronave->seguroVigente(),
            ]),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Aeronave::class);

        $data = $request->validate([
            'matricula'          => 'required|string|unique:aeronaves,matricula',
            'modelo'             => 'required|string',
            'fabricante'         => 'required|string',
            'serie'              => 'required|string',
            'anio'               => 'required|integer|min:1940|max:' . date('Y'),
            'categoria'          => 'required|in:normal,utilidad,acrobatico',
            'clase'              => 'required|in:monomotor,multimotor',
            'horas_celula_total' => 'required|numeric|min:0',
            'horas_motor_total'  => 'required|numeric|min:0',
            'horas_desde_oh'     => 'required|numeric|min:0',
            'cert_airworthiness' => 'required|string',
            'venc_airworthiness' => 'required|date',
            'venc_seguro'        => 'required|date',
        ]);

        $aeronave = Aeronave::create($data);

        return response()->json([
            'ok'      => true,
            'mensaje' => "Aeronave {$aeronave->matricula} registrada.",
            'data'    => $aeronave,
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $aeronave = Aeronave::findOrFail($id);
        $this->authorize('update', $aeronave);

        $data = $request->validate([
            'estado'             => 'sometimes|in:disponible,mantenimiento,baja',
            'venc_airworthiness' => 'sometimes|date',
            'venc_seguro'        => 'sometimes|date',
            'horas_celula_total' => 'sometimes|numeric|min:0',
            'horas_motor_total'  => 'sometimes|numeric|min:0',
            'horas_desde_oh'     => 'sometimes|numeric|min:0',
        ]);

        $aeronave->update($data);

        return response()->json(['ok' => true, 'data' => $aeronave]);
    }

    /* ─── Mantenimientos RAC 43 ─── */

    public function mantenimientos($id): JsonResponse
    {
        Aeronave::findOrFail($id);

        return response()->json([
            'ok'   => true,
            'data' => RegistroMantenimiento::where('aeronave_id', $id)
                ->orderByDesc('fecha_realizado')
                ->paginate(20),
        ]);
    }

    public function storeMantenimiento(Request $request, $id): JsonResponse
    {
        $aeronave = Aeronave::findOrFail($id);
        $this->authorize('registrarMantenimiento', $aeronave);

        $data = $request->validate([
            'tipo'             => 'required|in:inspeccion_50h,inspeccion_100h,anual,AD,SB,correctivo,preventivo',
            'descripcion'      => 'required|string',
            'fecha_realizado'  => 'required|date',
            'horas_aeronave'   => 'required|numeric|min:0',
            'realizado_por'    => 'required|string',
            'licencia_tecnico' => 'nullable|string',
            'proxima_fecha'    => 'nullable|date|after:fecha_realizado',
            'proximas_horas'   => 'nullable|numeric|min:0',
            'costo'            => 'nullable|numeric|min:0',
            'archivo_url'      => 'nullable|url',
        ]);

        $data['aeronave_id'] = $id;
        $registro = RegistroMantenimiento::create($data);

        // Si es mantenimiento mayor, reset SMOH
        if ($data['tipo'] === 'anual') {
            $aeronave->update([
                'horas_desde_oh' => 0,
                'estado'         => 'disponible',
            ]);
        }

        return response()->json([
            'ok'      => true,
            'mensaje' => 'Registro de mantenimiento creado (RAC 43).',
            'data'    => $registro,
        ], 201);
    }

    /* ─── MEL ─── */

    public function mel($id): JsonResponse
    {
        Aeronave::findOrFail($id);
        return response()->json([
            'ok'   => true,
            'data' => MelItem::where('aeronave_id', $id)->orderBy('fecha_limite')->get(),
        ]);
    }

    public function storeMel(Request $request, $id): JsonResponse
    {
        Aeronave::findOrFail($id);

        $data = $request->validate([
            'item_ata'       => 'required|string',
            'descripcion'    => 'required|string',
            'categoria'      => 'required|in:A,B,C,D',
            'fecha_apertura' => 'required|date',
            'procedimiento_o'=> 'nullable|string',
        ]);

        // Calcular fecha límite según categoría MEL
        $dias = match ($data['categoria']) {
            'A' => 3, 'B' => 10, 'C' => 30, 'D' => 120,
        };

        $data['aeronave_id']  = $id;
        $data['fecha_limite'] = Carbon::parse($data['fecha_apertura'])->addDays($dias)->toDateString();

        $mel = MelItem::create($data);

        return response()->json([
            'ok'      => true,
            'mensaje' => "MEL abierto. Categoría {$data['categoria']} — límite: {$mel->fecha_limite}.",
            'data'    => $mel,
        ], 201);
    }

    public function updateMel(Request $request, $id, $mid): JsonResponse
    {
        $mel  = MelItem::where('aeronave_id', $id)->findOrFail($mid);
        $data = $request->validate([
            'estado'         => 'required|in:abierto,cerrado',
            'cerrado_por'    => 'required_if:estado,cerrado|string',
        ]);

        if ($data['estado'] === 'cerrado') {
            $data['fecha_cierre'] = now()->toDateString();
        }

        $mel->update($data);
        return response()->json(['ok' => true, 'mensaje' => 'MEL actualizado.', 'data' => $mel]);
    }

    /* ─── ADs (Airworthiness Directives) ─── */

    public function ads($id): JsonResponse
    {
        Aeronave::findOrFail($id);
        return response()->json([
            'ok'   => true,
            'data' => AirworthinessDirective::where('aeronave_id', $id)
                ->orderBy('fecha_limite')->get(),
        ]);
    }

    public function storeAd(Request $request, $id): JsonResponse
    {
        Aeronave::findOrFail($id);
        $data = $request->validate([
            'numero_ad'    => 'required|string',
            'titulo'       => 'required|string',
            'fecha_emision'=> 'required|date',
            'fecha_limite' => 'nullable|date',
            'archivo_url'  => 'nullable|url',
        ]);
        $data['aeronave_id'] = $id;
        $ad = AirworthinessDirective::create($data);
        return response()->json(['ok' => true, 'data' => $ad], 201);
    }

    public function updateAd(Request $request, $id, $aid): JsonResponse
    {
        $ad   = AirworthinessDirective::where('aeronave_id', $id)->findOrFail($aid);
        $data = $request->validate([
            'estado'              => 'required|in:pendiente,cumplido,no_aplica',
            'metodo_cumplimiento' => 'required_if:estado,cumplido|string',
        ]);
        $ad->update($data);
        return response()->json(['ok' => true, 'data' => $ad]);
    }

    /* ─── Bitácora técnica / Horas acumuladas ─── */

    public function bitacoraTecnica($id): JsonResponse
    {
        $aeronave = Aeronave::findOrFail($id);
        $this->authorize('verBitacoraTecnica', $aeronave);

        return response()->json([
            'ok'   => true,
            'data' => [
                'aeronave'       => $aeronave,
                'mantenimientos' => $aeronave->mantenimientos()->paginate(20),
                'mel_abiertos'   => $aeronave->melAbiertos()->get(),
                'ads_pendientes' => $aeronave->adsPendientes()->get(),
            ],
        ]);
    }

    public function horasAcumuladas($id): JsonResponse
    {
        $aeronave = Aeronave::findOrFail($id);

        $horas = $aeronave->bitacoras()
            ->selectRaw("
                COUNT(*)                      AS total_vuelos,
                SUM(horas_totales)            AS horas_totales,
                SUM(horas_dual)               AS horas_dual,
                SUM(horas_solo)               AS horas_solo,
                SUM(horas_noche)              AS horas_noche,
                SUM(horas_ifr)                AS horas_ifr,
                MIN(fecha)                    AS primer_vuelo,
                MAX(fecha)                    AS ultimo_vuelo
            ")->first();

        return response()->json(['ok' => true, 'data' => $horas]);
    }
}
