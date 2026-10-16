<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificacion', function (Blueprint $table) {
            $table->integer('id_notificacion')->autoIncrement()->primary();

            $table->integer('id_usuario');
            $table->integer('id_tipo_notif')->nullable();
            $table->string('titulo', 100);
            $table->string('mensaje', 255);
            $table->string('url_destino', 255)->nullable();
            $table->integer('id_referencia')->nullable();
            $table->string('tipo_referencia', 30)->nullable();
            $table->timestamp('fecha')->nullable()->useCurrent();
            $table->boolean('leida')->nullable()->default(false);

            $table->foreign('id_usuario', 'notificacion_id_usuario_fkey')
                ->references('id_usuario')->on('usuario')
                ->onUpdate('no action')->onDelete('cascade');
            $table->foreign('id_tipo_notif', 'notificacion_id_tipo_notif_fkey')
                ->references('id_tipo_notif')->on('tipo_notificacion')
                ->onUpdate('no action')->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificacion');
    }
};
