<?php
session_start();
require_once __DIR__ . '/../../modelo/ModeloPlanilla.php';

if (empty($_SESSION['usuario'])) {
    header('Location: Login.php');
    exit;
}

$usuario = $_SESSION['usuario'];
$rol = $usuario['rol'] ?? '';

if (!in_array($rol, ['Administrador Total', 'Administrador'], true)) {
    header('Location: HomeAdminTotal.php');
    exit;
}

$anio = (int)($_GET['anio'] ?? date('Y'));
$mes = (int)($_GET['mes'] ?? date('n'));
$idTrabajador = (int)($_GET['trabajador'] ?? 0);

$trabajadores = ModeloPlanilla::obtenerTrabajadoresActivos();
$planillas = ModeloPlanilla::obtenerPlanillasAdmin($anio > 0 ? $anio : null, $mes > 0 ? $mes : null, $idTrabajador > 0 ? $idTrabajador : null, true);
$cssVersion = @filemtime(__DIR__ . '/../styles/custom.HistorialPagosAdmin.css') ?: time();

$totalMonto = 0.0;
foreach ($planillas as $planilla) {
    $totalMonto += (float)$planilla['monto_total'];
}

$error = $_SESSION['error_historial_pagos'] ?? '';
$exito = $_SESSION['exito_historial_pagos'] ?? '';
unset($_SESSION['error_historial_pagos'], $_SESSION['exito_historial_pagos']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Pagos | Administración</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/style.PlanillasAdmin.css?v=<?= (int)(@filemtime(__DIR__ . '/../styles/style.PlanillasAdmin.css') ?: time()) ?>">
    <link rel="stylesheet" href="../styles/custom.HistorialPagosAdmin.css?v=<?= (int)$cssVersion ?>">
</head>
<body>

<div class="navegacion-superior">
    <a href="HomeAdminTotal.php" class="btn-volver">← Volver al Panel</a>
</div>

<main class="contenedor-principal">
    <header>
        <h1>Historial de Pagos</h1>
        <p>Consulta el historial de pagos de planillas generadas para todos los empleados.</p>
    </header>

    <?php if ($error): ?>
        <div class="mensaje mensaje-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($exito): ?>
        <div class="mensaje mensaje-exito"><?= htmlspecialchars($exito) ?></div>
    <?php endif; ?>

    <section class="tarjeta tarjeta-filtros">
        <h2>Filtrar Historial de Pagos</h2>
        <form method="GET" class="grid-filtros">
            <div class="grupo-campo">
                <label for="f_anio">Año</label>
                <input type="number" id="f_anio" name="anio" min="2000" max="2100" value="<?= $anio ?>">
            </div>

            <div class="grupo-campo">
                <label for="f_mes">Mes</label>
                <input type="number" id="f_mes" name="mes" min="1" max="12" value="<?= $mes ?>">
            </div>

            <div class="grupo-campo">
                <label for="trabajador">Empleado</label>
                <select id="trabajador" name="trabajador">
                    <option value="0">Todos</option>
                    <?php foreach ($trabajadores as $trabajador): ?>
                        <option value="<?= (int)$trabajador['id'] ?>" <?= $idTrabajador === (int)$trabajador['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($trabajador['nombre'] . ' ' . $trabajador['apellido1'] . ' ' . $trabajador['apellido2']) ?>
                            (<?= htmlspecialchars($trabajador['id_empresa']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="acciones-filtro">
                <button type="submit" class="btn-principal">Aplicar Filtros</button>
                <a href="HistorialPagosAdmin.php" class="btn-secundario">Limpiar</a>
            </div>
        </form>
    </section>

    <section class="tarjeta tarjeta-tabla">
        <h2>Pagos Registrados</h2>
        <div class="acciones-tabla">
            <a href="../../controlador/controlador.HistorialPagos.php?accion=descargar_todo&anio=<?= $anio ?>&mes=<?= $mes ?>&trabajador=<?= $idTrabajador ?>" class="btn-descargar">
                Descargar Todos los Pagos
            </a>
        </div>
        <div class="tabla-contenedor">
            <table>
                <thead>
                    <tr>
                        <th>Empleado</th>
                        <th>ID Empresa</th>
                        <th>Periodo</th>
                        <th>Horas Totales</th>
                        <th>Tarifa/Hora</th>
                        <th>Bono</th>
                        <th>Monto Total</th>
                        <th>Generada</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($planillas)): ?>
                        <tr>
                            <td colspan="9">No hay pagos para los filtros seleccionados.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($planillas as $planilla): ?>
                            <tr>
                                <td><?= htmlspecialchars($planilla['trabajador']) ?></td>
                                <td><?= htmlspecialchars($planilla['id_empresa']) ?></td>
                                <td><?= htmlspecialchars($planilla['anio'] . '-' . str_pad((string)$planilla['mes'], 2, '0', STR_PAD_LEFT)) ?></td>
                                <td><?= number_format((float)$planilla['horas_totales'], 2) ?></td>
                                <td>$<?= number_format((float)$planilla['tarifa_hora'], 2) ?></td>
                                <td>$<?= number_format((float)($planilla['bono_manual'] ?? 0), 2) ?></td>
                                <td>$<?= number_format((float)$planilla['monto_total'], 2) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($planilla['fecha_generacion'])) ?></td>
                                <td>
                                    <a class="btn-descargar" href="../../controlador/controlador.HistorialPagos.php?accion=descargar&id=<?= (int)$planilla['id'] ?>">
                                        Exel
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr style="font-weight: bold; background-color: #f0f0f0;">
                            <td colspan="6">Total</td>
                            <td>$<?= number_format($totalMonto, 2) ?></td>
                            <td colspan="2"></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

</body>
</html>