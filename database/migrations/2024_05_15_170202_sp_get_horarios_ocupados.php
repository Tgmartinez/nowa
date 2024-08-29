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
        Schema::connection('mysql')->getConnection()->statement('
            CREATE PROCEDURE sp_get_horarios_ocupados()
            BEGIN

                SELECT
                    COUNT(hora_inicio) AS cantidad,
                    hora_inicio,
                    hora_fin,
                    fecha_cita
                FROM
                    citas
                GROUP BY
                    hora_inicio, hora_fin, fecha_cita;       

            END
        ');
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql')->getConnection()->statement('DROP PROCEDURE IF EXISTS sp_get_horarios_ocupados');

    }
};
