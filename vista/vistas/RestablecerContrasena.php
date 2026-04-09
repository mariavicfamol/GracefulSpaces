<?php
session_start();
if (!empty($_SESSION['usuario'])) {
    header('Location: HomeAdminTotal.php');
    exit;
}

$errorReset = $_SESSION['error_reset'] ?? '';
unset($_SESSION['error_reset']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Graceful Spaces Workers | Restablecer Contraseña</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/style.Login.css">
</head>
<body>

<header>
    <div class="contenedor-logo">
        <img src="../../publico/imagenes/LogoBlancoGracefulSpaces.jpg" alt="Logo de Graceful Spaces" class="logo">
    </div>
</header>

<main class="contenedor-principal">
    <section class="tarjeta">
        <h1>Restablecer contraseña</h1>
        <p class="descripcion">
            Ingresa tu correo y tu fecha de nacimiento para verificar tu identidad y crear una nueva contraseña.
        </p>

        <?php if ($errorReset): ?>
            <div class="mensaje-error"><?= htmlspecialchars($errorReset) ?></div>
        <?php endif; ?>

        <form id="formulario-reset" method="POST" action="../../controlador/controlador.RestablecerContrasena.php">
            <div class="grupo-entrada">
                <label>Correo Electrónico</label>
                <input type="email" name="correo_usuario" id="correo_usuario" required>
            </div>

            <div class="grupo-entrada">
                <label>Fecha de Nacimiento</label>
                <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" required>
            </div>

            <div class="grupo-entrada">
                <label>Nueva Contraseña</label>
                <input type="password" name="nueva_clave" id="nueva_clave" minlength="8" required>
            </div>

            <div class="grupo-entrada">
                <label>Confirmar Contraseña</label>
                <input type="password" name="confirmar_clave" id="confirmar_clave" minlength="8" required>
            </div>

            <button type="submit" class="boton-primario">Restablecer contraseña</button>
        </form>

        <div class="footer-tarjeta" style="margin-top:18px;">
            <a href="Login.php" class="enlace-secundario">Volver al inicio de sesión</a>
        </div>
    </section>
</main>

<script src="../javascript/controlador.RestablecerContrasena.js"></script>
</body>
</html>