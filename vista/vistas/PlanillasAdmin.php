<?php
session_start();
require_once __DIR__ . '/../../modelo/ModeloPlanilla.php';

if (empty($_SESSION['usuario'])) {
    header('Location: Login.php');
    exit;
}

$usuario = $_SESSION['usuario'];
$rol = $usuario['rol'] ?? '';

if (!in_array($rol, ['Administrador Total', 'Administrador'], true)) {
    header('Location: HomeAdminTotal.php');
    exit;
}

$anio = (int)($_GET['anio'] ?? date('Y'));
$mes = (int)($_GET['mes'] ?? date('n'));
$idTrabajador = (int)($_GET['trabajador'] ?? 0);

$trabajadores = ModeloPlanilla::obtenerTrabajadoresActivos();
$planillas = ModeloPlanilla::obtenerPlanillasAdmin($anio > 0 ? $anio : null, $mes > 0 ? $mes : null, $idTrabajador > 0 ? $idTrabajador : null);
$cssVersion = @filemtime(__DIR__ . '/../styles/style.PlanillasAdmin.css') ?: time();

$error = $_SESSION['error_planilla_admin'] ?? '';
$exito = $_SESSION['exito_planilla_admin'] ?? '';
unset($_SESSION['error_planilla_admin'], $_SESSION['exito_planilla_admin']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planillas Mensuales | Administración</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/style.PlanillasAdmin.css?v=<?= (int)$cssVersion ?>">
</head>
<body>

<div class="navegacion-superior">
    <a href="HomeAdminTotal.php" class="btn-volver">← Volver al Panel</a>
</div>

<main class="contenedor-principal">
    <header>
        <h1>Gestión de Planillas</h1>
        <p>Genera planillas mensuales automáticas con base en entradas y salidas registradas.</p>
    </header>

    <?php if ($error): ?>
        <div class="mensaje mensaje-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($exito): ?>
        <div class="mensaje mensaje-exito"><?= htmlspecialchars($exito) ?></div>
    <?php endif; ?>

    <section class="tarjeta tarjeta-generar">
        <h2>Generar Planillas del Mes</h2>
        <form method="POST" action="../../controlador/controlador.Planillas.php" class="grid-generacion">
            <input type="hidden" name="accion" value="generar">

            <div class="grupo-campo">
                <label for="anio">Año</label>
                <input type="number" id="anio" name="anio" min="2000" max="2100" value="<?= $anio ?>" required>
            </div>

            <div class="grupo-campo">
                <label for="mes">Mes</label>
                <input type="number" id="mes" name="mes" min="1" max="12" value="<?= $mes ?>" required>
            </div>

            <div class="grupo-campo">
                <label for="tarifa_hora">Tarifa por Hora (USD)</label>
                <input type="number" step="0.01" id="tarifa_hora" name="tarifa_hora" min="0.01" value="20.00" required>
            </div>

            <div class="grupo-campo grupo-campo-completo">
                <label>Bonos por empleado (opcional)</label>
                <small>Ingresa solo a quien quieras asignar bono. Si dejas en blanco o en 0.00, no se aplica bono.</small>

                <div class="tabla-bonos-contenedor">
                    <table class="tabla-bonos">
                        <thead>
                            <tr>
                                <th>Empleado</th>
                                <th>ID Empresa</th>
                                <th>Bono (USD)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($trabajadores as $trabajador): ?>
                                <tr>
                                    <td><?= htmlspecialchars($trabajador['nombre'] . ' ' . $trabajador['apellido1'] . ' ' . $trabajador['apellido2']) ?></td>
                                    <td><?= htmlspecialchars($trabajador['id_empresa']) ?></td>
                                    <td>
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            name="bonos_individuales[<?= (int)$trabajador['id'] ?>]"
                                            placeholder="0.00">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="acciones-generacion">
                <button type="submit" class="btn-principal">Generar / Actualizar Planillas</button>
            </div>
        </form>
    </section>

    <section class="tarjeta tarjeta-filtros">
        <h2>Consultar Planillas</h2>
        <form method="GET" class="grid-filtros">
            <div class="grupo-campo">
                <label for="f_anio">Año</label>
                <input type="number" id="f_anio" name="anio" min="2000" max="2100" value="<?= $anio ?>">
            </div>

            <div class="grupo-campo">
                <label for="f_mes">Mes</label>
                <input type="number" id="f_mes" name="mes" min="1" max="12" value="<?= $mes ?>">
            </div>

            <div class="grupo-campo">
                <label for="trabajador">Empleado</label>
                <select id="trabajador" name="trabajador">
                    <option value="0">Todos</option>
                    <?php foreach ($trabajadores as $trabajador): ?>
                        <option value="<?= (int)$trabajador['id'] ?>" <?= $idTrabajador === (int)$trabajador['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($trabajador['nombre'] . ' ' . $trabajador['apellido1'] . ' ' . $trabajador['apellido2']) ?>
                            (<?= htmlspecialchars($trabajador['id_empresa']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="acciones-filtro">
                <button type="submit" class="btn-principal">Aplicar Filtros</button>
                <a href="PlanillasAdmin.php" class="btn-secundario">Limpiar</a>
            </div>
        </form>
    </section>

    <section class="tarjeta tarjeta-tabla">
        <h2>Planillas Generadas</h2>
        <div class="tabla-contenedor">
            <table>
                <thead>
                    <tr>
                        <th>Empleado</th>
                        <th>ID Empresa</th>
                        <th>Periodo</th>
                        <th>Horas Totales</th>
                        <th>Tarifa/Hora</th>
                        <th>Bono</th>
                        <th>Monto Total</th>
                        <th>Generada</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($planillas)): ?>
                        <tr>
                            <td colspan="9">No hay planillas para los filtros seleccionados.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($planillas as $planilla): ?>
                            <tr>
                                <td><?= htmlspecialchars($planilla['trabajador']) ?></td>
                                <td><?= htmlspecialchars($planilla['id_empresa']) ?></td>
                                <td><?= htmlspecialchars($planilla['anio'] . '-' . str_pad((string)$planilla['mes'], 2, '0', STR_PAD_LEFT)) ?></td>
                                <td><?= number_format((float)$planilla['horas_totales'], 2) ?></td>
                                <td>$<?= number_format((float)$planilla['tarifa_hora'], 2) ?></td>
                                <td>$<?= number_format((float)($planilla['bono_manual'] ?? 0), 2) ?></td>
                                <td>$<?= number_format((float)$planilla['monto_total'], 2) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($planilla['fecha_generacion'])) ?></td>
                                <td>
                                    <div class="acciones-planilla">
                                        <?php if ((int)($planilla['aprobada'] ?? 0) !== 1): ?>
                                            <form method="POST" action="../../controlador/controlador.Planillas.php" class="form-aprobar">
                                                <input type="hidden" name="accion" value="aprobar">
                                                <input type="hidden" name="id_planilla" value="<?= (int)$planilla['id'] ?>">
                                                <input type="hidden" name="anio" value="<?= $anio ?>">
                                                <input type="hidden" name="mes" value="<?= $mes ?>">
                                                <input type="hidden" name="trabajador" value="<?= $idTrabajador ?>">
                                                <button type="submit" class="btn-accion">Aprobar Nómina</button>
                                            </form>
                                        <?php else: ?>
                                            <a class="btn-accion" href="../../controlador/controlador.Planillas.php?accion=descargar&id=<?= (int)$planilla['id'] ?>">
                                                Descargar Excel
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

</body>
</html>
