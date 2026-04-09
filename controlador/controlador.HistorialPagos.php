<?php

session_start();
require_once __DIR__ . '/../modelo/ModeloPlanilla.php';

if (empty($_SESSION['usuario'])) {
    header('Location: ../vista/vistas/Login.php');
    exit;
}

$usuario = $_SESSION['usuario'];
$rol = $usuario['rol'] ?? '';

if (!in_array($rol, ['Administrador Total', 'Administrador'], true)) {
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

    if (!$planilla) {
        http_response_code(404);
        echo 'No se encontró la planilla solicitada.';
        exit;
    }

    if ((int)($planilla['aprobada'] ?? 0) !== 1) {
        http_response_code(403);
        echo 'La planilla no está aprobada para historial de pagos.';
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
    echo "Bono\t" . number_format((float)($planilla['bono_manual'] ?? 0), 2, '.', '') . "\n";
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

if ($accion === 'descargar_todo') {
    $anio = (int)($_GET['anio'] ?? 0);
    $mes = (int)($_GET['mes'] ?? 0);
    $idTrabajador = (int)($_GET['trabajador'] ?? 0);

    $planillas = ModeloPlanilla::obtenerPlanillasAdmin(
        $anio > 0 ? $anio : null,
        $mes > 0 ? $mes : null,
        $idTrabajador > 0 ? $idTrabajador : null,
        true
    );

    $filtro = '';
    if ($anio > 0 && $mes > 0) {
        $filtro = '_' . $anio . '_' . str_pad((string)$mes, 2, '0', STR_PAD_LEFT);
    } elseif ($anio > 0) {
        $filtro = '_' . $anio;
    } elseif ($mes > 0) {
        $filtro = '_mes_' . str_pad((string)$mes, 2, '0', STR_PAD_LEFT);
    }
    if ($idTrabajador > 0) {
        $trabajador = ModeloPlanilla::obtenerTrabajadoresActivos();
        $idEmpresa = '';
        foreach ($trabajador as $t) {
            if ((int)$t['id'] === $idTrabajador) {
                $idEmpresa = preg_replace('/[^A-Za-z0-9\-_]/', '_', $t['id_empresa']);
                break;
            }
        }
        $filtro .= '_' . $idEmpresa;
    }

    $archivo = 'Historial_Pagos' . $filtro . '.xls';

    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $archivo);

    echo "Historial de Pagos\tGenerado el " . date('d/m/Y H:i') . "\n\n";

    echo "Empleado\tID Empresa\tPeriodo\tHoras Totales\tTarifa/Hora\tBono\tMonto Total\tGenerada\n";

    $totalMontoExcel = 0.0;
    foreach ($planillas as $planilla) {
        $periodo = $planilla['anio'] . '-' . str_pad((string)$planilla['mes'], 2, '0', STR_PAD_LEFT);
        $horas = number_format((float)$planilla['horas_totales'], 2, '.', '');
        $tarifa = number_format((float)$planilla['tarifa_hora'], 2, '.', '');
        $bono = number_format((float)($planilla['bono_manual'] ?? 0), 2, '.', '');
        $monto = number_format((float)$planilla['monto_total'], 2, '.', '');
        $generada = date('d/m/Y H:i', strtotime($planilla['fecha_generacion']));

        echo htmlspecialchars($planilla['trabajador']) . "\t" .
             htmlspecialchars($planilla['id_empresa']) . "\t" .
             $periodo . "\t" .
             $horas . "\t" .
             $tarifa . "\t" .
             $bono . "\t" .
             $monto . "\t" .
             $generada . "\n";

        $totalMontoExcel += (float)$planilla['monto_total'];
    }

    echo "\nTotal\t\t\t\t" . number_format($totalMontoExcel, 2, '.', '') . "\n";

    exit;
}

header('Location: ../vista/vistas/HistorialPagosAdmin.php');
exit;