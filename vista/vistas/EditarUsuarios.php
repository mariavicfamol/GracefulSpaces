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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Graceful Spaces Workers | Editar Usuario</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/style.EditarUsuarios.css">
</head>
<body>

<div class="navegacion-superior">
    <a href="HomeAdminTotal.php" class="btn-volver">← Volver al Panel</a>
</div>

<div class="contenedor-principal">
    <header>
        <h1>Editar Perfil de Usuario</h1>
        <p>Gestión técnica y administrativa de colaboradores</p>
    </header>

    <div class="seccion-busqueda">
        <label>Búsqueda de Colaborador:</label>
        <div class="fila-busqueda">
            <input type="text" id="busquedaUsuario" placeholder="ID Empresa, Cédula o Nombre...">
            <button type="button" id="botonBuscar" class="btn-buscar">Consultar</button>
        </div>
        <div id="mensajeBusqueda" style="display:none; margin-top:8px;"></div>
    </div>

    <form id="formularioEditar" enctype="multipart/form-data">
        <!-- ID oculto del usuario cargado -->
        <input type="hidden" id="idUsuario" name="id">
        <input type="hidden" id="fotoActual" name="fotoActual">
        
        <div class="subir-foto-perfil">
            <div class="vista-previa-foto" id="cuadroVistaPrevia">
                <img id="imagenPerfil" src="https://via.placeholder.com/125" alt="">
            </div>
            <label class="boton-subir">
                Actualizar Imagen
                <input type="file" name="fotoPerfil" id="entradaFoto" accept="image/*" style="display: none;">
            </label>
        </div>

        <div class="titulo-seccion">1. Información Personal</div>
        <div class="rejilla-formulario">
            <div class="grupo-campo">
                <label>Nombre</label>
                <input type="text" name="nombreUsuario" id="nombreUsuario">
            </div>
            <div class="grupo-campo">
                <label>Primer Apellido</label>
                <input type="text" name="apellido1" id="apellido1">
            </div>
            <div class="grupo-campo">
                <label>Segundo Apellido</label>
                <input type="text" name="apellido2" id="apellido2">
            </div>
            <div class="grupo-campo">
                <label>ID Empresa</label>
                <input type="text" id="idEmpresa" readonly class="campo-lectura">
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
                <input type="text" name="numeroIdentificacion" id="numeroIdentificacion">
            </div>
            <div class="grupo-campo">
                <label>Fecha de Nacimiento</label>
                <input type="date" name="fechaNacimiento" id="fechaNacimiento">
            </div>
            <div class="grupo-campo">
                <label>Género</label>
                <select name="generoUsuario" id="generoUsuario">
                    <option value="Hombre">Hombre</option>
                    <option value="Mujer">Mujer</option>
                    <option value="Prefiero no decir">Prefiero no decir</option>
                </select>
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
                <label>Correo Personal</label>
                <input type="email" name="correoPersonal" id="correoPersonal">
            </div>
            <div class="grupo-campo">
                <label>Correo Corporativo</label>
                <input type="email" name="correoCorporativo" id="correoCorporativo">
            </div>
            <div class="grupo-campo">
                <label>Teléfono</label>
                <input type="tel" name="telefono" id="telefono">
            </div>
            <div class="grupo-campo">
                <label>Contacto Emergencia</label>
                <input type="text" name="nombreEmergencia" id="nombreEmergencia">
            </div>
            <div class="grupo-campo">
                <label>Teléfono Emergencia</label>
                <input type="tel" name="telefonoEmergencia" id="telefonoEmergencia">
            </div>
            <div class="grupo-campo ancho-completo">
                <label>Dirección Exacta</label>
                <textarea name="direccionExacta" id="direccionExacta" rows="2"></textarea>
            </div>
        </div>

        <div class="titulo-seccion">4. Acceso al Sistema</div>
        <div class="rejilla-formulario">
            <div class="grupo-campo">
                <label>Nombre de Usuario</label>
                <input type="text" name="nombreAcceso" id="nombreAcceso">
            </div>
            <div class="grupo-campo">
                <label>Nueva Contraseña (dejar vacío para no cambiar)</label>
                <div class="fila-password">
                    <input type="password" name="campoPassword" id="campoPassword">
                    <button type="button" id="botonVerPass" class="btn-mini">Ver</button>
                </div>
            </div>
            <div class="grupo-campo">
                <label>Rol de Usuario</label>
                <select name="rolSistema" id="rolSistema">
                    <option>Administrador Total</option>
                    <option>Administrador</option>
                    <option>Supervisor</option>
                    <option>Trabajador</option>
                </select>
            </div>
            <div class="grupo-campo">
                <label>Estado de la Cuenta</label>
                <select name="estadoCuenta" id="estadoCuenta">
                    <option>Activo</option>
                    <option>Inactivo</option>
                </select>
            </div>
        </div>

        <div class="acciones-finales">
            <button type="button" class="btn-eliminar" id="botonDarDeBaja">Dar de Baja Colaborador</button>
            <div class="grupo-botones-derecha">
                <button type="button" class="btn-cancelar" id="botonDescartar">Descartar</button>
                <button type="button" class="btn-guardar" id="botonGuardar">Actualizar Ficha</button>
            </div>
        </div>

        <div id="mensajeAccion" style="display:none; margin-top:16px; text-align:center;"></div>
    </form>
</div>

<script src="../javascript/controlador.EditarUsuarios.js"></script>
</body>
</html>
