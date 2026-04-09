<?php
session_start();
require_once __DIR__ . '/../../modelo/ModeloPlanilla.php';

if (empty($_SESSION['usuario'])) {
    header('Location: Login.php');
    exit;
}

$usuario = $_SESSION['usuario'];
$rol = $usuario['rol'] ?? '';

if (!in_array($rol, ['Trabajador', 'Supervisor'], true)) {
    header('Location: HomeAdminTotal.php');
    exit;
}

$idTrabajador = (int)($usuario['id'] ?? 0);
$planillas = $idTrabajador > 0 ? ModeloPlanilla::obtenerPlanillasPorTrabajador($idTrabajador) : [];
$cssVersion = @filemtime(__DIR__ . '/../styles/style.MisPlanillas.css') ?: time();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Planillas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/style.MisPlanillas.css?v=<?= (int)$cssVersion ?>">
</head>
<body>

<div class="navegacion-superior">
    <a href="HomeAdminTotal.php" class="btn-volver">← Volver al Menú</a>
</div>

<main class="contenedor-principal">
    <header>
        <h1>Mis Planillas</h1>
        <p>Aquí puedes consultar y descargar tus planillas mensuales.</p>
    </header>

    <section class="tarjeta-tabla">
        <div class="tabla-contenedor">
            <table>
                <thead>
                    <tr>
                        <th>Periodo</th>
                        <th>Horas Totales</th>
                        <th>Tarifa/Hora</th>
                        <th>Bono</th>
                        <th>Monto Total</th>
                        <th>Fecha Generación</th>
                        <th>Descarga</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($planillas)): ?>
                        <tr>
                            <td colspan="7">No tienes planillas generadas por el momento.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($planillas as $planilla): ?>
                            <tr>
                                <td><?= htmlspecialchars($planilla['anio'] . '-' . str_pad((string)$planilla['mes'], 2, '0', STR_PAD_LEFT)) ?></td>
                                <td><?= number_format((float)$planilla['horas_totales'], 2) ?></td>
                                <td>$<?= number_format((float)$planilla['tarifa_hora'], 2) ?></td>
                                <td>$<?= number_format((float)($planilla['bono_manual'] ?? 0), 2) ?></td>
                                <td>$<?= number_format((float)$planilla['monto_total'], 2) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($planilla['fecha_generacion'])) ?></td>
                                <td>
                                    <a class="btn-descargar" href="../../controlador/controlador.Planillas.php?accion=descargar&id=<?= (int)$planilla['id'] ?>">
                                        Excel
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

</body>
</html>
