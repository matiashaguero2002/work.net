<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuario', function (Blueprint $table) {
            $table->integer('id_usuario')->autoIncrement()->primary();

            $table->string('nombre', 100);
            $table->string('email', 100)->unique();
            $table->string('password', 255);
            $table->timestamp('fecha_registro')->nullable()->useCurrent();
            $table->string('estado', 20)->nullable()->default('activo');
            $table->string('rol', 20);
            $table->timestamp('ultimo_acceso')->nullable();
            $table->integer('intentos_fallidos')->nullable()->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario');
    }
};
