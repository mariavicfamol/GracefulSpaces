<?php

//Importa el modelo que maneja los empleados en BD
require_once __DIR__ . '/../modelo/ModeloEmpleado.php';

//Respuestas en JSON
header('Content-Type: application/json; charset=utf-8');

$metodo = $_SERVER['REQUEST_METHOD'];
$modeloEmpleado = new ModeloEmpleado();



  //GET - Obtener lista de empleados
if ($metodo === 'GET') {
    $listaEmpleados = $modeloEmpleado->obtenerTodos();
    echo json_encode($listaEmpleados);
    exit;
}

  // POST - Crear, Editar o Eliminar un empleado
if ($metodo === 'POST') {

    // Detectar si viene FormData (con posible archivo) o JSON
    $vieneFormData = isset($_POST['accion']);

    if ($vieneFormData) {
        // Extrae los datos desde $_POST y procesa la foto si fue adjuntada
        $accion      = trim($_POST['accion']   ?? '');
        $idEmpleado  = (int) ($_POST['id']     ?? 0);
        $nombre      = trim($_POST['nombre']   ?? '');
        $funcion     = trim($_POST['funcion']  ?? '');
        $rutaFoto    = procesarSubidaFoto();
    } else {
        // Decodifica el cuerpo JSON de la solicitud
        $datosJson   = json_decode(file_get_contents('php://input'), true) ?? [];
        $accion      = trim($datosJson['accion']  ?? '');
        $idEmpleado  = (int) ($datosJson['id']    ?? 0);
        $nombre      = trim($datosJson['nombre']  ?? '');
        $funcion     = trim($datosJson['funcion'] ?? '');
        $rutaFoto    = '';
    }

    $respuesta = ['status' => 'error', 'mensaje' => 'Accion no reconocida'];

    switch ($accion) {

        case 'crear':
            $exito = $modeloEmpleado->crear($nombre, $funcion, $rutaFoto);
            $respuesta = $exito
                ? ['status' => 'ok', 'mensaje' => 'Empleado creado']
                : ['status' => 'error', 'mensaje' => 'No se pudo crear el empleado'];
            break;

        case 'editar':
            $exito = $modeloEmpleado->editar($idEmpleado, $nombre, $funcion, $rutaFoto);
            $respuesta = $exito
                ? ['status' => 'ok', 'mensaje' => 'Empleado actualizado']
                : ['status' => 'error', 'mensaje' => 'No se pudo actualizar el empleado'];
            break;

        case 'eliminar':
            $exito = $modeloEmpleado->eliminar($idEmpleado);
            $respuesta = $exito
                ? ['status' => 'ok', 'mensaje' => 'Empleado eliminado']
                : ['status' => 'error', 'mensaje' => 'No se pudo eliminar el empleado'];
            break;
    }

    echo json_encode($respuesta);
    exit;
}

// Metodo no permitido
http_response_code(405);
echo json_encode(['status' => 'error', 'mensaje' => 'Metodo no permitido']);

// FUNCION AUXILIAR - Subida de foto
//Procesa y guarda la foto subida por el usuario

function procesarSubidaFoto(): string {
    if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
        return '';
    }

    $carpetaSubidas = __DIR__ . '/../publico/subidas/';

//Crea la carpeta de destino si no existe
    if (!is_dir($carpetaSubidas)) {
        mkdir($carpetaSubidas, 0755, true);
    }
//Genera un nombre unico para la foto
    $nombreArchivo  = time() . '_' . basename($_FILES['foto']['name']);
    $rutaDestino    = $carpetaSubidas . $nombreArchivo;

//Mueve el archivo temporal al destino final y retorna la ruta
    if (move_uploaded_file($_FILES['foto']['tmp_name'], $rutaDestino)) {
        return 'publico/subidas/' . $nombreArchivo;
    }
//Si falla, retorna cadena vacía
    return '';
}
