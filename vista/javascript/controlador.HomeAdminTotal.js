document.addEventListener('DOMContentLoaded', () => {

    // Referencias de elementos en español
    const botonSalir = document.getElementById('enlaceSalir');
    const logoImagen = document.getElementById('logoCorporativo');
    const tituloPrincipal = document.getElementById('textoSaludo');

    /**
     * Manejo del cierre de sesión
     */
    if (botonSalir) {
        botonSalir.addEventListener('click', (evento) => {
            const confirmarSalida = confirm("¿Está seguro de que desea finalizar su sesión administrativa?");
            
            if (!confirmarSalida) {
                evento.preventDefault(); // Cancela la redirección si el usuario dice NO
            } else {
                console.log("Cerrando sesión y limpiando datos locales...");
                // Aquí podrías añadir: localStorage.clear(); o sessionStorage.clear();
            }
        });
    }

    /**
     * Validación de carga de logo
     */
    if (logoImagen) {
        logoImagen.onerror = function() {
            console.warn("El logo de la empresa no se encontró en la ruta especificada.");
            this.style.display = 'none'; // Oculta el icono de imagen rota si falla
        };
    }

    /**
     * Efecto de bienvenida personalizado (opcional)
     * Podrías obtener el nombre del usuario desde un localStorage
     */
    const nombreUsuario = localStorage.getItem('usuarioActual') || 'Administrador';
    if (tituloPrincipal) {
        // Ejemplo de personalización dinámica
        // tituloPrincipal.textContent = `Bienvenido de nuevo, ${nombreUsuario}`;
    }

    // Registro de actividad en consola
    console.log("Panel de Administración Total cargado correctamente.");
});