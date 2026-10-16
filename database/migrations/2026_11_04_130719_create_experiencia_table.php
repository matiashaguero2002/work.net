<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('experiencia', function (Blueprint $table) {
            $table->integer('id_experiencia')->autoIncrement()->primary();

            $table->integer('id_candidato');
            $table->string('puesto', 100);
            $table->string('empresa', 150);
            $table->text('tareas')->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->boolean('actual')->nullable()->default(false);
            $table->string('ubicacion', 150)->nullable();

            $table->foreign('id_candidato', 'experiencia_id_candidato_fkey')
                ->references('id_candidato')->on('candidato')
                ->onUpdate('no action')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('experiencia');
    }
};
