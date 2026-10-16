<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('idioma', function (Blueprint $table) {
            $table->integer('id_idioma')->autoIncrement()->primary();

            $table->string('nombre', 50)->unique();
            $table->string('codigo_iso', 5)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idioma');
    }
};
