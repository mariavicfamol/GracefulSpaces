<?php

define('DB_SERVIDOR',  'bxqtzm3rnnsr7lwf9akd-mysql.services.clever-cloud.com');
define('DB_NOMBRE',    'bxqtzm3rnnsr7lwf9akd');
define('DB_USUARIO',   'udgygsl4vnqzvmgp');
define('DB_PASSWORD',  'e1vbFaaz1kHfFlZYbqII');
define('DB_PUERTO',    3306);
define('APP_TIMEZONE', 'America/Vancouver');

date_default_timezone_set(APP_TIMEZONE);

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
            'mensaje' => 'Error de conexion a la base de datos: ' . $conexion->connect_error
        ]));
    }

    $conexion->set_charset('utf8');
    $conexion->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
    $conexion->options(MYSQLI_READ_DEFAULT_GROUP, 'max_allowed_packet=16M');

    // Alinea la sesion SQL al desfase horario actual de Vancouver.
    $offsetVancouver = (new DateTime('now', new DateTimeZone(APP_TIMEZONE)))->format('P');
    $conexion->query("SET time_zone = '$offsetVancouver'");

    return $conexion;
}
