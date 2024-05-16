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
            CREATE PROCEDURE sp_set_marcas(IN `v_id` BIGINT(20)
                                                              , IN `v_nombre` VARCHAR(200)
                                                              , IN `v_logo` VARCHAR(210)
                                                              , OUT `v_i_response` INTEGER)
            BEGIN
                SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

                UPDATE marcas 
                  SET nombre   = v_nombre
                    , logo   = v_logo
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
        Schema::connection('mysql')->getConnection()->statement('DROP PROCEDURE IF EXISTS sp_set_update_marcas');

    }
};