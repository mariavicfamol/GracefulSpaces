<?php

session_start();
//Importa el modelo planillas de bd
require_once __DIR__ . '/../modelo/ModeloPlanilla.php';
//Valida la sesión y el obtiene datos del usuario
if (empty($_SESSION['usuario'])) {
    header('Location: ../vista/vistas/Login.php');
    exit;
}

$usuario = $_SESSION['usuario'];
$rol = $usuario['rol'] ?? '';
$idTrabajador = (int)($usuario['id'] ?? 0);

//rol y id valido
if (!in_array($rol, ['Trabajador', 'Supervisor'], true) || $idTrabajador <= 0) {
    header('Location: ../vista/vistas/HomeAdminTotal.php');
    exit;
}

$accion = $_GET['accion'] ?? '';

//descarga planilla propia y valida el ID
if ($accion === 'descargar') {
    $idPlanilla = (int)($_GET['id'] ?? 0);

    if ($idPlanilla <= 0) {
        http_response_code(400);
        echo 'Planilla no válida.';
        exit;
    }
    //obtiene la planilla desde la BD y valida que exista y que si sea del trabajador
    $planilla = ModeloPlanilla::obtenerPlanillaConDetalles($idPlanilla);

    if (!$planilla || (int)$planilla['id_trabajador'] !== $idTrabajador) {
        http_response_code(403);
        echo 'No tienes permiso para descargar esta planilla.';
        exit;
    }
    //Valida que si esté aprobaad
    if ((int)($planilla['aprobada'] ?? 0) !== 1) {
        http_response_code(403);
        echo 'Esta planilla todavía no ha sido aprobada por administración.';
        exit;
    }
    //crea el nombre del archivo y descarga en exel
    $mes = str_pad((string)$planilla['mes'], 2, '0', STR_PAD_LEFT);
    $archivo = 'Planilla_' . preg_replace('/[^A-Za-z0-9\-_]/', '_', $planilla['id_empresa']) . '_' . $planilla['anio'] . '_' . $mes . '.xls';

    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $archivo);

    //Datos generales
    echo "Planilla\t" . $planilla['id_empresa'] . "\n";
    echo "Empleado\t" . $planilla['trabajador'] . "\n";
    echo "Periodo\t" . $planilla['anio'] . '-' . $mes . "\n";
    echo "Tarifa por Hora\t" . number_format((float)$planilla['tarifa_hora'], 2, '.', '') . "\n";
    echo "Horas Totales\t" . number_format((float)$planilla['horas_totales'], 2, '.', '') . "\n";
    echo "Bono\t" . number_format((float)($planilla['bono_manual'] ?? 0), 2, '.', '') . "\n";
    echo "Monto Total\t" . number_format((float)$planilla['monto_total'], 2, '.', '') . "\n\n";

    echo "Fecha\tEntrada\tSalida\tHoras Laboradas\n";
    //Recorre detalles
    foreach ($planilla['detalles'] as $detalle) {
        $fecha = date('d/m/Y', strtotime($detalle['fecha_marcacion']));
        $entrada = !empty($detalle['hora_entrada']) ? date('H:i:s', strtotime($detalle['hora_entrada'])) : '--:--:--';
        $salida = !empty($detalle['hora_salida']) ? date('H:i:s', strtotime($detalle['hora_salida'])) : '--:--:--';
        $horas = number_format((float)$detalle['horas_laboradas'], 2, '.', '');

        echo $fecha . "\t" . $entrada . "\t" . $salida . "\t" . $horas . "\n";
    }

    exit;
}
//Redirige si no hay acción
header('Location: ../vista/vistas/MisHistorialPagos.php');
exit;