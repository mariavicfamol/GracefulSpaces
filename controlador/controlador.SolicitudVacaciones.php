<?php

session_start();
require_once __DIR__ . '/../modelo/ModeloSolicitudVacaciones.php';
require_once __DIR__ . '/../modelo/ModeloNotificacion.php';

if (empty($_SESSION['usuario'])) {
    header('Location: ../vista/vistas/Login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../vista/vistas/SolicitudVacaciones.php');
    exit;
}

$accion = trim($_POST['accion'] ?? '');
$idTrabajador = (int)($_SESSION['usuario']['id'] ?? 0);
$idAdmin = $_SESSION['usuario']['id'] ?? 0;
$rol = $_SESSION['usuario']['rol'] ?? '';

if ($idTrabajador <= 0) {
    $_SESSION['error_vacaciones'] = 'No se pudo identificar al trabajador en sesion.';
    header('Location: ../vista/vistas/SolicitudVacaciones.php');
    exit;
}

// Acciones para trabajadores
if ($accion === 'solicitar' && in_array($rol, ['Trabajador', 'Supervisor'], true)) {
    $datos = [
        'fecha_inicio'    => trim($_POST['fechaInicio'] ?? ''),
        'fecha_fin'       => trim($_POST['fechaFin'] ?? ''),
        'dias_solicitados' => (int)($_POST['diasSolicitados'] ?? 0),
        'motivo'          => trim($_POST['motivo'] ?? ''),
    ];

    if (empty($datos['fecha_inicio'])) {
        $_SESSION['error_vacaciones'] = 'La fecha de inicio es obligatoria.';
    } elseif (empty($datos['fecha_fin'])) {
        $_SESSION['error_vacaciones'] = 'La fecha de fin es obligatoria.';
    } elseif ($datos['dias_solicitados'] <= 0) {
        $_SESSION['error_vacaciones'] = 'Debe especificar al menos 1 dia.';
    } else {
        $resultado = ModeloSolicitudVacaciones::crearSolicitud($datos, $idTrabajador);
        
        if ($resultado['error']) {
            $_SESSION['error_vacaciones'] = $resultado['mensaje'];
        } else {
            $_SESSION['exito_vacaciones'] = $resultado['mensaje'];
            ModeloNotificacion::enviarAAdmins(
                'Nueva solicitud de vacaciones de ' . $usuario['nombre'] . ' ' . $usuario['apellido1'] . '.',
                'vacaciones_solicitud'
            );
            header('Location: ../vista/vistas/HistorialSolicitudesVacaciones.php');
            exit;
        }
    }

    header('Location: ../vista/vistas/SolicitudVacaciones.php');
    exit;
}

// Acciones para administradores
if (!in_array($rol, ['Administrador Total', 'Administrador'], true)) {
    header('Location: ../vista/vistas/HomeAdminTotal.php');
    exit;
}

if ($accion === 'aprobar' && !empty($_POST['idSolicitud'])) {
    $idSolicitud = (int)$_POST['idSolicitud'];
    $comentario = trim($_POST['comentario'] ?? '');

    $resultado = ModeloSolicitudVacaciones::aprobarSolicitud($idSolicitud, $idAdmin, $comentario);

    if ($resultado['error']) {
        $_SESSION['error_vacaciones'] = $resultado['mensaje'];
    } else {
        $_SESSION['exito_vacaciones'] = $resultado['mensaje'];
        $solicitud = ModeloSolicitudVacaciones::obtenerSolicitudPorId($idSolicitud);
        if (!empty($solicitud['id_trabajador'])) {
            ModeloNotificacion::crearNotificacion(
                (int)$solicitud['id_trabajador'], 
                'Tu solicitud de vacaciones ha sido aprobada.',
                'vacaciones_aprobada'
            );
        }
    }

    header('Location: ../vista/vistas/GestionSolicitudesVacaciones.php');
    exit;
} elseif ($accion === 'rechazar' && !empty($_POST['idSolicitud'])) {
    $idSolicitud = (int)$_POST['idSolicitud'];
    $motivo = trim($_POST['motivo'] ?? '');

    if (empty($motivo)) {
        $_SESSION['error_vacaciones'] = 'Debe proporcionar un motivo para rechazar.';
    } else {
        $resultado = ModeloSolicitudVacaciones::rechazarSolicitud($idSolicitud, $idAdmin, $motivo);

        if ($resultado['error']) {
            $_SESSION['error_vacaciones'] = $resultado['mensaje'];
        } else {
            $_SESSION['exito_vacaciones'] = $resultado['mensaje'];
            $solicitud = ModeloSolicitudVacaciones::obtenerSolicitudPorId($idSolicitud);
            if (!empty($solicitud['id_trabajador'])) {
                ModeloNotificacion::crearNotificacion(
                    (int)$solicitud['id_trabajador'], 
                    'Tu solicitud de vacaciones ha sido rechazada.',
                    'vacaciones_rechazada'
                );
            }
        }
    }

    header('Location: ../vista/vistas/GestionSolicitudesVacaciones.php');
    exit;
}

$_SESSION['error_vacaciones'] = 'Accion no valida.';
header('Location: ../vista/vistas/GestionSolicitudesVacaciones.php');
exit;
