<?php

/**
 * CAPA DE CONFIGURACION
 * Archivo: config/baseDatos.php
 * Descripcion: Parametros de conexion a la base de datos y funcion de conexion
 */

define('DB_SERVIDOR',  'bxqtzm3rnnsr7lwf9akd-mysql.services.clever-cloud.com');
define('DB_NOMBRE',    'bxqtzm3rnnsr7lwf9akd');
define('DB_USUARIO',   'udgygsl4vnqzvmgp');
define('DB_PASSWORD',  'e1vbFaaz1kHfFlZYbqII');
define('DB_PUERTO',    3306);

/**
 * Crea y retorna una conexion activa a la base de datos
 * @return mysqli Objeto de conexion
 */
function obtenerConexion(): mysqli {
    $conexion = new mysqli(
        DB_SERVIDOR,
        DB_USUARIO,
        DB_PASSWORD,
        DB_NOMBRE,
        DB_PUERTO
    );

    if ($conexion->connect_error) {
        http_response_code(500);
        die(json_encode([
            'error'   => true,
            'mensaje' => 'Error de conexion a la base de datos'
        ]));
    }

    $conexion->set_charset('utf8');
    return $conexion;
}
