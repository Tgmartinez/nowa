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
            $table->bigInteger('id_empresa')->comment('Identificador de la empresa')->index();
            $table->bigInteger('id_empleado')->comment('Identificador del empleado')->index();
            $table->bigInteger('id_cliente')->comment('Identificador del cliente')->index();
            $table->string('fecha_cita', 30)->nullable()->comment('Fecha de la cita');
            $table->time('hora_inicio')->comment('Hora de inicio de la cita');
            $table->time('hora_fin')->comment('Hora de fin de la cita');
            $table->string('estado', 30)->nullable()->comment('Estado actual de la cita');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('Fecha de creación de la cita');
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->comment('Fecha de última actualización de la cita');
            $table->boolean('b_status')->index()->default(1)->comment('Estado binario de la cita, activo o inactivo');
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
