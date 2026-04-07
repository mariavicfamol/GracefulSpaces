<?php

session_start();
require_once __DIR__ . '/../modelo/ModeloPlanilla.php';

if (empty($_SESSION['usuario'])) {
    header('Location: ../vista/vistas/Login.php');
    exit;
}

$usuario = $_SESSION['usuario'];
$rol = $usuario['rol'] ?? '';
$idUsuario = (int)($usuario['id'] ?? 0);

$esAdmin = in_array($rol, ['Administrador Total', 'Administrador'], true);
$esEmpleado = in_array($rol, ['Trabajador', 'Supervisor'], true);

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

if ($accion === 'aprobar') {
    if (!$esAdmin) {
        http_response_code(403);
        header('Location: ../vista/vistas/HomeAdminTotal.php');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ../vista/vistas/PlanillasAdmin.php');
        exit;
    }

    $idPlanilla = (int)($_POST['id_planilla'] ?? 0);
    $resultado = ModeloPlanilla::aprobarPlanilla($idPlanilla, $idUsuario);

    if ($resultado['error']) {
        $_SESSION['error_planilla_admin'] = $resultado['mensaje'];
    } else {
        $_SESSION['exito_planilla_admin'] = $resultado['mensaje'];
    }

    $anioRedir = (int)($_POST['anio'] ?? 0);
    $mesRedir = (int)($_POST['mes'] ?? 0);
    $trabajadorRedir = (int)($_POST['trabajador'] ?? 0);

    $params = [];
    if ($anioRedir > 0) {
        $params[] = 'anio=' . $anioRedir;
    }
    if ($mesRedir > 0) {
        $params[] = 'mes=' . $mesRedir;
    }
    if ($trabajadorRedir > 0) {
        $params[] = 'trabajador=' . $trabajadorRedir;
    }

    $query = empty($params) ? '' : ('?' . implode('&', $params));
    header('Location: ../vista/vistas/PlanillasAdmin.php' . $query);
    exit;
}

if ($accion === 'generar') {
    if (!$esAdmin) {
        http_response_code(403);
        header('Location: ../vista/vistas/HomeAdminTotal.php');
        exit;
    }

    $anio = (int)($_POST['anio'] ?? 0);
    $mes = (int)($_POST['mes'] ?? 0);
    $tarifa = (float)($_POST['tarifa_hora'] ?? 0);

    $resultado = ModeloPlanilla::generarPlanillasMensuales($anio, $mes, $tarifa, $idUsuario);

    if ($resultado['error']) {
        $_SESSION['error_planilla_admin'] = $resultado['mensaje'];
    } else {
        $_SESSION['exito_planilla_admin'] = $resultado['mensaje']
            . ' Total empleados: ' . $resultado['total_empleados']
            . ' | Planillas procesadas: ' . $resultado['total_planillas'];
    }

    header('Location: ../vista/vistas/PlanillasAdmin.php?anio=' . $anio . '&mes=' . $mes);
    exit;
}

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

    $idTrabajadorPlanilla = (int)$planilla['id_trabajador'];

    if ($esEmpleado && $idTrabajadorPlanilla !== $idUsuario) {
        http_response_code(403);
        echo 'No tienes permiso para descargar esta planilla.';
        exit;
    }

    if (!$esAdmin && !$esEmpleado) {
        http_response_code(403);
        echo 'No tienes permisos para descargar planillas.';
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

if ($esAdmin) {
    header('Location: ../vista/vistas/PlanillasAdmin.php');
} elseif ($esEmpleado) {
    header('Location: ../vista/vistas/MisPlanillas.php');
} else {
    header('Location: ../vista/vistas/HomeAdminTotal.php');
}
exit;
