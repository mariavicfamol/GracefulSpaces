<?php
session_start();
require_once __DIR__ . '/../../modelo/ModeloSolicitudVacaciones.php';

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

$idTrabajador = (int)$usuario['id'];
$estadoSolicitado = trim($_GET['estado'] ?? '');
$estadosPermitidos = ['Pendiente', 'Aprobada', 'Rechazada'];
$estadoFiltro = in_array($estadoSolicitado, $estadosPermitidos, true) ? $estadoSolicitado : null;

$solicitudes = ModeloSolicitudVacaciones::obtenerSolicitudesTrabajador($idTrabajador, $estadoFiltro);

$errorVacaciones = $_SESSION['error_vacaciones'] ?? '';
$exitoVacaciones = $_SESSION['exito_vacaciones'] ?? '';
unset($_SESSION['error_vacaciones'], $_SESSION['exito_vacaciones']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Historial de Solicitudes | Graceful Spaces</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/style.HistorialSolicitudesVacaciones.css">
</head>
<body>

<div class="navegacion-superior">
    <a href="HomeAdminTotal.php" class="btn-volver-menu">← Menu Principal</a>
    <a href="SolicitudVacaciones.php" class="btn-nueva-solicitud">+ Nueva Solicitud</a>
</div>

<main class="contenedor-principal">
    <section class="tarjeta-encabezado">
        <h1>Mi Historial de Solicitudes de Vacaciones</h1>
        <p>Consulte el estado de todas sus solicitudes de vacaciones.</p>
    </section>

    <?php if ($errorVacaciones): ?>
        <div class="mensaje mensaje-error"><?= htmlspecialchars($errorVacaciones) ?></div>
    <?php endif; ?>
    <?php if ($exitoVacaciones): ?>
        <div class="mensaje mensaje-exito"><?= htmlspecialchars($exitoVacaciones) ?></div>
    <?php endif; ?>

    <div class="filtros-seccion">
        <h3>Filtrar por Estado</h3>
        <div class="botones-filtro">
            <a href="?estado=" class="btn-filtro <?= empty($estadoFiltro) ? 'activo' : '' ?>">Todas</a>
            <a href="?estado=Pendiente" class="btn-filtro <?= $estadoFiltro === 'Pendiente' ? 'activo' : '' ?>">Pendientes</a>
            <a href="?estado=Aprobada" class="btn-filtro <?= $estadoFiltro === 'Aprobada' ? 'activo' : '' ?>">Aprobadas</a>
            <a href="?estado=Rechazada" class="btn-filtro <?= $estadoFiltro === 'Rechazada' ? 'activo' : '' ?>">Rechazadas</a>
        </div>
    </div>

    <div class="tabla-contenedor">
        <?php if (empty($solicitudes)): ?>
            <div class="sin-datos">
                <p>No tiene solicitudes de vacaciones registradas aún.</p>
                <a href="SolicitudVacaciones.php" class="btn-crear">Crear Nueva Solicitud</a>
            </div>
        <?php else: ?>
            <table class="tabla-solicitudes">
                <thead>
                    <tr>
                        <th>Fecha Inicio</th>
                        <th>Fecha Fin</th>
                        <th>Días</th>
                        <th>Estado</th>
                        <th>Fecha Solicitud</th>
                        <th>Procesado Por</th>
                        <th>Comentario</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($solicitudes as $solicitud): ?>
                        <tr class="fila-estado-<?= strtolower($solicitud['estado']) ?>">
                            <td><?= date('d/m/Y', strtotime($solicitud['fecha_inicio'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($solicitud['fecha_fin'])) ?></td>
                            <td><strong><?= htmlspecialchars($solicitud['dias_solicitados']) ?></strong></td>
                            <td>
                                <span class="badge badge-<?= strtolower(str_replace(' ', '-', $solicitud['estado'])) ?>">
                                    <?= htmlspecialchars($solicitud['estado']) ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($solicitud['creado_en'])) ?></td>
                            <td><?= htmlspecialchars($solicitud['procesado_por'] ?? '--') ?></td>
                            <td class="celda-comentario">
                                <?php if (!empty($solicitud['comentario_admin'])): ?>
                                    <span class="comentario" title="<?= htmlspecialchars($solicitud['comentario_admin']) ?>">
                                        <?= htmlspecialchars(substr($solicitud['comentario_admin'], 0, 30)) ?>...
                                    </span>
                                <?php else: ?>
                                    <span class="sin-comentario">--</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</main>

<script src="../javascript/controlador.HistorialSolicitudesVacaciones.js"></script>
</body>
</html>
