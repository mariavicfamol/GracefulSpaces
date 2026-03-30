<?php

require_once __DIR__ . '/../configuracion/GracefulSpacesDB.configuracion.php';

class ModeloUsuario {

    // -------------------------------------------------------
    // CREAR usuario nuevo
    // -------------------------------------------------------
    public static function crearUsuario(array $datos): array {
        $conexion = obtenerConexion();

        // Generar ID empresa
        $idEmpresa = self::generarIdEmpresa($conexion);

        // Hash de contraseña
        $passwordHash = password_hash($datos['password'], PASSWORD_BCRYPT, ['cost' => 12]);

        // Guardar foto si viene
        $rutaFoto = null;
        if (!empty($datos['foto_tmp']) && !empty($datos['foto_nombre'])) {
            $rutaFoto = self::guardarFoto($datos['foto_tmp'], $datos['foto_nombre']);
        }

        $sql = "INSERT INTO trabajadores (
                    id_empresa, nombre, apellido1, apellido2,
                    tipo_documento, numero_identificacion, fecha_nacimiento,
                    sexo, genero, nacionalidad,
                    cargo, tipo_contrato, fecha_ingreso,
                    correo_personal, correo_corporativo, telefono,
                    contacto_emergencia, telefono_emergencia, direccion,
                    login_usuario, password_hash, rol, estado, foto_perfil
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            return ['error' => true, 'mensaje' => 'Error preparando consulta: ' . $conexion->error];
        }

        $stmt->bind_param(
            'ssssssssssssssssssssssss',
            $idEmpresa,
            $datos['nombre'],
            $datos['apellido1'],
            $datos['apellido2'],
            $datos['tipo_documento'],
            $datos['numero_identificacion'],
            $datos['fecha_nacimiento'],
            $datos['sexo'],
            $datos['genero'],
            $datos['nacionalidad'],
            $datos['cargo'],
            $datos['tipo_contrato'],
            $datos['fecha_ingreso'],
            $datos['correo_personal'],
            $datos['correo_corporativo'],
            $datos['telefono'],
            $datos['contacto_emergencia'],
            $datos['telefono_emergencia'],
            $datos['direccion'],
            $datos['login_usuario'],
            $passwordHash,
            $datos['rol'],
            $datos['estado'],
            $rutaFoto
        );

        if ($stmt->execute()) {
            $stmt->close();
            $conexion->close();
            return ['error' => false, 'mensaje' => 'Usuario creado exitosamente.', 'id_empresa' => $idEmpresa];
        }

        $error = $stmt->error;
        $stmt->close();
        $conexion->close();

        if (str_contains($error, 'Duplicate')) {
            return ['error' => true, 'mensaje' => 'El correo de usuario ya está registrado.'];
        }
        return ['error' => true, 'mensaje' => 'Error al guardar: ' . $error];
    }

    // -------------------------------------------------------
    // BUSCAR usuario por id_empresa, identificación o nombre
    // -------------------------------------------------------
    public static function buscarUsuario(string $termino): ?array {
        $conexion = obtenerConexion();
        $like = '%' . $termino . '%';

        $sql = "SELECT * FROM trabajadores
                WHERE id_empresa = ?
                   OR numero_identificacion = ?
                   OR CONCAT(nombre, ' ', apellido1) LIKE ?
                   OR login_usuario = ?
                LIMIT 1";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param('ssss', $termino, $termino, $like, $termino);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $usuario = $resultado->fetch_assoc();
        $stmt->close();
        $conexion->close();
        return $usuario ?: null;
    }

    // -------------------------------------------------------
    // OBTENER usuario por ID interno
    // -------------------------------------------------------
    public static function obtenerPorId(int $id): ?array {
        $conexion = obtenerConexion();
        $stmt = $conexion->prepare("SELECT * FROM trabajadores WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $usuario = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $conexion->close();
        return $usuario ?: null;
    }

    // -------------------------------------------------------
    // ACTUALIZAR usuario
    // -------------------------------------------------------
    public static function actualizarUsuario(int $id, array $datos): array {
        $conexion = obtenerConexion();

        // Si viene nueva foto
        $rutaFoto = $datos['foto_actual'] ?? null;
        if (!empty($datos['foto_tmp']) && !empty($datos['foto_nombre'])) {
            $rutaNuevaFoto = self::guardarFoto($datos['foto_tmp'], $datos['foto_nombre']);

            if ($rutaNuevaFoto === null) {
                $conexion->close();
                return ['error' => true, 'mensaje' => 'No se pudo guardar la imagen. Verifique formato, tamaño y permisos de la carpeta de subidas.'];
            }

            $rutaFoto = $rutaNuevaFoto;
        }

        // Si viene nueva contraseña
        $sqlPassword = '';
        if (!empty($datos['password'])) {
            $hash = password_hash($datos['password'], PASSWORD_BCRYPT, ['cost' => 12]);
            $datos['password_hash'] = $hash;
            $sqlPassword = ', password_hash = ?';
        }

        $sql = "UPDATE trabajadores SET
                    nombre = ?, apellido1 = ?, apellido2 = ?,
                    tipo_documento = ?, numero_identificacion = ?, fecha_nacimiento = ?,
                    sexo = ?, genero = ?, nacionalidad = ?,
                    cargo = ?, tipo_contrato = ?, fecha_ingreso = ?,
                    correo_personal = ?, correo_corporativo = ?, telefono = ?,
                    contacto_emergencia = ?, telefono_emergencia = ?, direccion = ?,
                    login_usuario = ?, rol = ?, estado = ?, foto_perfil = ?
                    $sqlPassword
                WHERE id = ?";

        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            return ['error' => true, 'mensaje' => 'Error preparando consulta: ' . $conexion->error];
        }

        if (!empty($datos['password'])) {
            $stmt->bind_param(
                'sssssssssssssssssssssssi',
                $datos['nombre'], $datos['apellido1'], $datos['apellido2'],
                $datos['tipo_documento'], $datos['numero_identificacion'], $datos['fecha_nacimiento'],
                $datos['sexo'], $datos['genero'], $datos['nacionalidad'],
                $datos['cargo'], $datos['tipo_contrato'], $datos['fecha_ingreso'],
                $datos['correo_personal'], $datos['correo_corporativo'], $datos['telefono'],
                $datos['contacto_emergencia'], $datos['telefono_emergencia'], $datos['direccion'],
                $datos['login_usuario'], $datos['rol'], $datos['estado'], $rutaFoto,
                $datos['password_hash'],
                $id
            );
        } else {
            $stmt->bind_param(
                'ssssssssssssssssssssssi',
                $datos['nombre'], $datos['apellido1'], $datos['apellido2'],
                $datos['tipo_documento'], $datos['numero_identificacion'], $datos['fecha_nacimiento'],
                $datos['sexo'], $datos['genero'], $datos['nacionalidad'],
                $datos['cargo'], $datos['tipo_contrato'], $datos['fecha_ingreso'],
                $datos['correo_personal'], $datos['correo_corporativo'], $datos['telefono'],
                $datos['contacto_emergencia'], $datos['telefono_emergencia'], $datos['direccion'],
                $datos['login_usuario'], $datos['rol'], $datos['estado'], $rutaFoto,
                $id
            );
        }

        if ($stmt->execute()) {
            $stmt->close();
            $conexion->close();
            return ['error' => false, 'mensaje' => 'Usuario actualizado exitosamente.'];
        }

        $error = $stmt->error;
        $stmt->close();
        $conexion->close();
        return ['error' => true, 'mensaje' => 'Error al actualizar: ' . $error];
    }

    // -------------------------------------------------------
    // DAR DE BAJA (desactivar) usuario
    // -------------------------------------------------------
    public static function darDeBaja(int $id): array {
        $conexion = obtenerConexion();
        $stmt = $conexion->prepare("UPDATE trabajadores SET estado = 'Inactivo' WHERE id = ?");
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $stmt->close();
        $conexion->close();
        return $ok
            ? ['error' => false, 'mensaje' => 'Usuario desactivado.']
            : ['error' => true,  'mensaje' => 'No se pudo desactivar el usuario.'];
    }

    // -------------------------------------------------------
    // LOGIN
    // -------------------------------------------------------
    public static function autenticar(string $login, string $password): ?array {
        $conexion = obtenerConexion();
        $stmt = $conexion->prepare(
            "SELECT * FROM trabajadores WHERE login_usuario = ? AND estado = 'Activo' LIMIT 1"
        );
        $stmt->bind_param('s', $login);
        $stmt->execute();
        $usuario = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $conexion->close();

        if ($usuario && password_verify($password, $usuario['password_hash'])) {
            unset($usuario['password_hash']); // nunca exponer el hash
            return $usuario;
        }
        return null;
    }

    // -------------------------------------------------------
    // Helpers privados
    // -------------------------------------------------------
    private static function generarIdEmpresa(mysqli $con): string {
        $anio = date('Y');
        $res = $con->query("SELECT COUNT(*) AS total FROM trabajadores");
        $total = (int)$res->fetch_assoc()['total'];
        return 'GS-' . $anio . '-' . str_pad($total + 1, 3, '0', STR_PAD_LEFT);
    }

    private static function guardarFoto(string $tmpPath, string $nombreOriginal): ?string {
        $ext = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
        $permitidos = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($ext, $permitidos)) return null;

        $nombreNuevo = uniqid('foto_', true) . '.' . $ext;
        $carpetaSubidas = __DIR__ . '/../publico/subidas/';

        if (!is_dir($carpetaSubidas)) {
            if (!mkdir($carpetaSubidas, 0755, true) && !is_dir($carpetaSubidas)) {
                return null;
            }
        }

        if (!is_uploaded_file($tmpPath)) {
            return null;
        }

        $destino = $carpetaSubidas . $nombreNuevo;

        if (@move_uploaded_file($tmpPath, $destino)) {
            return 'publico/subidas/' . $nombreNuevo;
        }
        return null;
    }
}
