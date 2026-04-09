<?php
session_start();
require_once __DIR__ . '/../../modelo/ModeloNotificacion.php';

if (empty($_SESSION['usuario'])) {
    header('Location: Login.php');
    exit;
}

$usuario = $_SESSION['usuario'];
$rol = $usuario['rol'] ?? '';
$idUsuario = (int)($usuario['id'] ?? 0);
$notificaciones = ModeloNotificacion::obtenerNotificaciones($idUsuario);
$noLeidas = ModeloNotificacion::contarNotificacionesNoLeidas($idUsuario);

function etiquetaTipo(string $tipo): string {
    return match ($tipo) {
        'proyecto_asignado' => 'Proyecto asignado',
        'proyecto_terminado' => 'Proyecto finalizado',
        'vacaciones_solicitud' => 'Solicitud de vacaciones',
        'vacaciones_aprobada' => 'Vacaciones aprobadas',
        'vacaciones_rechazada' => 'Vacaciones rechazadas',
        'pago_aplicado' => 'Pago aplicado',
        'producto_faltante' => 'Producto faltante',
        default => 'General',
    };
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificaciones</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/style.Notificaciones.css">
</head>
<body>
    <header class="cabecera-notificaciones">
        <div class="bloque-titulo-notificaciones">
            <a href="HomeAdminTotal.php" class="btn-regresar">← Volver al Panel</a>
            <h1>Notificaciones</h1>
            <p class="subtitulo">Todas las alertas de tu cuenta se muestran aquí.</p>
        </div>

        <nav class="acciones-header">
            <form id="formMarcarTodas" class="form-marcar-todas" method="POST" action="../../controlador/controlador.Notificaciones.php">
                <input type="hidden" name="accion" value="marcarTodasLeidas">
                <button type="submit" class="btn-marcar-todas">Marcar todas como leídas</button>
            </form>
        </nav>
    </header>

    <main class="contenedor-notificaciones">
        <section class="panel-resumen">
            <div class="resumen-item">
                <span>Total</span>
                <strong><?= count($notificaciones) ?></strong>
            </div>
            <div class="resumen-item">
                <span>Sin leer</span>
                <strong><?= $noLeidas ?></strong>
            </div>
        </section>

        <section class="tabla-notificaciones">
            <div class="encabezado-tabla">
                <h2>Historial de notificaciones</h2>
            </div>
            <div class="tabla-contenedor">
                <table>
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Mensaje</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($notificaciones)): ?>
                            <tr>
                                <td colspan="5" class="sin-resultados">No hay notificaciones registradas.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($notificaciones as $notificacion): ?>
                                <tr class="fila-notificacion <?= $notificacion['leido'] ? '' : 'fila-no-leida' ?>">
                                    <td><?= date('d/m/Y H:i', strtotime($notificacion['fecha'])) ?></td>
                                    <td><?= htmlspecialchars(etiquetaTipo($notificacion['tipo'])) ?></td>
                                    <td><?= htmlspecialchars($notificacion['mensaje']) ?></td>
                                    <td><?= $notificacion['leido'] ? 'Leída' : 'Pendiente' ?></td>
                                    <td>
                                        <?php if (!$notificacion['leido']): ?>
                                            <form class="form-marcar-leida" method="POST" action="../../controlador/controlador.Notificaciones.php">
                                                <input type="hidden" name="accion" value="marcarLeida">
                                                <input type="hidden" name="id_notificacion" value="<?= (int)$notificacion['id'] ?>">
                                                <button type="submit" class="btn-marcar-leida">Marcar leída</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="estado-leido">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script src="../javascript/controlador.Notificaciones.js"></script>
</body>
</html>
