<?php

session_start();
require_once __DIR__ . '/../modelo/ModeloMarcacion.php';

if (empty($_SESSION['usuario'])) {
    header('Location: ../vista/vistas/Login.php');
    exit;
}

$rol = $_SESSION['usuario']['rol'] ?? '';
if (!in_array($rol, ['Trabajador', 'Supervisor'], true)) {
    header('Location: ../vista/vistas/HomeAdminTotal.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../vista/vistas/HomeTrabajador.php');
    exit;
}

$accion = trim($_POST['accion'] ?? '');
$idTrabajador = (int)($_SESSION['usuario']['id'] ?? 0);

if ($idTrabajador <= 0) {
    $_SESSION['error_marcacion'] = 'No se pudo identificar al trabajador en sesion.';
    header('Location: ../vista/vistas/HomeTrabajador.php');
    exit;
}

if ($accion === 'entrada') {
    $resultado = ModeloMarcacion::registrarEntrada($idTrabajador);
} elseif ($accion === 'salida') {
    $resultado = ModeloMarcacion::registrarSalida($idTrabajador);
} else {
    $resultado = ['error' => true, 'mensaje' => 'Accion de marcacion no valida.'];
}

if ($resultado['error']) {
    $_SESSION['error_marcacion'] = $resultado['mensaje'];
} else {
    $_SESSION['exito_marcacion'] = $resultado['mensaje'];
}

header('Location: ../vista/vistas/HomeTrabajador.php');
exit;
