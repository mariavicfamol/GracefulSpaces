document.addEventListener('DOMContentLoaded', function() {
    const fechaInicio = document.getElementById('fechaInicio');
    const fechaFin = document.getElementById('fechaFin');
    const diasSolicitados = document.getElementById('diasSolicitados');
    const calendarioPreview = document.getElementById('calendarioPreview');

    // Establecer fecha mínima como hoy
    const hoy = new Date();
    const hoyString = hoy.toISOString().split('T')[0];
    fechaInicio.min = hoyString;
    fechaFin.min = hoyString;

    // Actualizar días cuando cambian las fechas
    fechaInicio.addEventListener('change', actualizarDias);
    fechaFin.addEventListener('change', actualizarDias);

    function actualizarDias() {
        const inicio = new Date(fechaInicio.value);
        const fin = new Date(fechaFin.value);

        if (!fechaInicio.value || !fechaFin.value) {
            diasSolicitados.value = '';
            calendarioPreview.innerHTML = '';
            return;
        }

        if (fin < inicio) {
            diasSolicitados.value = '';
            return;
        }

        const diferencia = Math.floor((fin - inicio) / (1000 * 60 * 60 * 24)) + 1;

        if (diferencia > 0 && diferencia <= 30) {
            diasSolicitados.value = diferencia;
            generarCalendarioPreview(inicio, fin);
        } else {
            diasSolicitados.value = '';
            calendarioPreview.innerHTML = '';
        }
    }

    function normalizarFecha(fecha) {
        const anio = fecha.getFullYear();
        const mes = String(fecha.getMonth() + 1).padStart(2, '0');
        const dia = String(fecha.getDate()).padStart(2, '0');
        return `${anio}-${mes}-${dia}`;
    }

    function formatearFecha(fecha) {
        return fecha.toLocaleDateString('es-ES', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        });
    }

    function obtenerNombreMes(fecha) {
        return fecha.toLocaleDateString('es-ES', {
            month: 'long',
            year: 'numeric'
        });
    }

    function generarCalendarioPreview(inicio, fin) {
        calendarioPreview.innerHTML = '';

        const diasPeriodo = Math.floor((fin - inicio) / (1000 * 60 * 60 * 24)) + 1;

        const resumen = document.createElement('div');
        resumen.className = 'calendario-resumen';

        const resumenTexto = document.createElement('strong');
        resumenTexto.textContent = `${formatearFecha(inicio)} - ${formatearFecha(fin)}`;

        const resumenDias = document.createElement('span');
        resumenDias.textContent = `${diasPeriodo} día${diasPeriodo === 1 ? '' : 's'} solicitados`;

        resumen.appendChild(resumenTexto);
        resumen.appendChild(resumenDias);
        calendarioPreview.appendChild(resumen);

        const leyenda = document.createElement('div');
        leyenda.className = 'leyenda-calendario';
        leyenda.innerHTML = `
            <span class="leyenda-item"><i class="leyenda-color seleccionado"></i>Periodo solicitado</span>
            <span class="leyenda-item"><i class="leyenda-color fuera-rango"></i>Otros días del mes</span>
        `;
        calendarioPreview.appendChild(leyenda);

        const contenedorMeses = document.createElement('div');
        contenedorMeses.className = 'calendarios-meses';

        const diasSemana = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sab'];
        const inicioNormalizado = normalizarFecha(inicio);
        const finNormalizado = normalizarFecha(fin);

        const mesActual = new Date(inicio.getFullYear(), inicio.getMonth(), 1);
        const ultimoMes = new Date(fin.getFullYear(), fin.getMonth(), 1);

        while (mesActual <= ultimoMes) {
            const seccionMes = document.createElement('section');
            seccionMes.className = 'calendario-mes';

            const encabezadoMes = document.createElement('div');
            encabezadoMes.className = 'calendario-mes-header';

            const nombreMes = document.createElement('span');
            nombreMes.textContent = obtenerNombreMes(mesActual);

            const cantidadDias = document.createElement('small');
            cantidadDias.textContent = 'Vista mensual';

            encabezadoMes.appendChild(nombreMes);
            encabezadoMes.appendChild(cantidadDias);
            seccionMes.appendChild(encabezadoMes);

            const encabezadosSemana = document.createElement('div');
            encabezadosSemana.className = 'calendario-semana';
            diasSemana.forEach(dia => {
                const diaHeader = document.createElement('div');
                diaHeader.className = 'dia-semana';
                diaHeader.textContent = dia;
                encabezadosSemana.appendChild(diaHeader);
            });
            seccionMes.appendChild(encabezadosSemana);

            const grillaMes = document.createElement('div');
            grillaMes.className = 'calendario-grid';

            const primerDiaMes = new Date(mesActual.getFullYear(), mesActual.getMonth(), 1);
            const ultimoDiaMes = new Date(mesActual.getFullYear(), mesActual.getMonth() + 1, 0);

            for (let i = 0; i < primerDiaMes.getDay(); i++) {
                const diaVacio = document.createElement('div');
                diaVacio.className = 'dia-calendario vacio';
                grillaMes.appendChild(diaVacio);
            }

            let fechaActual = new Date(primerDiaMes);
            while (fechaActual <= ultimoDiaMes) {
                const diaElement = document.createElement('div');
                const fechaActualString = normalizarFecha(fechaActual);
                diaElement.className = 'dia-calendario';
                diaElement.textContent = fechaActual.getDate();

                if (fechaActualString < inicioNormalizado || fechaActualString > finNormalizado) {
                    diaElement.classList.add('fuera-rango');
                } else {
                    diaElement.classList.add('seleccionado');

                    if (fechaActualString === inicioNormalizado) {
                        diaElement.classList.add('rango-inicio');
                    }

                    if (fechaActualString === finNormalizado) {
                        diaElement.classList.add('rango-fin');
                    }
                }

                grillaMes.appendChild(diaElement);
                fechaActual.setDate(fechaActual.getDate() + 1);
            }

            seccionMes.appendChild(grillaMes);
            contenedorMeses.appendChild(seccionMes);
            mesActual.setMonth(mesActual.getMonth() + 1);
        }

        calendarioPreview.appendChild(contenedorMeses);
    }

    // Validación del formulario
    document.getElementById('formularioVacaciones').addEventListener('submit', function(e) {
        const inicio = new Date(fechaInicio.value);
        const fin = new Date(fechaFin.value);

        if (!fechaInicio.value || !fechaFin.value) {
            e.preventDefault();
            alert('Por favor complete las fechas de inicio y fin.');
            return;
        }

        if (fin < inicio) {
            e.preventDefault();
            alert('La fecha de fin no puede ser anterior a la fecha de inicio.');
            return;
        }

        if (diasSolicitados.value <= 0 || diasSolicitados.value > 30) {
            e.preventDefault();
            alert('El período debe estar entre 1 y 30 días.');
            return;
        }
    });
});
