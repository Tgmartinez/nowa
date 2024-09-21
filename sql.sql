use nowa;

-- CALL sp_get_horarios_ocupados_by_fecha("2024-10-08");

SELECT
    fecha_cita,
    TIME_FORMAT(hora_inicio, '%H:%i') AS hora_inicio,
    TIME_FORMAT(hora_fin, '%H:%i') AS hora_fin,
    COUNT(id_empleado) AS cantidad,
    (SELECT COUNT(*) FROM empleados WHERE b_status = 1) AS totalEmpleados,
    CASE 
        WHEN COUNT(id_empleado) = (SELECT COUNT(*) FROM empleados WHERE b_status = 1)
        AND fecha_cita = "2024-10-08" -- Comprobar si la fecha está llena solo para "2024-09-27"
        THEN 'Iguales'
        ELSE 'Diferentes'
    END AS comparacion
FROM
    citas
WHERE
    fecha_cita IN ( "2024-10-08" ) -- Asegúrate de incluir las fechas a comparar
GROUP BY
    fecha_cita, hora_inicio, hora_fin;

