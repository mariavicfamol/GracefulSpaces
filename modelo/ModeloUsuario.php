<?php

/**
 * CAPA DE MODELO
 * Archivo: modelo/ModeloUsuario.php
 * Descripcion: Acceso a datos y logica de autenticacion de usuarios
 */

require_once __DIR__ . '/../config/baseDatos.php';

class ModeloUsuario {

    private mysqli $conexion;

    public function __construct() {
        $this->conexion = obtenerConexion();
    }

    /**
     * Verifica las credenciales de un usuario
     * @param string $usuario   Nombre de usuario
     * @param string $password  Contrasena del usuario
     * @return bool True si las credenciales son validas
     */
    public function verificarCredenciales(string $usuario, string $password): bool {
        $usuarioSeguro  = $this->conexion->real_escape_string($usuario);
        $passwordSeguro = $this->conexion->real_escape_string($password);

        $sql = "SELECT id FROM usuarios
                WHERE usuario  = '$usuarioSeguro'
                AND   password = '$passwordSeguro'
                LIMIT 1";

        $resultado = $this->conexion->query($sql);
        return $resultado && $resultado->num_rows > 0;
    }

    public function __destruct() {
        $this->conexion->close();
    }
}
