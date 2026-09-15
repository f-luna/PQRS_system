<?php

namespace App\Filament\Resources\PqrsResource\Pages;

use App\Filament\Resources\PqrsResource;
use App\Models\PqrsHistorial;
use Filament\Resources\Pages\EditRecord;

class EditPqrs extends EditRecord
{
    protected static string $resource = PqrsResource::class;

    protected static ?string $title = 'Gestionar PQRS';

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function afterSave(): void
    {
        $changes = $this->record->getChanges();

        if (isset($changes['estado'])) {
            PqrsHistorial::create([
                'pqrs_id' => $this->record->id,
                'estado_anterior' => $this->record->getOriginal('estado'),
                'estado_nuevo' => $this->record->estado,
                'comentario' => $this->record->estado === 'resuelta'
                    ? 'Respuesta final enviada'
                    : null,
                'usuario_id' => auth()->id(),
                'created_at' => now(),
            ]);
        }

        if (isset($changes['agente_id'])) {
            PqrsHistorial::create([
                'pqrs_id' => $this->record->id,
                'estado_anterior' => $this->record->estado,
                'estado_nuevo' => $this->record->estado,
                'comentario' => 'Agente asignado',
                'usuario_id' => auth()->id(),
                'created_at' => now(),
            ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
