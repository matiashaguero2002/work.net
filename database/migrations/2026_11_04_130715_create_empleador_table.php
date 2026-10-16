<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empleador', function (Blueprint $table) {
            $table->integer('id_empleador')->autoIncrement()->primary();

            $table->integer('id_usuario');
            $table->string('nombre_empresa', 100);
            $table->string('ruc', 20)->nullable()->unique();
            $table->text('descripcion')->nullable();
            $table->string('sitio_web', 255)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('logo', 255)->nullable();

            $table->foreign('id_usuario', 'empleador_id_usuario_fkey')
                ->references('id_usuario')->on('usuario')
                ->onUpdate('no action')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empleador');
    }
};
