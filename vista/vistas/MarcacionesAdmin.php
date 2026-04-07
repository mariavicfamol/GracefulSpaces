<?php
session_start();
require_once __DIR__ . '/../../modelo/ModeloMarcacion.php';

if (empty($_SESSION['usuario'])) {
    header('Location: Login.php');
    exit;
}

$usuario = $_SESSION['usuario'];
$rol = $usuario['rol'] ?? '';
$rolesPermitidos = ['Administrador Total', 'Administrador'];

if (!in_array($rol, $rolesPermitidos, true)) {
    header('Location: HomeAdminTotal.php');
    exit;
}

$idTrabajador = (int)($_GET['trabajador'] ?? 0);
$fechaInicio = trim($_GET['fecha_inicio'] ?? '');
$fechaFin = trim($_GET['fecha_fin'] ?? '');
$cssVersion = @filemtime(__DIR__ . '/../styles/style.MarcacionesAdmin.css') ?: time();

$trabajadores = ModeloMarcacion::obtenerTrabajadoresConMarcacion();
$registros = ModeloMarcacion::obtenerMarcacionesParaAdmin(
    $idTrabajador > 0 ? $idTrabajador : null,
    $fechaInicio !== '' ? $fechaInicio : null,
    $fechaFin !== '' ? $fechaFin : null
);

function calcularHorasLaboradas(?string $entrada, ?string $salida): string {
    if (!$entrada || !$salida) {
        return '--';
    }

    try {
        $dtEntrada = new DateTime($entrada);
        $dtSalida = new DateTime($salida);

        if ($dtSalida < $dtEntrada) {
            return '--';
        }

        $segundos = $dtSalida->getTimestamp() - $dtEntrada->getTimestamp();
        $horas = floor($segundos / 3600);
        $minutos = floor(($segundos % 3600) / 60);

        return sprintf('%02d:%02d', $horas, $minutos);
    } catch (Throwable $e) {
        return '--';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registros de Marcación | Administración</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/style.MarcacionesAdmin.css?v=<?= (int)$cssVersion ?>">
</head>
<body>

<div class="navegacion-superior">
    <a href="HomeAdminTotal.php" class="btn-volver">← Volver al Panel</a>
</div>

<main class="contenedor-principal">
    <header>
        <h1>Registros de Marcación</h1>
        <p>Consulta las horas de entrada y salida registradas por cada empleado.</p>
    </header>

    <section class="tarjeta-filtros">
        <form method="GET" class="filtros-grid">
            <div class="grupo-campo">
                <label for="trabajador">Empleado</label>
                <select id="trabajador" name="trabajador">
                    <option value="0">Todos</option>
                    <?php foreach ($trabajadores as $trabajador): ?>
                        <option value="<?= (int)$trabajador['id'] ?>" <?= $idTrabajador === (int)$trabajador['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($trabajador['trabajador']) ?> (<?= htmlspecialchars($trabajador['id_empresa']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grupo-campo">
                <label for="fecha_inicio">Desde</label>
                <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?= htmlspecialchars($fechaInicio) ?>">
            </div>

            <div class="grupo-campo">
                <label for="fecha_fin">Hasta</label>
                <input type="date" id="fecha_fin" name="fecha_fin" value="<?= htmlspecialchars($fechaFin) ?>">
            </div>

            <div class="acciones-filtro">
                <button type="submit" class="btn-aplicar">Aplicar Filtros</button>
                <a href="MarcacionesAdmin.php" class="btn-limpiar">Limpiar</a>
            </div>
        </form>
    </section>

    <section class="tarjeta-tabla">
        <div class="tabla-contenedor">
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Empleado</th>
                        <th>ID Empresa</th>
                        <th>Entrada</th>
                        <th>Salida</th>
                        <th>Horas</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($registros)): ?>
                        <tr>
                            <td colspan="7">No se encontraron marcaciones con los filtros aplicados.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($registros as $registro): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($registro['fecha_marcacion'])) ?></td>
                                <td><?= htmlspecialchars($registro['trabajador']) ?></td>
                                <td><?= htmlspecialchars($registro['id_empresa']) ?></td>
                                <td><?= !empty($registro['hora_entrada']) ? date('H:i:s', strtotime($registro['hora_entrada'])) : '--:--:--' ?></td>
                                <td><?= !empty($registro['hora_salida']) ? date('H:i:s', strtotime($registro['hora_salida'])) : '--:--:--' ?></td>
                                <td><?= calcularHorasLaboradas($registro['hora_entrada'] ?? null, $registro['hora_salida'] ?? null) ?></td>
                                <td><?= htmlspecialchars($registro['estado']) ?></td>
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
