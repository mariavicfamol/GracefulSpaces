<?php

session_start();
require_once __DIR__ . '/../modelo/ModeloUsuario.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../vista/vistas/Login.php');
    exit;
}

$login    = trim($_POST['correo_usuario'] ?? '');
$password = $_POST['clave_usuario'] ?? '';

if (empty($login) || empty($password)) {
    $_SESSION['error_login'] = 'Por favor ingrese su correo y contraseña.';
    header('Location: ../vista/vistas/Login.php');
    exit;
}

$usuario = ModeloUsuario::autenticar($login, $password);

if ($usuario) {
    $_SESSION['usuario'] = $usuario;
    header('Location: ../vista/vistas/HomeAdminTotal.php');
    exit;
} else {
    $_SESSION['error_login'] = 'Correo o contraseña incorrectos, o cuenta inactiva.';
    header('Location: ../vista/vistas/Login.php');
    exit;
}
