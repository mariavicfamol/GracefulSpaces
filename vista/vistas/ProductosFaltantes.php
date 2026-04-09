<?php
session_start();
require_once __DIR__ . '/../../modelo/ModeloProductoFaltante.php';

if (empty($_SESSION['usuario'])) {
    header('Location: Login.php');
    exit;
}

$usuario = $_SESSION['usuario'];
$rol = $usuario['rol'] ?? '';
$idUsuario = (int)($usuario['id'] ?? 0);

$esAdmin = in_array($rol, ['Administrador Total', 'Administrador'], true);
$esEmpleado = in_array($rol, ['Trabajador', 'Supervisor'], true);

if (!$esAdmin && !$esEmpleado) {
    header('Location: HomeAdminTotal.php');
    exit;
}

$productos = ModeloProductoFaltante::obtenerProductos();
$error = $_SESSION['error_producto'] ?? '';
$exito = $_SESSION['exito_producto'] ?? '';
unset($_SESSION['error_producto'], $_SESSION['exito_producto']);
$cssVersion = @filemtime(__DIR__ . '/../styles/style.ProductosFaltantes.css') ?: time();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos Faltantes</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/style.ProductosFaltantes.css?v=<?= (int)$cssVersion ?>">
</head>
<body>

<div class="navegacion-superior">
    <a href="HomeAdminTotal.php" class="btn-volver">← Volver</a>
</div>

<main class="contenedor-principal">
    <header>
        <div class="cabecera-titulo">
            <div>
                <h1>Productos Faltantes</h1>
                <p>Registra y gestiona los productos que necesitan ser comprados.</p>
            </div>
            <?php if ($esAdmin): ?>
                <a href="../../controlador/controlador.ProductosFaltantes.php?accion=descargar_pdf" class="btn-pdf" download rel="noopener">Descargar PDF</a>
            <?php endif; ?>
        </div>
    </header>

    <?php if ($error): ?>
        <div class="mensaje mensaje-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($exito): ?>
        <div class="mensaje mensaje-exito"><?= htmlspecialchars($exito) ?></div>
    <?php endif; ?>

    <section class="tarjeta tarjeta-registrar">
        <h2>Registrar Producto Faltante</h2>
        <form method="POST" action="../../controlador/controlador.ProductosFaltantes.php" class="formulario-producto">
            <input type="hidden" name="accion" value="crear">

            <div class="grupo-campo">
                <label for="nombre">Nombre del Producto *</label>
                <input type="text" id="nombre" name="nombre" required placeholder="Ej: Papel A4, Tóner negro, etc.">
            </div>

            <div class="grupo-campo">
                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" rows="1" placeholder="Detalles adicionales del producto..."></textarea>
            </div>

            <div class="grupo-campo">
                <label for="cantidad">Cantidad</label>
                <input type="number" id="cantidad" name="cantidad_solicitada" min="1" value="1">
            </div>

            <button type="submit" class="btn-principal">Registrar Producto</button>
        </form>
    </section>

    <section class="tarjeta tarjeta-lista">
        <h2>Lista de Productos</h2>
        
        <div class="tabla-contenedor">
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Descripción</th>
                        <th>Cantidad</th>
                        <th>Solicitante</th>
                        <th>Fecha Solicitud</th>
                        <th>Estado</th>
                        <?php if ($esAdmin): ?>
                        <th>Comprador</th>
                        <?php endif; ?>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($productos)): ?>
                        <tr>
                            <td colspan="<?= $esAdmin ? 8 : 7 ?>">No hay productos registrados.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($productos as $producto): ?>
                            <tr class="producto-<?= htmlspecialchars($producto['estado']) ?>">
                                <td><strong><?= htmlspecialchars($producto['nombre']) ?></strong></td>
                                <td><?= htmlspecialchars(substr($producto['descripcion'] ?? '', 0, 50)) ?><?= strlen($producto['descripcion'] ?? '') > 50 ? '...' : '' ?></td>
                                <td><?= (int)$producto['cantidad_solicitada'] ?></td>
                                <td><?= htmlspecialchars($producto['solicitante']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($producto['fecha_solicitud'])) ?></td>
                                <td>
                                    <span class="badge badge-<?= strtolower($producto['estado']) ?>">
                                        <?= htmlspecialchars($producto['estado']) ?>
                                    </span>
                                </td>
                                <?php if ($esAdmin): ?>
                                <td><?= htmlspecialchars($producto['comprador'] ?? '--') ?></td>
                                <?php endif; ?>
                                <td>
                                    <div class="acciones-producto">
                                        <?php if ($producto['estado'] === 'Pendiente' && $esAdmin): ?>
                                            <form method="POST" action="../../controlador/controlador.ProductosFaltantes.php" class="form-accion-producto">
                                                <input type="hidden" name="accion" value="marcarComprado">
                                                <input type="hidden" name="id_producto" value="<?= (int)$producto['id'] ?>">
                                                <button type="submit" class="btn-accion-producto btn-comprado" title="Marcar como comprado"><span>✓</span> Comprado</button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if ($esAdmin || ((int)$producto['id'] === $idUsuario && $producto['estado'] === 'Pendiente')): ?>
                                            <form method="POST" action="../../controlador/controlador.ProductosFaltantes.php" class="form-accion-producto" onsubmit="return confirm('¿Eliminar este producto?');">
                                                <input type="hidden" name="accion" value="eliminar">
                                                <input type="hidden" name="id_producto" value="<?= (int)$producto['id'] ?>">
                                                <button type="submit" class="btn-accion-producto btn-eliminar" title="Eliminar"><span>✕</span> Eliminar</button>
                                            </form>
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
