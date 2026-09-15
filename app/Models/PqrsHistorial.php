<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PqrsHistorial extends Model
{
    public $timestamps = false;

    protected $table = 'pqrs_historial';

    protected $fillable = [
        'pqrs_id',
        'estado_anterior',
        'estado_nuevo',
        'comentario',
        'usuario_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
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
