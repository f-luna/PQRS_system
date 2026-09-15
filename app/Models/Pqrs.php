<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pqrs extends Model
{
    protected $fillable = [
        'numero_radicado',
        'categoria',
        'descripcion',
        'estado',
        'fecha_limite',
        'dias_habiles_usados',
        'cliente_id',
        'agente_id',
        'respuesta_final',
    ];

    protected function casts(): array
    {
        return [
            'fecha_limite' => 'date',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }

    public function agente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agente_id');
    }

    public function historial(): HasMany
    {
        return $this->hasMany(PqrsHistorial::class, 'pqrs_id');
    }

    public function adjuntos(): HasMany
    {
        return $this->hasMany(Adjunto::class, 'pqrs_id');
    }

    public function notificaciones(): HasMany
    {
        return $this->hasMany(NotificacionLog::class, 'pqrs_id');
    }
}
