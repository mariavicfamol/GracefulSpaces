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

    /**
     * Registra un nuevo cliente en la tabla usuarios
     * Guarda siempre usuario y password, y agrega nombre/correo/tipo si existen esas columnas
     * @return array Resultado estandar para respuesta JSON
     */
    public function registrarCliente(string $nombre, string $correo, string $usuario, string $password): array {
        $stmtUsuario = $this->conexion->prepare('SELECT id FROM usuarios WHERE usuario = ? LIMIT 1');

        if (!$stmtUsuario) {
            return ['status' => 'error', 'mensaje' => 'No se pudo validar el usuario'];
        }

        $stmtUsuario->bind_param('s', $usuario);
        $stmtUsuario->execute();
        $resultadoUsuario = $stmtUsuario->get_result();

        if ($resultadoUsuario && $resultadoUsuario->num_rows > 0) {
            $stmtUsuario->close();
            return ['status' => 'error', 'mensaje' => 'El usuario ya existe'];
        }

        $stmtUsuario->close();

        $tieneNombre = $this->columnaExiste('usuarios', 'nombre');
        $tieneCorreo = $this->columnaExiste('usuarios', 'correo');
        $tieneTipo   = $this->columnaExiste('usuarios', 'tipo');

        if ($tieneCorreo) {
            $stmtCorreo = $this->conexion->prepare('SELECT id FROM usuarios WHERE correo = ? LIMIT 1');

            if ($stmtCorreo) {
                $stmtCorreo->bind_param('s', $correo);
                $stmtCorreo->execute();
                $resultadoCorreo = $stmtCorreo->get_result();

                if ($resultadoCorreo && $resultadoCorreo->num_rows > 0) {
                    $stmtCorreo->close();
                    return ['status' => 'error', 'mensaje' => 'El correo ya esta registrado'];
                }

                $stmtCorreo->close();
            }
        }

        $columnas = ['usuario', 'password'];
        $valores = [$usuario, $password];
        $tipos = 'ss';

        if ($tieneNombre) {
            $columnas[] = 'nombre';
            $valores[] = $nombre;
            $tipos .= 's';
        }

        if ($tieneCorreo) {
            $columnas[] = 'correo';
            $valores[] = $correo;
            $tipos .= 's';
        }

        if ($tieneTipo) {
            $columnas[] = 'tipo';
            $valores[] = 'cliente';
            $tipos .= 's';
        }

        $placeholders = implode(', ', array_fill(0, count($columnas), '?'));
        $sql = 'INSERT INTO usuarios (' . implode(', ', $columnas) . ') VALUES (' . $placeholders . ')';

        $stmtInsert = $this->conexion->prepare($sql);

        if (!$stmtInsert) {
            return ['status' => 'error', 'mensaje' => 'No se pudo crear el registro'];
        }

        if (!$this->bindParamsDinamicos($stmtInsert, $tipos, $valores)) {
            $stmtInsert->close();
            return ['status' => 'error', 'mensaje' => 'No se pudo preparar el registro'];
        }

        $exito = $stmtInsert->execute();
        $stmtInsert->close();

        return $exito
            ? ['status' => 'ok', 'mensaje' => 'Registro completado correctamente']
            : ['status' => 'error', 'mensaje' => 'No se pudo guardar el registro'];
    }

    /**
     * Verifica si una columna existe en una tabla
     */
    private function columnaExiste(string $tabla, string $columna): bool {
        $tablaSegura = $this->conexion->real_escape_string($tabla);
        $columnaSegura = $this->conexion->real_escape_string($columna);
        $sql = "SHOW COLUMNS FROM {$tablaSegura} LIKE '{$columnaSegura}'";
        $resultado = $this->conexion->query($sql);

        return $resultado && $resultado->num_rows > 0;
    }

    /**
     * Enlaza parametros dinamicos en sentencias preparadas
     */
    private function bindParamsDinamicos(mysqli_stmt $sentencia, string $tipos, array &$valores): bool {
        $parametros = [$tipos];

        foreach ($valores as $indice => &$valor) {
            $parametros[] = &$valores[$indice];
        }

        return call_user_func_array([$sentencia, 'bind_param'], $parametros);
    }

    public function __destruct() {
        $this->conexion->close();
    }
}
