<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mensaje;
use Illuminate\Http\Request;

class MensajeController extends Controller
{
    public function recibidos(Request $request)
    {
        $msgs = Mensaje::with(['remitente.persona'])
            ->where('destinatario_id', $request->user()->id)
            ->whereNull('respuesta_a')
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['data' => $msgs]);
    }

    public function enviados(Request $request)
    {
        $msgs = Mensaje::with(['destinatario.persona'])
            ->where('remitente_id', $request->user()->id)
            ->whereNull('respuesta_a')
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['data' => $msgs]);
    }

    public function hilo(int $id)
    {
        $raiz = Mensaje::with(['remitente.persona', 'destinatario.persona'])->findOrFail($id);
        $respuestas = Mensaje::with(['remitente.persona'])
            ->where('respuesta_a', $id)
            ->orderBy('created_at')
            ->get();

        return response()->json(['data' => ['mensaje' => $raiz, 'respuestas' => $respuestas]]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'destinatario_id' => 'required|exists:usuarios,id',
            'asunto'          => 'required|string|max:200',
            'cuerpo'          => 'required|string',
            'respuesta_a'     => 'nullable|exists:mensajes,id',
        ]);

        $data['remitente_id'] = $request->user()->id;
        $msg = Mensaje::create($data);

        return response()->json([
            'data'    => $msg->load(['remitente.persona', 'destinatario.persona']),
            'mensaje' => 'Mensaje enviado.',
        ], 201);
    }

    public function marcarLeido(int $id, Request $request)
    {
        $msg = Mensaje::where('destinatario_id', $request->user()->id)->findOrFail($id);
        $msg->update(['leido' => true, 'leido_en' => now()]);

        return response()->json(['mensaje' => 'Marcado como leído.']);
    }

    public function noLeidos(Request $request)
    {
        $count = Mensaje::where('destinatario_id', $request->user()->id)
            ->where('leido', false)
            ->count();

        return response()->json(['data' => $count]);
    }

    public function destroy(int $id, Request $request)
    {
        $userId = $request->user()->id;

        $msg = Mensaje::where(function ($q) use ($userId) {
            $q->where('remitente_id', $userId)
              ->orWhere('destinatario_id', $userId);
        })->findOrFail($id);

        // Si tiene respuestas, eliminarlas también
        Mensaje::where('respuesta_a', $id)->delete();
        $msg->delete();

        return response()->json(['mensaje' => 'Mensaje eliminado.']);
    }

    public function usuarios(Request $request)
    {
        $lista = \App\Models\Usuario::with('persona')
            ->where('id', '!=', $request->user()->id)
            ->where('activo', true)
            ->get()
            ->map(fn($u) => [
                'id'     => $u->id,
                'nombre' => trim(($u->persona->nombres ?? '') . ' ' . ($u->persona->apellidos ?? '')),
                'rol'    => $u->rol?->nombre,
            ]);

        return response()->json(['data' => $lista]);
    }
}
