<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('postulacion', function (Blueprint $table) {
            $table->integer('id_postulacion')->autoIncrement()->primary();

            $table->integer('id_candidato');
            $table->integer('id_oferta');
            $table->timestamp('fecha_postulacion')->nullable()->useCurrent();
            $table->integer('id_estado_post')->nullable();
            $table->text('observaciones')->nullable();
            $table->string('cv_adjunto', 255)->nullable();
            $table->text('carta_presentacion')->nullable();
            $table->text('respuestas_preguntas')->nullable();

            $table->foreign('id_candidato', 'postulacion_id_candidato_fkey')
                ->references('id_candidato')->on('candidato')
                ->onUpdate('no action')->onDelete('cascade');
            $table->foreign('id_oferta', 'postulacion_id_oferta_fkey')
                ->references('id_oferta')->on('oferta')
                ->onUpdate('no action')->onDelete('cascade');
            $table->foreign('id_estado_post', 'postulacion_id_estado_post_fkey')
                ->references('id_estado_post')->on('estado_postulacion')
                ->onUpdate('no action')->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('postulacion');
    }
};
