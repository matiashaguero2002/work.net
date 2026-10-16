<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('titulo', function (Blueprint $table) {
            $table->integer('id_titulo')->autoIncrement()->primary();

            $table->integer('id_candidato');
            $table->string('institucion', 150);
            $table->string('nombre', 150);
            $table->string('tipo', 50)->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->string('certificado', 255)->nullable();

            $table->foreign('id_candidato', 'titulo_id_candidato_fkey')
                ->references('id_candidato')->on('candidato')
                ->onUpdate('no action')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('titulo');
    }
};
