<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * El docente decide, tarea por tarea, si los alumnos pueden ver la rúbrica.
     * Por defecto queda oculta: así las tareas que ya existen no cambian.
     */
    public function up(): void
    {
        Schema::table('tareas', function (Blueprint $table) {
            $table->boolean('rubrica_visible')->default(false)->after('rubrica_id');
        });
    }

    public function down(): void
    {
        Schema::table('tareas', function (Blueprint $table) {
            $table->dropColumn('rubrica_visible');
        });
    }
};
