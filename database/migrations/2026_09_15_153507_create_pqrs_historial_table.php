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
        Schema::create('pqrs_historial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pqrs_id')->constrained('pqrs');
            $table->enum('estado_anterior', ['recibida', 'en_gestion', 'resuelta', 'cerrada'])->nullable();
            $table->enum('estado_nuevo', ['recibida', 'en_gestion', 'resuelta', 'cerrada']);
            $table->text('comentario')->nullable();
            $table->foreignId('usuario_id')->constrained('users');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pqrs_historial');
    }
};
