<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entregas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tarea_id')->constrained('tareas')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // ID del Alumno
            $table->longText('contenido')->nullable(); // Texto del trabajo entregado
            $table->string('archivo_url')->nullable(); // Por si adjuntan un archivo o link
            
            // --- CAMPOS DE INTELIGENCIA ARTIFICIAL (GEMINI) ---
            $table->decimal('calificacion_ia', 5, 2)->nullable(); // Calificación sugerida por IA
            $table->longText('retroalimentacion_ia')->nullable(); // Análisis y feedback de la IA
            
            // --- CAMPOS DE EVALUACIÓN FINAL (DOCENTE) ---
            $table->decimal('calificacion_final', 5, 2)->nullable(); // Nota que aprueba el docente
            $table->longText('retroalimentacion_final')->nullable(); // Comentarios finales editados
            $table->enum('estado', ['entregado', 'pre_evaluado', 'calificado'])->default('entregado');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entregas');
    }
};