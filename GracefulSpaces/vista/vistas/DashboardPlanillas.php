<?php
session_start();
require_once __DIR__ . '/../../modelo/ModeloPlanilla.php';

if (empty($_SESSION['usuario'])) {
    header('Location: Login.php');
    exit;
}

$usuario = $_SESSION['usuario'];
$planillas = ModeloPlanilla::obtenerPlanillas();

// Calcular estadísticas
$totalPlanillas = count($planillas);
$montoTotal = 0;
$pendientes = 0;

foreach ($planillas as $planilla) {
    $montoTotal += $planilla['monto_total'];
    if ($planilla['estado'] === 'Pendiente') {
        $pendientes++;
    }
}

$exito = $_SESSION['exito_crear_planilla'] ?? null;
if ($exito) unset($_SESSION['exito_crear_planilla']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Planillas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/style.DashboardPlanillas.css">
</head>
<body>

    <div class="navegacion-superior">
        <a href="HomeAdminTotal.php" class="btn-volver">← Volver al Panel</a>
    </div>

    <div class="contenedor-principal">
        <header>
            <h1>Dashboard de Planillas</h1>
            <p>Visualiza y gestiona todas las planillas de pago</p>
        </header>

        <?php if ($exito): ?>
            <div class="alerta alerta-exito">
                <span><?= htmlspecialchars($exito) ?></span>
            </div>
        <?php endif; ?>

        <div class="contenedor-estadisticas">
            <div class="tarjeta-estadistica">
                <div class="numero-estadistica"><?= $totalPlanillas ?></div>
                <h3>Total de Planillas</h3>
            </div>
            <div class="tarjeta-estadistica">
                <div class="numero-estadistica"><?= $pendientes ?></div>
                <h3>Pendientes</h3>
            </div>
            <div class="tarjeta-estadistica">
                <div class="numero-estadistica">$<?= number_format($montoTotal, 2) ?></div>
                <h3>Monto Total</h3>
            </div>
        </div>

        <div class="titulo-seccion">Todas las Planillas</div>

        <?php if (!empty($planillas)): ?>
            <div class="tabla-responsiva">
                <table>
                    <thead>
                        <tr>
                            <th>ID Planilla</th>
                            <th>Trabajador</th>
                            <th>Período</th>
                            <th>Horas</th>
                            <th>Tarifa (USD)</th>
                            <th>Total (USD)</th>
                            <th>Estado</th>
                            <th>Opción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($planillas as $planilla): ?>
                            <tr>
                                <td><?= htmlspecialchars($planilla['id_planilla']) ?></td>
                                <td><?= htmlspecialchars($planilla['trabajador']) ?></td>
                                <td>
                                    <?= date('d/m/Y', strtotime($planilla['periodo_inicio'])) ?>
                                    -
                                    <?= date('d/m/Y', strtotime($planilla['periodo_fin'])) ?>
                                </td>
                                <td><?= number_format($planilla['cantidad_horas'], 2) ?></td>
                                <td>$<?= number_format($planilla['tarifa_hora'], 2) ?></td>
                                <td>$<?= number_format($planilla['monto_total'], 2) ?></td>
                                <td>
                                    <span class="badge-estado badge-<?= strtolower(str_replace(' ', '-', $planilla['estado'])) ?>">
                                        <?= htmlspecialchars($planilla['estado']) ?>
                                    </span>
                                </td>
                                <td class="acciones-tabla">
                                    <a href="EditarPlanillas.php?id=<?= $planilla['id'] ?>" class="btn-accion">✎</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="mensaje-vacio">
                <h3>No hay planillas registradas</h3>
                <p>Crea tu primera planilla para comenzar a gestionar los pagos.</p>
            </div>
        <?php endif; ?>
    </div>

    <script src="../javascript/controlador.DashboardPlanillas.js"></script>
</body>
</html>
