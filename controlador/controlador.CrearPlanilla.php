<?php

session_start();
require_once __DIR__ . '/../modelo/ModeloPlanilla.php';

// Proteger ruta
if (empty($_SESSION['usuario'])) {
    header('Location: ../vista/vistas/Login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../vista/vistas/CrearPlanilla.php');
    exit;
}

// Recoger datos del formulario
$datos = [
    'id_trabajador'  => (int)($_POST['idTrabajador']   ?? 0),
    'periodo_inicio' => $_POST['periodoInicio']        ?? '',
    'periodo_fin'    => $_POST['periodoFin']           ?? '',
    'cantidad_horas' => (float)($_POST['cantidadHoras'] ?? 0),
    'tarifa_hora'    => (float)($_POST['tarifaHora']   ?? 0),
    'estado'         => $_POST['estado']               ?? 'Pendiente',
    'notas'          => trim($_POST['notas']           ?? ''),
];

// Validaciones básicas
if ($datos['id_trabajador'] <= 0) {
    $_SESSION['error_crear_planilla'] = 'Debe seleccionar un trabajador.';
    header('Location: ../vista/vistas/CrearPlanilla.php');
    exit;
}

if (empty($datos['periodo_inicio']) || empty($datos['periodo_fin'])) {
    $_SESSION['error_crear_planilla'] = 'Las fechas del período son obligatorias.';
    header('Location: ../vista/vistas/CrearPlanilla.php');
    exit;
}

if ($datos['cantidad_horas'] <= 0) {
    $_SESSION['error_crear_planilla'] = 'La cantidad de horas debe ser mayor a 0.';
    header('Location: ../vista/vistas/CrearPlanilla.php');
    exit;
}

if ($datos['tarifa_hora'] <= 0) {
    $_SESSION['error_crear_planilla'] = 'La tarifa por hora debe ser mayor a 0.';
    header('Location: ../vista/vistas/CrearPlanilla.php');
    exit;
}

if (strtotime($datos['periodo_inicio']) > strtotime($datos['periodo_fin'])) {
    $_SESSION['error_crear_planilla'] = 'La fecha de inicio no puede ser mayor a la fecha de fin.';
    header('Location: ../vista/vistas/CrearPlanilla.php');
    exit;
}

$resultado = ModeloPlanilla::crearPlanilla($datos);

if ($resultado['error']) {
    $_SESSION['error_crear_planilla'] = $resultado['mensaje'];
    header('Location: ../vista/vistas/CrearPlanilla.php');
} else {
    $_SESSION['exito_crear_planilla'] = '¡Planilla creada exitosamente! ID: ' . $resultado['id_planilla'];
    header('Location: ../vista/vistas/DashboardPlanillas.php');
}
exit;
