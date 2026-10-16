<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beneficio_oferta', function (Blueprint $table) {
            $table->integer('id_beneficio')->autoIncrement()->primary();

            $table->integer('id_oferta');
            $table->string('descripcion', 255);

            $table->foreign('id_oferta', 'beneficio_oferta_id_oferta_fkey')
                ->references('id_oferta')->on('oferta')
                ->onUpdate('no action')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beneficio_oferta');
    }
};
