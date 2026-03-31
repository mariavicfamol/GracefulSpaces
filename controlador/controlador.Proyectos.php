<?php

session_start();
require_once __DIR__ . '/../modelo/ModeloProyecto.php';

if (empty($_SESSION['usuario'])) {
    header('Location: ../vista/vistas/Login.php');
    exit;
}

$usuario = $_SESSION['usuario'];
$rol = $usuario['rol'] ?? '';
$idUsuario = (int)($usuario['id'] ?? 0);

$esAdmin = in_array($rol, ['Administrador Total', 'Administrador'], true);
$esColaborador = in_array($rol, ['Trabajador', 'Supervisor'], true);

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

if ($accion === 'colaboradoresDisponibles') {
    if (!$esAdmin) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([]);
        exit;
    }

    $fechaProyecto = trim($_GET['fecha'] ?? '');
    $horaProyecto = trim($_GET['hora'] ?? '');

    header('Content-Type: application/json; charset=utf-8');

    if ($fechaProyecto === '' || $horaProyecto === '') {
        echo json_encode([]);
        exit;
    }

    $colaboradores = ModeloProyecto::obtenerColaboradoresDisponibles($fechaProyecto, $horaProyecto);
    echo json_encode($colaboradores);
    exit;
}

if ($accion === 'crear') {
    if (!$esAdmin) {
        $_SESSION['error_proyecto_admin'] = 'No tienes permisos para crear proyectos.';
        header('Location: ../vista/vistas/HomeAdminTotal.php');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ../vista/vistas/ProyectosAdmin.php');
        exit;
    }

    $idsColaboradores = $_POST['colaboradores'] ?? [];
    if (!is_array($idsColaboradores)) {
        $idsColaboradores = [];
    }

    $idsColaboradores = array_values(array_unique(array_map('intval', $idsColaboradores)));
    $idsColaboradores = array_filter($idsColaboradores, static fn($id) => $id > 0);

    $datos = [
        'nombre' => trim($_POST['nombre'] ?? ''),
        'detalles' => trim($_POST['descripcion'] ?? ($_POST['detalles'] ?? '')),
        'especificaciones' => trim($_POST['especificaciones'] ?? ''),
        'fecha_proyecto' => trim($_POST['fecha_proyecto'] ?? ''),
        'hora_proyecto' => trim($_POST['hora_proyecto'] ?? ''),
        'materiales' => trim($_POST['materiales'] ?? ''),
    ];

    $resultado = ModeloProyecto::crearProyecto($datos, $idsColaboradores, $idUsuario);

    if ($resultado['error']) {
        $_SESSION['error_proyecto_admin'] = $resultado['mensaje'];
    } else {
        $_SESSION['exito_proyecto_admin'] = $resultado['mensaje'];
    }

    header('Location: ../vista/vistas/ProyectosAdmin.php');
    exit;
}

if ($accion === 'actualizarEstado') {
    if (!$esColaborador) {
        $_SESSION['error_mis_proyectos'] = 'No tienes permisos para actualizar proyectos.';
        header('Location: ../vista/vistas/HomeAdminTotal.php');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ../vista/vistas/MisProyectos.php');
        exit;
    }

    $idProyecto = (int)($_POST['id_proyecto'] ?? 0);
    $terminado = (int)($_POST['terminado'] ?? 0) === 1;

    if ($idProyecto <= 0) {
        $_SESSION['error_mis_proyectos'] = 'Proyecto no válido.';
        header('Location: ../vista/vistas/MisProyectos.php');
        exit;
    }

    $resultado = ModeloProyecto::actualizarTerminadoColaborador($idProyecto, $idUsuario, $terminado);

    if ($resultado['error']) {
        $_SESSION['error_mis_proyectos'] = $resultado['mensaje'];
    } else {
        $_SESSION['exito_mis_proyectos'] = $resultado['mensaje'];
    }

    header('Location: ../vista/vistas/MisProyectos.php');
    exit;
}

if ($esAdmin) {
    header('Location: ../vista/vistas/ProyectosAdmin.php');
} elseif ($esColaborador) {
    header('Location: ../vista/vistas/MisProyectos.php');
} else {
    header('Location: ../vista/vistas/HomeAdminTotal.php');
}
exit;
