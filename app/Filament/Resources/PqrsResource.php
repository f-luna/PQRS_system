<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PqrsResource\Pages;
use App\Models\Pqrs;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PqrsResource extends Resource
{
    protected static ?string $model = Pqrs::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';

    protected static ?string $navigationLabel = 'PQRS';

    protected static ?string $modelLabel = 'PQRS';

    protected static ?string $pluralModelLabel = 'PQRS';

    protected static ?string $slug = 'pqrs';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        $isAdmin = auth()->user()->role === 'admin';

        return $form
            ->schema([
                Forms\Components\Section::make('Información de la PQRS')
                    ->schema([
                        Forms\Components\TextInput::make('numero_radicado')
                            ->label('Número de radicado')
                            ->disabled(),
                        Forms\Components\TextInput::make('categoria')
                            ->label('Categoría')
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'peticion' => 'Petición',
                                'queja' => 'Queja',
                                'reclamo' => 'Reclamo',
                                'sugerencia' => 'Sugerencia',
                            })
                            ->disabled(),
                        Forms\Components\TextInput::make('cliente.name')
                            ->label('Cliente')
                            ->disabled(),
                        Forms\Components\DatePicker::make('fecha_limite')
                            ->label('Fecha límite')
                            ->disabled(),
                        Forms\Components\TextInput::make('dias_habiles_usados')
                            ->label('Días hábiles usados')
                            ->disabled(),
                        Forms\Components\Placeholder::make('dias_restantes')
                            ->label('Días restantes')
                            ->content(function (Pqrs $record): string {
                                if (in_array($record->estado, ['resuelta', 'cerrada'])) {
                                    return 'Finalizada';
                                }

                                $diasRestantes = (int) now()->diffInDays($record->fecha_limite, false);

                                if ($diasRestantes < 0) {
                                    return abs($diasRestantes).' días vencida';
                                }

                                return $diasRestantes.' días';
                            }),
                    ])->columns(3),

                Forms\Components\Section::make('Descripción del cliente')
                    ->schema([
                        Forms\Components\Textarea::make('descripcion')
                            ->label('Descripción')
                            ->disabled()
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Asignación')
                    ->schema([
                        Forms\Components\Select::make('agente_id')
                            ->label('Agente asignado')
                            ->options(
                                User::where('role', 'agente')
                                    ->where('is_active', true)
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->placeholder('Seleccionar agente')
                            ->disabled(! $isAdmin),
                    ])
                    ->visible($isAdmin),

                Forms\Components\Section::make('Gestión')
                    ->schema([
                        Forms\Components\Select::make('estado')
                            ->options(function (Pqrs $record) use ($isAdmin): array {
                                if ($isAdmin) {
                                    return [
                                        'recibida' => 'Recibida',
                                        'en_gestion' => 'En gestión',
                                        'resuelta' => 'Resuelta',
                                        'cerrada' => 'Cerrada',
                                    ];
                                }

                                return match ($record->estado) {
                                    'en_gestion' => [
                                        'en_gestion' => 'En gestión',
                                        'resuelta' => 'Resuelta',
                                    ],
                                    default => [
                                        $record->estado => match ($record->estado) {
                                            'recibida' => 'Recibida',
                                            'resuelta' => 'Resuelta',
                                            'cerrada' => 'Cerrada',
                                        },
                                    ],
                                };
                            })
                            ->required()
                            ->native(false),
                        Forms\Components\Textarea::make('respuesta_final')
                            ->label('Respuesta final')
                            ->rows(4)
                            ->columnSpanFull()
                            ->required(fn (Forms\Get $get): bool => $get('estado') === 'resuelta'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        $isAdmin = auth()->user()->role === 'admin';

        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $user = auth()->user();

                if ($user->role === 'agente') {
                    $query->where('agente_id', $user->id);
                }
            })
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
                    ->placeholder('Sin asignar')
                    ->visible($isAdmin),
                Tables\Columns\TextColumn::make('fecha_limite')
                    ->label('Fecha límite')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn (Pqrs $record): string => ! in_array($record->estado, ['resuelta', 'cerrada']) && $record->fecha_limite->isPast()
                        ? 'danger'
                        : 'gray'
                    ),
                Tables\Columns\TextColumn::make('dias_restantes')
                    ->label('Tiempo restante')
                    ->state(function (Pqrs $record): string {
                        if (in_array($record->estado, ['resuelta', 'cerrada'])) {
                            return 'Finalizada';
                        }

                        $dias = (int) now()->diffInDays($record->fecha_limite, false);

                        if ($dias < 0) {
                            return abs($dias).'d vencida';
                        }

                        return $dias.'d';
                    })
                    ->badge()
                    ->color(function (Pqrs $record): string {
                        if (in_array($record->estado, ['resuelta', 'cerrada'])) {
                            return 'gray';
                        }

                        $dias = (int) now()->diffInDays($record->fecha_limite, false);

                        if ($dias < 0) {
                            return 'danger';
                        }
                        if ($dias <= 3) {
                            return 'warning';
                        }

                        return 'success';
                    }),
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
                    ->relationship('agente', 'name')
                    ->visible($isAdmin),
                Tables\Filters\Filter::make('vencidas')
                    ->label('Solo vencidas')
                    ->query(fn (Builder $query): Builder => $query
                        ->where('fecha_limite', '<', now())
                        ->whereNotIn('estado', ['resuelta', 'cerrada'])
                    ),
                Tables\Filters\Filter::make('sin_asignar')
                    ->label('Sin agente asignado')
                    ->query(fn (Builder $query): Builder => $query->whereNull('agente_id'))
                    ->visible($isAdmin),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->label('Gestionar'),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPqrs::route('/'),
            'edit' => Pages\EditPqrs::route('/{record}/edit'),
        ];
    }
}
