<?php
session_start();
if (empty($_SESSION['usuario'])) {
    header('Location: Login.php');
    exit;
}

$rol = $_SESSION['usuario']['rol'] ?? '';
if ($rol === 'Trabajador') {
    header('Location: HomeAdminTotal.php');
    exit;
}

$errorCrear  = $_SESSION['error_crear']  ?? '';
$exitoCrear  = $_SESSION['exito_crear']  ?? '';
unset($_SESSION['error_crear'], $_SESSION['exito_crear']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Graceful Spaces Workers | Crear Usuario</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/style.CrearUsuario.css">
</head>
<body>

<div class="navegacion-superior">
    <a href="HomeAdminTotal.php" class="btn-volver">← Volver al Panel</a>
</div>

<div class="contenedor-principal">
    <header>
        <h1>Crear Nuevo Usuario</h1>
        <p>Registro administrativo de personal</p>
    </header>

    <?php if ($errorCrear): ?>
        <div class="mensaje-error"><?= htmlspecialchars($errorCrear) ?></div>
    <?php endif; ?>
    <?php if ($exitoCrear): ?>
        <div class="mensaje-exito"><?= htmlspecialchars($exitoCrear) ?></div>
    <?php endif; ?>

    <form id="formularioUsuario" method="POST" action="../../controlador/controlador.CrearUsuario.php" enctype="multipart/form-data">
        
        <div class="subir-foto-perfil">
            <div class="vista-previa-foto" id="cuadroVistaPrevia">
                <img id="imagenPerfil" src="" alt="Previsualización" style="display: none;">
                <span id="textoMarcador"></span>
            </div>
            <label class="boton-subir">
                Subir Fotografía
                <input type="file" name="fotoPerfil" id="entradaFoto" accept="image/*" style="display: none;">
            </label>
        </div>

        <div class="titulo-seccion">1. Información Personal</div>
        <div class="rejilla-formulario">
            <div class="grupo-campo">
                <label>Nombre</label>
                <input type="text" name="nombreUsuario" id="nombreUsuario" required>
            </div>
            <div class="grupo-campo">
                <label>Primer Apellido</label>
                <input type="text" name="apellido1" id="apellido1" required>
            </div>
            <div class="grupo-campo">
                <label>Segundo Apellido</label>
                <input type="text" name="apellido2" id="apellido2">
            </div>
            <div class="grupo-campo">
                <label>ID Empresa (Auto-generado)</label>
                <input type="text" value="GS-<?= date('Y') ?>-#" readonly class="campo-lectura">
            </div>
            <div class="grupo-campo">
                <label>Tipo de Documento</label>
                <select name="tipoDocumento" id="tipoDocumento">
                    <option>Cedula</option>
                    <option>Pasaporte</option>
                    <option>DIMEX</option>
                </select>
            </div>
            <div class="grupo-campo">
                <label>Número de Identificación</label>
                <input type="text" name="numeroIdentificacion" id="numeroIdentificacion" placeholder="Solo números" maxlength="15">
            </div>
            <div class="grupo-campo">
                <label>Fecha de Nacimiento</label>
                <input type="date" name="fechaNacimiento" id="fechaNacimiento">
            </div>
            <div class="grupo-campo">
                <label>Sexo</label>
                <select name="sexoUsuario" id="sexoUsuario">
                    <option>Hombre</option>
                    <option>Mujer</option>
                    <option>Prefiero no decir</option>
                </select>
            </div>
            <div class="grupo-campo">
                <label>Género</label>
                <input type="text" name="generoEscrito" id="generoEscrito" placeholder="Identidad de género">
            </div>
            <div class="grupo-campo">
                <label>País de Origen</label>
                <select name="nacionalidadUsuario" id="nacionalidadUsuario">
                    <option>Canada</option>
                    <option>Estados Unidos</option>
                    <option>Costa Rica</option>
                    <option>Otro</option>
                </select>
            </div>
        </div>

        <div class="titulo-seccion">2. Información Laboral</div>
        <div class="rejilla-formulario">
            <div class="grupo-campo">
                <label>Cargo / Puesto</label>
                <select name="cargoPuesto" id="cargoPuesto">
                    <option>Administrador</option>
                    <option>Supervisor</option>
                    <option>Trabajador</option>
                </select>
            </div>
            <div class="grupo-campo">
                <label>Tipo de Contrato</label>
                <select name="tipoContrato" id="tipoContrato">
                    <option>Tiempo completo</option>
                    <option>Medio tiempo</option>
                    <option>Turno rotativo</option>
                    <option>Turno nocturno</option>
                    <option>Trabajo remoto</option>
                    <option>Trabajo temporal</option>
                </select>
            </div>
            <div class="grupo-campo">
                <label>Fecha de Ingreso</label>
                <input type="date" name="fechaIngreso" id="fechaIngreso">
            </div>
        </div>

        <div class="titulo-seccion">3. Datos de Contacto</div>
        <div class="rejilla-formulario">
            <div class="grupo-campo">
                <label>Correo personal</label>
                <input type="email" name="correoPersonal" id="correoPersonal">
            </div>
            <div class="grupo-campo">
                <label>Correo corporativo</label>
                <input type="email" name="correoCorporativo" id="correoCorporativo">
            </div>
            <div class="grupo-campo">
                <label>Teléfono</label>
                <input type="tel" name="telefono" id="telefono">
            </div>
            <div class="grupo-campo">
                <label>Contacto Emergencia</label>
                <input type="text" name="nombreEmergencia" id="nombreEmergencia" placeholder="¿Quién es?">
            </div>
            <div class="grupo-campo">
                <label>Teléfono Emergencia</label>
                <input type="tel" name="telefonoEmergencia" id="telefonoEmergencia">
            </div>
            <div class="grupo-campo ancho-completo">
                <label>Dirección Exacta</label>
                <textarea name="direccion" id="direccion" rows="2"></textarea>
            </div>
        </div>

        <div class="titulo-seccion">4. Acceso al Sistema</div>
        <div class="rejilla-formulario">
            <div class="grupo-campo">
                <label>Nombre de usuario (correo de acceso)</label>
                <input type="text" name="loginUsuario" id="loginUsuario">
            </div>
            <div class="grupo-campo">
                <label>Contraseña</label>
                <div class="fila-password">
                    <input type="text" name="passGenerada" id="passGenerada" readonly>
                    <button type="button" id="botonGenerar" class="btn-mini">Generar</button>
                </div>
            </div>
            <div class="grupo-campo">
                <label>Confirmar contraseña</label>
                <input type="password" id="confirmarPass">
            </div>
            <div class="grupo-campo">
                <label>Rol de Usuario</label>
                <select name="rolSistema" id="rolSistema">
                    <option>Administrador Total</option>
                    <option>Administrador</option>
                    <option>Supervisor</option>
                    <option selected>Trabajador</option>
                </select>
            </div>
            <div class="grupo-campo">
                <label>Estado de la cuenta</label>
                <select name="estadoCuenta" id="estadoCuenta">
                    <option>Activo</option>
                    <option>Inactivo</option>
                </select>
            </div>
        </div>

        <div class="acciones-finales">
            <button type="reset" class="btn-cancelar">Limpiar Formulario</button>
            <button type="submit" class="btn-guardar">Registrar Usuario</button>
        </div>
    </form>
</div>

<script src="../javascript/controlador.CrearUsuario.js"></script>
</body>
</html>
