<?php

session_start();
//Importa los modelos para la solicitud de vacaciones y notificaciones
require_once __DIR__ . '/../modelo/ModeloSolicitudVacaciones.php';
require_once __DIR__ . '/../modelo/ModeloNotificacion.php';

//Si no hay usuario logueado redirige al login
if (empty($_SESSION['usuario'])) {
    header('Location: ../vista/vistas/Login.php');
    exit;
}
//Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../vista/vistas/SolicitudVacaciones.php');
    exit;
}
//Obtiene los datos del usuario y valida el rol
$accion = trim($_POST['accion'] ?? '');
$idTrabajador = (int)($_SESSION['usuario']['id'] ?? 0);
$idAdmin = $_SESSION['usuario']['id'] ?? 0;
$rol = $_SESSION['usuario']['rol'] ?? '';

//Valida que el ID del usuario sea válido antes de continuar
if ($idTrabajador <= 0) {
    $_SESSION['error_vacaciones'] = 'No se pudo identificar al trabajador en sesion.';
    header('Location: ../vista/vistas/SolicitudVacaciones.php');
    exit;
}

// Acciones para trabajadores: Solicitar vacaiones solo trabajadores
if ($accion === 'solicitar' && in_array($rol, ['Trabajador', 'Supervisor'], true)) {

    //obtiene y valida los datos del formulario
    $datos = [
        'fecha_inicio'    => trim($_POST['fechaInicio'] ?? ''),
        'fecha_fin'       => trim($_POST['fechaFin'] ?? ''),
        'dias_solicitados' => (int)($_POST['diasSolicitados'] ?? 0),
        'motivo'          => trim($_POST['motivo'] ?? ''),
    ];

    //validaciones basicas, se detiene en el primer error encontrado
    if (empty($datos['fecha_inicio'])) {
        $_SESSION['error_vacaciones'] = 'La fecha de inicio es obligatoria.';
    } elseif (empty($datos['fecha_fin'])) {
        $_SESSION['error_vacaciones'] = 'La fecha de fin es obligatoria.';
    } elseif ($datos['dias_solicitados'] <= 0) {
        $_SESSION['error_vacaciones'] = 'Debe especificar al menos 1 dia.';
    } else {
        //Crea la solicitud en la bd
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
    //Redirige de vuelta al formulario 
    header('Location: ../vista/vistas/SolicitudVacaciones.php');
    exit;
}

// Acciones para administradores
if (!in_array($rol, ['Administrador Total', 'Administrador'], true)) {
    header('Location: ../vista/vistas/HomeAdminTotal.php');
    exit;
}
    //Aprobar solicitudes de vacaciones
if ($accion === 'aprobar' && !empty($_POST['idSolicitud'])) {
    $idSolicitud = (int)$_POST['idSolicitud'];
    $comentario = trim($_POST['comentario'] ?? '');
    
    $resultado = ModeloSolicitudVacaciones::aprobarSolicitud($idSolicitud, $idAdmin, $comentario);

    if ($resultado['error']) {
        $_SESSION['error_vacaciones'] = $resultado['mensaje'];
    } else {
        $_SESSION['exito_vacaciones'] = $resultado['mensaje'];

        //Notifica al trabajador que su solicitud fue aprobada
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

    //Rechazar solicitudes de vacaciones
} elseif ($accion === 'rechazar' && !empty($_POST['idSolicitud'])) {
    $idSolicitud = (int)$_POST['idSolicitud'];
    $motivo = trim($_POST['motivo'] ?? '');

    //Obligatorio el motivo del rechazo
    if (empty($motivo)) {
        $_SESSION['error_vacaciones'] = 'Debe proporcionar un motivo para rechazar.';
    } else {
        $resultado = ModeloSolicitudVacaciones::rechazarSolicitud($idSolicitud, $idAdmin, $motivo);

        if ($resultado['error']) {
            $_SESSION['error_vacaciones'] = $resultado['mensaje'];
        } else {
            $_SESSION['exito_vacaciones'] = $resultado['mensaje'];

            //Le notifica al trabajador que su solicitud fue rechazada
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

//Si la acción no coincide con ninguna, redirige con un error
$_SESSION['error_vacaciones'] = 'Accion no valida.';
header('Location: ../vista/vistas/GestionSolicitudesVacaciones.php');
exit;
