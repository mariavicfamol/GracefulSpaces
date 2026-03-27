<?php

session_start();
require_once __DIR__ . '/../modelo/ModeloUsuario.php';

// Proteger ruta
if (empty($_SESSION['usuario'])) {
    header('Location: ../vista/vistas/Login.php');
    exit;
}

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

// -------------------------------------------------------
// BUSCAR usuario (GET o POST con ?accion=buscar)
// -------------------------------------------------------
if ($accion === 'buscar') {
    header('Content-Type: application/json; charset=utf-8');
    $termino = trim($_GET['termino'] ?? $_POST['termino'] ?? '');
    if (empty($termino)) {
        echo json_encode(['error' => true, 'mensaje' => 'Ingrese un término de búsqueda.']);
        exit;
    }
    $usuario = ModeloUsuario::buscarUsuario($termino);
    if ($usuario) {
        unset($usuario['password_hash']);
        echo json_encode(['error' => false, 'usuario' => $usuario]);
    } else {
        echo json_encode(['error' => true, 'mensaje' => 'No se encontró ningún colaborador con ese dato.']);
    }
    exit;
}

// -------------------------------------------------------
// ACTUALIZAR usuario (POST con accion=actualizar)
// -------------------------------------------------------
if ($accion === 'actualizar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) {
        echo json_encode(['error' => true, 'mensaje' => 'ID de usuario no válido.']);
        exit;
    }

    $datos = [
        'nombre'                => trim($_POST['nombreUsuario']        ?? ''),
        'apellido1'             => trim($_POST['apellido1']            ?? ''),
        'apellido2'             => trim($_POST['apellido2']            ?? ''),
        'tipo_documento'        => $_POST['tipoDocumento']             ?? 'Cédula',
        'numero_identificacion' => trim($_POST['numeroIdentificacion'] ?? ''),
        'fecha_nacimiento'      => $_POST['fechaNacimiento']           ?? null,
        'sexo'                  => $_POST['sexoUsuario']               ?? 'Prefiero no decir',
        'genero'                => trim($_POST['generoUsuario']        ?? ''),
        'nacionalidad'          => $_POST['nacionalidadUsuario']       ?? 'Costa Rica',
        'cargo'                 => $_POST['cargoPuesto']               ?? 'Trabajador',
        'tipo_contrato'         => $_POST['tipoContrato']              ?? 'Tiempo completo',
        'fecha_ingreso'         => $_POST['fechaIngreso']              ?? null,
        'correo_personal'       => trim($_POST['correoPersonal']       ?? ''),
        'correo_corporativo'    => trim($_POST['correoCorporativo']    ?? ''),
        'telefono'              => trim($_POST['telefono']             ?? ''),
        'contacto_emergencia'   => trim($_POST['nombreEmergencia']     ?? ''),
        'telefono_emergencia'   => trim($_POST['telefonoEmergencia']   ?? ''),
        'direccion'             => trim($_POST['direccionExacta']      ?? ''),
        'login_usuario'         => trim($_POST['nombreAcceso']         ?? ''),
        'password'              => $_POST['campoPassword']             ?? '',
        'rol'                   => $_POST['rolSistema']                ?? 'Trabajador',
        'estado'                => $_POST['estadoCuenta']              ?? 'Activo',
        'foto_actual'           => $_POST['fotoActual']                ?? null,
    ];

    // Foto actualizada
    if (!empty($_FILES['fotoPerfil']['tmp_name'])) {
        $datos['foto_tmp']    = $_FILES['fotoPerfil']['tmp_name'];
        $datos['foto_nombre'] = $_FILES['fotoPerfil']['name'];
    }

    if (empty($datos['nombre']) || empty($datos['apellido1'])) {
        echo json_encode(['error' => true, 'mensaje' => 'Nombre y primer apellido son obligatorios.']);
        exit;
    }

    echo json_encode(ModeloUsuario::actualizarUsuario($id, $datos));
    exit;
}

// -------------------------------------------------------
// DAR DE BAJA (POST con accion=darDeBaja)
// -------------------------------------------------------
if ($accion === 'darDeBaja' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) {
        echo json_encode(['error' => true, 'mensaje' => 'ID no válido.']);
        exit;
    }
    echo json_encode(ModeloUsuario::darDeBaja($id));
    exit;
}

// Redirigir si llegan sin acción válida
header('Location: ../vista/vistas/EditarUsuarios.php');
exit;
