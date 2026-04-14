<?php

session_start();
//Importa modelo planilas de la BD
require_once __DIR__ . '/../modelo/ModeloPlanilla.php';

//Proceso los bonos ingresados en el formulario y los convierte en un arreglo
function parsearBonosPorEmpleado($bonosCrudos): array {
    //Si no hay bonos, devuelve vacío
    if (!is_array($bonosCrudos) || empty($bonosCrudos)) {
        return ['bonos' => [], 'errores' => []];
    }
    //Obtiene trabajador4es activvos, guarda IDs y recorre bonos enviados
    $trabajadores = ModeloPlanilla::obtenerTrabajadoresActivos();
    $idsValidos = [];
    foreach ($trabajadores as $trabajador) {
        $idTrabajador = (int)($trabajador['id'] ?? 0);
        if ($idTrabajador > 0) {
            $idsValidos[$idTrabajador] = true;
        }
    }

    $bonos = [];
    $errores = [];

    foreach ($bonosCrudos as $idTexto => $montoTexto) {
        $idTrabajador = (int)$idTexto;
        //ignora IDs inválidos
        if ($idTrabajador <= 0 || !isset($idsValidos[$idTrabajador])) {
            continue;
        }
        //ignora valores vacios o en cero
        $montoTexto = trim((string)$montoTexto);
        if ($montoTexto === '' || $montoTexto === '0' || $montoTexto === '0.00') {
            continue;
        }
        //Valida que si sean numeros
        if (!is_numeric($montoTexto)) {
            $errores[] = 'El bono del empleado ID ' . $idTrabajador . ' no es numérico.';
            continue;
        }
        // y valida que no sean negativos
        $monto = round((float)$montoTexto, 2);
        if ($monto < 0) {
            $errores[] = 'El bono del empleado ID ' . $idTrabajador . ' no puede ser negativo.';
            continue;
        }
        //Guarda el bono
        $bonos[$idTrabajador] = $monto;
    }

    return ['bonos' => $bonos, 'errores' => $errores];
}
//Valida sesión y obtiene datos del usuario
if (empty($_SESSION['usuario'])) {
    header('Location: ../vista/vistas/Login.php');
    exit;
}
//determina el rol y la accion solicitada
$usuario = $_SESSION['usuario'];
$rol = $usuario['rol'] ?? '';
$idUsuario = (int)($usuario['id'] ?? 0);

$esAdmin = in_array($rol, ['Administrador Total', 'Administrador'], true);
$esEmpleado = in_array($rol, ['Trabajador', 'Supervisor'], true);

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

//Procesa la aprobación de la planilla, solo para admin y metodo post
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
    //obtiene el id de la planilla
    $idPlanilla = (int)($_POST['id_planilla'] ?? 0);
    
    //aprueba la planilla y gaurada el mensaje en sesión
    $resultado = ModeloPlanilla::aprobarPlanilla($idPlanilla, $idUsuario);

    if ($resultado['error']) {
        $_SESSION['error_planilla_admin'] = $resultado['mensaje'];
    } else {
        $_SESSION['exito_planilla_admin'] = $resultado['mensaje'];
    }

    //Redirige al historial con los mismos filotros aplicados
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
//generar planillas
if ($accion === 'generar') {
    //valida permisos 
    if (!$esAdmin) {
        http_response_code(403);
        header('Location: ../vista/vistas/HomeAdminTotal.php');
        exit;
    }
    //obtiene los datos del formulario
    $anio = (int)($_POST['anio'] ?? 0);
    $mes = (int)($_POST['mes'] ?? 0);
    $tarifa = (float)($_POST['tarifa_hora'] ?? 0);
    $bonosCrudos = $_POST['bonos_individuales'] ?? [];

//Procesa los bonps y si hay errores detiene el proceso
    $resultadoBonos = parsearBonosPorEmpleado($bonosCrudos);
    if (!empty($resultadoBonos['errores'])) {
        $_SESSION['error_planilla_admin'] = implode(' ', $resultadoBonos['errores']);
        header('Location: ../vista/vistas/PlanillasAdmin.php?anio=' . $anio . '&mes=' . $mes);
        exit;
    }
    //generar planillas
    $resultado = ModeloPlanilla::generarPlanillasMensuales($anio, $mes, $tarifa, $resultadoBonos['bonos'], $idUsuario);

    //guarda resultado en sesión y redirige al historial con los mismos filtros
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
 //Descargar planillas
if ($accion === 'descargar') {
    $idPlanilla = (int)($_GET['id'] ?? 0);

    if ($idPlanilla <= 0) {
        http_response_code(400);
        echo 'Planilla no válida.';
        exit;
    }
    //obtener planilla y valida acceso
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

    if ((int)($planilla['aprobada'] ?? 0) !== 1) {
        http_response_code(403);
        echo 'La nómina debe estar aprobada para descargarla.';
        exit;
    }
    //Genera el archivo exel
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
    //Detalles de la planilla
    foreach ($planilla['detalles'] as $detalle) {
        $fecha = date('d/m/Y', strtotime($detalle['fecha_marcacion']));
        $entrada = !empty($detalle['hora_entrada']) ? date('H:i:s', strtotime($detalle['hora_entrada'])) : '--:--:--';
        $salida = !empty($detalle['hora_salida']) ? date('H:i:s', strtotime($detalle['hora_salida'])) : '--:--:--';
        $horas = number_format((float)$detalle['horas_laboradas'], 2, '.', '');

        echo $fecha . "\t" . $entrada . "\t" . $salida . "\t" . $horas . "\n";
    }

    exit;
}
//Redige según el rol
if ($esAdmin) {
    header('Location: ../vista/vistas/PlanillasAdmin.php');
} elseif ($esEmpleado) {
    header('Location: ../vista/vistas/MisPlanillas.php');
} else {
    header('Location: ../vista/vistas/HomeAdminTotal.php');
}
exit;
