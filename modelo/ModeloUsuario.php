<?php

// Importa la conexión y configuración general de la base de datos.
require_once __DIR__ . '/../configuracion/GracefulSpacesDB.configuracion.php';

class ModeloUsuario {

    // -------------------------------------------------------
    // CREAR usuario nuevo
    // -------------------------------------------------------
    public static function crearUsuario(array $datos): array {
        $conexion = obtenerConexion();
        $stmt = null;

        // Normaliza fechas para validar correctamente antes de guardar.
        $fechaNacimiento = self::normalizarFecha($datos['fecha_nacimiento'] ?? null);
        $fechaIngreso = self::normalizarFecha($datos['fecha_ingreso'] ?? null);

        if (!self::esFechaValida($fechaNacimiento)) {
            return ['error' => true, 'mensaje' => 'La fecha de nacimiento no es valida.'];
        }
        if (!self::esFechaValida($fechaIngreso)) {
            return ['error' => true, 'mensaje' => 'La fecha de ingreso no es valida.'];
        }

        try {
            // Generar ID empresa
            $idEmpresa = self::generarIdEmpresa($conexion);

            // Hash de contraseña
            $passwordHash = password_hash($datos['password'], PASSWORD_BCRYPT, ['cost' => 12]);

            // Guardar foto del ID en la columna existente foto_perfil
            $rutaFoto = null;
            if (!empty($datos['foto_tmp']) && !empty($datos['foto_nombre'])) {
                $rutaFoto = self::guardarFoto($datos['foto_tmp'], $datos['foto_nombre']);
            }

            $sql = "INSERT INTO trabajadores (
                        id_empresa, nombre, apellido1, apellido2,
                        tipo_documento, numero_identificacion, fecha_nacimiento,
                        sexo, genero, nacionalidad,
                        cargo, tipo_contrato, fecha_ingreso,
                        correo_personal, telefono,
                        contacto_emergencia, telefono_emergencia, direccion,
                        login_usuario, password_hash, rol, estado, foto_perfil
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conexion->prepare($sql);
            if (!$stmt) {
                return ['error' => true, 'mensaje' => 'Error preparando consulta: ' . $conexion->error];
            }

            $tiposCrear = str_repeat('s', 23);
            $stmt->bind_param(
                $tiposCrear,
                $idEmpresa,
                $datos['nombre'],
                $datos['apellido1'],
                $datos['apellido2'],
                $datos['tipo_documento'],
                $datos['numero_identificacion'],
                $fechaNacimiento,
                $datos['sexo'],
                $datos['genero'],
                $datos['nacionalidad'],
                $datos['cargo'],
                $datos['tipo_contrato'],
                $fechaIngreso,
                $datos['correo_personal'],
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

            $stmt->execute();
            return ['error' => false, 'mensaje' => 'Usuario creado exitosamente.', 'id_empresa' => $idEmpresa];
        } catch (mysqli_sql_exception $e) {
            if ((int)$e->getCode() === 1062) {
                return ['error' => true, 'mensaje' => 'El correo de usuario ya esta registrado.'];
            }
            return ['error' => true, 'mensaje' => 'Error al guardar: ' . $e->getMessage()];
        } finally {
            if ($stmt instanceof mysqli_stmt) {
                $stmt->close();
            }
            $conexion->close();
        }
    }

    // -------------------------------------------------------
    // BUSCAR usuario por id_empresa, identificación o nombre
    // -------------------------------------------------------
    public static function buscarUsuario(string $termino): ?array {
        $conexion = obtenerConexion();
        // Permite buscar por coincidencia parcial de nombre.
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
        $stmt = null;

        $fechaNacimiento = self::normalizarFecha($datos['fecha_nacimiento'] ?? null);
        $fechaIngreso = self::normalizarFecha($datos['fecha_ingreso'] ?? null);

        if (!self::esFechaValida($fechaNacimiento)) {
            return ['error' => true, 'mensaje' => 'La fecha de nacimiento no es valida.'];
        }
        if (!self::esFechaValida($fechaIngreso)) {
            return ['error' => true, 'mensaje' => 'La fecha de ingreso no es valida.'];
        }

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

        // Si viene nueva contraseña, genera un nuevo hash seguro.
        $sqlPassword = '';
        if (!empty($datos['password'])) {
            $hash = password_hash($datos['password'], PASSWORD_BCRYPT, ['cost' => 12]);
            $datos['password_hash'] = $hash;
            $sqlPassword = ', password_hash = ?';
        }

        try {
            $sql = "UPDATE trabajadores SET
                        nombre = ?, apellido1 = ?, apellido2 = ?,
                        tipo_documento = ?, numero_identificacion = ?, fecha_nacimiento = ?,
                        sexo = ?, genero = ?, nacionalidad = ?,
                        cargo = ?, tipo_contrato = ?, fecha_ingreso = ?,
                        correo_personal = ?, telefono = ?,
                        contacto_emergencia = ?, telefono_emergencia = ?, direccion = ?,
                        login_usuario = ?, rol = ?, estado = ?, foto_perfil = ?
                        $sqlPassword
                    WHERE id = ?";

            $stmt = $conexion->prepare($sql);
            if (!$stmt) {
                return ['error' => true, 'mensaje' => 'Error preparando consulta: ' . $conexion->error];
            }

            if (!empty($datos['password'])) {
                $tiposActualizacionConPassword = str_repeat('s', 22) . 'i';
                $stmt->bind_param(
                    $tiposActualizacionConPassword,
                    $datos['nombre'], $datos['apellido1'], $datos['apellido2'],
                    $datos['tipo_documento'], $datos['numero_identificacion'], $fechaNacimiento,
                    $datos['sexo'], $datos['genero'], $datos['nacionalidad'],
                    $datos['cargo'], $datos['tipo_contrato'], $fechaIngreso,
                    $datos['correo_personal'], $datos['telefono'],
                    $datos['contacto_emergencia'], $datos['telefono_emergencia'], $datos['direccion'],
                    $datos['login_usuario'], $datos['rol'], $datos['estado'], $rutaFoto,
                    $datos['password_hash'],
                    $id
                );
            } else {
                $tiposActualizacionSinPassword = str_repeat('s', 21) . 'i';
                $stmt->bind_param(
                    $tiposActualizacionSinPassword,
                    $datos['nombre'], $datos['apellido1'], $datos['apellido2'],
                    $datos['tipo_documento'], $datos['numero_identificacion'], $fechaNacimiento,
                    $datos['sexo'], $datos['genero'], $datos['nacionalidad'],
                    $datos['cargo'], $datos['tipo_contrato'], $fechaIngreso,
                    $datos['correo_personal'], $datos['telefono'],
                    $datos['contacto_emergencia'], $datos['telefono_emergencia'], $datos['direccion'],
                    $datos['login_usuario'], $datos['rol'], $datos['estado'], $rutaFoto,
                    $id
                );
            }

            $stmt->execute();
            return ['error' => false, 'mensaje' => 'Usuario actualizado exitosamente.'];
        } catch (mysqli_sql_exception $e) {
            if ((int)$e->getCode() === 1062) {
                return ['error' => true, 'mensaje' => 'El correo de usuario ya esta registrado.'];
            }
            return ['error' => true, 'mensaje' => 'Error al actualizar: ' . $e->getMessage()];
        } finally {
            if ($stmt instanceof mysqli_stmt) {
                $stmt->close();
            }
            $conexion->close();
        }
    }

    // -------------------------------------------------------
    // DAR DE BAJA (desactivar) usuario
    // -------------------------------------------------------
    public static function darDeBaja(int $id): array {
        // Baja lógica: no elimina el registro, solo lo desactiva.
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
        // Solo autentica cuentas activas.
        $conexion = obtenerConexion();
        $stmt = $conexion->prepare(
            "SELECT * FROM trabajadores WHERE login_usuario = ? AND estado = 'Activo' LIMIT 1"
        );
        $stmt->bind_param('s', $login);
        $stmt->execute();
        $usuario = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $conexion->close();

        // Compara contraseña ingresada con hash almacenado.
        if ($usuario && password_verify($password, $usuario['password_hash'])) {
            unset($usuario['password_hash']); // nunca exponer el hash
            return $usuario;
        }
        return null;
    }

    // -------------------------------------------------------
    // RESTABLECER contraseña
    // -------------------------------------------------------
    public static function restablecerContrasena(string $login, string $fechaNacimiento, string $nuevaContrasena): array {
        $conexion = obtenerConexion();
        $stmt = null;

        $login = trim($login);
        $fechaNacimiento = self::normalizarFecha($fechaNacimiento);

        if (!filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $conexion->close();
            return ['error' => true, 'mensaje' => 'Debe ingresar un correo valido.'];
        }

        if (!self::esFechaValida($fechaNacimiento)) {
            $conexion->close();
            return ['error' => true, 'mensaje' => 'La fecha de nacimiento no es valida.'];
        }

        if (strlen($nuevaContrasena) < 8) {
            $conexion->close();
            return ['error' => true, 'mensaje' => 'La nueva contraseña debe tener al menos 8 caracteres.'];
        }

        try {
            $stmt = $conexion->prepare("SELECT id FROM trabajadores WHERE login_usuario = ? AND fecha_nacimiento = ? LIMIT 1");
            if (!$stmt) {
                return ['error' => true, 'mensaje' => 'Error preparando consulta: ' . $conexion->error];
            }

            $stmt->bind_param('ss', $login, $fechaNacimiento);
            $stmt->execute();
            $usuario = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            $stmt = null;

            if (!$usuario) {
                return ['error' => true, 'mensaje' => 'No se pudo verificar la informacion proporcionada.'];
            }

            // Guarda nueva contraseña con hash.
            $hash = password_hash($nuevaContrasena, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = $conexion->prepare("UPDATE trabajadores SET password_hash = ? WHERE id = ? LIMIT 1");
            if (!$stmt) {
                return ['error' => true, 'mensaje' => 'Error preparando consulta: ' . $conexion->error];
            }

            $stmt->bind_param('si', $hash, $usuario['id']);
            $stmt->execute();

            return ['error' => false, 'mensaje' => 'La contraseña ha sido actualizada correctamente.'];
        } catch (mysqli_sql_exception $e) {
            return ['error' => true, 'mensaje' => 'No se pudo restablecer la contraseña en este momento.'];
        } finally {
            if ($stmt instanceof mysqli_stmt) {
                $stmt->close();
            }
            $conexion->close();
        }
    }

    // -------------------------------------------------------
    // Helpers privados
    // -------------------------------------------------------
    private static function generarIdEmpresa(mysqli $con): string {
        // Genera un ID empresarial con formato GS-YYYY-NNN.
        $anio = date('Y');
        $res = $con->query("SELECT COUNT(*) AS total FROM trabajadores");
        $total = (int)$res->fetch_assoc()['total'];
        return 'GS-' . $anio . '-' . str_pad($total + 1, 3, '0', STR_PAD_LEFT);
    }

    private static function guardarFoto(string $tmpPath, string $nombreOriginal): ?string {
        // Valida extensión y mueve la foto al directorio de subidas.
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

    private static function normalizarFecha(?string $fecha): ?string {
        // Convierte cadenas vacías a null para evitar fechas inválidas.
        if ($fecha === null) {
            return null;
        }

        $fecha = trim($fecha);
        return $fecha === '' ? null : $fecha;
    }

    private static function esFechaValida(?string $fecha): bool {
        // Valida formato YYYY-MM-DD de forma estricta.
        if ($fecha === null) {
            return true;
        }

        $dt = DateTime::createFromFormat('Y-m-d', $fecha);
        return $dt instanceof DateTime && $dt->format('Y-m-d') === $fecha;
    }
}
