<?php
session_start();
require_once __DIR__ . '/../../modelo/ModeloProyecto.php';

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

$error = $_SESSION['error_proyecto_admin'] ?? '';
$exito = $_SESSION['exito_proyecto_admin'] ?? '';
unset($_SESSION['error_proyecto_admin'], $_SESSION['exito_proyecto_admin']);

$colaboradores = ModeloProyecto::obtenerColaboradoresDisponibles();
$proyectos = ModeloProyecto::obtenerProyectosAdmin();
$hayColaboradores = !empty($colaboradores);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proyectos | Administración</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/style.ProyectosAdmin.css">
</head>
<body>

<div class="navegacion-superior">
    <a href="HomeAdminTotal.php" class="btn-volver">← Volver al Panel</a>
</div>

<main class="contenedor-principal">
    <header>
        <h1>Gestión de Proyectos</h1>
        <p>Crea proyectos y asigna uno o varios colaboradores.</p>
    </header>

    <?php if ($error): ?>
        <div class="mensaje mensaje-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($exito): ?>
        <div class="mensaje mensaje-exito"><?= htmlspecialchars($exito) ?></div>
    <?php endif; ?>

    <section class="tarjeta-formulario">
        <h2>Crear Nuevo Proyecto</h2>
        <form method="POST" action="../../controlador/controlador.Proyectos.php" class="grid-formulario">
            <input type="hidden" name="accion" value="crear">

            <div class="grupo-campo ancho-completo">
                <label for="nombre">Nombre del Proyecto</label>
                <input type="text" id="nombre" name="nombre" required>
            </div>

            <div class="grupo-campo">
                <label for="detalles">Detalles del Proyecto</label>
                <textarea id="detalles" name="detalles" rows="4"></textarea>
            </div>

            <div class="grupo-campo">
                <label for="especificaciones">Especificaciones</label>
                <textarea id="especificaciones" name="especificaciones" rows="4"></textarea>
            </div>

            <div class="grupo-campo">
                <label for="horarios">Horarios</label>
                <textarea id="horarios" name="horarios" rows="4" placeholder="Ejemplo: L-V 8:00 a 17:00"></textarea>
            </div>

            <div class="grupo-campo">
                <label for="materiales">Materiales</label>
                <textarea id="materiales" name="materiales" rows="4"></textarea>
            </div>

            <div class="grupo-campo ancho-completo">
                <label for="colaboradores">Colaboradores Asignados (puedes seleccionar varios)</label>
                <select id="colaboradores" name="colaboradores[]" multiple <?= $hayColaboradores ? 'required' : 'disabled' ?>>
                    <?php if ($hayColaboradores): ?>
                        <?php foreach ($colaboradores as $colaborador): ?>
                            <option value="<?= (int)$colaborador['id'] ?>">
                                <?= htmlspecialchars($colaborador['nombre'] . ' ' . $colaborador['apellido1'] . ' ' . $colaborador['apellido2']) ?>
                                (<?= htmlspecialchars($colaborador['id_empresa']) ?> - <?= htmlspecialchars($colaborador['rol']) ?> - <?= htmlspecialchars($colaborador['estado']) ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option>No hay colaboradores (Trabajador/Supervisor) en la base de datos</option>
                    <?php endif; ?>
                </select>
                <?php if ($hayColaboradores): ?>
                    <small>Usa Ctrl + clic para seleccionar múltiples colaboradores.</small>
                <?php else: ?>
                    <small>Crea usuarios con rol Trabajador o Supervisor para poder asignarlos.</small>
                <?php endif; ?>
            </div>

            <div class="acciones">
                <button type="submit" class="btn-principal" <?= $hayColaboradores ? '' : 'disabled' ?>>Crear Proyecto</button>
            </div>
        </form>
    </section>

    <section class="tarjeta-tabla">
        <h2>Proyectos Registrados</h2>
        <div class="tabla-contenedor">
            <table>
                <thead>
                    <tr>
                        <th>Proyecto</th>
                        <th>Estado</th>
                        <th>Horarios</th>
                        <th>Materiales</th>
                        <th>Colaboradores</th>
                        <th>Progreso</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($proyectos)): ?>
                        <tr>
                            <td colspan="6">No hay proyectos registrados.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($proyectos as $proyecto): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($proyecto['nombre']) ?></strong>
                                    <div class="subtexto"><?= nl2br(htmlspecialchars($proyecto['detalles'] ?: 'Sin detalles')) ?></div>
                                    <div class="subtexto"><?= nl2br(htmlspecialchars($proyecto['especificaciones'] ?: 'Sin especificaciones')) ?></div>
                                </td>
                                <td><?= htmlspecialchars($proyecto['estado_general']) ?></td>
                                <td><?= nl2br(htmlspecialchars($proyecto['horarios'] ?: 'No definido')) ?></td>
                                <td><?= nl2br(htmlspecialchars($proyecto['materiales'] ?: 'No definido')) ?></td>
                                <td>
                                    <?php if (empty($proyecto['colaboradores'])): ?>
                                        Sin colaboradores
                                    <?php else: ?>
                                        <?php foreach ($proyecto['colaboradores'] as $col): ?>
                                            <div>
                                                <?= htmlspecialchars($col['colaborador']) ?>
                                                (<?= htmlspecialchars($col['id_empresa']) ?>)
                                                - <?= (int)$col['terminado'] === 1 ? 'Terminado' : 'Pendiente' ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= (int)$proyecto['colaboradores_terminados'] ?> / <?= (int)$proyecto['total_colaboradores'] ?></td>
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
