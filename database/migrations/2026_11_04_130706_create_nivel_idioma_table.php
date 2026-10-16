<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nivel_idioma', function (Blueprint $table) {
            $table->integer('id_nivel')->autoIncrement()->primary();

            $table->string('nombre', 30)->unique();
            $table->integer('orden')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nivel_idioma');
    }
};
