<?php

session_start();
require_once __DIR__ . '/../modelo/ModeloProductoFaltante.php';
require_once __DIR__ . '/GeneradorPdfProductosFaltantes.php';

if (empty($_SESSION['usuario'])) {
    header('Location: ../vista/vistas/Login.php');
    exit;
}

$usuario = $_SESSION['usuario'];
$rol = $usuario['rol'] ?? '';
$idUsuario = (int)($usuario['id'] ?? 0);

$esAdmin = in_array($rol, ['Administrador Total', 'Administrador'], true);
$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

if ($accion === 'descargar_pdf') {
    if (!$esAdmin) {
        http_response_code(403);
        header('Location: ../vista/vistas/ProductosFaltantes.php');
        exit;
    }

    $productos = ModeloProductoFaltante::obtenerProductos();
    $pdf = generarPdfProductosFaltantes($productos);
    $archivo = 'Productos_Faltantes_' . date('Y-m-d_H-i') . '.pdf';

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename=' . $archivo);
    header('Content-Length: ' . strlen($pdf));

    echo $pdf;
    exit;
}

if ($accion === 'crear') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ../vista/vistas/ProductosFaltantes.php');
        exit;
    }

    $datos = [
        'nombre' => trim($_POST['nombre'] ?? ''),
        'descripcion' => trim($_POST['descripcion'] ?? ''),
        'cantidad_solicitada' => (int)($_POST['cantidad_solicitada'] ?? 1),
    ];

    $resultado = ModeloProductoFaltante::crearProducto($datos, $idUsuario);

    if ($resultado['error']) {
        $_SESSION['error_producto'] = $resultado['mensaje'];
    } else {
        $_SESSION['exito_producto'] = $resultado['mensaje'];
    }

    header('Location: ../vista/vistas/ProductosFaltantes.php');
    exit;
}

if ($accion === 'marcarComprado') {
    if (!$esAdmin) {
        $_SESSION['error_producto'] = 'No tienes permisos para marcar productos como comprados.';
        header('Location: ../vista/vistas/ProductosFaltantes.php');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ../vista/vistas/ProductosFaltantes.php');
        exit;
    }

    $idProducto = (int)($_POST['id_producto'] ?? 0);

    if ($idProducto <= 0) {
        $_SESSION['error_producto'] = 'Producto no válido.';
        header('Location: ../vista/vistas/ProductosFaltantes.php');
        exit;
    }

    $resultado = ModeloProductoFaltante::marcarComprrado($idProducto, $idUsuario);

    if ($resultado['error']) {
        $_SESSION['error_producto'] = $resultado['mensaje'];
    } else {
        $_SESSION['exito_producto'] = $resultado['mensaje'];
    }

    header('Location: ../vista/vistas/ProductosFaltantes.php');
    exit;
}

if ($accion === 'eliminar') {
    $idProducto = (int)($_POST['id_producto'] ?? $_GET['id'] ?? 0);

    if ($idProducto <= 0) {
        http_response_code(400);
        echo 'Producto no válido.';
        exit;
    }

    $productos = ModeloProductoFaltante::obtenerProductos();
    $permisoEliminar = false;

    foreach ($productos as $prod) {
        if ((int)$prod['id'] === $idProducto) {
            if ($esAdmin || (int)$prod['id'] === $idUsuario) {
                $permisoEliminar = true;
            }
            break;
        }
    }

    if (!$permisoEliminar) {
        $_SESSION['error_producto'] = 'No tienes permisos para eliminar este producto.';
        header('Location: ../vista/vistas/ProductosFaltantes.php');
        exit;
    }

    $resultado = ModeloProductoFaltante::eliminarProducto($idProducto);

    if ($resultado['error']) {
        $_SESSION['error_producto'] = $resultado['mensaje'];
    } else {
        $_SESSION['exito_producto'] = $resultado['mensaje'];
    }

    header('Location: ../vista/vistas/ProductosFaltantes.php');
    exit;
}

header('Location: ../vista/vistas/ProductosFaltantes.php');
exit;
