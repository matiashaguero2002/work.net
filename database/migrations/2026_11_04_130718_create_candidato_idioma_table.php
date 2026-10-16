<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidato_idioma', function (Blueprint $table) {
            // PK compuesta (sin id propio)
            $table->integer('id_candidato');
            $table->integer('id_idioma');
            $table->integer('id_nivel')->nullable();

            $table->primary(['id_candidato', 'id_idioma']);

            $table->foreign('id_candidato', 'candidato_idioma_id_candidato_fkey')
                ->references('id_candidato')->on('candidato')
                ->onUpdate('no action')->onDelete('cascade');
            $table->foreign('id_idioma', 'candidato_idioma_id_idioma_fkey')
                ->references('id_idioma')->on('idioma')
                ->onUpdate('no action')->onDelete('cascade');
            $table->foreign('id_nivel', 'candidato_idioma_id_nivel_fkey')
                ->references('id_nivel')->on('nivel_idioma')
                ->onUpdate('no action')->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidato_idioma');
    }
};
