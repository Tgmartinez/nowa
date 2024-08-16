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
            CREATE PROCEDURE sp_get_citas(   b_filtro_like bool
                                                    , vc_string_filtro varchar(100)
                                                    , buscar_id_empleado varchar(100)
                                                    , buscar_fecha_cita varchar(100)
                                                    , buscar_hora_inicio varchar(100)
                                                    , buscar_hora_fin varchar(100)
                                                    , buscar_id_cliente varchar(100)
                                                    , buscar_estado varchar(100)
                                                    , i_limit_init int
                                                    , i_limit_end int
                                                    , i_colum_order int
                                                    , vc_order_direct varchar(20)
                                                    , OUT v_registro_total BIGINT(20)
                                                  )
            BEGIN
                DECLARE vc_column_order VARCHAR(100);

                SET vc_column_order=CASE 
                                    WHEN i_colum_order=0 THEN CONCAT(" ORDER BY id ",vc_order_direct)
                                    WHEN i_colum_order=1 THEN CONCAT(" ORDER BY id_empleado ",vc_order_direct)
                                    WHEN i_colum_order=2 THEN CONCAT(" ORDER BY fecha_cita ",vc_order_direct)
                                    WHEN i_colum_order=3 THEN CONCAT(" ORDER BY hora_inicio ",vc_order_direct)
                                    WHEN i_colum_order=4 THEN CONCAT(" ORDER BY hora_fin ",vc_order_direct)
                                    WHEN i_colum_order=5 THEN CONCAT(" ORDER BY id_cliente ",vc_order_direct)
                                    WHEN i_colum_order=6 THEN CONCAT(" ORDER BY estado ",vc_order_direct)
                                    ELSE ""
                END;

                SET @_QUERY:=CONCAT("SELECT   id
                                            , id_empleado
                                            , fecha_cita
                                            , hora_inicio
                                            , hora_fin
                                            , id_cliente
                                            , estado
                                        FROM citas 
                                        WHERE citas.b_status > 0 "
                );

                IF(b_filtro_like=true) THEN BEGIN

                    SET @_QUERY:=CONCAT(@_QUERY, " AND (id_empleado LIKE \'%",TRIM(vc_string_filtro),"%\'");
                    SET @_QUERY:=CONCAT(@_QUERY, " OR  fecha_cita LIKE \'%",TRIM(vc_string_filtro),"%\'");
                    SET @_QUERY:=CONCAT(@_QUERY, " OR  hora_inicio LIKE \'%",TRIM(vc_string_filtro),"%\'");
                    SET @_QUERY:=CONCAT(@_QUERY, " OR  hora_fin LIKE \'%",TRIM(vc_string_filtro),"%\'");
                    SET @_QUERY:=CONCAT(@_QUERY, " OR  id_cliente LIKE \'%",TRIM(vc_string_filtro),"%\'");
                    SET @_QUERY:=CONCAT(@_QUERY, " OR  estado LIKE \'%",TRIM(vc_string_filtro),"%\'");
                    SET @_QUERY:=CONCAT(@_QUERY, " )");

                END; END IF;

                IF(b_filtro_like = false) THEN BEGIN

                    SET @_QUERY:=CONCAT(@_QUERY, " AND (id_empleado LIKE \'%",TRIM(buscar_id_empleado),"%\'");
                    SET @_QUERY:=CONCAT(@_QUERY, " AND  fecha_cita LIKE \'%",TRIM(buscar_fecha_cita),"%\'");
                    SET @_QUERY:=CONCAT(@_QUERY, " AND  hora_inicio LIKE \'%",TRIM(buscar_hora_inicio),"%\'");
                    SET @_QUERY:=CONCAT(@_QUERY, " AND  hora_fin LIKE \'%",TRIM(buscar_hora_fin),"%\'");
                    SET @_QUERY:=CONCAT(@_QUERY, " AND  id_cliente LIKE \'%",TRIM(buscar_id_cliente),"%\'");
                    SET @_QUERY:=CONCAT(@_QUERY, " AND  estado LIKE \'%",TRIM(buscar_estado),"%\'");
                    SET @_QUERY:=CONCAT(@_QUERY, " )");

                END; END IF;

                IF(i_colum_order IS NOT NULL) THEN BEGIN
                    SET @_QUERY:=CONCAT(@_QUERY,vc_column_order);
                END; END IF;

                IF(i_limit_init >= 0 AND i_limit_end > 0 ) THEN BEGIN
                    SET @_QUERY:=CONCAT(@_QUERY, " LIMIT ",i_limit_init,",",i_limit_end);
                END; END IF;

                PREPARE QRY FROM @_QUERY; EXECUTE QRY ; DEALLOCATE PREPARE QRY ;

                SELECT COUNT(*) INTO v_registro_total FROM citas WHERE b_status > 0;            

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
        Schema::connection('mysql')->getConnection()->statement('DROP PROCEDURE IF EXISTS sp_get_citas');

    }
};
