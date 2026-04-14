<?php
//Importa el archivo de configuración de la BD
require_once __DIR__ . '/../config/baseDatos.php';

//Gestiona las operaciones CRUD
class ModeloEmpleado {

    private mysqli $conexion;

    public function __construct() {
        $this->conexion = obtenerConexion();
    }

    //Retorna todos los empleados registrados

    public function obtenerTodos(): array {
        //Ejecuta la consulta paar obtner todos lo empleados y los guarda en un arreglo
        $resultado = $this->conexion->query('SELECT * FROM empleados');
        $listaEmpleados = [];

        while ($fila = $resultado->fetch_assoc()) {
            $listaEmpleados[] = $fila;
        }

        return $listaEmpleados;
    }
//Crea un nuevo empleado

    public function crear(string $nombre, string $funcion, string $rutaFoto = ''): bool {
        $nombreSeguro  = $this->conexion->real_escape_string($nombre);
        $funcionSegura = $this->conexion->real_escape_string($funcion);

    //Si se proporciona una foto, se incluye en la consulta
        if ($rutaFoto !== '') {
            $fotoSegura = $this->conexion->real_escape_string($rutaFoto);
            //insert con los tres campos 
            $sql = "INSERT INTO empleados (nombre_empleado, funcion, foto)
                    VALUES ('$nombreSeguro', '$funcionSegura', '$fotoSegura')";
        } else {
            //Insert sin foto
            $sql = "INSERT INTO empleados (nombre_empleado, funcion)
                    VALUES ('$nombreSeguro', '$funcionSegura')";
        }

        return $this->conexion->query($sql);
    }

     //Actualiza los datos de un empleado existente

    public function editar(int $idEmpleado, string $nombre, string $funcion, string $rutaFoto = ''): bool {
        $nombreSeguro   = $this->conexion->real_escape_string($nombre);
        $funcionSegura  = $this->conexion->real_escape_string($funcion);
        //Si se envió una foto, tambien se actualiza
        if ($rutaFoto !== '') {
            $fotoSegura = $this->conexion->real_escape_string($rutaFoto);
            //UPDATE con foto
            $sql = "UPDATE empleados
                    SET nombre_empleado = '$nombreSeguro',
                        funcion         = '$funcionSegura',
                        foto            = '$fotoSegura'
                    WHERE id = $idEmpleado";
        } else {
            //UPDATE sin foto
            $sql = "UPDATE empleados
                    SET nombre_empleado = '$nombreSeguro',
                        funcion         = '$funcionSegura'
                    WHERE id = $idEmpleado";
        }

        return $this->conexion->query($sql);
    }

     //Elimina un empleado por su ID
    public function eliminar(int $idEmpleado): bool {
        //DELETE para eliminar empleado por ID
        $sql = "DELETE FROM empleados WHERE id = $idEmpleado";
        return $this->conexion->query($sql);
    }
//Se ejecuta cuando el objeto deja de usarse y cierra la conexión a la BD
    public function __destruct() {
        $this->conexion->close();
    }
}
