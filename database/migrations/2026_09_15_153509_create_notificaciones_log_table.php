<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notificaciones_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pqrs_id')->constrained('pqrs');
            $table->foreignId('usuario_id')->constrained('users');
            $table->enum('tipo', ['registro', 'cambio_estado', 'alerta_vencimiento', 'respuesta_final']);
            $table->string('asunto', 255);
            $table->boolean('enviado')->default(false);
            $table->timestamp('enviado_at')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificaciones_log');
    }
};
