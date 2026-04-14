<?php

session_start();
// Importa el modelo de marcaciones (BD)
require_once __DIR__ . '/../modelo/ModeloMarcacion.php';

//Valida la sesión y el rol del usuario
if (empty($_SESSION['usuario'])) {
    header('Location: ../vista/vistas/Login.php');
    exit;
}

$rol = $_SESSION['usuario']['rol'] ?? '';
if (!in_array($rol, ['Trabajador', 'Supervisor'], true)) {
    header('Location: ../vista/vistas/HomeAdminTotal.php');
    exit;
}
//solo método post
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../vista/vistas/HomeTrabajador.php');
    exit;
}
//obtiene entrada o salida y el id del trabajador
$accion = trim($_POST['accion'] ?? '');
$idTrabajador = (int)($_SESSION['usuario']['id'] ?? 0);

//valida ID
if ($idTrabajador <= 0) {
    $_SESSION['error_marcacion'] = 'No se pudo identificar al trabajador en sesion.';
    header('Location: ../vista/vistas/HomeTrabajador.php');
    exit;
}
//Registra entrada o salida
if ($accion === 'entrada') {
    $resultado = ModeloMarcacion::registrarEntrada($idTrabajador);
} elseif ($accion === 'salida') {
    $resultado = ModeloMarcacion::registrarSalida($idTrabajador);
} else {
    $resultado = ['error' => true, 'mensaje' => 'Accion de marcacion no valida.'];
}
//Guarda mensaje en sesion y redirige al home del trabajador
if ($resultado['error']) {
    $_SESSION['error_marcacion'] = $resultado['mensaje'];
} else {
    $_SESSION['exito_marcacion'] = $resultado['mensaje'];
}

header('Location: ../vista/vistas/HomeTrabajador.php');
exit;
