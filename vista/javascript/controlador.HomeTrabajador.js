const formSalida = document.getElementById('formSalida');

if (formSalida) {
    formSalida.addEventListener('submit', function (evento) {
        const confirmar = window.confirm('Confirmar registro de salida para hoy?');
        if (!confirmar) {
            evento.preventDefault();
        }
    });
}
