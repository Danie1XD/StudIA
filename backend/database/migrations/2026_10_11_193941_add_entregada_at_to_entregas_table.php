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
        Schema::table('entregas', function (Blueprint $table) {
            $table->timestamp('entregada_at')->nullable()->after('archivo_path');
            $table->decimal('calificacion_final', 6, 2)->nullable()->change();
        });

        Schema::table('evaluaciones_ia', function (Blueprint $table) {
            $table->decimal('calificacion_sugerida', 6, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */

    public function down(): void
    {
        Schema::table('entregas', function (Blueprint $table) {
            $table->dropColumn('entregada_at');
            $table->decimal('calificacion_final', 5, 2)->nullable()->change();
        });

        Schema::table('evaluaciones_ia', function (Blueprint $table) {
            $table->decimal('calificacion_sugerida', 5, 2)->change();
        });
    }
};
