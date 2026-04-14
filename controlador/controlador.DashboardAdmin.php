<?php

session_start();
//importa la configuracion de la BD
require_once __DIR__ . '/../configuracion/GracefulSpacesDB.configuracion.php';

//Valida la sesión y el rol para acceder al dashboard
if (empty($_SESSION['usuario'])) {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => true, 'mensaje' => 'Sesion no valida.']);
    exit;
}

$rol = $_SESSION['usuario']['rol'] ?? '';
if (!in_array($rol, ['Administrador Total', 'Administrador'], true)) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => true, 'mensaje' => 'No autorizado.']);
    exit;
}

//Valida la acción solicitada (resumen dashboard)
$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';
if ($accion !== 'resumen') {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => true, 'mensaje' => 'Accion no valida.']);
    exit;
}

//Conexion bd
$conexion = obtenerConexion();
header('Content-Type: application/json; charset=utf-8');

//Fechas y variables para consultas
$hoy = (new DateTimeImmutable('now', new DateTimeZone(APP_TIMEZONE)))->format('Y-m-d');
$anioActual = (int)(new DateTimeImmutable('now', new DateTimeZone(APP_TIMEZONE)))->format('Y');
$mesActual = (int)(new DateTimeImmutable('now', new DateTimeZone(APP_TIMEZONE)))->format('m');

$kpis = [
    'usuarios_activos' => 0,
    'proyectos_en_progreso' => 0,
    'marcaciones_hoy' => 0,
    'marcaciones_abiertas_hoy' => 0,
    'marcaciones_cerradas_hoy' => 0,
    'planillas_mes' => 0,
    'monto_planillas_mes' => 0,
];

$consultas = [
    'usuarios_activos' => "SELECT COUNT(*) AS total FROM trabajadores WHERE estado = 'Activo'",
    'proyectos_en_progreso' => "SELECT COUNT(*) AS total FROM proyectos WHERE estado_general = 'En progreso'",
    'marcaciones_hoy' => "SELECT COUNT(*) AS total FROM marcaciones WHERE fecha_marcacion = ?",
    'marcaciones_abiertas_hoy' => "SELECT COUNT(*) AS total FROM marcaciones WHERE fecha_marcacion = ? AND estado = 'Abierta'",
    'marcaciones_cerradas_hoy' => "SELECT COUNT(*) AS total FROM marcaciones WHERE fecha_marcacion = ? AND estado = 'Cerrada'",
    'planillas_mes' => 'SELECT COUNT(*) AS total FROM planillas_mensuales WHERE anio = ? AND mes = ?',
    'monto_planillas_mes' => 'SELECT COALESCE(SUM(monto_total), 0) AS total FROM planillas_mensuales WHERE anio = ? AND mes = ?',
];

try {
    foreach ($consultas as $clave => $sql) {
        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            continue;
        }

        if (strpos($sql, 'fecha_marcacion = ?') !== false) {
            $stmt->bind_param('s', $hoy);
        } elseif (strpos($sql, 'anio = ? AND mes = ?') !== false) {
            $stmt->bind_param('ii', $anioActual, $mesActual);
        }

        $stmt->execute();
        $resultado = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($clave === 'monto_planillas_mes') {
            $kpis[$clave] = (float)($resultado['total'] ?? 0);
        } else {
            $kpis[$clave] = (int)($resultado['total'] ?? 0);
        }
    }
    //ultimas marcaciones
    $ultimasMarcaciones = [];
    $sqlMarcaciones = "SELECT CONCAT(t.nombre, ' ', t.apellido1, ' ', COALESCE(t.apellido2, '')) AS trabajador,
                              m.fecha_marcacion,
                              m.hora_entrada,
                              m.hora_salida,
                              m.estado
                       FROM marcaciones m
                       INNER JOIN trabajadores t ON t.id = m.id_trabajador
                       ORDER BY m.fecha_marcacion DESC, m.creado_en DESC
                       LIMIT 8";

    $resultadoMarcaciones = $conexion->query($sqlMarcaciones);
    if ($resultadoMarcaciones) {
        while ($fila = $resultadoMarcaciones->fetch_assoc()) {
            $ultimasMarcaciones[] = $fila;
        }
    }
    
    //Proyectos prox a vencer
    $proyectosProximos = [];
    $fechaHoy = new DateTimeImmutable('now', new DateTimeZone(APP_TIMEZONE));
    $fecha7Dias = $fechaHoy->modify('+7 days')->format('Y-m-d');
    $fechaHoyStr = $fechaHoy->format('Y-m-d');

    $sqlProximos = "SELECT p.id,
                           p.nombre,
                           p.fecha_proyecto,
                           p.hora_proyecto,
                           p.estado_general,
                           COUNT(pc.id) AS total_colaboradores,
                           SUM(CASE WHEN pc.terminado = 1 THEN 1 ELSE 0 END) AS colaboradores_terminados
                    FROM proyectos p
                    LEFT JOIN proyecto_colaboradores pc ON pc.id_proyecto = p.id
                    WHERE p.estado_general = 'En progreso'
                      AND p.fecha_proyecto IS NOT NULL
                      AND p.fecha_proyecto >= ?
                      AND p.fecha_proyecto <= ?
                    GROUP BY p.id
                    ORDER BY p.fecha_proyecto ASC
                    LIMIT 5";

    $stmtProximos = $conexion->prepare($sqlProximos);
    if ($stmtProximos) {
        $stmtProximos->bind_param('ss', $fechaHoyStr, $fecha7Dias);
        $stmtProximos->execute();
        $resultadoProximos = $stmtProximos->get_result();

        while ($fila = $resultadoProximos->fetch_assoc()) {
            $diasParaVencer = (new DateTime($fila['fecha_proyecto']))->diff($fechaHoy)->days;
            $fila['dias_para_vencer'] = $diasParaVencer;
            $fila['alerta'] = $diasParaVencer <= 1 ? 'critica' : ($diasParaVencer <= 3 ? 'advertencia' : 'normal');
            $proyectosProximos[] = $fila;
        }

        $stmtProximos->close();
    }
    //Respuesta final con KPIs, ultimas marcaciones y proyectos prox a vencer
    $respuesta = [
        'error' => false,
        'fecha' => $hoy,
        'kpis' => $kpis,
        'ultimas_marcaciones' => $ultimasMarcaciones,
        'proyectos_proximos' => $proyectosProximos,
    ];

    echo json_encode($respuesta);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'mensaje' => 'No se pudo generar el resumen del dashboard.',
    ]);
} finally {
    $conexion->close();
}
