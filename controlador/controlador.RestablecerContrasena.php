<?php

session_start();
//Importa el modelo de usuarios de la BD
require_once __DIR__ . '/../modelo/ModeloUsuario.php';

//Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../vista/vistas/RestablecerContrasena.php');
    exit;
}
//Obtiene los datos enviados por el formulario y los valida
$login = trim($_POST['correo_usuario'] ?? '');
$fechaNacimiento = trim($_POST['fecha_nacimiento'] ?? '');
$nuevaClave = $_POST['nueva_clave'] ?? '';
$confirmarClave = $_POST['confirmar_clave'] ?? '';

//Verfica que ningún espacio quede vacío
if ($login === '' || $fechaNacimiento === '' || $nuevaClave === '' || $confirmarClave === '') {
    $_SESSION['error_reset'] = 'Complete todos los campos para continuar.';
    header('Location: ../vista/vistas/RestablecerContrasena.php');
    exit;
}

//Verifica que ambas contraseñas coincidan
if ($nuevaClave !== $confirmarClave) {
    $_SESSION['error_reset'] = 'Las contraseñas no coinciden.';
    header('Location: ../vista/vistas/RestablecerContrasena.php');
    exit;
}
//Valida la fecha de nacimiento
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaNacimiento)) {
    $_SESSION['error_reset'] = 'La fecha de nacimiento no tiene un formato valido.';
    header('Location: ../vista/vistas/RestablecerContrasena.php');
    exit;
}
//restablece la contraseña llamando al modelo
try {
    $resultado = ModeloUsuario::restablecerContrasena($login, $fechaNacimiento, $nuevaClave);
} catch (Throwable $e) {
    $resultado = ['error' => true, 'mensaje' => 'No se pudo restablecer la contraseña en este momento.'];
}
//Si hay algún error devuelve mensaje
if ($resultado['error']) {
    $_SESSION['error_reset'] = $resultado['mensaje'];
    header('Location: ../vista/vistas/RestablecerContrasena.php');
    exit;
}
//Redirige al login
$_SESSION['exito_login'] = 'Contraseña actualizada correctamente. Ahora puede iniciar sesión.';
header('Location: ../vista/vistas/Login.php');
exit;