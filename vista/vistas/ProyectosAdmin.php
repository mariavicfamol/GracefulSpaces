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

$colaboradores = [];
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
        <form method="POST" action="../../controlador/controlador.Proyectos.php" class="grid-formulario" id="formCrearProyecto">
            <input type="hidden" name="accion" value="crear">

            <div class="grupo-campo ancho-completo">
                <label for="nombre">Nombre del Proyecto</label>
                <input type="text" id="nombre" name="nombre" required>
            </div>

            <div class="grupo-campo">
                <label for="descripcion">Descripcion del Proyecto</label>
                <textarea id="descripcion" name="descripcion" rows="4" required></textarea>
            </div>

            <div class="grupo-campo">
                <label for="especificaciones">Especificaciones</label>
                <textarea id="especificaciones" name="especificaciones" rows="4"></textarea>
            </div>

            <div class="grupo-campo">
                <label for="fecha_proyecto">Fecha del Proyecto</label>
                <input type="date" id="fecha_proyecto" name="fecha_proyecto" required>
            </div>

            <div class="grupo-campo">
                <label for="hora_proyecto">Hora del Proyecto</label>
                <input type="time" id="hora_proyecto" name="hora_proyecto" step="900" required>
            </div>

            <div class="grupo-campo">
                <label for="materiales">Materiales</label>
                <textarea id="materiales" name="materiales" rows="4"></textarea>
            </div>

            <div class="grupo-campo ancho-completo">
                <label for="colaboradores">Colaboradores Disponibles (puedes seleccionar varios)</label>
                <select id="colaboradores" name="colaboradores[]" multiple required disabled>
                    <option>Selecciona primero fecha y hora</option>
                </select>
                <small id="ayudaColaboradores">Usa Ctrl + clic para seleccionar multiples colaboradores.</small>
            </div>

            <div class="acciones">
                <button type="submit" class="btn-principal">Crear Proyecto</button>
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
                                <td>
                                    <?php if (!empty($proyecto['fecha_proyecto']) && !empty($proyecto['hora_proyecto'])): ?>
                                        <?= htmlspecialchars($proyecto['fecha_proyecto']) ?> <?= htmlspecialchars(substr((string)$proyecto['hora_proyecto'], 0, 5)) ?>
                                    <?php else: ?>
                                        <?= nl2br(htmlspecialchars($proyecto['horarios'] ?: 'No definido')) ?>
                                    <?php endif; ?>
                                </td>
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

<script>
const campoFecha = document.getElementById('fecha_proyecto');
const campoHora = document.getElementById('hora_proyecto');
const selectColaboradores = document.getElementById('colaboradores');
const ayudaColaboradores = document.getElementById('ayudaColaboradores');
const formCrearProyecto = document.getElementById('formCrearProyecto');
const cacheColaboradores = {};

function resetearColaboradores(mensaje) {
    selectColaboradores.innerHTML = '';
    const opcion = document.createElement('option');
    opcion.textContent = mensaje;
    selectColaboradores.appendChild(opcion);
    selectColaboradores.disabled = true;
}

function cargarColaboradoresDisponibles() {
    const fecha = campoFecha.value;
    const hora = campoHora.value;

    if (!fecha || !hora) {
        resetearColaboradores('Selecciona primero fecha y hora');
        ayudaColaboradores.textContent = 'Selecciona fecha y hora para ver disponibilidad.';
        return;
    }

    const cacheKey = `${fecha}|${hora}`;
    
    // Si ya está en caché, no hace request
    if (cacheColaboradores[cacheKey] !== undefined) {
        mostrarColaboradores(cacheColaboradores[cacheKey]);
        return;
    }

    resetearColaboradores('Cargando colaboradores disponibles...');
    ayudaColaboradores.textContent = 'Consultando disponibilidad...';

    const url = `../../controlador/controlador.Proyectos.php?accion=colaboradoresDisponibles&fecha=${encodeURIComponent(fecha)}&hora=${encodeURIComponent(hora)}`;

    fetch(url)
        .then((resp) => resp.json())
        .then((data) => {
            cacheColaboradores[cacheKey] = data;
            mostrarColaboradores(data);
        })
        .catch(() => {
            resetearColaboradores('No se pudo cargar la disponibilidad');
            ayudaColaboradores.textContent = 'Error al consultar disponibilidad.';
        });
}

function mostrarColaboradores(data) {
    selectColaboradores.innerHTML = '';

    if (!Array.isArray(data) || data.length === 0) {
        resetearColaboradores('No hay colaboradores disponibles para esa fecha y hora');
        ayudaColaboradores.textContent = 'Intenta con otra fecha u horario.';
        return;
    }

    data.forEach((colaborador) => {
        const opcion = document.createElement('option');
        opcion.value = colaborador.id;
        opcion.textContent = `${colaborador.nombre} ${colaborador.apellido1} ${colaborador.apellido2} (${colaborador.id_empresa} - ${colaborador.rol} - ${colaborador.estado})`;
        selectColaboradores.appendChild(opcion);
    });

    selectColaboradores.disabled = false;
    ayudaColaboradores.textContent = 'Usa Ctrl + clic para seleccionar multiples colaboradores.';
}

campoFecha.addEventListener('change', cargarColaboradoresDisponibles);
campoHora.addEventListener('change', cargarColaboradoresDisponibles);

formCrearProyecto.addEventListener('submit', (evento) => {
    if (selectColaboradores.disabled || selectColaboradores.selectedOptions.length === 0) {
        evento.preventDefault();
        ayudaColaboradores.textContent = 'Debes seleccionar fecha/hora y al menos un colaborador disponible.';
    }
});
</script>

</body>
</html>
