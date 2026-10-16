<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modalidad_trabajo', function (Blueprint $table) {
            $table->integer('id_modalidad')->autoIncrement()->primary();

            $table->string('nombre', 50)->unique();
            $table->boolean('activo')->nullable()->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modalidad_trabajo');
    }
};
