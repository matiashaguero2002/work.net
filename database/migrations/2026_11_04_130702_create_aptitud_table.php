<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aptitud', function (Blueprint $table) {
            $table->integer('id_aptitud')->autoIncrement()->primary();

            $table->string('nombre', 100)->unique();
            $table->string('tipo', 50)->nullable();
            $table->boolean('activo')->nullable()->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aptitud');
    }
};
