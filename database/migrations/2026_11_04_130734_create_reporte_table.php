<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reporte', function (Blueprint $table) {
            $table->integer('id_reporte')->autoIncrement()->primary();

            $table->integer('id_usuario')->nullable();
            $table->string('tipo', 50)->nullable();
            $table->text('motivo')->nullable();
            $table->string('estado', 30)->nullable()->default('pendiente');
            $table->timestamp('fecha_creacion')->nullable()->useCurrent();
            $table->timestamp('fecha_resolucion')->nullable();
            $table->integer('id_admin_resolucion')->nullable();

            $table->foreign('id_usuario', 'reporte_id_usuario_fkey')
                ->references('id_usuario')->on('usuario')
                ->onUpdate('no action')->onDelete('no action');
            $table->foreign('id_admin_resolucion', 'reporte_id_admin_resolucion_fkey')
                ->references('id_usuario')->on('usuario')
                ->onUpdate('no action')->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reporte');
    }
};
