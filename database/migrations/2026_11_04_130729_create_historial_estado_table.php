<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_estado', function (Blueprint $table) {
            $table->integer('id_historial')->autoIncrement()->primary();

            $table->integer('id_postulacion');
            $table->integer('id_estado_post')->nullable();
            $table->timestamp('fecha_cambio')->nullable()->useCurrent();
            $table->text('comentario')->nullable();
            $table->integer('id_usuario_cambio')->nullable();

            $table->foreign('id_postulacion', 'historial_estado_id_postulacion_fkey')
                ->references('id_postulacion')->on('postulacion')
                ->onUpdate('no action')->onDelete('cascade');
            $table->foreign('id_estado_post', 'historial_estado_id_estado_post_fkey')
                ->references('id_estado_post')->on('estado_postulacion')
                ->onUpdate('no action')->onDelete('no action');
            $table->foreign('id_usuario_cambio', 'historial_estado_id_usuario_cambio_fkey')
                ->references('id_usuario')->on('usuario')
                ->onUpdate('no action')->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_estado');
    }
};
