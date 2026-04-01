<?php

session_start();
require_once __DIR__ . '/../modelo/ModeloPlanilla.php';

if (empty($_SESSION['usuario'])) {
    header('Location: ../vista/vistas/Login.php');
    exit;
}

$usuario = $_SESSION['usuario'];
$rol = $usuario['rol'] ?? '';
$idTrabajador = (int)($usuario['id'] ?? 0);

if (!in_array($rol, ['Trabajador', 'Supervisor'], true) || $idTrabajador <= 0) {
    header('Location: ../vista/vistas/HomeAdminTotal.php');
    exit;
}

$accion = $_GET['accion'] ?? '';

if ($accion === 'descargar') {
    $idPlanilla = (int)($_GET['id'] ?? 0);

    if ($idPlanilla <= 0) {
        http_response_code(400);
        echo 'Planilla no válida.';
        exit;
    }

    $planilla = ModeloPlanilla::obtenerPlanillaConDetalles($idPlanilla);

    if (!$planilla || (int)$planilla['id_trabajador'] !== $idTrabajador) {
        http_response_code(403);
        echo 'No tienes permiso para descargar esta planilla.';
        exit;
    }

    $mes = str_pad((string)$planilla['mes'], 2, '0', STR_PAD_LEFT);
    $archivo = 'Planilla_' . preg_replace('/[^A-Za-z0-9\-_]/', '_', $planilla['id_empresa']) . '_' . $planilla['anio'] . '_' . $mes . '.xls';

    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $archivo);

    echo "Planilla\t" . $planilla['id_empresa'] . "\n";
    echo "Empleado\t" . $planilla['trabajador'] . "\n";
    echo "Periodo\t" . $planilla['anio'] . '-' . $mes . "\n";
    echo "Tarifa por Hora\t" . number_format((float)$planilla['tarifa_hora'], 2, '.', '') . "\n";
    echo "Horas Totales\t" . number_format((float)$planilla['horas_totales'], 2, '.', '') . "\n";
    echo "Monto Total\t" . number_format((float)$planilla['monto_total'], 2, '.', '') . "\n\n";

    echo "Fecha\tEntrada\tSalida\tHoras Laboradas\n";

    foreach ($planilla['detalles'] as $detalle) {
        $fecha = date('d/m/Y', strtotime($detalle['fecha_marcacion']));
        $entrada = !empty($detalle['hora_entrada']) ? date('H:i:s', strtotime($detalle['hora_entrada'])) : '--:--:--';
        $salida = !empty($detalle['hora_salida']) ? date('H:i:s', strtotime($detalle['hora_salida'])) : '--:--:--';
        $horas = number_format((float)$detalle['horas_laboradas'], 2, '.', '');

        echo $fecha . "\t" . $entrada . "\t" . $salida . "\t" . $horas . "\n";
    }

    exit;
}

header('Location: ../vista/vistas/MisHistorialPagos.php');
exit;