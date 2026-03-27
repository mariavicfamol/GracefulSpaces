document.addEventListener('DOMContentLoaded', function() {
    const cantidadHoras = document.getElementById('cantidadHoras');
    const tarifaHora = document.getElementById('tarifaHora');
    const montoTotal = document.getElementById('montoTotal');

    // Calcular monto total automáticamente
    function calcularMontoTotal() {
        const horas = parseFloat(cantidadHoras.value) || 0;
        const tarifa = parseFloat(tarifaHora.value) || 0;
        const total = horas * tarifa;
        montoTotal.value = total > 0 ? '$' + total.toFixed(2) : '';
    }

    if (cantidadHoras && tarifaHora) {
        cantidadHoras.addEventListener('change', calcularMontoTotal);
        cantidadHoras.addEventListener('input', calcularMontoTotal);
        tarifaHora.addEventListener('change', calcularMontoTotal);
        tarifaHora.addEventListener('input', calcularMontoTotal);
    }

    // Permitir solo números en campos numéricos
    if (cantidadHoras) {
        cantidadHoras.addEventListener('keypress', function(e) {
            const char = e.key;
            if (!/[0-9.,]/.test(char) && e.key !== 'Backspace' && e.key !== 'Delete') {
                e.preventDefault();
            }
        });
    }

    if (tarifaHora) {
        tarifaHora.addEventListener('keypress', function(e) {
            const char = e.key;
            if (!/[0-9.,]/.test(char) && e.key !== 'Backspace' && e.key !== 'Delete') {
                e.preventDefault();
            }
        });
    }

    // Cerrar alertas automáticamente
    const alertas = document.querySelectorAll('.alerta');
    alertas.forEach(alerta => {
        setTimeout(() => {
            alerta.style.opacity = '0';
            alerta.style.transition = 'opacity 0.3s';
            setTimeout(() => {
                alerta.style.display = 'none';
            }, 300);
        }, 5000);
    });
});
