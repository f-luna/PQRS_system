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
        Schema::create('pqrs', function (Blueprint $table) {
            $table->id();
            $table->string('numero_radicado', 20)->unique();
            $table->enum('categoria', ['peticion', 'queja', 'reclamo', 'sugerencia']);
            $table->text('descripcion');
            $table->enum('estado', ['recibida', 'en_gestion', 'resuelta', 'cerrada'])->default('recibida');
            $table->date('fecha_limite');
            $table->integer('dias_habiles_usados')->default(0);
            $table->foreignId('cliente_id')->constrained('users');
            $table->foreignId('agente_id')->nullable()->constrained('users');
            $table->text('respuesta_final')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pqrs');
    }
};
