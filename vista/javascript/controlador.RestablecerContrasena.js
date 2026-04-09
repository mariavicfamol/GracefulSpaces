document.addEventListener('DOMContentLoaded', () => {
    const formulario = document.getElementById('formulario-reset');

    formulario.addEventListener('submit', function (evento) {
        const correo = document.getElementById('correo_usuario').value.trim();
        const fechaNacimiento = document.getElementById('fecha_nacimiento').value.trim();
        const nuevaClave = document.getElementById('nueva_clave').value;
        const confirmarClave = document.getElementById('confirmar_clave').value;
        const patronFecha = /^\d{4}-\d{2}-\d{2}$/;

        if (!correo || !fechaNacimiento || !nuevaClave || !confirmarClave) {
            evento.preventDefault();
            alert('Complete todos los campos para continuar.');
            return;
        }

        if (!patronFecha.test(fechaNacimiento)) {
            evento.preventDefault();
            alert('La fecha de nacimiento no es valida.');
            return;
        }

        if (nuevaClave.length < 8) {
            evento.preventDefault();
            alert('La nueva contraseña debe tener al menos 8 caracteres.');
            return;
        }

        if (nuevaClave !== confirmarClave) {
            evento.preventDefault();
            alert('Las contraseñas no coinciden.');
        }
    });
});