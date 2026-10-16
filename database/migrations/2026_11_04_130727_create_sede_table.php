<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sede', function (Blueprint $table) {
            $table->integer('id_sede')->autoIncrement()->primary();

            $table->integer('id_empleador');
            $table->integer('id_ubicacion');
            $table->string('nombre', 100)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('horario', 100)->nullable();

            $table->foreign('id_empleador', 'sede_id_empleador_fkey')
                ->references('id_empleador')->on('empleador')
                ->onUpdate('no action')->onDelete('cascade');
            $table->foreign('id_ubicacion', 'sede_id_ubicacion_fkey')
                ->references('id_ubicacion')->on('ubicacion')
                ->onUpdate('no action')->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sede');
    }
};
