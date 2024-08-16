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
            CREATE PROCEDURE sp_set_citas(IN `v_id` BIGINT(20)
                                                              , IN `v_id_empleado` VARCHAR(200)
                                                              , IN `v_fecha_cita` VARCHAR(210)
                                                              , IN `v_hora_inicio` VARCHAR(220)
                                                              , IN `v_hora_fin` VARCHAR(230)
                                                              , IN `v_id_cliente` VARCHAR(240)
                                                              , IN `v_estado` VARCHAR(250)
                                                              , OUT `v_i_response` INTEGER)
            BEGIN
                SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

                UPDATE citas 
                  SET id_empleado   = v_id_empleado
                    , fecha_cita   = v_fecha_cita
                    , hora_inicio   = v_hora_inicio
                    , hora_fin   = v_hora_fin
                    , id_cliente   = v_id_cliente
                    , estado   = v_estado
                WHERE id= v_id ;
                SET v_i_response := LAST_INSERT_ID();            
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
        Schema::connection('mysql')->getConnection()->statement('DROP PROCEDURE IF EXISTS sp_set_update_citas');

    }
};
