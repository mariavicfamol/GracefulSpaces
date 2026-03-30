<?php

session_start();
require_once __DIR__ . '/../modelo/ModeloUsuario.php';

function responderJson(int $statusCode, array $payload): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload);
    exit;
}

function esAccionAjaxUsuarios(string $accion): bool {
    return in_array($accion, ['buscar', 'actualizar', 'darDeBaja'], true);
}

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';
$accionAjax = esAccionAjaxUsuarios($accion);

// Proteger ruta
if (empty($_SESSION['usuario'])) {
    if ($accionAjax) {
        responderJson(401, ['error' => true, 'mensaje' => 'Sesión expirada. Vuelve a iniciar sesión.']);
    }
    header('Location: ../vista/vistas/Login.php');
    exit;
}

$rol = $_SESSION['usuario']['rol'] ?? '';

if ($rol === 'Trabajador') {
    if ($accionAjax) {
        responderJson(403, ['error' => true, 'mensaje' => 'No tienes permisos para gestionar usuarios.']);
    } else {
        http_response_code(403);
        header('Location: ../vista/vistas/HomeAdminTotal.php');
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === '') {
    responderJson(413, [
        'error' => true,
        'mensaje' => 'La solicitud no pudo procesarse. Verifique el tamaño del archivo de imagen e intente nuevamente.'
    ]);
}

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
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) {
        responderJson(400, ['error' => true, 'mensaje' => 'ID de usuario no válido.']);
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
    if (isset($_FILES['fotoPerfil']) && ($_FILES['fotoPerfil']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $errorSubida = (int)($_FILES['fotoPerfil']['error'] ?? UPLOAD_ERR_OK);

        if ($errorSubida !== UPLOAD_ERR_OK) {
            $mensaje = 'No se pudo subir la imagen seleccionada.';
            if ($errorSubida === UPLOAD_ERR_INI_SIZE || $errorSubida === UPLOAD_ERR_FORM_SIZE) {
                $mensaje = 'La imagen supera el tamaño máximo permitido por el servidor.';
            }
            responderJson(413, ['error' => true, 'mensaje' => $mensaje]);
        }

        $datos['foto_tmp']    = $_FILES['fotoPerfil']['tmp_name'];
        $datos['foto_nombre'] = $_FILES['fotoPerfil']['name'];
    }

    if (empty($datos['nombre']) || empty($datos['apellido1'])) {
        responderJson(400, ['error' => true, 'mensaje' => 'Nombre y primer apellido son obligatorios.']);
    }

    responderJson(200, ModeloUsuario::actualizarUsuario($id, $datos));
}

// -------------------------------------------------------
// DAR DE BAJA (POST con accion=darDeBaja)
// -------------------------------------------------------
if ($accion === 'darDeBaja' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) {
        responderJson(400, ['error' => true, 'mensaje' => 'ID no válido.']);
    }
    responderJson(200, ModeloUsuario::darDeBaja($id));
}

// Redirigir si llegan sin acción válida
if ($accionAjax) {
    responderJson(400, ['error' => true, 'mensaje' => 'Acción no válida para gestión de usuarios.']);
}
header('Location: ../vista/vistas/EditarUsuarios.php');
exit;
