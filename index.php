<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Graceful Spaces</title>

    <!-- CAPA DE VISTA: Estilos -->
    <link rel="stylesheet" href="vistas/estilos.css">

    <!-- jQuery (requerido por aplicacion.js) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>

<!-- ============================================================
     VISTA: LOGIN
============================================================ -->
<div id="vistaLogin">

    <img src="publico/imagenes/GracefulSpacesLogo.jpg" class="login-logo" alt="Graceful Spaces Logo">

    <div class="layout-login">

        <h1 class="titulo-login"></h1>

        <div class="caja-login">

            <div id="bloqueLogin">

            <label class="etiqueta-input">USUARIO</label>
            <input type="text" id="campoUsuario" placeholder="Ingrese su usuario">

            <label class="etiqueta-input">CONTRASENA</label>
            <input type="password" id="campoPassword" placeholder="Ingrese su contrasena">

            <div class="enlace-olvido">
                <a href="#" onclick="irAlCampus(); return false;">
                    Olvide mi contrasena
                </a>
            </div>

            <button class="btn btn-primario" id="btnIngresar">Ingresar</button>
            <button class="btn btn-secundario" onclick="mostrarFormularioRegistro()">Registrarme</button>

            <div id="mensajeError"></div>

            </div>

            <div id="formularioRegistro" class="oculto">
                <label class="etiqueta-input">NOMBRE COMPLETO</label>
                <input type="text" id="registroNombre" placeholder="Ingrese su nombre completo">

                <label class="etiqueta-input">CORREO</label>
                <input type="email" id="registroCorreo" placeholder="Ingrese su correo">

                <label class="etiqueta-input">USUARIO</label>
                <input type="text" id="registroUsuario" placeholder="Cree su usuario">

                <label class="etiqueta-input">CONTRASENA</label>
                <input type="password" id="registroPassword" placeholder="Cree su contrasena">

                <label class="etiqueta-input">CONFIRMAR CONTRASENA</label>
                <input type="password" id="registroConfirmar" placeholder="Repita su contrasena">

                <button class="btn btn-primario" id="btnRegistrar" onclick="registrarCliente()">Crear cuenta</button>
                <button class="btn btn-secundario" onclick="ocultarFormularioRegistro()">Cancelar</button>

                <div id="mensajeRegistro"></div>
            </div>

        </div>

    </div>

</div>


<!-- ============================================================
     VISTA: PANEL ADMINISTRATIVO
============================================================ -->
<div id="vistaPanel" class="oculto">

    <header class="panel-header">
        <img src="publico/imagenes/GracefulSpaces1.jpg" alt="Logo Graceful Spaces">
        GRACEFUL SPACES
    </header>

    <!-- Barra lateral: boton para volver al login -->
    <div class="panel-barra-lateral" onclick="volverLogin()" title="Cerrar sesion">&#10554;</div>

    <!-- Boton superior: mostrar/ocultar tabla -->
    <button class="btn-superior" onclick="mostrarTarjetas()">OPCIONES | TABLA</button>

    <div class="panel-contenido">

        <h1 class="panel-titulo">OPCIONES</h1>

        <!-- Tarjetas de accion -->
        <div id="vistaTarjetas" class="grilla-opciones">

            <div class="tarjeta-opcion">
                <button class="btn-panel" onclick="activarAccion('crear')">CREAR</button>
            </div>

            <div class="tarjeta-opcion">
                <button class="btn-panel" onclick="activarAccion('editar')">EDITAR</button>
            </div>

            <div class="tarjeta-opcion">
                <button class="btn-panel" onclick="activarAccion('eliminar')">ELIMINAR</button>
            </div>

        </div>

        <!-- Formulario CRUD -->
        <div id="formularioCRUD" class="oculto">

            <button
                class="btn-imagen"
                id="btnSeleccionarFoto"
                onclick="document.getElementById('inputFoto').click()"
                style="display: none;">
                Seleccionar Foto
            </button>

            <!-- Campo oculto para el ID -->
            <input type="hidden" id="campoId">

            <input type="text" id="campoNombre"  placeholder="Nombre del empleado">
            <input type="text" id="campoFuncion" placeholder="Funcion del empleado">

            <!-- Input de foto (oculto, activado por el boton de arriba) -->
            <input
                type="file"
                id="inputFoto"
                accept="image/*"
                style="display: none;"
                onchange="previsualizarFoto()">

            <img id="imagenPrevia" class="imagen-previa oculto" alt="Vista previa de la foto">

        </div>

        <!-- Boton guardar (siempre visible en el panel) -->
        <button class="btn-guardar" onclick="enviarFormulario()">GUARDAR</button>

        <!-- Tabla de empleados -->
        <div id="vistaTabla" class="oculto">

            <div class="contenedor-tabla">

                <table class="tabla-empleados">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>FOTO</th>
                            <th>EMPLEADO</th>
                            <th>FUNCION</th>
                        </tr>
                    </thead>
                    <tbody id="cuerpoTabla"></tbody>
                </table>

            </div>

        </div>

    </div>

</div>


<!-- CAPA DE VISTA: JavaScript -->
<script src="vistas/aplicacion.js"></script>

<footer>GRACEFUL SPACES &middot; 2026</footer>

</body>
</html>
