<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PqrsResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'numero_radicado' => $this->numero_radicado,
            'categoria' => $this->categoria,
            'descripcion' => $this->descripcion,
            'estado' => $this->estado,
            'fecha_limite' => $this->fecha_limite?->toDateString(),
            'dias_habiles_usados' => $this->dias_habiles_usados,
            'respuesta_final' => $this->respuesta_final,
            'cliente' => new UserResource($this->whenLoaded('cliente')),
            'agente' => new UserResource($this->whenLoaded('agente')),
            'historial' => PqrsHistorialResource::collection($this->whenLoaded('historial')),
            'adjuntos' => AdjuntoResource::collection($this->whenLoaded('adjuntos')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
