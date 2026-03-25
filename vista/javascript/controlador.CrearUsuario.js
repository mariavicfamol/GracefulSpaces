document.addEventListener('DOMContentLoaded', () => {

    // ── 1. Cargar el próximo ID Empresa desde el servidor ──
    const campoID = document.querySelector('.campo-lectura');
    if (campoID) {
        fetch('../../controlador/controlador.ObtenerID.php')
            .then(r => r.json())
            .then(data => {
                if (data.id_empresa) {
                    campoID.value = data.id_empresa;
                }
            })
            .catch(() => {
                campoID.value = 'GS-' + new Date().getFullYear() + '-???';
            });
    }

    // ── 2. Previsualización de fotografía ──
    const entradaFoto   = document.getElementById('entradaFoto');
    const imagenPerfil  = document.getElementById('imagenPerfil');
    const textoMarcador = document.getElementById('textoMarcador');

    entradaFoto.addEventListener('change', function () {
        const archivo = this.files[0];
        if (archivo) {
            const lector = new FileReader();
            lector.onload = function (evento) {
                imagenPerfil.src = evento.target.result;
                imagenPerfil.style.display = 'block';
                if (textoMarcador) textoMarcador.style.display = 'none';
            };
            lector.readAsDataURL(archivo);
        }
    });

    // ── 3. Solo números en campos específicos ──
    const restringirSoloNumeros = (idCampo) => {
        const campo = document.getElementById(idCampo);
        if (campo) {
            campo.addEventListener('input', function () {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        }
    };

    restringirSoloNumeros('numeroIdentificacion');
    restringirSoloNumeros('telefono');
    restringirSoloNumeros('telefonoEmergencia');

    // ── 4. Generador de contraseña ──
    const botonGenerar = document.getElementById('botonGenerar');
    const campoPass    = document.getElementById('passGenerada');

    botonGenerar.addEventListener('click', () => {
        const caracteres   = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$';
        let nuevaContrasena = '';
        for (let i = 0; i < 12; i++) {
            nuevaContrasena += caracteres.charAt(Math.floor(Math.random() * caracteres.length));
        }
        campoPass.value = nuevaContrasena;
    });

    // ── 5. Validación antes de enviar ──
    const formulario = document.getElementById('formularioUsuario');
    formulario.addEventListener('submit', function (evento) {
        const pass     = document.getElementById('passGenerada').value;
        const confirma = document.getElementById('confirmarPass').value;

        if (pass && confirma && pass !== confirma) {
            evento.preventDefault();
            alert('Las contraseñas no coinciden. Por favor verifique.');
        }
    });
});