<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ubicacion', function (Blueprint $table) {
            $table->integer('id_ubicacion')->autoIncrement()->primary();

            $table->string('direccion', 200);
            $table->string('ciudad', 100);
            $table->string('provincia', 100)->nullable();
            $table->string('pais', 100)->nullable()->default('Paraguay');
            $table->decimal('latitud', 9, 6);
            $table->decimal('longitud', 9, 6);
            $table->string('referencia', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ubicacion');
    }
};
