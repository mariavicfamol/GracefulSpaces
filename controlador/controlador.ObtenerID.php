<?php
session_start();
if (empty($_SESSION['usuario'])) {
    http_response_code(403);
    echo json_encode(['error' => true]);
    exit;
}

require_once __DIR__ . '/../configuracion/GracefulSpacesDB.configuracion.php';

$conexion = obtenerConexion();
$res      = $conexion->query("SELECT COUNT(*) AS total FROM trabajadores");
$total    = (int)$res->fetch_assoc()['total'];
$conexion->close();

$anio      = date('Y');
$siguiente = 'GS-' . $anio . '-' . str_pad($total + 1, 3, '0', STR_PAD_LEFT);

echo json_encode(['id_empresa' => $siguiente]);