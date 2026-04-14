<?php
session_start();
//Valida que haya un usuario logeado
if (empty($_SESSION['usuario'])) {
    http_response_code(403);
    echo json_encode(['error' => true]);
    exit;
}
//Importa la config de la BD
require_once __DIR__ . '/../configuracion/GracefulSpacesDB.configuracion.php';
//conecta la bd
$conexion = obtenerConexion();
//Obtiene el total de trabajadores para generar un nuevo ID
$res      = $conexion->query("SELECT COUNT(*) AS total FROM trabajadores");
$total    = (int)$res->fetch_assoc()['total'];
$conexion->close();
//Obtiene el año actual y genera el nuevo ID con formato
$anio      = date('Y');
$siguiente = 'GS-' . $anio . '-' . str_pad($total + 1, 3, '0', STR_PAD_LEFT);
//Devueve en JSON
echo json_encode(['id_empresa' => $siguiente]);