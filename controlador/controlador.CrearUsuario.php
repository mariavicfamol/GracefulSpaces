<?php

session_start();
require_once __DIR__ . '/../modelo/ModeloUsuario.php';

// Proteger ruta
if (empty($_SESSION['usuario'])) {
    header('Location: ../vista/vistas/Login.php');
    exit;
}

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
    'correo_corporativo'    => trim($_POST['correoCorporativo']    ?? ''),
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
if (empty($datos['nombre']) || empty($datos['apellido1'])) {
    $_SESSION['error_crear'] = 'El nombre y primer apellido son obligatorios.';
    header('Location: ../vista/vistas/CrearUsuario.php');
    exit;
}
if (empty($datos['login_usuario'])) {
    $_SESSION['error_crear'] = 'El nombre de usuario (correo) es obligatorio.';
    header('Location: ../vista/vistas/CrearUsuario.php');
    exit;
}
if (empty($datos['password'])) {
    $_SESSION['error_crear'] = 'Debe generar o ingresar una contraseña.';
    header('Location: ../vista/vistas/CrearUsuario.php');
    exit;
}

// Foto de perfil
if (!empty($_FILES['fotoPerfil']['tmp_name'])) {
    $datos['foto_tmp']    = $_FILES['fotoPerfil']['tmp_name'];
    $datos['foto_nombre'] = $_FILES['fotoPerfil']['name'];
}

$resultado = ModeloUsuario::crearUsuario($datos);

if ($resultado['error']) {
    $_SESSION['error_crear'] = $resultado['mensaje'];
    header('Location: ../vista/vistas/CrearUsuario.php');
} else {
    $_SESSION['exito_crear'] = '¡Usuario ' . htmlspecialchars($datos['nombre']) . ' creado! ID: ' . $resultado['id_empresa'];
    header('Location: ../vista/vistas/CrearUsuario.php');
}
exit;
