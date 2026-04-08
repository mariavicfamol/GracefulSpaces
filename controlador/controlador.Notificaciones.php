<?php

session_start();
require_once __DIR__ . '/../modelo/ModeloNotificacion.php';

if (empty($_SESSION['usuario'])) {
    header('Location: ../vista/vistas/Login.php');
    exit;
}

$usuario = $_SESSION['usuario'];
$idUsuario = (int)($usuario['id'] ?? 0);
$accion = trim($_POST['accion'] ?? $_GET['accion'] ?? '');

if ($accion === 'marcarLeida' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $idNotificacion = (int)($_POST['id_notificacion'] ?? 0);
    ModeloNotificacion::marcarLeida($idNotificacion, $idUsuario);
    if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true]);
        exit;
    }
    header('Location: ../vista/vistas/Notificaciones.php');
    exit;
}

if ($accion === 'marcarTodasLeidas' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    ModeloNotificacion::marcarTodasLeidas($idUsuario);
    if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true]);
        exit;
    }
    header('Location: ../vista/vistas/Notificaciones.php');
    exit;
}

header('Location: ../vista/vistas/Notificaciones.php');
exit;
