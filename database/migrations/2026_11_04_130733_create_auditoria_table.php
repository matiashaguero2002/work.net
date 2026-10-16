<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditoria', function (Blueprint $table) {
            $table->integer('id_auditoria')->autoIncrement()->primary();

            $table->integer('id_usuario')->nullable();
            $table->string('accion', 50)->nullable();
            $table->string('tabla_afectada', 50)->nullable();
            $table->integer('id_registro')->nullable();
            $table->text('datos_anteriores')->nullable();
            $table->text('datos_nuevos')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('fecha')->nullable()->useCurrent();

            $table->foreign('id_usuario', 'auditoria_id_usuario_fkey')
                ->references('id_usuario')->on('usuario')
                ->onUpdate('no action')->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditoria');
    }
};
