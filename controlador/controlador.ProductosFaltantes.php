<?php

session_start();
//Importa los modelos necesarios desde la BD
require_once __DIR__ . '/../modelo/ModeloProductoFaltante.php';
require_once __DIR__ . '/../modelo/ModeloNotificacion.php';
require_once __DIR__ . '/GeneradorPdfProductosFaltantes.php';

//Si el usuario no está logueado, redirige al login
if (empty($_SESSION['usuario'])) {
    header('Location: ../vista/vistas/Login.php');
    exit;
}
//Valida los datos del usuario y su rol
$usuario = $_SESSION['usuario'];
$rol = $usuario['rol'] ?? '';
$idUsuario = (int)($usuario['id'] ?? 0);
//Determina si tiene rol admin
$esAdmin = in_array($rol, ['Administrador Total', 'Administrador'], true);
//lee la accion con post o get
$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

//Descargar pdf para productos faltantes
if ($accion === 'descargar_pdf') {
    //Solo admin descarga el pdf
    if (!$esAdmin) {
        http_response_code(403);
        header('Location: ../vista/vistas/ProductosFaltantes.php');
        exit;
    }
    //Obtiene la lista de los productos y genera el pdf
    $productos = ModeloProductoFaltante::obtenerProductos();
    $pdf = generarPdfProductosFaltantes($productos);
    $archivo = 'Productos_Faltantes_' . date('Y-m-d_H-i') . '.pdf';

    //Forazr la descarga del archivo
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename=' . $archivo);
    header('Content-Length: ' . strlen($pdf));

    echo $pdf;
    exit;
}

//Crear nuevo producto faltante
if ($accion === 'crear') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ../vista/vistas/ProductosFaltantes.php');
        exit;
    }
    //Recoge los datos enviados por el formulario y los valida
    $datos = [
        'nombre' => trim($_POST['nombre'] ?? ''),
        'descripcion' => trim($_POST['descripcion'] ?? ''),
        'cantidad_solicitada' => (int)($_POST['cantidad_solicitada'] ?? 1),
    ];
    //crea el registro en la bd y guarda el mensaje en sesión
    $resultado = ModeloProductoFaltante::crearProducto($datos, $idUsuario);

    if ($resultado['error']) {
        $_SESSION['error_producto'] = $resultado['mensaje'];
    } else {
        $_SESSION['exito_producto'] = $resultado['mensaje'];
        ModeloNotificacion::enviarAAdmins(
            'Se ha reportado un producto faltante: ' . $datos['nombre'] . '.',
            'producto_faltante'
        );
    }

    header('Location: ../vista/vistas/ProductosFaltantes.php');
    exit;
}
//Marcar producto como comprado
if ($accion === 'marcarComprado') {
    //Solo admin puede marcarlo 
    if (!$esAdmin) {
        $_SESSION['error_producto'] = 'No tienes permisos para marcar productos como comprados.';
        header('Location: ../vista/vistas/ProductosFaltantes.php');
        exit;
    }
//solo permite post
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ../vista/vistas/ProductosFaltantes.php');
        exit;
    }
    //Obtiene el id del productoy lo valida
    $idProducto = (int)($_POST['id_producto'] ?? 0);

    if ($idProducto <= 0) {
        $_SESSION['error_producto'] = 'Producto no válido.';
        header('Location: ../vista/vistas/ProductosFaltantes.php');
        exit;
    }
//llama al modelo para actualizar el estado del producto
    $resultado = ModeloProductoFaltante::marcarComprrado($idProducto, $idUsuario);

    if ($resultado['error']) {
        $_SESSION['error_producto'] = $resultado['mensaje'];
    } else {
        $_SESSION['exito_producto'] = $resultado['mensaje'];
    }

    header('Location: ../vista/vistas/ProductosFaltantes.php');
    exit;
}
//Eliminar producto faltante
if ($accion === 'eliminar') {
    $idProducto = (int)($_POST['id_producto'] ?? $_GET['id'] ?? 0);

    if ($idProducto <= 0) {
        http_response_code(400);
        echo 'Producto no válido.';
        exit;
    }
//Obtiene la lista de productos para validar permisos
    $productos = ModeloProductoFaltante::obtenerProductos();
    $permisoEliminar = false;
//Recorre los productos para validar que si tenga permisos para eliminarlo
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
    //Ejecuta la eliminacion del producto en la bd y guarda el mensaje en sesión
    $resultado = ModeloProductoFaltante::eliminarProducto($idProducto);

    if ($resultado['error']) {
        $_SESSION['error_producto'] = $resultado['mensaje'];
    } else {
        $_SESSION['exito_producto'] = $resultado['mensaje'];
    }

    header('Location: ../vista/vistas/ProductosFaltantes.php');
    exit;
}

//Redirige a la vista principal
header('Location: ../vista/vistas/ProductosFaltantes.php');
exit;
