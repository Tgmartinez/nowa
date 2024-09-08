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
                    fecha_cita,
                    hora_inicio,
                    hora_fin,
                    COUNT(id_empleado) AS cantidad,
                    (SELECT COUNT(*) FROM empleados WHERE b_status = 1) AS totalEmpleados
                FROM
                    citas
                GROUP BY
                    fecha_cita, hora_inicio, hora_fin;

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
