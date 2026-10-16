<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curso', function (Blueprint $table) {
            $table->integer('id_curso')->autoIncrement()->primary();

            $table->integer('id_candidato');
            $table->string('institucion', 150)->nullable();
            $table->string('nombre', 150);
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->string('certificado', 255)->nullable();
            $table->integer('horas')->nullable();

            $table->foreign('id_candidato', 'curso_id_candidato_fkey')
                ->references('id_candidato')->on('candidato')
                ->onUpdate('no action')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curso');
    }
};
