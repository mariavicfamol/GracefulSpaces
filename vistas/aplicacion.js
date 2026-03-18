/**
 * CAPA DE VISTA
 * Archivo: vistas/aplicacion.js
 * Descripcion: Logica del cliente (navegacion, CRUD, login via AJAX)
 */

/* ============================================================
   ESTADO GLOBAL
============================================================ */
let accionActual = '';


/* ============================================================
   NAVEGACION ENTRE VISTAS
============================================================ */

/**
 * Oculta el panel y regresa a la pantalla de login
 */
function volverLogin() {
    $('#vistaPanel').addClass('oculto');
    $('#vistaLogin').removeClass('oculto');
}

/**
 * Navega al campus universitario
 */
function irAlCampus() {
    window.location.href = 'https://campus.ulatina.ac.cr';
}

/**
 * Regresa a la vista de tarjetas principales del panel
 * y limpia el estado del formulario
 */
function mostrarTarjetas() {
    $('#formularioCRUD').addClass('oculto');
    $('#btnSeleccionarFoto').hide();

    if ($('#vistaTabla').hasClass('oculto')) {
        $('#vistaTarjetas').addClass('oculto');
        $('#vistaTabla').removeClass('oculto');
        cargarEmpleados();
    } else {
        $('#vistaTabla').addClass('oculto');
        $('#vistaTarjetas').removeClass('oculto');
    }
}


/* ============================================================
   ACCIONES CRUD
============================================================ */

/**
 * Activa la accion seleccionada (crear, editar, eliminar)
 * y configura el formulario segun corresponda
 * @param {string} tipo - 'crear' | 'editar' | 'eliminar'
 */
function activarAccion(tipo) {
    accionActual = tipo;

    $('#formularioCRUD').removeClass('oculto');
    $('#vistaTarjetas').addClass('oculto');

    if ((tipo === 'editar' || tipo === 'eliminar') && $('#vistaTabla').hasClass('oculto')) {
        $('#vistaTabla').removeClass('oculto');
    }

    if (tipo === 'crear') {
        $('#btnSeleccionarFoto').show();
        $('#campoNombre').prop('disabled', false);
        $('#campoFuncion').prop('disabled', false);

    } else if (tipo === 'editar') {
        $('#btnSeleccionarFoto').show();
        $('#campoNombre').prop('disabled', false);
        $('#campoFuncion').prop('disabled', false);

    } else if (tipo === 'eliminar') {
        $('#btnSeleccionarFoto').hide();
        $('#campoNombre').prop('disabled', true);
        $('#campoFuncion').prop('disabled', true);
    }
}

/**
 * Envia el formulario al servidor segun la accion activa
 * Usa FormData si hay foto, JSON en caso contrario
 */
function enviarFormulario() {
    const idEmpleado = $('#campoId').val();
    const nombre     = $('#campoNombre').val();
    const funcionEmp = $('#campoFuncion').val();
    const archivoFoto = document.getElementById('inputFoto').files[0];

    if (archivoFoto) {
        const datosFormulario = new FormData();
        datosFormulario.append('accion', accionActual);
        datosFormulario.append('id', idEmpleado);
        datosFormulario.append('nombre', nombre);
        datosFormulario.append('funcion', funcionEmp);
        datosFormulario.append('foto', archivoFoto);

        fetch('controlador/ControladorEmpleado.php', {
            method: 'POST',
            body: datosFormulario
        })
        .then(res => res.json())
        .then(respuesta => {
            alert(respuesta.mensaje || 'Operacion realizada');
            cargarEmpleados();
            limpiarFormulario();
        })
        .catch(() => alert('Error al conectar con el servidor'));

    } else {
        fetch('controlador/ControladorEmpleado.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                accion:  accionActual,
                id:      idEmpleado,
                nombre:  nombre,
                funcion: funcionEmp
            })
        })
        .then(res => res.json())
        .then(respuesta => {
            alert(respuesta.mensaje || 'Operacion realizada');
            cargarEmpleados();
            limpiarFormulario();
        })
        .catch(() => alert('Error al conectar con el servidor'));
    }
}


/* ============================================================
   TABLA DE EMPLEADOS
============================================================ */

/**
 * Obtiene todos los empleados del servidor y renderiza la tabla
 */
function cargarEmpleados() {
    const imagenPorDefecto = 'publico/imagenes/GracefulSpacesLogo.jpg';

    fetch('controlador/ControladorEmpleado.php')
        .then(res => res.json())
        .then(listaEmpleados => {
            const cuerpoTabla = document.getElementById('cuerpoTabla');
            cuerpoTabla.innerHTML = '';

            listaEmpleados.forEach(empleado => {
                const fila = document.createElement('tr');
                const rutaFoto = construirRutaFoto(empleado.foto);
                const fotoFinal = rutaFoto || imagenPorDefecto;
                const fotoHTML = `<img src="${fotoFinal}" alt="Foto de ${empleado.nombre_empleado}" onerror="this.onerror=null;this.src='${imagenPorDefecto}';">`;

                fila.innerHTML = `
                    <td>${empleado.id}</td>
                    <td>${fotoHTML}</td>
                    <td>${empleado.nombre_empleado}</td>
                    <td>${empleado.funcion}</td>
                `;

                fila.onclick = () => seleccionarEmpleado(empleado);
                cuerpoTabla.appendChild(fila);
            });
        })
        .catch(() => console.error('No se pudo cargar la lista de empleados'));
}

/**
 * Normaliza la ruta de una foto almacenada en BD para que sea valida en el navegador
 * @param {string} rutaOriginal
 * @returns {string}
 */
function construirRutaFoto(rutaOriginal) {
    if (!rutaOriginal) {
        return '';
    }

    let ruta = String(rutaOriginal).trim().replace(/\\/g, '/');

    const indicePublico = ruta.lastIndexOf('publico/');
    if (indicePublico !== -1) {
        ruta = ruta.substring(indicePublico);
    }

    ruta = ruta.replace(/^\.?\//, '');
    ruta = ruta.replace(/^GracefulSpaces\//i, '');

    return ruta;
}

/**
 * Rellena el formulario con los datos del empleado seleccionado
 * @param {Object} empleado - Objeto con los datos del empleado
 */
function seleccionarEmpleado(empleado) {
    $('#campoId').val(empleado.id);
    $('#campoNombre').val(empleado.nombre_empleado);
    $('#campoFuncion').val(empleado.funcion);
}


/* ============================================================
   UTILIDADES DEL FORMULARIO
============================================================ */

/**
 * Muestra una previa de la imagen seleccionada
 */
function previsualizarFoto() {
    const inputFoto = document.getElementById('inputFoto');
    const imagenPrevia = document.getElementById('imagenPrevia');
    const archivo = inputFoto.files[0];

    if (archivo) {
        const lector = new FileReader();
        lector.onload = function (evento) {
            imagenPrevia.src = evento.target.result;
            imagenPrevia.classList.remove('oculto');
        };
        lector.readAsDataURL(archivo);
    }
}

/**
 * Limpia todos los campos del formulario y oculta la seccion
 */
function limpiarFormulario() {
    $('#campoId').val('');
    $('#campoNombre').val('').prop('disabled', false);
    $('#campoFuncion').val('').prop('disabled', false);
    $('#inputFoto').val('');
    $('#imagenPrevia').addClass('oculto').attr('src', '');
    $('#formularioCRUD').addClass('oculto');
    $('#vistaTarjetas').removeClass('oculto');
    accionActual = '';
}


/* ============================================================
   LOGIN
============================================================ */

$(document).ready(function () {

    // Boton ingresar
    $('#btnIngresar').click(function () {
        const usuario  = $('#campoUsuario').val().trim();
        const password = $('#campoPassword').val().trim();

        if (usuario === '' || password === '') {
            alert('Complete todos los campos');
            return;
        }

        $.ajax({
            url:    'controlador/ControladorLogin.php',
            method: 'POST',
            data:   { usuario: usuario, password: password },

            success: function (respuesta) {
                respuesta = respuesta.trim();

                if (respuesta === 'success') {
                    $('#vistaLogin').addClass('oculto');
                    $('#vistaPanel').removeClass('oculto');
                    cargarEmpleados();
                } else {
                    const mensajeError = $('#mensajeError');
                    mensajeError.text('Usuario o contrasena incorrectos');
                    mensajeError.show();
                }
            },

            error: function () {
                $('#mensajeError').text('Error al conectar con el servidor').show();
            }
        });
    });

    // Permitir login con Enter
    $('#campoUsuario, #campoPassword').keypress(function (evento) {
        if (evento.which === 13) {
            $('#btnIngresar').click();
        }
    });

    // Limpiar mensaje de error al escribir
    $('#campoUsuario, #campoPassword').on('input', function () {
        $('#mensajeError').hide();
    });

});
