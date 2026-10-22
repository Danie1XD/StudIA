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
        Schema::create('tareas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('materia_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seccion_id')->nullable()->constrained('secciones')->nullOnDelete();
            $table->foreignId('rubrica_id')->nullable()->constrained('rubricas')->nullOnDelete();
            $table->string('titulo');
            $table->text('instrucciones');
            $table->string('archivo_path')->nullable();
            $table->dateTime('fecha_limite')->nullable();
            $table->unsignedSmallInteger('puntaje_maximo')->default(100);
            $table->unsignedTinyInteger('parcial')->default(1);   // 1, 2 o 3
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tareas');
    }
};
