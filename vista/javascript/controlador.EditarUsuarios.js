document.addEventListener('DOMContentLoaded', () => {

    // -------------------------------------------------------
    // Referencias
    // -------------------------------------------------------
    const selectorFoto    = document.getElementById('entradaFoto');
    const vistaPrevia     = document.getElementById('imagenPerfil');
    const campoPass       = document.getElementById('campoPassword');
    const botonVerPass    = document.getElementById('botonVerPass');
    const botonGuardar    = document.getElementById('botonGuardar');
    const botonDescartar  = document.getElementById('botonDescartar');
    const botonBajar      = document.getElementById('botonDarDeBaja');
    const mensajeAccion   = document.getElementById('mensajeAccion');
    const mensajeBusqueda = document.getElementById('mensajeBusqueda');
    const TAMANO_MAX_FOTO = 5 * 1024 * 1024; // 5 MB

    // URL del controlador PHP (relativa desde la vista)
    const URL_CONTROLADOR = '../../controlador/controlador.EditarUsuarios.php';

    // -------------------------------------------------------
    // Helpers de UI
    // -------------------------------------------------------
    function mostrarMensaje(elem, texto, esError = false) {
        elem.textContent = texto;
        elem.style.display = 'block';
        elem.style.color = esError ? '#c0392b' : '#27ae60';
        elem.style.fontWeight = '600';
        setTimeout(() => { elem.style.display = 'none'; }, 5000);
    }

    async function parsearRespuestaServidor(resp) {
        const contenido = await resp.text();

        try {
            return JSON.parse(contenido);
        } catch {
            const resumen = (contenido || '').trim().slice(0, 140);
            throw new Error(`Respuesta inesperada del servidor (${resp.status}). ${resumen}`);
        }
    }

    function setSelect(id, valor) {
        const sel = document.getElementById(id);
        if (!sel) return;
        for (const opt of sel.options) {
            if (opt.value === valor || opt.text === valor) {
                opt.selected = true;
                return;
            }
        }
    }

    // -------------------------------------------------------
    // 1. Cambio de foto de perfil
    // -------------------------------------------------------
    selectorFoto.addEventListener('change', function () {
        const archivo = this.files[0];
        if (archivo) {
            if (archivo.size > TAMANO_MAX_FOTO) {
                this.value = '';
                mostrarMensaje(mensajeAccion, 'La imagen supera el máximo permitido de 5 MB.', true);
                return;
            }

            const lector = new FileReader();
            lector.onload = e => { vistaPrevia.src = e.target.result; };
            lector.readAsDataURL(archivo);
        }
    });

    // -------------------------------------------------------
    // 2. Solo números
    // -------------------------------------------------------
    ['numeroIdentificacion', 'telefono', 'telefonoEmergencia'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', function () {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });

    // -------------------------------------------------------
    // 3. Ver / ocultar contraseña
    // -------------------------------------------------------
    botonVerPass.addEventListener('click', () => {
        const esPassword = campoPass.type === 'password';
        campoPass.type = esPassword ? 'text' : 'password';
        botonVerPass.textContent = esPassword ? 'Ocultar' : 'Ver';
    });

    // -------------------------------------------------------
    // 4. BUSCAR colaborador → llama al controlador PHP
    // -------------------------------------------------------
    document.getElementById('botonBuscar').addEventListener('click', async () => {
        const termino = document.getElementById('busquedaUsuario').value.trim();
        if (!termino) {
            mostrarMensaje(mensajeBusqueda, 'Por favor, ingrese un ID o nombre para buscar.', true);
            return;
        }

        mensajeBusqueda.textContent = 'Buscando...';
        mensajeBusqueda.style.display = 'block';
        mensajeBusqueda.style.color = '#555';

        try {
            const resp = await fetch(`${URL_CONTROLADOR}?accion=buscar&termino=${encodeURIComponent(termino)}`);
            const data = await parsearRespuestaServidor(resp);

            if (data.error) {
                mostrarMensaje(mensajeBusqueda, data.mensaje, true);
                return;
            }

            // Poblar formulario
            const u = data.usuario;
            document.getElementById('idUsuario').value        = u.id;
            document.getElementById('fotoActual').value       = u.foto_perfil || '';
            document.getElementById('idEmpresa').value        = u.id_empresa || '';
            document.getElementById('nombreUsuario').value    = u.nombre || '';
            document.getElementById('apellido1').value        = u.apellido1 || '';
            document.getElementById('apellido2').value        = u.apellido2 || '';
            document.getElementById('numeroIdentificacion').value = u.numero_identificacion || '';
            document.getElementById('fechaNacimiento').value  = u.fecha_nacimiento || '';
            document.getElementById('fechaIngreso').value     = u.fecha_ingreso || '';
            document.getElementById('correoPersonal').value   = u.correo_personal || '';
            document.getElementById('correoCorporativo').value = u.correo_corporativo || '';
            document.getElementById('telefono').value         = u.telefono || '';
            document.getElementById('nombreEmergencia').value = u.contacto_emergencia || '';
            document.getElementById('telefonoEmergencia').value = u.telefono_emergencia || '';
            document.getElementById('direccionExacta').value  = u.direccion || '';
            document.getElementById('nombreAcceso').value     = u.login_usuario || '';

            setSelect('tipoDocumento',    u.tipo_documento);
            setSelect('generoUsuario',    u.genero || 'Prefiero no decir');
            setSelect('nacionalidadUsuario', u.nacionalidad);
            setSelect('cargoPuesto',      u.cargo);
            setSelect('tipoContrato',     u.tipo_contrato);
            setSelect('rolSistema',       u.rol);
            setSelect('estadoCuenta',     u.estado);

            // Foto
            if (u.foto_perfil) {
                vistaPrevia.src = '../../' + u.foto_perfil;
            } else {
                vistaPrevia.src = 'https://via.placeholder.com/125';
            }

            mostrarMensaje(mensajeBusqueda, `Colaborador "${u.nombre} ${u.apellido1}" cargado correctamente.`);

        } catch (err) {
            mostrarMensaje(mensajeBusqueda, err.message || 'Error de comunicación con el servidor.', true);
            console.error(err);
        }
    });

    // -------------------------------------------------------
    // 5. GUARDAR / ACTUALIZAR → llama al controlador PHP
    // -------------------------------------------------------
    botonGuardar.addEventListener('click', async () => {
        const idUsuario = document.getElementById('idUsuario').value;
        if (!idUsuario) {
            mostrarMensaje(mensajeAccion, 'Primero busque un colaborador para editar.', true);
            return;
        }

        const nombre    = document.getElementById('nombreUsuario').value;
        const apellido1 = document.getElementById('apellido1').value;
        if (!nombre || !apellido1) {
            mostrarMensaje(mensajeAccion, 'El nombre y primer apellido son obligatorios.', true);
            return;
        }

        const formData = new FormData(document.getElementById('formularioEditar'));
        formData.append('accion', 'actualizar');

        try {
            const resp = await fetch(URL_CONTROLADOR, { method: 'POST', body: formData });
            const data = await parsearRespuestaServidor(resp);

            mostrarMensaje(mensajeAccion, data.mensaje, data.error);
        } catch (err) {
            mostrarMensaje(mensajeAccion, err.message || 'Error de comunicación con el servidor.', true);
            console.error(err);
        }
    });

    // -------------------------------------------------------
    // 6. DAR DE BAJA → llama al controlador PHP
    // -------------------------------------------------------
    botonBajar.addEventListener('click', async () => {
        const idUsuario = document.getElementById('idUsuario').value;
        const nombre    = document.getElementById('nombreUsuario').value;
        if (!idUsuario) {
            mostrarMensaje(mensajeAccion, 'Primero busque un colaborador.', true);
            return;
        }
        const confirmar = confirm(`¿Está seguro de dar de baja a "${nombre}"? Se desactivará su acceso.`);
        if (!confirmar) return;

        const formData = new FormData();
        formData.append('accion', 'darDeBaja');
        formData.append('id', idUsuario);

        try {
            const resp = await fetch(URL_CONTROLADOR, { method: 'POST', body: formData });
            const data = await parsearRespuestaServidor(resp);
            mostrarMensaje(mensajeAccion, data.mensaje, data.error);
            if (!data.error) {
                setSelect('estadoCuenta', 'Inactivo');
            }
        } catch (err) {
            mostrarMensaje(mensajeAccion, err.message || 'Error de comunicación con el servidor.', true);
        }
    });

    // -------------------------------------------------------
    // 7. DESCARTAR → limpiar formulario
    // -------------------------------------------------------
    botonDescartar.addEventListener('click', () => {
        if (confirm('¿Descartar todos los cambios?')) {
            document.getElementById('formularioEditar').reset();
            document.getElementById('idUsuario').value = '';
            vistaPrevia.src = 'https://via.placeholder.com/125';
            mensajeAccion.style.display = 'none';
        }
    });
});
