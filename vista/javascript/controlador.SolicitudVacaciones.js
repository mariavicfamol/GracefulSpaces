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

    function generarCalendarioPreview(inicio, fin) {
        calendarioPreview.innerHTML = '';

        // Header con días de la semana
        const diasSemana = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sab'];
        const header = document.createElement('div');
        header.style.cssText = 'display: grid; grid-template-columns: repeat(7, 1fr); gap: 0.25rem; margin-bottom: 1rem;';

        diasSemana.forEach(dia => {
            const diaHeader = document.createElement('div');
            diaHeader.textContent = dia;
            diaHeader.style.cssText = 'text-align: center; font-weight: 600; color: #667eea; font-size: 0.85rem;';
            header.appendChild(diaHeader);
        });
        calendarioPreview.appendChild(header);

        // Generar calendario
        const calendario = document.createElement('div');
        calendario.className = 'calendario-preview';

        const primerDia = new Date(inicio.getFullYear(), inicio.getMonth(), 1);
        const ultimoDia = new Date(fin.getFullYear(), fin.getMonth() + 1, 0);

        // Días en blanco al inicio
        const inicioGridDia = primerDia.getDay();
        for (let i = 0; i < inicioGridDia; i++) {
            const diaVacio = document.createElement('div');
            diaVacio.style.cssText = 'background: #f9fafb;';
            calendario.appendChild(diaVacio);
        }

        // Recorrer todos los días del mes
        let fechaActual = new Date(primerDia);
        while (fechaActual <= ultimoDia) {
            const diaElement = document.createElement('div');
            diaElement.className = 'dia-calendario';
            diaElement.textContent = fechaActual.getDate();

            const fechaActualString = fechaActual.toISOString().split('T')[0];
            const inicioString = inicio.toISOString().split('T')[0];
            const finString = fin.toISOString().split('T')[0];

            if (fechaActualString >= inicioString && fechaActualString <= finString) {
                diaElement.classList.add('seleccionado');
            }

            calendario.appendChild(diaElement);
            fechaActual.setDate(fechaActual.getDate() + 1);
        }

        calendarioPreview.appendChild(calendario);
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
