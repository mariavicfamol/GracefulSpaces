<?php

/**
 * CAPA DE CONTROLADOR
 * Archivo: controlador/ControladorLogin.php
 * Descripcion: Maneja la peticion de inicio de sesion
 * Metodo esperado: POST
 * Parametros POST: usuario, password
 */

require_once __DIR__ . '/../modelo/ModeloUsuario.php';

header('Content-Type: text/plain; charset=utf-8');

// Solo aceptar peticiones POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'error';
    exit;
}

$usuario  = trim($_POST['usuario']  ?? '');
$password = trim($_POST['password'] ?? '');

if ($usuario === '' || $password === '') {
    echo 'error';
    exit;
}

$modeloUsuario = new ModeloUsuario();
$credencialesValidas = $modeloUsuario->verificarCredenciales($usuario, $password);

echo $credencialesValidas ? 'success' : 'error';
