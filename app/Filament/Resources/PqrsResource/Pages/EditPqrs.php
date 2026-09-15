<?php

namespace App\Filament\Resources\PqrsResource\Pages;

use App\Filament\Resources\PqrsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPqrs extends EditRecord
{
    protected static string $resource = PqrsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
