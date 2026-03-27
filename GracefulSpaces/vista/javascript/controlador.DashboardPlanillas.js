document.addEventListener('DOMContentLoaded', function() {
    // Cerrar alertas automáticamente
    const alertas = document.querySelectorAll('.alerta');
    alertas.forEach(alerta => {
        setTimeout(() => {
            alerta.style.opacity = '0';
            alerta.style.transition = 'opacity 0.3s';
            setTimeout(() => {
                alerta.style.display = 'none';
            }, 300);
        }, 4000);
    });

    // Efecto hover en filas de tabla
    const filas = document.querySelectorAll('tbody tr');
    filas.forEach(fila => {
        fila.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#f0f4ff';
        });
        fila.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    });

    // Animación suave al cargar
    const tarjetas = document.querySelectorAll('.tarjeta-estadistica');
    tarjetas.forEach((tarjeta, index) => {
        tarjeta.style.opacity = '0';
        tarjeta.style.transform = 'translateY(20px)';
        setTimeout(() => {
            tarjeta.style.transition = 'all 0.3s ease';
            tarjeta.style.opacity = '1';
            tarjeta.style.transform = 'translateY(0)';
        }, index * 100);
    });
});
