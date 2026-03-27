<?php

session_start();
require_once __DIR__ . '/../modelo/ModeloPlanilla.php';

// Proteger ruta
if (empty($_SESSION['usuario'])) {
    header('Location: ../vista/vistas/Login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../vista/vistas/EditarPlanillas.php');
    exit;
}

$accion = $_POST['accion'] ?? '';

if ($accion === 'actualizar') {
    $id = (int)($_POST['idPlanilla'] ?? 0);

    if ($id <= 0) {
        $_SESSION['error_editar_planilla'] = 'ID de planilla inválido.';
        header('Location: ../vista/vistas/EditarPlanillas.php');
        exit;
    }

    $datos = [
        'cantidad_horas' => (float)($_POST['cantidadHoras'] ?? 0),
        'tarifa_hora'    => (float)($_POST['tarifaHora']    ?? 0),
        'estado'         => $_POST['estado']                ?? 'Pendiente',
        'notas'          => trim($_POST['notas']            ?? ''),
    ];

    // Validaciones
    if ($datos['cantidad_horas'] <= 0) {
        $_SESSION['error_editar_planilla'] = 'La cantidad de horas debe ser mayor a 0.';
        header('Location: ../vista/vistas/EditarPlanillas.php');
        exit;
    }

    if ($datos['tarifa_hora'] <= 0) {
        $_SESSION['error_editar_planilla'] = 'La tarifa por hora debe ser mayor a 0.';
        header('Location: ../vista/vistas/EditarPlanillas.php');
        exit;
    }

    $resultado = ModeloPlanilla::actualizarPlanilla($id, $datos);

    if ($resultado['error']) {
        $_SESSION['error_editar_planilla'] = $resultado['mensaje'];
    } else {
        $_SESSION['exito_editar_planilla'] = 'Planilla actualizada exitosamente.';
    }
    
    header('Location: ../vista/vistas/EditarPlanillas.php');
    exit;

} elseif ($accion === 'cancelar') {
    $id = (int)($_POST['idPlanilla'] ?? 0);

    if ($id <= 0) {
        $_SESSION['error_editar_planilla'] = 'ID de planilla inválido.';
        header('Location: ../vista/vistas/EditarPlanillas.php');
        exit;
    }

    $resultado = ModeloPlanilla::cancelarPlanilla($id);

    if ($resultado['error']) {
        $_SESSION['error_editar_planilla'] = $resultado['mensaje'];
    } else {
        $_SESSION['exito_editar_planilla'] = 'Planilla cancelada exitosamente.';
    }
    
    header('Location: ../vista/vistas/EditarPlanillas.php');
    exit;

} else {
    header('Location: ../vista/vistas/EditarPlanillas.php');
    exit;
}
