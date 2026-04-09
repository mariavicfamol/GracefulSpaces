<?php
session_start();
// Si ya está logueado, redirigir al home
if (!empty($_SESSION['usuario'])) {
    header('Location: HomeAdminTotal.php');
    exit;
}
$errorLogin = $_SESSION['error_login'] ?? '';
$exitoLogin = $_SESSION['exito_login'] ?? '';
unset($_SESSION['error_login']);
unset($_SESSION['exito_login']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Graceful Spaces | Iniciar Sesión</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
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
            <h1>Graceful Spaces </h1>
            <h1>¡Bienvenido de vuelta!</h1>
            <p class="descripcion">
                Plataforma exclusiva para nuestros trabajadores. Sirviendo en cualquier lugar de Lower Mainland Vancouver, Canadá.
            </p>

            <?php if ($errorLogin): ?>
                <div class="mensaje-error"><?= htmlspecialchars($errorLogin) ?></div>
            <?php endif; ?>
            <?php if ($exitoLogin): ?>
                <div class="mensaje-exito" style="background:#eaf7ee;color:#1e7e34;border:1px solid #28a745;border-radius:6px;padding:10px 14px;margin-bottom:14px;font-size:0.87rem;font-weight:500;"><?= htmlspecialchars($exitoLogin) ?></div>
            <?php endif; ?>

            <form id="formulario-login" method="POST" action="../../controlador/controlador.Login.php">
                <div class="grupo-entrada">
                    <label>Correo Electrónico</label>
                    <input type="email" name="correo_usuario" id="correo_usuario" required>
                </div>

                <div class="grupo-entrada">
                    <label>Contraseña</label>
                    <input type="password" name="clave_usuario" id="clave_usuario" required>
                </div>

                <div class="opciones-adicionales">
                    <label class="recordarme">
                        <input type="checkbox"> Recordarme
                    </label>
                    <a href="RestablecerContrasena.php" class="enlace-secundario">¿Ha olvidado su contraseña?</a>
                </div>

                <button type="submit" class="boton-primario">Iniciar Sesión</button>
            </form>

            <div class="footer-tarjeta">
                <span>¿No tienes una cuenta? </span>
                <a href="Registro.php" class="enlace-secundario">Registrarse</a>
            </div>
        </section>
    </main>
    <script src="../javascript/controlador.Login.js"></script>
</body>
</html>
