<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificacionLog extends Model
{
    public $timestamps = false;

    protected $table = 'notificaciones_log';

    protected $fillable = [
        'pqrs_id',
        'usuario_id',
        'tipo',
        'asunto',
        'enviado',
        'enviado_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'enviado' => 'boolean',
            'enviado_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function pqrs(): BelongsTo
    {
        return $this->belongsTo(Pqrs::class, 'pqrs_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
