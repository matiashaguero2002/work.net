<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entrevista', function (Blueprint $table) {
            $table->integer('id_entrevista')->autoIncrement()->primary();

            $table->integer('id_postulacion');
            $table->integer('id_sede')->nullable();
            $table->timestamp('fecha_hora');
            $table->integer('duracion_minutos')->nullable();
            $table->string('modalidad', 30)->nullable();
            $table->string('enlace_virtual', 255)->nullable();
            $table->text('notas')->nullable();
            $table->string('estado', 30)->nullable()->default('pendiente');
            $table->timestamp('fecha_creacion')->nullable()->useCurrent();

            $table->foreign('id_postulacion', 'entrevista_id_postulacion_fkey')
                ->references('id_postulacion')->on('postulacion')
                ->onUpdate('no action')->onDelete('cascade');
            $table->foreign('id_sede', 'entrevista_id_sede_fkey')
                ->references('id_sede')->on('sede')
                ->onUpdate('no action')->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entrevista');
    }
};
