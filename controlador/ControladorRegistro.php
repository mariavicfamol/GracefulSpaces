<?php

/**
 * CAPA DE CONTROLADOR
 * Archivo: controlador/ControladorRegistro.php
 * Descripcion: Maneja el registro de nuevos clientes
 * Metodo esperado: POST
 */

require_once __DIR__ . '/../modelo/ModeloUsuario.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Metodo no permitido'
    ]);
    exit;
}

$datosEntrada = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$nombre = trim($datosEntrada['nombre'] ?? '');
$correo = trim($datosEntrada['correo'] ?? '');
$usuario = trim($datosEntrada['usuario'] ?? '');
$password = trim($datosEntrada['password'] ?? '');

if ($nombre === '' || $correo === '' || $usuario === '' || $password === '') {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Complete todos los campos'
    ]);
    exit;
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Correo invalido'
    ]);
    exit;
}

$modeloUsuario = new ModeloUsuario();
$resultado = $modeloUsuario->registrarCliente($nombre, $correo, $usuario, $password);

echo json_encode($resultado);
