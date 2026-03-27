// El formulario envía directamente al controlador PHP
// Solo validación visual básica antes de enviar
document.getElementById('formulario-login').addEventListener('submit', function(evento) {
    const correo = document.getElementById('correo_usuario').value.trim();
    const clave  = document.getElementById('clave_usuario').value;

    if (!correo || !clave) {
        evento.preventDefault();
        alert('Por favor ingrese su correo y contraseña.');
    }
    // Si hay datos, deja que el form haga POST al PHP normalmente
});