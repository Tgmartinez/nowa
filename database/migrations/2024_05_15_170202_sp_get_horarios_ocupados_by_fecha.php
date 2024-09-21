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
            CREATE PROCEDURE sp_get_horarios_ocupados_by_fecha(IN fecha_param DATE)
            BEGIN
                SELECT
                    fecha_cita,
                    TIME_FORMAT(hora_inicio, \'%H:%i\') AS hora_inicio,
                    TIME_FORMAT(hora_fin, \'%H:%i\') AS hora_fin,
                    COUNT(id_empleado) AS cantidad,
                    (SELECT COUNT(*) FROM empleados WHERE b_status = 1) AS totalEmpleados,
                    CASE 
                        WHEN COUNT(id_empleado) = (SELECT COUNT(*) FROM empleados WHERE b_status = 1)
                        THEN \'Iguales\'
                        ELSE \'Diferentes\'
                    END AS comparacion
                FROM
                    citas
                WHERE
                    fecha_cita = fecha_param AND citas.b_status = 1
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
        Schema::connection('mysql')->getConnection()->statement('DROP PROCEDURE IF EXISTS sp_get_horarios_ocupados_by_fecha');
    }
};
