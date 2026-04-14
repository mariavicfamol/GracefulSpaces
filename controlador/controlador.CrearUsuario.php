<?php

session_start();
//importa el modelo de usuario (funciones del BD)
require_once __DIR__ . '/../modelo/ModeloUsuario.php';

// Proteger ruta
if (empty($_SESSION['usuario'])) {
    header('Location: ../vista/vistas/Login.php');
    exit;
}

//Obtiene el rol ya sea admin o trabajador
$rol = $_SESSION['usuario']['rol'] ?? '';
//Bloquea el acceso a trabajadores
if ($rol === 'Trabajador') {
    http_response_code(403);
    $_SESSION['error_crear'] = 'No tienes permisos para crear usuarios.';
    header('Location: ../vista/vistas/HomeAdminTotal.php');
    exit;
}
//Solo permite el metodo post para crear los usuarios
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../vista/vistas/CrearUsuario.php');
    exit;
}

// Recoger datos del formulario
$datos = [
    'nombre'                => trim($_POST['nombreUsuario']        ?? ''),
    'apellido1'             => trim($_POST['apellido1']            ?? ''),
    'apellido2'             => trim($_POST['apellido2']            ?? ''),
    'tipo_documento'        => $_POST['tipoDocumento']             ?? 'Cédula',
    'numero_identificacion' => trim($_POST['numeroIdentificacion'] ?? ''),
    'fecha_nacimiento'      => $_POST['fechaNacimiento']           ?? null,
    'sexo'                  => $_POST['sexoUsuario']               ?? 'Prefiero no decir',
    'genero'                => trim($_POST['generoEscrito']        ?? ''),
    'nacionalidad'          => $_POST['nacionalidadUsuario']       ?? 'Costa Rica',
    'cargo'                 => $_POST['cargoPuesto']               ?? 'Trabajador',
    'tipo_contrato'         => $_POST['tipoContrato']              ?? 'Tiempo completo',
    'fecha_ingreso'         => $_POST['fechaIngreso']              ?? null,
    'correo_personal'       => trim($_POST['correoPersonal']       ?? ''),
    'telefono'              => trim($_POST['telefono']             ?? ''),
    'contacto_emergencia'   => trim($_POST['nombreEmergencia']     ?? ''),
    'telefono_emergencia'   => trim($_POST['telefonoEmergencia']   ?? ''),
    'direccion'             => trim($_POST['direccion']            ?? ''),
    'login_usuario'         => trim($_POST['loginUsuario']         ?? ''),
    'password'              => $_POST['passGenerada']              ?? '',
    'rol'                   => $_POST['rolSistema']                ?? 'Trabajador',
    'estado'                => $_POST['estadoCuenta']              ?? 'Activo',
];

// Validaciones básicas

//Valida nombre y apellido
if (empty($datos['nombre']) || empty($datos['apellido1'])) {
    $_SESSION['error_crear'] = 'El nombre y primer apellido son obligatorios.';
    header('Location: ../vista/vistas/CrearUsuario.php');
    exit;
}
//Valida el correo 
if (empty($datos['login_usuario'])) {
    $_SESSION['error_crear'] = 'El nombre de usuario (correo) es obligatorio.';
    header('Location: ../vista/vistas/CrearUsuario.php');
    exit;
}
//Valida la contraseña
if (empty($datos['password'])) {
    $_SESSION['error_crear'] = 'Debe generar o ingresar una contraseña.';
    header('Location: ../vista/vistas/CrearUsuario.php');
    exit;
}
//Valida si es admin o trabajador
$rolesPermitidos = ['Administrador', 'Trabajador'];
if (!in_array($datos['rol'], $rolesPermitidos, true)) {
    $_SESSION['error_crear'] = 'El rol seleccionado no es válido.';
    header('Location: ../vista/vistas/CrearUsuario.php');
    exit;
}
//Solo permite admin o traabajador
$cargosPermitidos = ['Administrador', 'Trabajador'];
if (!in_array($datos['cargo'], $cargosPermitidos, true)) {
    $_SESSION['error_crear'] = 'El cargo seleccionado no es válido.';
    header('Location: ../vista/vistas/CrearUsuario.php');
    exit;
}
//Formato de la fecha 
$fechaNacimiento = trim((string)($datos['fecha_nacimiento'] ?? ''));
if ($fechaNacimiento !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaNacimiento)) {
    $_SESSION['error_crear'] = 'La fecha de nacimiento no tiene un formato valido.';
    header('Location: ../vista/vistas/CrearUsuario.php');
    exit;
}

$fechaIngreso = trim((string)($datos['fecha_ingreso'] ?? ''));
if ($fechaIngreso !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaIngreso)) {
    $_SESSION['error_crear'] = 'La fecha de ingreso no tiene un formato valido.';
    header('Location: ../vista/vistas/CrearUsuario.php');
    exit;
}

// Foto del ID
if (!empty($_FILES['fotoIdentidad']['tmp_name'])) {
    $datos['foto_tmp']    = $_FILES['fotoIdentidad']['tmp_name'];
    $datos['foto_nombre'] = $_FILES['fotoIdentidad']['name'];
}
//Intenta crear el usuario y captuar errores
try {
    $resultado = ModeloUsuario::crearUsuario($datos);
} catch (Throwable $e) {
    $resultado = ['error' => true, 'mensaje' => 'No se pudo crear el usuario en este momento. Intente nuevamente.'];
}
//Redirige con mensaje exito o error
if ($resultado['error']) {
    $_SESSION['error_crear'] = $resultado['mensaje'];
    header('Location: ../vista/vistas/CrearUsuario.php');
} else {
    $_SESSION['exito_crear'] = '¡Usuario ' . htmlspecialchars($datos['nombre']) . ' creado! ID: ' . $resultado['id_empresa'];
    header('Location: ../vista/vistas/CrearUsuario.php');
}
exit;
