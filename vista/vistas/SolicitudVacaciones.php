<?php
session_start();
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

$errorVacaciones = $_SESSION['error_vacaciones'] ?? '';
$exitoVacaciones = $_SESSION['exito_vacaciones'] ?? '';
unset($_SESSION['error_vacaciones'], $_SESSION['exito_vacaciones']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar Vacaciones | Graceful Spaces</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/style.SolicitudVacaciones.css">
</head>
<body>

<div class="navegacion-superior">
    <a href="HomeAdminTotal.php" class="btn-volver-menu">← Menu Principal</a>
</div>

<main class="contenedor-principal">
    <section class="tarjeta-encabezado">
        <h1>Solicitar Vacaciones</h1>
        <p>Complete el formulario para solicitar sus dias de vacaciones. El administrador revisará y aprobará su solicitud.</p>
    </section>

    <?php if ($errorVacaciones): ?>
        <div class="mensaje mensaje-error"><?= htmlspecialchars($errorVacaciones) ?></div>
    <?php endif; ?>
    <?php if ($exitoVacaciones): ?>
        <div class="mensaje mensaje-exito"><?= htmlspecialchars($exitoVacaciones) ?></div>
    <?php endif; ?>

    <form id="formularioVacaciones" method="POST" action="../../controlador/controlador.SolicitudVacaciones.php" class="formulario-vacaciones">
        <input type="hidden" name="accion" value="solicitar">

        <div class="seccion-formulario">
            <h2>1. Información Personal</h2>
            <div class="grupo-campo">
                <label>Nombre Completo del Empleado</label>
                <input type="text" value="<?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido1'] . ' ' . ($usuario['apellido2'] ?? '')) ?>" readonly class="campo-lectura">
                <small>Este campo se completa automaticamente desde tu perfil.</small>
            </div>

            <div class="grupo-campo">
                <label>Cargo</label>
                <input type="text" value="<?= htmlspecialchars($usuario['cargo'] ?? 'No especificado') ?>" readonly class="campo-lectura">
            </div>
        </div>

        <div class="seccion-formulario">
            <h2>2. Periodo de Vacaciones</h2>
            
            <div class="grupo-campo">
                <label>Fecha de Inicio *</label>
                <input type="date" name="fechaInicio" id="fechaInicio" required>
            </div>

            <div class="grupo-campo">
                <label>Fecha de Fin *</label>
                <input type="date" name="fechaFin" id="fechaFin" required>
            </div>

            <div class="grupo-campo">
                <label>Número de Días Solicitados *</label>
                <input type="number" name="diasSolicitados" id="diasSolicitados" min="1" max="30" readonly class="campo-lectura" required>
                <small>Se calcula automaticamente según el rango de fechas.</small>
            </div>

            <div class="vista-calendario">
                <h3>Calendario</h3>
                <p class="texto-ayuda-calendario">La franja resaltada muestra el periodo que estás solicitando.</p>
                <div id="calendarioPreview" class="calendario-preview"></div>
            </div>
        </div>

        <div class="seccion-formulario">
            <h2>3. Detalles Adicionales</h2>
            
            <div class="grupo-campo">
                <label>Motivo o Descripción (Opcional)</label>
                <textarea name="motivo" id="motivo" rows="4" placeholder="Ej: Viaje familiar, descanso personal, etc."></textarea>
            </div>
        </div>

        <div class="seccion-acciones">
            <button type="submit" class="btn-enviar">Enviar Solicitud</button>
            <a href="HomeAdminTotal.php" class="btn-cancelar">Cancelar</a>
        </div>
    </form>
</main>

<script src="../javascript/controlador.SolicitudVacaciones.js"></script>
</body>
</html>
