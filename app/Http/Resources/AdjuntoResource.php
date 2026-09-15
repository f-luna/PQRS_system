<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdjuntoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre_original' => $this->nombre_original,
            'mime_type' => $this->mime_type,
            'tamanio_kb' => $this->tamanio_kb,
            'created_at' => $this->created_at,
        ];
    }
}
