<?php
session_start();
if (empty($_SESSION['usuario'])) {
    header('Location: Login.php');
    exit;
}

$usuario = $_SESSION['usuario'];
$rol = $usuario['rol'] ?? '';
$esAdmin = in_array($rol, ['Administrador Total', 'Administrador'], true);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión Administrativa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/style.HomeAdminTotal.css">
</head>
<body>

    <aside class="barra-lateral">
        <div class="cabecera-sidebar">
            <img src="../../publico/imagenes/LogoBlancoGracefulSpaces.jpg" alt="Logo" class="logo-empresa" id="logoCorporativo">
            <h2 class="titulo-gestion">Gestión Central</h2>
        </div>
        
        <nav class="menu-navegacion">
            <ul class="lista-menu">
                <?php if ($rol !== 'Trabajador'): ?>
                <?php if ($esAdmin): ?>
                <li class="item-menu">
                    <a href="DashboardAdmin.php" class="enlace-menu">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"></rect><rect x="14" y="3" width="7" height="5"></rect><rect x="14" y="12" width="7" height="9"></rect><rect x="3" y="16" width="7" height="5"></rect></svg>
                        <span>Dashboard</span>
                    </a>
                </li>
                <?php endif; ?>
                <li class="item-menu">
                    <a href="CrearUsuario.php" class="enlace-menu">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="17" y1="11" x2="23" y2="11"></line></svg>
                        <span>Crear Nuevo Usuario</span>
                    </a>
                </li>
                <li class="item-menu">
                    <a href="EditarUsuarios.php" class="enlace-menu">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                        <span>Gestión de Usuarios</span>
                    </a>
                </li>
                <?php if ($esAdmin): ?>
                <li class="item-menu">
                    <a href="MarcacionesAdmin.php" class="enlace-menu">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4"></path><path d="M16 2v4"></path><rect x="3" y="4" width="18" height="18" rx="2"></rect><path d="M3 10h18"></path><path d="M8 14h3"></path><path d="M13 14h3"></path><path d="M8 18h3"></path></svg>
                        <span>Registros de Marcación</span>
                    </a>
                </li>
                <li class="item-menu">
                    <a href="PlanillasAdmin.php" class="enlace-menu">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"></rect><path d="M2 10h20"></path><path d="M7 14h3"></path><path d="M14 14h3"></path></svg>
                        <span>Gestión de Planillas</span>
                    </a>
                </li>
                <li class="item-menu">
                    <a href="HistorialPagosAdmin.php" class="enlace-menu">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"></rect><path d="M2 10h20"></path><path d="M7 14h3"></path><path d="M14 14h3"></path></svg>
                        <span>Historial de Pagos</span>
                    </a>
                </li>
                <li class="item-menu">
                    <a href="ProyectosAdmin.php" class="enlace-menu">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h18"></path><path d="M3 12h18"></path><path d="M3 17h18"></path><circle cx="7" cy="7" r="1"></circle><circle cx="7" cy="12" r="1"></circle><circle cx="7" cy="17" r="1"></circle></svg>
                        <span>Gestión de Proyectos</span>
                    </a>
                </li>
                <li class="item-menu">
                    <a href="ProductosFaltantes.php" class="enlace-menu">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                        <span>Productos Faltantes</span>
                    </a>
                </li>
                <?php endif; ?>
                <?php endif; ?>
                <?php if (in_array($rol, ['Trabajador', 'Supervisor'], true)): ?>
                <li class="item-menu">
                    <a href="HomeTrabajador.php" class="enlace-menu">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        <span>Registrar Entradas y Salidas</span>
                    </a>
                </li>
                <li class="item-menu">
                    <a href="MisPlanillas.php" class="enlace-menu">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="8" y1="13" x2="16" y2="13"></line><line x1="8" y1="17" x2="16" y2="17"></line></svg>
                        <span>Mis Planillas</span>
                    </a>
                </li>
                <li class="item-menu">
                    <a href="MisHistorialPagos.php" class="enlace-menu">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="8" y1="13" x2="16" y2="13"></line><line x1="8" y1="17" x2="16" y2="17"></line></svg>
                        <span>Mi Historial de Pagos</span>
                    </a>
                </li>
                <li class="item-menu">
                    <a href="MisProyectos.php" class="enlace-menu">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"></path><path d="M3 12h18"></path><path d="M3 18h18"></path><path d="M6 6v12"></path></svg>
                        <span>Mis Proyectos</span>
                    </a>
                </li>
                <li class="item-menu">
                    <a href="ProductosFaltantes.php" class="enlace-menu">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                        <span>Productos Faltantes</span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>

        <div class="seccion-salir">
            <a href="../../controlador/controlador.CerrarSesion.php" class="boton-cerrar-sesion" id="enlaceSalir">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                <span>Finalizar Sesión</span>
            </a>
        </div>
    </aside>

    <main class="contenido-principal">
        <div class="capa-superpuesta">
            <div class="tarjeta-bienvenida">
                <div class="linea-decorativa"></div>
                <h1 id="textoSaludo">Bienvenido, <?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido1']) ?></h1>
                <p>Bienvenido al núcleo de gestión de recursos humanos y usuarios. Desde este panel puede supervisar la integridad de los datos, gestionar roles y asegurar la operatividad del personal corporativo.</p>
                <small class="instruccion">Seleccione una opción del menú lateral para comenzar su gestión.</small>
            </div>
        </div>
    </main>

    <script src="../javascript/controlador.HomeAdminTotal.js"></script>
</body>
</html>
