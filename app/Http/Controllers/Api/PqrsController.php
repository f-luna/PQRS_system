<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResponderPqrsRequest;
use App\Http\Requests\StorePqrsRequest;
use App\Http\Resources\AdjuntoResource;
use App\Http\Resources\PqrsHistorialResource;
use App\Http\Resources\PqrsResource;
use App\Models\Adjunto;
use App\Models\Pqrs;
use App\Models\PqrsHistorial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PqrsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Pqrs::with(['cliente', 'agente']);

        if ($user->role === 'cliente') {
            $query->where('cliente_id', $user->id);
        } elseif ($user->role === 'agente') {
            $query->where('agente_id', $user->id);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }

        $pqrs = $query->latest()->paginate(15);

        return PqrsResource::collection($pqrs)->response();
    }

    public function store(StorePqrsRequest $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'cliente') {
            return response()->json(['message' => 'Solo los clientes pueden crear PQRS.'], 403);
        }

        $radicado = 'PQRS-'.now()->format('Ymd').'-'.strtoupper(Str::random(5));

        $fechaLimite = Carbon::now()->addBusinessDays(15);

        $pqrs = Pqrs::create([
            'numero_radicado' => $radicado,
            'categoria' => $request->categoria,
            'descripcion' => $request->descripcion,
            'estado' => 'recibida',
            'fecha_limite' => $fechaLimite,
            'cliente_id' => $user->id,
        ]);

        PqrsHistorial::create([
            'pqrs_id' => $pqrs->id,
            'estado_nuevo' => 'recibida',
            'comentario' => 'PQRS creada por el cliente.',
            'usuario_id' => $user->id,
            'created_at' => now(),
        ]);

        if ($request->hasFile('adjuntos')) {
            foreach ($request->file('adjuntos') as $archivo) {
                $ruta = $archivo->store('adjuntos/'.$pqrs->id, 'local');

                Adjunto::create([
                    'pqrs_id' => $pqrs->id,
                    'nombre_original' => $archivo->getClientOriginalName(),
                    'ruta' => $ruta,
                    'mime_type' => $archivo->getMimeType(),
                    'tamanio_kb' => (int) ceil($archivo->getSize() / 1024),
                ]);
            }
        }

        $pqrs->load(['cliente', 'adjuntos']);

        return (new PqrsResource($pqrs))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Pqrs $pqrs): PqrsResource|JsonResponse
    {
        $user = $request->user();

        if ($user->role === 'cliente' && $pqrs->cliente_id !== $user->id) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        if ($user->role === 'agente' && $pqrs->agente_id !== $user->id) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $pqrs->load(['cliente', 'agente', 'historial.usuario', 'adjuntos']);

        return new PqrsResource($pqrs);
    }

    public function asignar(Request $request, Pqrs $pqrs): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Solo el administrador puede asignar agentes.'], 403);
        }

        $request->validate([
            'agente_id' => ['required', 'exists:users,id'],
        ]);

        $estadoAnterior = $pqrs->estado;
        $pqrs->update([
            'agente_id' => $request->agente_id,
            'estado' => 'en_gestion',
        ]);

        PqrsHistorial::create([
            'pqrs_id' => $pqrs->id,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => 'en_gestion',
            'comentario' => 'Agente asignado por el administrador.',
            'usuario_id' => $user->id,
            'created_at' => now(),
        ]);

        $pqrs->load(['cliente', 'agente']);

        return response()->json([
            'message' => 'Agente asignado correctamente.',
            'data' => new PqrsResource($pqrs),
        ]);
    }

    public function responder(ResponderPqrsRequest $request, Pqrs $pqrs): JsonResponse
    {
        $user = $request->user();

        if ($user->role === 'agente' && $pqrs->agente_id !== $user->id) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $estadoAnterior = $pqrs->estado;

        $diasUsados = (int) Carbon::parse($pqrs->created_at)->diffInBusinessDays(Carbon::now());

        $pqrs->update([
            'respuesta_final' => $request->respuesta_final,
            'estado' => 'resuelta',
            'dias_habiles_usados' => $diasUsados,
        ]);

        PqrsHistorial::create([
            'pqrs_id' => $pqrs->id,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => 'resuelta',
            'comentario' => 'Respuesta final enviada.',
            'usuario_id' => $user->id,
            'created_at' => now(),
        ]);

        $pqrs->load(['cliente', 'agente']);

        return response()->json([
            'message' => 'PQRS respondida correctamente.',
            'data' => new PqrsResource($pqrs),
        ]);
    }

    public function cambiarEstado(Request $request, Pqrs $pqrs): JsonResponse
    {
        $user = $request->user();

        if (! in_array($user->role, ['admin', 'agente'])) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $request->validate([
            'estado' => ['required', Rule::in(['recibida', 'en_gestion', 'resuelta', 'cerrada'])],
            'comentario' => ['nullable', 'string'],
        ]);

        $estadoAnterior = $pqrs->estado;
        $pqrs->update(['estado' => $request->estado]);

        PqrsHistorial::create([
            'pqrs_id' => $pqrs->id,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $request->estado,
            'comentario' => $request->comentario,
            'usuario_id' => $user->id,
            'created_at' => now(),
        ]);

        $pqrs->load(['cliente', 'agente']);

        return response()->json([
            'message' => 'Estado actualizado.',
            'data' => new PqrsResource($pqrs),
        ]);
    }

    public function historial(Pqrs $pqrs): JsonResponse
    {
        $historial = $pqrs->historial()->with('usuario')->latest('created_at')->get();

        return PqrsHistorialResource::collection($historial)->response();
    }

    public function subirAdjunto(Request $request, Pqrs $pqrs): JsonResponse
    {
        $request->validate([
            'archivo' => ['required', 'file', 'max:5120'],
        ]);

        $archivo = $request->file('archivo');
        $ruta = $archivo->store('adjuntos/'.$pqrs->id, 'local');

        $adjunto = Adjunto::create([
            'pqrs_id' => $pqrs->id,
            'nombre_original' => $archivo->getClientOriginalName(),
            'ruta' => $ruta,
            'mime_type' => $archivo->getMimeType(),
            'tamanio_kb' => (int) ceil($archivo->getSize() / 1024),
        ]);

        return (new AdjuntoResource($adjunto))
            ->response()
            ->setStatusCode(201);
    }

    public function descargarAdjunto(Adjunto $adjunto): BinaryFileResponse
    {
        return response()->download(
            storage_path('app/private/'.$adjunto->ruta),
            $adjunto->nombre_original,
        );
    }
}
