<?php
session_start();
require_once __DIR__ . '/../../modelo/ModeloPlanilla.php';

if (empty($_SESSION['usuario'])) {
    header('Location: Login.php');
    exit;
}

$usuario = $_SESSION['usuario'];
$planillas = ModeloPlanilla::obtenerPlanillas();
$planillaEdicion = null;

if (isset($_GET['id'])) {
    $id = (int)($_GET['id']);
    $planillaEdicion = ModeloPlanilla::obtenerPlanillaPorId($id);
}

$error = $_SESSION['error_editar_planilla'] ?? null;
$exito = $_SESSION['exito_editar_planilla'] ?? null;
if ($error) unset($_SESSION['error_editar_planilla']);
if ($exito) unset($_SESSION['exito_editar_planilla']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Planillas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/style.EditarPlanillas.css">
</head>
<body>

    <div class="navegacion-superior">
        <a href="HomeAdminTotal.php" class="btn-volver">← Volver al Panel</a>
    </div>

    <div class="contenedor-principal">
        <header>
            <h1><?= $planillaEdicion ? 'Editar Planilla' : 'Gestión de Planillas' ?></h1>
            <p><?= $planillaEdicion ? 'Actualiza los datos de la planilla seleccionada' : 'Selecciona una planilla para editar' ?></p>
        </header>

        <?php if ($error): ?>
            <div class="alerta alerta-error">
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($exito): ?>
            <div class="alerta alerta-exito">
                <span><?= htmlspecialchars($exito) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($planillaEdicion): ?>
            <form method="POST" action="../../controlador/controlador.EditarPlanillas.php">
                <input type="hidden" name="accion" value="actualizar">
                <input type="hidden" name="idPlanilla" value="<?= $planillaEdicion['id'] ?>">

                <div class="titulo-seccion">1. Información de la Planilla</div>
                <div class="rejilla-formulario">
                    <div class="grupo-campo ancho-completo">
                        <label>ID Planilla</label>
                        <input type="text" value="<?= htmlspecialchars($planillaEdicion['id_planilla']) ?>" readonly>
                    </div>
                    <div class="grupo-campo ancho-completo">
                        <label>Trabajador</label>
                        <input type="text" value="<?= htmlspecialchars($planillaEdicion['trabajador']) ?>" readonly>
                    </div>
                    <div class="grupo-campo">
                        <label>Período Inicio</label>
                        <input type="date" value="<?= $planillaEdicion['periodo_inicio'] ?>" readonly>
                    </div>
                    <div class="grupo-campo">
                        <label>Período Fin</label>
                        <input type="date" value="<?= $planillaEdicion['periodo_fin'] ?>" readonly>
                    </div>
                </div>

                <div class="titulo-seccion">2. Datos Editables</div>
                <div class="rejilla-formulario">
                    <div class="grupo-campo">
                        <label>Cantidad de Horas</label>
                        <input type="number" id="cantidadHoras" name="cantidadHoras" step="0.01" min="0" value="<?= $planillaEdicion['cantidad_horas'] ?>" required>
                    </div>
                    <div class="grupo-campo">
                        <label>Tarifa por Hora (USD)</label>
                        <input type="number" id="tarifaHora" name="tarifaHora" step="0.01" min="0" value="<?= $planillaEdicion['tarifa_hora'] ?>" required>
                    </div>
                    <div class="grupo-campo ancho-completo">
                        <label>Monto Total (USD)</label>
                        <input type="text" id="montoTotal" value="<?= number_format($planillaEdicion['monto_total'], 2) ?>" readonly>
                    </div>
                    <div class="grupo-campo">
                        <label>Estado</label>
                        <select name="estado" required>
                            <option value="Pendiente" <?= $planillaEdicion['estado'] === 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                            <option value="Aprobada" <?= $planillaEdicion['estado'] === 'Aprobada' ? 'selected' : '' ?>>Aprobada</option>
                            <option value="Pagada" <?= $planillaEdicion['estado'] === 'Pagada' ? 'selected' : '' ?>>Pagada</option>
                            <option value="Cancelada" <?= $planillaEdicion['estado'] === 'Cancelada' ? 'selected' : '' ?>>Cancelada</option>
                        </select>
                    </div>
                    <div class="grupo-campo ancho-completo">
                        <label>Notas</label>
                        <textarea name="notas" rows="4"><?= htmlspecialchars($planillaEdicion['notas'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="acciones-finales">
                    <a href="EditarPlanillas.php" class="btn-cancelar">Volver a Listado</a>
                    <button type="submit" class="btn-guardar">Actualizar Planilla</button>
                </div>
            </form>
        <?php else: ?>
            <div class="titulo-seccion">Lista de Planillas</div>
            <?php if (!empty($planillas)): ?>
            <div class="tabla-responsiva">
                <table>
                    <thead>
                        <tr>
                            <th>ID Planilla</th>
                            <th>Trabajador</th>
                            <th>Período</th>
                            <th>Horas</th>
                            <th>Tarifa (USD)</th>
                            <th>Total (USD)</th>
                            <th>Estado</th>
                            <th>Opción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($planillas as $planilla): ?>
                            <tr>
                                <td><?= htmlspecialchars($planilla['id_planilla']) ?></td>
                                <td><?= htmlspecialchars($planilla['trabajador']) ?></td>
                                <td>
                                    <?= date('d/m/Y', strtotime($planilla['periodo_inicio'])) ?>
                                    -
                                    <?= date('d/m/Y', strtotime($planilla['periodo_fin'])) ?>
                                </td>
                                <td><?= number_format($planilla['cantidad_horas'], 2) ?></td>
                                <td>$<?= number_format($planilla['tarifa_hora'], 2) ?></td>
                                <td>$<?= number_format($planilla['monto_total'], 2) ?></td>
                                <td>
                                    <span class="badge-estado badge-<?= strtolower(str_replace(' ', '-', $planilla['estado'])) ?>">
                                        <?= htmlspecialchars($planilla['estado']) ?>
                                    </span>
                                </td>
                                <td class="acciones-tabla">
                                    <a href="?id=<?= $planilla['id'] ?>" class="btn-accion">✎</a>
                                    <?php if ($planilla['estado'] !== 'Cancelada'): ?>
                                        <form method="POST" action="../../controlador/controlador.EditarPlanillas.php" style="display:inline;">
                                            <input type="hidden" name="accion" value="cancelar">
                                            <input type="hidden" name="idPlanilla" value="<?= $planilla['id'] ?>">
                                            <button type="submit" class="btn-accion btn-eliminar" title="Cancelar" 
                                                    onclick="return confirm('¿Está seguro que desea cancelar esta planilla?');">✕</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="mensaje-vacio">
                <h3>No hay planillas registradas</h3>
                <p>Crea tu primera planilla para comenzar a gestionar los pagos.</p>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script src="../javascript/controlador.EditarPlanillas.js"></script>
</body>
</html>
