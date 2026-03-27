document.addEventListener('DOMContentLoaded', function() {
    const formulario = document.getElementById('formularioPlanilla');
    const cantidadHoras = document.getElementById('cantidadHoras');
    const tarifaHora = document.getElementById('tarifaHora');
    const montoTotal = document.getElementById('montoTotal');
    const periodoInicio = document.getElementById('periodoInicio');
    const periodoFin = document.getElementById('periodoFin');

    // Calcular monto total automáticamente
    function calcularMontoTotal() {
        const horas = parseFloat(cantidadHoras.value) || 0;
        const tarifa = parseFloat(tarifaHora.value) || 0;
        const total = horas * tarifa;
        montoTotal.value = total > 0 ? total.toFixed(2) : '';
    }

    cantidadHoras.addEventListener('change', calcularMontoTotal);
    cantidadHoras.addEventListener('input', calcularMontoTotal);
    tarifaHora.addEventListener('change', calcularMontoTotal);
    tarifaHora.addEventListener('input', calcularMontoTotal);

    // Validar que fecha inicio no sea mayor a fecha fin
    function validarFechas() {
        if (periodoInicio.value && periodoFin.value) {
            if (new Date(periodoInicio.value) > new Date(periodoFin.value)) {
                alert('La fecha de inicio no puede ser posterior a la fecha de fin');
                periodoFin.value = '';
            }
        }
    }

    periodoInicio.addEventListener('change', validarFechas);
    periodoFin.addEventListener('change', validarFechas);

    // Validación del formulario
    formulario.addEventListener('submit', function(e) {
        const trabajador = document.getElementById('idTrabajador').value;
        const horas = parseFloat(cantidadHoras.value);
        const tarifa = parseFloat(tarifaHora.value);

        if (!trabajador) {
            e.preventDefault();
            alert('Por favor, selecciona un trabajador');
            return;
        }

        if (!periodoInicio.value || !periodoFin.value) {
            e.preventDefault();
            alert('Por favor, completa las fechas del período');
            return;
        }

        if (horas <= 0) {
            e.preventDefault();
            alert('La cantidad de horas debe ser mayor a 0');
            return;
        }

        if (tarifa <= 0) {
            e.preventDefault();
            alert('La tarifa por hora debe ser mayor a 0');
            return;
        }
    });

    // Permitir solo números en campos numéricos
    [cantidadHoras, tarifaHora].forEach(field => {
        field.addEventListener('keypress', function(e) {
            const char = e.key;
            if (!/[0-9.,]/.test(char) && e.key !== 'Backspace' && e.key !== 'Delete') {
                e.preventDefault();
            }
        });
    });
});
