<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PqrsResource\Pages;
use App\Models\Pqrs;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PqrsResource extends Resource
{
    protected static ?string $model = Pqrs::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';

    protected static ?string $navigationLabel = 'PQRS';

    protected static ?string $modelLabel = 'PQRS';

    protected static ?string $pluralModelLabel = 'PQRS';

    protected static ?string $slug = 'pqrs';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de la PQRS')
                    ->schema([
                        Forms\Components\TextInput::make('numero_radicado')
                            ->label('Número de radicado')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true)
                            ->disabled(fn (string $operation): bool => $operation === 'edit'),
                        Forms\Components\Select::make('categoria')
                            ->label('Categoría')
                            ->options([
                                'peticion' => 'Petición',
                                'queja' => 'Queja',
                                'reclamo' => 'Reclamo',
                                'sugerencia' => 'Sugerencia',
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\Select::make('estado')
                            ->options([
                                'recibida' => 'Recibida',
                                'en_gestion' => 'En gestión',
                                'resuelta' => 'Resuelta',
                                'cerrada' => 'Cerrada',
                            ])
                            ->required()
                            ->default('recibida')
                            ->native(false),
                        Forms\Components\DatePicker::make('fecha_limite')
                            ->label('Fecha límite')
                            ->required(),
                        Forms\Components\TextInput::make('dias_habiles_usados')
                            ->label('Días hábiles usados')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                    ])->columns(2),

                Forms\Components\Section::make('Asignación')
                    ->schema([
                        Forms\Components\Select::make('cliente_id')
                            ->label('Cliente')
                            ->relationship('cliente', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('agente_id')
                            ->label('Agente asignado')
                            ->relationship('agente', 'name')
                            ->searchable()
                            ->preload(),
                    ])->columns(2),

                Forms\Components\Section::make('Detalle')
                    ->schema([
                        Forms\Components\Textarea::make('descripcion')
                            ->label('Descripción')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('respuesta_final')
                            ->label('Respuesta final')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_radicado')
                    ->label('Radicado')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('categoria')
                    ->label('Categoría')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'peticion' => 'info',
                        'queja' => 'warning',
                        'reclamo' => 'danger',
                        'sugerencia' => 'success',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'peticion' => 'Petición',
                        'queja' => 'Queja',
                        'reclamo' => 'Reclamo',
                        'sugerencia' => 'Sugerencia',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'recibida' => 'gray',
                        'en_gestion' => 'warning',
                        'resuelta' => 'success',
                        'cerrada' => 'info',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'recibida' => 'Recibida',
                        'en_gestion' => 'En gestión',
                        'resuelta' => 'Resuelta',
                        'cerrada' => 'Cerrada',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('cliente.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('agente.name')
                    ->label('Agente')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Sin asignar'),
                Tables\Columns\TextColumn::make('fecha_limite')
                    ->label('Fecha límite')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn (Pqrs $record): string => $record->fecha_limite->isPast() && $record->estado !== 'cerrada' ? 'danger' : 'gray'),
                Tables\Columns\TextColumn::make('dias_habiles_usados')
                    ->label('Días usados')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('categoria')
                    ->label('Categoría')
                    ->options([
                        'peticion' => 'Petición',
                        'queja' => 'Queja',
                        'reclamo' => 'Reclamo',
                        'sugerencia' => 'Sugerencia',
                    ]),
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'recibida' => 'Recibida',
                        'en_gestion' => 'En gestión',
                        'resuelta' => 'Resuelta',
                        'cerrada' => 'Cerrada',
                    ]),
                Tables\Filters\SelectFilter::make('agente_id')
                    ->label('Agente')
                    ->relationship('agente', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPqrs::route('/'),
            'create' => Pages\CreatePqrs::route('/create'),
            'edit' => Pages\EditPqrs::route('/{record}/edit'),
        ];
    }
}
