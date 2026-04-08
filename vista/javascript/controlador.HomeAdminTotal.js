document.addEventListener('DOMContentLoaded', () => {
    const botonSalir = document.getElementById('enlaceSalir');
    const logoImagen = document.getElementById('logoCorporativo');
    const botonRefrescar = document.getElementById('botonRefrescar');

    const kpiUsuariosActivos = document.getElementById('kpiUsuariosActivos');
    const kpiProyectosActivos = document.getElementById('kpiProyectosActivos');
    const kpiMarcacionesHoy = document.getElementById('kpiMarcacionesHoy');
    const kpiMontoPlanillasMes = document.getElementById('kpiMontoPlanillasMes');
    const resumenMarcaciones = document.getElementById('resumenMarcaciones');
    const tablaUltimasMarcaciones = document.getElementById('tablaUltimasMarcaciones');
    const contenedorProyectosProximos = document.getElementById('contenedorProyectosProximos');
    const hayDashboardAdmin = Boolean(kpiUsuariosActivos && kpiProyectosActivos && kpiMarcacionesHoy && kpiMontoPlanillasMes && tablaUltimasMarcaciones);

    const formateadorMoneda = new Intl.NumberFormat('en-CA', {
        style: 'currency',
        currency: 'CAD',
        maximumFractionDigits: 2,
    });

    const formateadorFecha = new Intl.DateTimeFormat('es-CR', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
    });

    if (botonSalir) {
        botonSalir.addEventListener('click', (evento) => {
            const confirmarSalida = confirm('¿Está seguro de que desea finalizar su sesión administrativa?');
            if (!confirmarSalida) {
                evento.preventDefault();
            }
        });
    }

    if (logoImagen) {
        logoImagen.onerror = function onLogoError() {
            this.style.display = 'none';
        };
    }

    function pintarCargando() {
        if (kpiUsuariosActivos) kpiUsuariosActivos.textContent = '...';
        if (kpiProyectosActivos) kpiProyectosActivos.textContent = '...';
        if (kpiMarcacionesHoy) kpiMarcacionesHoy.textContent = '...';
        if (kpiMontoPlanillasMes) kpiMontoPlanillasMes.textContent = '...';
        if (resumenMarcaciones) resumenMarcaciones.textContent = 'Actualizando...';
        if (tablaUltimasMarcaciones) {
            tablaUltimasMarcaciones.innerHTML = '<tr><td colspan="5">Cargando datos...</td></tr>';
        }
    }

    function formatearFechaISO(fechaISO) {
        if (!fechaISO) return '--';
        const fecha = new Date(`${fechaISO}T00:00:00`);
        if (Number.isNaN(fecha.getTime())) return '--';
        return formateadorFecha.format(fecha);
    }

    function formatearHora(valorFechaHora) {
        if (!valorFechaHora) return '--';
        const coincidencia = valorFechaHora.match(/\d{2}:\d{2}/);
        return coincidencia ? coincidencia[0] : '--';
    }

    function pintarMarcaciones(listaMarcaciones) {
        if (!tablaUltimasMarcaciones) return;

        if (!Array.isArray(listaMarcaciones) || listaMarcaciones.length === 0) {
            tablaUltimasMarcaciones.innerHTML = '<tr><td colspan="5">No hay marcaciones recientes.</td></tr>';
            return;
        }

        const filas = listaMarcaciones.map((item) => {
            const trabajador = item.trabajador || 'Sin nombre';
            const fecha = formatearFechaISO(item.fecha_marcacion);
            const entrada = formatearHora(item.hora_entrada);
            const salida = formatearHora(item.hora_salida);
            const estado = item.estado || 'Sin estado';

            return `<tr>
                <td>${trabajador}</td>
                <td>${fecha}</td>
                <td>${entrada}</td>
                <td>${salida}</td>
                <td>${estado}</td>
            </tr>`;
        }).join('');

        tablaUltimasMarcaciones.innerHTML = filas;
    }

    function pintarProyectosProximos(listaProyectos) {
        if (!contenedorProyectosProximos) return;

        if (!Array.isArray(listaProyectos) || listaProyectos.length === 0) {
            contenedorProyectosProximos.innerHTML = '<p class="texto-secundario">No hay proyectos próximos a vencer.</p>';
            return;
        }

        const items = listaProyectos.map((proyecto) => {
            const icono = proyecto.alerta === 'critica' ? '🔴' : (proyecto.alerta === 'advertencia' ? '🟡' : '🟢');
            const clase = `proyecto-item alerta-${proyecto.alerta}`;
            const diasTexto = proyecto.dias_para_vencer === 0 ? 'Hoy' : (proyecto.dias_para_vencer === 1 ? 'Mañana' : `En ${proyecto.dias_para_vencer} días`);
            const fecha = formatearFechaISO(proyecto.fecha_proyecto);
            const progreso = proyecto.total_colaboradores > 0 
                ? Math.round((proyecto.colaboradores_terminados / proyecto.total_colaboradores) * 100) 
                : 0;

            return `<div class="${clase}">
                <div class="proyecto-encabezado">
                    <span class="icono-alerta">${icono}</span>
                    <h3>${proyecto.nombre}</h3>
                </div>
                <div class="proyecto-detalles">
                    <p><strong>Vence:</strong> ${fecha} (${diasTexto})</p>
                    <p><strong>Progreso:</strong> ${proyecto.colaboradores_terminados}/${proyecto.total_colaboradores} colaboradores (${progreso}%)</p>
                </div>
            </div>`;
        }).join('');

        contenedorProyectosProximos.innerHTML = items;
    }

    async function cargarDashboard() {
        pintarCargando();

        try {
            const endpointDashboard = window.DASHBOARD_ENDPOINT || '../../controlador/controlador.DashboardAdmin.php?accion=resumen';
            const respuesta = await fetch(endpointDashboard, {
                credentials: 'same-origin',
                cache: 'no-store',
            });

            if (!respuesta.ok) {
                throw new Error('No se pudo obtener el resumen del dashboard.');
            }

            const data = await respuesta.json();
            const kpis = data.kpis || {};

            if (kpiUsuariosActivos) kpiUsuariosActivos.textContent = String(kpis.usuarios_activos ?? 0);
            if (kpiProyectosActivos) kpiProyectosActivos.textContent = String(kpis.proyectos_en_progreso ?? 0);
            if (kpiMarcacionesHoy) kpiMarcacionesHoy.textContent = String(kpis.marcaciones_hoy ?? 0);
            if (kpiMontoPlanillasMes) {
                kpiMontoPlanillasMes.textContent = formateadorMoneda.format(Number(kpis.monto_planillas_mes ?? 0));
            }

            if (resumenMarcaciones) {
                const abiertas = Number(kpis.marcaciones_abiertas_hoy ?? 0);
                const cerradas = Number(kpis.marcaciones_cerradas_hoy ?? 0);
                resumenMarcaciones.textContent = `Abiertas: ${abiertas} | Cerradas: ${cerradas}`;
            }

            pintarMarcaciones(data.ultimas_marcaciones || []);
            pintarProyectosProximos(data.proyectos_proximos || []);
        } catch (error) {
            if (kpiUsuariosActivos) kpiUsuariosActivos.textContent = '0';
            if (kpiProyectosActivos) kpiProyectosActivos.textContent = '0';
            if (kpiMarcacionesHoy) kpiMarcacionesHoy.textContent = '0';
            if (kpiMontoPlanillasMes) kpiMontoPlanillasMes.textContent = formateadorMoneda.format(0);
            if (resumenMarcaciones) resumenMarcaciones.textContent = 'No disponible';
            if (tablaUltimasMarcaciones) {
                tablaUltimasMarcaciones.innerHTML = '<tr><td colspan="5">No se pudo cargar la información.</td></tr>';
            }

            console.error(error);
        }
    }

    if (hayDashboardAdmin && botonRefrescar) {
        botonRefrescar.addEventListener('click', cargarDashboard);
    }

    if (hayDashboardAdmin) {
        cargarDashboard();
    }
});