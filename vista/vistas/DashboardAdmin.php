<?php
session_start();
if (empty($_SESSION['usuario'])) {
    header('Location: Login.php');
    exit;
}

$usuario = $_SESSION['usuario'];
$rol = $usuario['rol'] ?? '';
$esAdmin = in_array($rol, ['Administrador Total', 'Administrador'], true);
$basePath = dirname($_SERVER['SCRIPT_NAME'], 3);

if (!$esAdmin) {
    header('Location: HomeAdminTotal.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrativo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/style.DashboardAdmin.css">
</head>
<body>

<div class="navegacion-superior">
    <a href="HomeAdminTotal.php" class="btn-volver">← Volver al Panel</a>
</div>

<main class="contenedor-principal">
    <header class="cabecera-dashboard">
        <div>
            <h1 id="textoSaludo">Dashboard Administrativo</h1>
            <p class="subtitulo-dashboard">Bienvenido, <?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido1']) ?>. Resumen general de operaciones.</p>
        </div>
        <button type="button" class="boton-refrescar" id="botonRefrescar">Actualizar datos</button>
    </header>

    <section class="tarjeta">
        <section class="rejilla-kpi" aria-label="Indicadores principales">
            <article class="tarjeta-kpi">
                <h2>Usuarios activos</h2>
                <p id="kpiUsuariosActivos">--</p>
            </article>
            <article class="tarjeta-kpi">
                <h2>Proyectos en progreso</h2>
                <p id="kpiProyectosActivos">--</p>
            </article>
            <article class="tarjeta-kpi">
                <h2>Marcaciones hoy</h2>
                <p id="kpiMarcacionesHoy">--</p>
            </article>
            <article class="tarjeta-kpi">
                <h2>Monto planillas del mes</h2>
                <p id="kpiMontoPlanillasMes">--</p>
            </article>
        </section>
    </section>

    <section class="tarjeta">
        <h2>Ultimas marcaciones</h2>
        <div class="tabla-contenedor">
            <table>
                <thead>
                    <tr>
                        <th>Trabajador</th>
                        <th>Fecha</th>
                        <th>Entrada</th>
                        <th>Salida</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody id="tablaUltimasMarcaciones">
                    <tr><td colspan="5">Cargando datos...</td></tr>
                </tbody>
            </table>
        </div>
    </section>

    <section class="tarjeta">
        <h2>Proyectos proximos a vencer (próximos 7 días)</h2>
        <div id="contenedorProyectosProximos" class="lista-proyectos-proximos">
            <p class="texto-secundario">Cargando proyectos...</p>
        </div>
    </section>

</main>

    <script>
        window.GRACEFULSPACES_BASE = <?= json_encode($basePath) ?>;
        window.DASHBOARD_ENDPOINT = <?= json_encode($basePath . '/controlador/controlador.DashboardAdmin.php?accion=resumen') ?>;
    </script>
    <script src="<?= htmlspecialchars($basePath) ?>/vista/javascript/controlador.HomeAdminTotal.js"></script>
</body>
</html>
