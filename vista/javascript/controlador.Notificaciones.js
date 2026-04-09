document.addEventListener('DOMContentLoaded', () => {
    const btnMarcarTodas = document.querySelector('#formMarcarTodas button');
    const formsMarcarLeida = document.querySelectorAll('.form-marcar-leida');

    if (btnMarcarTodas) {
        btnMarcarTodas.addEventListener('click', async (event) => {
            event.preventDefault();
            const form = btnMarcarTodas.closest('form');
            if (!form) {
                return;
            }

            const formData = new FormData(form);
            formData.set('accion', 'marcarTodasLeidas');

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const result = await response.json();
                if (result.success) {
                    window.location.reload();
                }
            } catch (error) {
                console.error('No se pudo actualizar el estado de las notificaciones.', error);
            }
        });
    }

    formsMarcarLeida.forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const formData = new FormData(form);
            formData.set('accion', 'marcarLeida');

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    window.location.reload();
                }
            } catch (error) {
                console.error('No se pudo marcar la notificación como leída.', error);
            }
        });
    });
});
