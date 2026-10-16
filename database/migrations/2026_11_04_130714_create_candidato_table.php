<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidato', function (Blueprint $table) {
            $table->integer('id_candidato')->autoIncrement()->primary();

            $table->integer('id_usuario');
            $table->date('fecha_nacimiento')->nullable();
            $table->string('genero', 30)->nullable();
            $table->string('nacionalidad', 50)->nullable();
            $table->boolean('es_estudiante')->nullable()->default(false);
            $table->string('universidad', 150)->nullable();
            $table->string('estado_laboral', 50)->nullable();
            $table->text('perfil')->nullable();
            $table->string('cv', 255)->nullable();
            $table->timestamp('cv_fecha_act')->nullable();

            $table->foreign('id_usuario', 'candidato_id_usuario_fkey')
                ->references('id_usuario')->on('usuario')
                ->onUpdate('no action')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidato');
    }
};
