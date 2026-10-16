<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('oferta', function (Blueprint $table) {
            $table->integer('id_oferta')->autoIncrement()->primary();

            $table->integer('id_empleador');
            $table->integer('id_ubicacion');
            $table->integer('id_area')->nullable();
            $table->integer('id_modalidad')->nullable();
            $table->integer('id_tipo_contrato')->nullable();
            $table->integer('id_estado_oferta')->nullable();
            $table->string('titulo', 100);
            $table->text('descripcion');
            $table->text('requisitos')->nullable();
            $table->string('forma_postulacion', 255)->nullable();
            $table->decimal('remuneracion_min', 10, 2)->nullable();
            $table->decimal('remuneracion_max', 10, 2)->nullable();
            $table->boolean('mostrar_remuneracion')->nullable()->default(true);
            $table->text('beneficios')->nullable();
            $table->timestamp('fecha_publicacion')->nullable()->useCurrent();
            $table->timestamp('fecha_limite')->nullable();
            $table->timestamp('fecha_cierre')->nullable();
            $table->integer('vacantes')->nullable()->default(1);
            $table->integer('visualizaciones')->nullable()->default(0);
            $table->text('preguntas_seleccion')->nullable();

            $table->foreign('id_empleador', 'oferta_id_empleador_fkey')
                ->references('id_empleador')->on('empleador')
                ->onUpdate('no action')->onDelete('cascade');
            $table->foreign('id_ubicacion', 'oferta_id_ubicacion_fkey')
                ->references('id_ubicacion')->on('ubicacion')
                ->onUpdate('no action')->onDelete('no action');
            $table->foreign('id_area', 'oferta_id_area_fkey')
                ->references('id_area')->on('area_laboral')
                ->onUpdate('no action')->onDelete('no action');
            $table->foreign('id_modalidad', 'oferta_id_modalidad_fkey')
                ->references('id_modalidad')->on('modalidad_trabajo')
                ->onUpdate('no action')->onDelete('no action');
            $table->foreign('id_tipo_contrato', 'oferta_id_tipo_contrato_fkey')
                ->references('id_tipo_contrato')->on('tipo_contrato')
                ->onUpdate('no action')->onDelete('no action');
            $table->foreign('id_estado_oferta', 'oferta_id_estado_oferta_fkey')
                ->references('id_estado_oferta')->on('estado_oferta')
                ->onUpdate('no action')->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oferta');
    }
};
