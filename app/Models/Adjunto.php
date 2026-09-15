<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Adjunto extends Model
{
    protected $fillable = [
        'pqrs_id',
        'nombre_original',
        'ruta',
        'mime_type',
        'tamanio_kb',
    ];

    public function pqrs(): BelongsTo
    {
        return $this->belongsTo(Pqrs::class, 'pqrs_id');
    }
}
