<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('oferta_guardada', function (Blueprint $table) {
            $table->integer('id_oferta_guardada')->autoIncrement()->primary();

            $table->integer('id_candidato');
            $table->integer('id_oferta');
            $table->timestamp('fecha_guardado')->nullable()->useCurrent();

            $table->unique(['id_candidato', 'id_oferta'], 'oferta_guardada_id_candidato_id_oferta_key');

            $table->foreign('id_candidato', 'oferta_guardada_id_candidato_fkey')
                ->references('id_candidato')->on('candidato')
                ->onUpdate('no action')->onDelete('cascade');
            $table->foreign('id_oferta', 'oferta_guardada_id_oferta_fkey')
                ->references('id_oferta')->on('oferta')
                ->onUpdate('no action')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oferta_guardada');
    }
};
