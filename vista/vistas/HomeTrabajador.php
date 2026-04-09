<?php
session_start();
require_once __DIR__ . '/../../modelo/ModeloMarcacion.php';

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
$marcacionHoy = $idTrabajador > 0 ? ModeloMarcacion::obtenerMarcacionHoy($idTrabajador) : null;
$historial = $idTrabajador > 0 ? ModeloMarcacion::obtenerUltimasMarcaciones($idTrabajador, 7) : [];

$error = $_SESSION['error_marcacion'] ?? '';
$exito = $_SESSION['exito_marcacion'] ?? '';
unset($_SESSION['error_marcacion'], $_SESSION['exito_marcacion']);

$puedeEntrada = !$marcacionHoy || empty($marcacionHoy['hora_entrada']);
$puedeSalida = $marcacionHoy && !empty($marcacionHoy['hora_entrada']) && empty($marcacionHoy['hora_salida']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Marcacion | Entradas y Salidas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/style.HomeTrabajador.css">
</head>
<body>

<div class="navegacion-superior">
    <a href="HomeAdminTotal.php" class="btn-volver-menu">← Menu Principal</a>
    <a href="../../controlador/controlador.CerrarSesion.php" class="btn-salir">Cerrar Sesion</a>
</div>

<main class="contenedor-principal">
    <section class="tarjeta-bienvenida">
        <h1>Hola, <?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido1']) ?></h1>
        <p>Desde este panel puedes registrar tus horas de entrada y salida del dia laboral.</p>
        <div class="fecha">Fecha actual: <?= date('d/m/Y') ?></div>
    </section>

    <?php if ($error): ?>
        <div class="mensaje mensaje-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($exito): ?>
        <div class="mensaje mensaje-exito"><?= htmlspecialchars($exito) ?></div>
    <?php endif; ?>

    <section class="tarjeta-marcacion">
        <h2>Marcacion de Hoy</h2>

        <div class="estado-grid">
            <div class="estado-item">
                <span>Entrada</span>
                <strong><?= !empty($marcacionHoy['hora_entrada']) ? date('H:i:s', strtotime($marcacionHoy['hora_entrada'])) : '--:--:--' ?></strong>
            </div>
            <div class="estado-item">
                <span>Salida</span>
                <strong><?= !empty($marcacionHoy['hora_salida']) ? date('H:i:s', strtotime($marcacionHoy['hora_salida'])) : '--:--:--' ?></strong>
            </div>
            <div class="estado-item">
                <span>Estado</span>
                <strong><?= htmlspecialchars($marcacionHoy['estado'] ?? 'Sin registro') ?></strong>
            </div>
        </div>

        <div class="acciones">
            <form method="POST" action="../../controlador/controlador.Marcacion.php">
                <input type="hidden" name="accion" value="entrada">
                <button type="submit" class="btn-entrada" <?= $puedeEntrada ? '' : 'disabled' ?>>Registrar Entrada</button>
            </form>

            <form method="POST" action="../../controlador/controlador.Marcacion.php" id="formSalida">
                <input type="hidden" name="accion" value="salida">
                <button type="submit" class="btn-salida" <?= $puedeSalida ? '' : 'disabled' ?>>Registrar Salida</button>
            </form>
        </div>
    </section>

    <section class="tarjeta-historial">
        <h2>Ultimos Registros</h2>
        <div class="tabla-contenedor">
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Entrada</th>
                        <th>Salida</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($historial)): ?>
                        <tr>
                            <td colspan="4">Aun no hay marcaciones registradas.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($historial as $fila): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($fila['fecha_marcacion'])) ?></td>
                                <td><?= !empty($fila['hora_entrada']) ? date('H:i:s', strtotime($fila['hora_entrada'])) : '--:--:--' ?></td>
                                <td><?= !empty($fila['hora_salida']) ? date('H:i:s', strtotime($fila['hora_salida'])) : '--:--:--' ?></td>
                                <td><?= htmlspecialchars($fila['estado']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<script src="../javascript/controlador.HomeTrabajador.js"></script>
</body>
</html>
