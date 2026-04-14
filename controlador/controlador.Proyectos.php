<?php

session_start();
//Importa el modelo de proyectos de la BD
require_once __DIR__ . '/../modelo/ModeloProyecto.php';

//Si no hay sesión redirige al login
if (empty($_SESSION['usuario'])) {
    header('Location: ../vista/vistas/Login.php');
    exit;
}
//obtiene los datos del usuario y el rol
$usuario = $_SESSION['usuario'];
$rol = $usuario['rol'] ?? '';
$idUsuario = (int)($usuario['id'] ?? 0);

//Determina los roles permitidos para cada tipo de usuario
$esAdmin = in_array($rol, ['Administrador Total', 'Administrador'], true);
$esColaborador = in_array($rol, ['Trabajador', 'Supervisor'], true);

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

//Obtener colaboradores disponibles
if ($accion === 'colaboradoresDisponibles') {
    //Solo admin pueden consultar los colaboradores
    if (!$esAdmin) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([]);
        exit;
    }
    //fecha y hora del proyecto
    $fechaProyecto = trim($_GET['fecha'] ?? '');
    $horaProyecto = trim($_GET['hora'] ?? '');

    header('Content-Type: application/json; charset=utf-8');
    //Si falta la fecha o la hora devuelve array vacio
    if ($fechaProyecto === '' || $horaProyecto === '') {
        echo json_encode([]);
        exit;
    }
    //Consulta y devuelve los colaboradores disponibles
    $colaboradores = ModeloProyecto::obtenerColaboradoresDisponibles($fechaProyecto, $horaProyecto);
    echo json_encode($colaboradores);
    exit;
}

//Crear un nuevo proyecto, solo administradores
if ($accion === 'crear') {
    if (!$esAdmin) {
        $_SESSION['error_proyecto_admin'] = 'No tienes permisos para crear proyectos.';
        header('Location: ../vista/vistas/HomeAdminTotal.php');
        exit;
    }
    //Solo solicitudes post
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ../vista/vistas/ProyectosAdmin.php');
        exit;
    }
    //Obtiene el arreglo de colaboradores y lo valida
    $idsColaboradores = $_POST['colaboradores'] ?? [];
    if (!is_array($idsColaboradores)) {
        $idsColaboradores = [];
    }
    //conviere enteros, elimina duplicados y filtra ids inválidos
    $idsColaboradores = array_values(array_unique(array_map('intval', $idsColaboradores)));
    $idsColaboradores = array_filter($idsColaboradores, static fn($id) => $id > 0);

    //obtiene y limipa datos del formulario
    $datos = [
        'nombre' => trim($_POST['nombre'] ?? ''),
        'detalles' => trim($_POST['descripcion'] ?? ($_POST['detalles'] ?? '')),
        'fecha_proyecto' => trim($_POST['fecha_proyecto'] ?? ''),
        'hora_proyecto' => trim($_POST['hora_proyecto'] ?? ''),
        'materiales' => trim($_POST['materiales'] ?? ''),
    ];
    //crea el proyecto en la bd y guarda el mensaje en sesión
    $resultado = ModeloProyecto::crearProyecto($datos, $idsColaboradores, $idUsuario);

    if ($resultado['error']) {
        $_SESSION['error_proyecto_admin'] = $resultado['mensaje'];
    } else {
        $_SESSION['exito_proyecto_admin'] = $resultado['mensaje'];
    }

    header('Location: ../vista/vistas/ProyectosAdmin.php');
    exit;
}
//Actualizar estado terminado de un proyecto, solo para trabajadores
if ($accion === 'actualizarEstado') {
    if (!$esColaborador) {
        $_SESSION['error_mis_proyectos'] = 'No tienes permisos para actualizar proyectos.';
        header('Location: ../vista/vistas/HomeAdminTotal.php');
        exit;
    }
//solo post
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ../vista/vistas/MisProyectos.php');
        exit;
    }
    //Obtiene el id del proyecto y el estado terminado
    $idProyecto = (int)($_POST['id_proyecto'] ?? 0);
    $terminado = (int)($_POST['terminado'] ?? 0) === 1;
//Valida el id sea un valor positivo
    if ($idProyecto <= 0) {
        $_SESSION['error_mis_proyectos'] = 'Proyecto no válido.';
        header('Location: ../vista/vistas/MisProyectos.php');
        exit;
    }
    //Actualiza el estado del proyecto en la bd y guarda mensaje en sesión
    $resultado = ModeloProyecto::actualizarTerminadoColaborador($idProyecto, $idUsuario, $terminado);

    if ($resultado['error']) {
        $_SESSION['error_mis_proyectos'] = $resultado['mensaje'];
    } else {
        $_SESSION['exito_mis_proyectos'] = $resultado['mensaje'];
    }

    header('Location: ../vista/vistas/MisProyectos.php');
    exit;
}
//Redirige a la vista segun el rol del usuario
if ($esAdmin) {
    header('Location: ../vista/vistas/ProyectosAdmin.php');
} elseif ($esColaborador) {
    header('Location: ../vista/vistas/MisProyectos.php');
} else {
    header('Location: ../vista/vistas/HomeAdminTotal.php');
}
exit;
