<?php

/**
 * CAPA DE MODELO
 * Archivo: modelo/ModeloEmpleado.php
 * Descripcion: Acceso a datos y operaciones CRUD de la tabla empleados
 */

require_once __DIR__ . '/../config/baseDatos.php';

class ModeloEmpleado {

    private mysqli $conexion;

    public function __construct() {
        $this->conexion = obtenerConexion();
    }

    /**
     * Retorna todos los empleados registrados
     * @return array Lista de empleados
     */
    public function obtenerTodos(): array {
        $resultado = $this->conexion->query('SELECT * FROM empleados');
        $listaEmpleados = [];

        while ($fila = $resultado->fetch_assoc()) {
            $listaEmpleados[] = $fila;
        }

        return $listaEmpleados;
    }

    /**
     * Crea un nuevo empleado
     * @param string $nombre Nombre del empleado
     * @param string $funcion Funcion del empleado
     * @param string $rutaFoto Ruta de la foto (opcional)
     * @return bool True si se creo correctamente
     */
    public function crear(string $nombre, string $funcion, string $rutaFoto = ''): bool {
        $nombreSeguro  = $this->conexion->real_escape_string($nombre);
        $funcionSegura = $this->conexion->real_escape_string($funcion);

        if ($rutaFoto !== '') {
            $fotoSegura = $this->conexion->real_escape_string($rutaFoto);
            $sql = "INSERT INTO empleados (nombre_empleado, funcion, foto)
                    VALUES ('$nombreSeguro', '$funcionSegura', '$fotoSegura')";
        } else {
            $sql = "INSERT INTO empleados (nombre_empleado, funcion)
                    VALUES ('$nombreSeguro', '$funcionSegura')";
        }

        return $this->conexion->query($sql);
    }

    /**
     * Actualiza los datos de un empleado existente
     * @param int    $idEmpleado   ID del empleado a editar
     * @param string $nombre       Nuevo nombre
     * @param string $funcion      Nueva funcion
     * @param string $rutaFoto     Nueva foto (opcional)
     * @return bool True si se edito correctamente
     */
    public function editar(int $idEmpleado, string $nombre, string $funcion, string $rutaFoto = ''): bool {
        $nombreSeguro   = $this->conexion->real_escape_string($nombre);
        $funcionSegura  = $this->conexion->real_escape_string($funcion);

        if ($rutaFoto !== '') {
            $fotoSegura = $this->conexion->real_escape_string($rutaFoto);
            $sql = "UPDATE empleados
                    SET nombre_empleado = '$nombreSeguro',
                        funcion         = '$funcionSegura',
                        foto            = '$fotoSegura'
                    WHERE id = $idEmpleado";
        } else {
            $sql = "UPDATE empleados
                    SET nombre_empleado = '$nombreSeguro',
                        funcion         = '$funcionSegura'
                    WHERE id = $idEmpleado";
        }

        return $this->conexion->query($sql);
    }

    /**
     * Elimina un empleado por su ID
     * @param int $idEmpleado ID del empleado a eliminar
     * @return bool True si se elimino correctamente
     */
    public function eliminar(int $idEmpleado): bool {
        $sql = "DELETE FROM empleados WHERE id = $idEmpleado";
        return $this->conexion->query($sql);
    }

    public function __destruct() {
        $this->conexion->close();
    }
}
