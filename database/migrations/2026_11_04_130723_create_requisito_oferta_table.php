<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requisito_oferta', function (Blueprint $table) {
            $table->integer('id_requisito')->autoIncrement()->primary();

            $table->integer('id_oferta');
            $table->integer('id_tipo_req')->nullable();
            $table->string('descripcion', 255);
            $table->boolean('obligatorio')->nullable()->default(true);

            $table->foreign('id_oferta', 'requisito_oferta_id_oferta_fkey')
                ->references('id_oferta')->on('oferta')
                ->onUpdate('no action')->onDelete('cascade');
            $table->foreign('id_tipo_req', 'requisito_oferta_id_tipo_req_fkey')
                ->references('id_tipo_req')->on('tipo_requisito')
                ->onUpdate('no action')->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requisito_oferta');
    }
};
