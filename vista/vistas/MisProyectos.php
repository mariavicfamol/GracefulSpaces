<?php
session_start();
require_once __DIR__ . '/../../modelo/ModeloProyecto.php';

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

$idUsuario = (int)($usuario['id'] ?? 0);
$proyectos = $idUsuario > 0 ? ModeloProyecto::obtenerProyectosPorColaborador($idUsuario) : [];

$error = $_SESSION['error_mis_proyectos'] ?? '';
$exito = $_SESSION['exito_mis_proyectos'] ?? '';
unset($_SESSION['error_mis_proyectos'], $_SESSION['exito_mis_proyectos']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Proyectos</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/style.MisProyectos.css">
</head>
<body>

<div class="navegacion-superior">
    <a href="HomeAdminTotal.php" class="btn-volver">← Volver al Menú</a>
</div>

<main class="contenedor-principal">
    <header>
        <h1>Mis Proyectos Asignados</h1>
        <p>Solo puedes actualizar si tu trabajo en el proyecto está terminado o pendiente.</p>
    </header>

    <?php if ($error): ?>
        <div class="mensaje mensaje-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($exito): ?>
        <div class="mensaje mensaje-exito"><?= htmlspecialchars($exito) ?></div>
    <?php endif; ?>

    <section class="tarjeta-proyectos">
        <?php if (empty($proyectos)): ?>
            <div class="sin-proyectos">No tienes proyectos asignados en este momento.</div>
        <?php else: ?>
            <?php foreach ($proyectos as $proyecto): ?>
                <article class="proyecto-item">
                    <div class="cabecera">
                        <h2><?= htmlspecialchars($proyecto['nombre']) ?></h2>
                        <span class="badge"><?= htmlspecialchars($proyecto['estado_general']) ?></span>
                    </div>

                    <div class="detalle"><strong>Detalles:</strong> <?= nl2br(htmlspecialchars($proyecto['detalles'] ?: 'Sin detalles')) ?></div>
                    <div class="detalle"><strong>Especificaciones:</strong> <?= nl2br(htmlspecialchars($proyecto['especificaciones'] ?: 'Sin especificaciones')) ?></div>
                    <div class="detalle"><strong>Horarios:</strong> <?= nl2br(htmlspecialchars($proyecto['horarios'] ?: 'No definido')) ?></div>
                    <div class="detalle"><strong>Materiales:</strong> <?= nl2br(htmlspecialchars($proyecto['materiales'] ?: 'No definido')) ?></div>

                    <form method="POST" action="../../controlador/controlador.Proyectos.php" class="form-estado">
                        <input type="hidden" name="accion" value="actualizarEstado">
                        <input type="hidden" name="id_proyecto" value="<?= (int)$proyecto['id'] ?>">

                        <?php if ((int)$proyecto['terminado'] === 1): ?>
                            <input type="hidden" name="terminado" value="0">
                            <button type="submit" class="btn-secundario">Marcar como Pendiente</button>
                            <span class="estado estado-ok">Tu estado: Terminado</span>
                        <?php else: ?>
                            <input type="hidden" name="terminado" value="1">
                            <button type="submit" class="btn-principal">Marcar como Terminado</button>
                            <span class="estado estado-pendiente">Tu estado: Pendiente</span>
                        <?php endif; ?>
                    </form>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>

</body>
</html>
