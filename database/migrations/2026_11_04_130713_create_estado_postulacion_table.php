<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estado_postulacion', function (Blueprint $table) {
            $table->integer('id_estado_post')->autoIncrement()->primary();

            $table->string('nombre', 30)->unique();
            $table->string('descripcion', 100)->nullable();
            $table->integer('orden')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estado_postulacion');
    }
};
