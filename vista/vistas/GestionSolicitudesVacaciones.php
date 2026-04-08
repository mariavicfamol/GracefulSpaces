<?php
session_start();
require_once __DIR__ . '/../../modelo/ModeloSolicitudVacaciones.php';

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

$estado = trim($_GET['estado'] ?? '');
$solicitudes = empty($estado) 
    ? ModeloSolicitudVacaciones::obtenerTodasSolicitudes()
    : ModeloSolicitudVacaciones::obtenerTodasSolicitudes($estado);

$errorVacaciones = $_SESSION['error_vacaciones'] ?? '';
$exitoVacaciones = $_SESSION['exito_vacaciones'] ?? '';
unset($_SESSION['error_vacaciones'], $_SESSION['exito_vacaciones']);

$contadorPendientes = count(array_filter($solicitudes, fn($s) => $s['estado'] === 'Pendiente'));
$contadorAprobadas = count(array_filter($solicitudes, fn($s) => $s['estado'] === 'Aprobada'));
$contadorRechazadas = count(array_filter($solicitudes, fn($s) => $s['estado'] === 'Rechazada'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Solicitudes de Vacaciones | Graceful Spaces</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/style.GestionSolicitudesVacaciones.css">
</head>
<body>

<div class="navegacion-superior">
    <a href="HomeAdminTotal.php" class="btn-volver-menu">← Menu Principal</a>
</div>

<main class="contenedor-principal">
    <section class="tarjeta-encabezado">
        <h1>Gestión de Solicitudes de Vacaciones</h1>
        <p>Revise, apruebe o rechace las solicitudes de vacaciones de los trabajadores.</p>
    </section>

    <?php if ($errorVacaciones): ?>
        <div class="mensaje mensaje-error"><?= htmlspecialchars($errorVacaciones) ?></div>
    <?php endif; ?>
    <?php if ($exitoVacaciones): ?>
        <div class="mensaje mensaje-exito"><?= htmlspecialchars($exitoVacaciones) ?></div>
    <?php endif; ?>

    <div class="estadisticas-seccion">
        <div class="estadistica-card">
            <div class="numero"><?= $contadorPendientes ?></div>
            <div class="etiqueta">Pendientes</div>
        </div>
        <div class="estadistica-card">
            <div class="numero"><?= $contadorAprobadas ?></div>
            <div class="etiqueta">Aprobadas</div>
        </div>
        <div class="estadistica-card">
            <div class="numero"><?= $contadorRechazadas ?></div>
            <div class="etiqueta">Rechazadas</div>
        </div>
    </div>

    <div class="filtros-seccion">
        <h3>Filtrar por Estado</h3>
        <div class="botones-filtro">
            <a href="?estado=" class="btn-filtro <?= empty($estado) ? 'activo' : '' ?>">Todas</a>
            <a href="?estado=Pendiente" class="btn-filtro <?= $estado === 'Pendiente' ? 'activo' : '' ?>">Pendientes</a>
            <a href="?estado=Aprobada" class="btn-filtro <?= $estado === 'Aprobada' ? 'activo' : '' ?>">Aprobadas</a>
            <a href="?estado=Rechazada" class="btn-filtro <?= $estado === 'Rechazada' ? 'activo' : '' ?>">Rechazadas</a>
        </div>
    </div>

    <div class="tabla-contenedor">
        <?php if (empty($solicitudes)): ?>
            <div class="sin-datos">
                <p>No hay solicitudes de vacaciones para mostrar.</p>
            </div>
        <?php else: ?>
            <table class="tabla-solicitudes">
                <thead>
                    <tr>
                        <th>Trabajador</th>
                        <th>Cargo</th>
                        <th>Fecha Inicio</th>
                        <th>Fecha Fin</th>
                        <th>Días</th>
                        <th>Motivo</th>
                        <th>Estado</th>
                        <th>Fecha Solicitud</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($solicitudes as $solicitud): ?>
                        <tr class="fila-estado-<?= strtolower($solicitud['estado']) ?>">
                            <td>
                                <strong><?= htmlspecialchars($solicitud['nombre_trabajador']) ?></strong><br>
                                <small><?= htmlspecialchars($solicitud['correo_personal'] ?? '--') ?></small>
                            </td>
                            <td><?= htmlspecialchars($solicitud['cargo']) ?></td>
                            <td><?= date('d/m/Y', strtotime($solicitud['fecha_inicio'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($solicitud['fecha_fin'])) ?></td>
                            <td><strong><?= htmlspecialchars($solicitud['dias_solicitados']) ?></strong></td>
                            <td class="celda-motivo">
                                <?php if (!empty($solicitud['motivo'])): ?>
                                    <span class="motivo" title="<?= htmlspecialchars($solicitud['motivo']) ?>">
                                        <?= htmlspecialchars(substr($solicitud['motivo'], 0, 25)) ?>...
                                    </span>
                                <?php else: ?>
                                    <span class="sin-motivo">Sin especificar</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-<?= strtolower(str_replace(' ', '-', $solicitud['estado'])) ?>">
                                    <?= htmlspecialchars($solicitud['estado']) ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($solicitud['creado_en'])) ?></td>
                            <td class="celda-acciones">
                                <?php if ($solicitud['estado'] === 'Pendiente'): ?>
                                    <button type="button" class="btn-accion btn-aprobar" onclick="abrirModalAprobar(<?= $solicitud['id'] ?>, '<?= addslashes($solicitud['nombre_trabajador']) ?>')">Aprobar</button>
                                    <button type="button" class="btn-accion btn-rechazar" onclick="abrirModalRechazar(<?= $solicitud['id'] ?>, '<?= addslashes($solicitud['nombre_trabajador']) ?>')">Rechazar</button>
                                <?php else: ?>
                                    <button type="button" class="btn-accion btn-ver" onclick="abrirModalDetalles(<?= $solicitud['id'] ?>)">Ver Detalles</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</main>

<!-- Modal para Aprobar -->
<div id="modalAprobar" class="modal" style="display: none;">
    <div class="modal-contenido">
        <span class="modal-cerrar" onclick="cerrarModal('modalAprobar')">&times;</span>
        <h2>Aprobar Solicitud de Vacaciones</h2>
        <form method="POST" action="../../controlador/controlador.SolicitudVacaciones.php">
            <input type="hidden" name="accion" value="aprobar">
            <input type="hidden" name="idSolicitud" id="idSolicitudAprobar">
            
            <p id="textoTrabajadorAprobar"></p>
            
            <div class="grupo-campo">
                <label>Comentario (Opcional)</label>
                <textarea name="comentario" rows="4" placeholder="Agregue un comentario si lo desea..."></textarea>
            </div>

            <div class="modal-acciones">
                <button type="submit" class="btn-modal btn-confirmar">Aprobar Solicitud</button>
                <button type="button" class="btn-modal btn-cancelar" onclick="cerrarModal('modalAprobar')">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para Rechazar -->
<div id="modalRechazar" class="modal" style="display: none;">
    <div class="modal-contenido">
        <span class="modal-cerrar" onclick="cerrarModal('modalRechazar')">&times;</span>
        <h2>Rechazar Solicitud de Vacaciones</h2>
        <form method="POST" action="../../controlador/controlador.SolicitudVacaciones.php">
            <input type="hidden" name="accion" value="rechazar">
            <input type="hidden" name="idSolicitud" id="idSolicitudRechazar">
            
            <p id="textoTrabajadorRechazar"></p>
            
            <div class="grupo-campo">
                <label>Motivo del Rechazo *</label>
                <textarea name="motivo" rows="4" placeholder="Explique el motivo del rechazo..." required></textarea>
            </div>

            <div class="modal-acciones">
                <button type="submit" class="btn-modal btn-confirmar btn-rechazar-modal">Rechazar Solicitud</button>
                <button type="button" class="btn-modal btn-cancelar" onclick="cerrarModal('modalRechazar')">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script src="../javascript/controlador.GestionSolicitudesVacaciones.js"></script>
</body>
</html>
