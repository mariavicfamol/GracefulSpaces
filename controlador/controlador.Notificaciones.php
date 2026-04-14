<?php

session_start();
// Importa el modelo de notificaciones de BD
require_once __DIR__ . '/../modelo/ModeloNotificacion.php';
//valida la sesión, obtiene datos del usuario
if (empty($_SESSION['usuario'])) {
    header('Location: ../vista/vistas/Login.php');
    exit;
}
$usuario = $_SESSION['usuario'];
$idUsuario = (int)($usuario['id'] ?? 0);
$accion = trim($_POST['accion'] ?? $_GET['accion'] ?? '');

//Marcar la notificación como leída
//Obtiene el ID de la notificación, la marca como leída en Bd
if ($accion === 'marcarLeida' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $idNotificacion = (int)($_POST['id_notificacion'] ?? 0);
    ModeloNotificacion::marcarLeida($idNotificacion, $idUsuario);
    //Si es petición AJAX, manda JSON
    if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true]);
        exit;
    }
    //Si no, redirige
    header('Location: ../vista/vistas/Notificaciones.php');
    exit;
}
//Marcar todas como leidas
if ($accion === 'marcarTodasLeidas' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    ModeloNotificacion::marcarTodasLeidas($idUsuario);
    if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true]);
        exit;
    }
    //Redirigue
    header('Location: ../vista/vistas/Notificaciones.php');
    exit;
}
header('Location: ../vista/vistas/Notificaciones.php');
exit;
