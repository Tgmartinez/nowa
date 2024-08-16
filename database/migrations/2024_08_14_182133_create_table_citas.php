<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql')->create('citas', function (Blueprint $table) {
            $table->id();
            $table->string('id_empleado', 30)->nullable();
            $table->string('fecha_cita', 30)->nullable();
            $table->string('hora_inicio', 30)->nullable();
            $table->string('hora_fin', 30)->nullable();
            $table->string('id_cliente', 30)->nullable();
            $table->string('estado', 30)->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            $table->boolean('b_status')->index()->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('citas');
    }
};
