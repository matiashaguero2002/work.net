<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidato_aptitud', function (Blueprint $table) {
            // PK compuesta (sin id propio)
            $table->integer('id_candidato');
            $table->integer('id_aptitud');
            $table->string('nivel', 30)->nullable();

            $table->primary(['id_candidato', 'id_aptitud']);

            $table->foreign('id_candidato', 'candidato_aptitud_id_candidato_fkey')
                ->references('id_candidato')->on('candidato')
                ->onUpdate('no action')->onDelete('cascade');
            $table->foreign('id_aptitud', 'candidato_aptitud_id_aptitud_fkey')
                ->references('id_aptitud')->on('aptitud')
                ->onUpdate('no action')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidato_aptitud');
    }
};
