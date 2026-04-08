function abrirModalAprobar(idSolicitud, nombreTrabajador) {
    document.getElementById('idSolicitudAprobar').value = idSolicitud;
    document.getElementById('textoTrabajadorAprobar').textContent = 
        `¿Aprobar la solicitud de vacaciones de ${nombreTrabajador}?`;
    document.getElementById('modalAprobar').style.display = 'flex';
}

function abrirModalRechazar(idSolicitud, nombreTrabajador) {
    document.getElementById('idSolicitudRechazar').value = idSolicitud;
    document.getElementById('textoTrabajadorRechazar').textContent = 
        `¿Rechazar la solicitud de vacaciones de ${nombreTrabajador}?`;
    document.getElementById('modalRechazar').style.display = 'flex';
}

function cerrarModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Cerrar modal al hacer clic fuera de él
window.addEventListener('click', function(event) {
    const modales = document.querySelectorAll('.modal');
    modales.forEach(modal => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
});

// Prevenir envío de formularios vacíos
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const motivo = form.querySelector('textarea[name="motivo"]');
        if (motivo && !motivo.value.trim()) {
            e.preventDefault();
            alert('Por favor complete el campo requerido.');
            return false;
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
    console.log('Gestión de Solicitudes de Vacaciones cargado');
});
