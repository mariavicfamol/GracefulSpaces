<?php
session_start();
require_once __DIR__ . '/../../modelo/ModeloPlanilla.php';

if (empty($_SESSION['usuario'])) {
    header('Location: Login.php');
    exit;
}

$usuario = $_SESSION['usuario'];
$trabajadores = ModeloPlanilla::obtenerTrabajadoresActivos();
$error = $_SESSION['error_crear_planilla'] ?? null;
if ($error) unset($_SESSION['error_crear_planilla']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Nueva Planilla</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/style.CrearPlanilla.css">
</head>
<body>

    <div class="navegacion-superior">
        <a href="HomeAdminTotal.php" class="btn-volver">← Volver al Panel</a>
    </div>

    <div class="contenedor-principal">
        <header>
            <h1>Crear Nueva Planilla</h1>
            <p>Completa los datos para crear una nueva planilla de pago</p>
        </header>

        <?php if ($error): ?>
            <div class="alerta alerta-error">
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form id="formularioPlanilla" method="POST" action="../../controlador/controlador.CrearPlanilla.php">

            <div class="titulo-seccion">1. Información Básica</div>
            <div class="rejilla-formulario">
                <div class="grupo-campo ancho-completo">
                    <label>Trabajador</label>
                    <select id="idTrabajador" name="idTrabajador" required>
                        <option value="">-- Selecciona un trabajador --</option>
                        <?php foreach ($trabajadores as $trabajador): ?>
                            <option value="<?= $trabajador['id'] ?>">
                                <?= htmlspecialchars($trabajador['nombre'] . ' ' . $trabajador['apellido1'] . ' ' . ($trabajador['apellido2'] ?? '')) ?>
                                (<?= htmlspecialchars($trabajador['id_empresa']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="grupo-campo">
                    <label>Fecha Inicio</label>
                    <input type="date" id="periodoInicio" name="periodoInicio" required>
                </div>
                <div class="grupo-campo">
                    <label>Fecha Fin</label>
                    <input type="date" id="periodoFin" name="periodoFin" required>
                </div>
            </div>

            <div class="titulo-seccion">2. Detalles de Pago</div>
            <div class="rejilla-formulario">
                <div class="grupo-campo">
                    <label>Cantidad de Horas</label>
                    <input type="number" id="cantidadHoras" name="cantidadHoras" step="0.01" min="0" required placeholder="Ej: 40.00">
                </div>
                <div class="grupo-campo">
                    <label>Tarifa por Hora (USD)</label>
                    <input type="number" id="tarifaHora" name="tarifaHora" step="0.01" min="0" required placeholder="Ej: 15.50">
                </div>
                <div class="grupo-campo ancho-completo">
                    <label>Monto Total (USD)</label>
                    <input type="text" id="montoTotal" name="montoTotal" readonly placeholder="Se calcula automáticamente">
                </div>
            </div>

            <div class="titulo-seccion">3. Información Adicional</div>
            <div class="rejilla-formulario">
                <div class="grupo-campo">
                    <label>Estado</label>
                    <select name="estado" required>
                        <option value="Pendiente" selected>Pendiente</option>
                        <option value="Aprobada">Aprobada</option>
                        <option value="Pagada">Pagada</option>
                    </select>
                </div>
                <div class="grupo-campo ancho-completo">
                    <label>Notas</label>
                    <textarea name="notas" rows="4" placeholder="Observaciones o comentarios adicionales..."></textarea>
                </div>
            </div>

            <div class="acciones-finales">
                <button type="reset" class="btn-cancelar">Limpiar</button>
                <button type="submit" class="btn-guardar">Crear Planilla</button>
            </div>
        </form>
    </div>

    <script src="../javascript/controlador.CrearPlanilla.js"></script>
</body>
</html>
