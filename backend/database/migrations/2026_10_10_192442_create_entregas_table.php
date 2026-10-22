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
        Schema::create('entregas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tarea_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();   // el alumno
            $table->longText('contenido')->nullable();
            $table->string('archivo_path')->nullable();
            $table->string('estado')->default('entregado');                  // entregado | calificado
            $table->decimal('calificacion_final', 5, 2)->nullable();
            $table->text('retroalimentacion_final')->nullable();
            $table->timestamp('calificada_at')->nullable();
            $table->timestamps();
            $table->unique(['tarea_id', 'user_id']);   // una entrega por alumno y tarea
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entregas');
    }
};
