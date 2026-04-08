<?php

require_once __DIR__ . '/../configuracion/GracefulSpacesDB.configuracion.php';
require_once __DIR__ . '/ModeloNotificacion.php';

class ModeloSolicitudVacaciones {

    /**
     * Crear una nueva solicitud de vacaciones
     */
    public static function crearSolicitud(array $datos, int $idTrabajador): array {
        if (empty($datos['fecha_inicio']) || empty($datos['fecha_fin'])) {
            return ['error' => true, 'mensaje' => 'Las fechas de inicio y fin son obligatorias.'];
        }

        if (!self::esFechaValida($datos['fecha_inicio']) || !self::esFechaValida($datos['fecha_fin'])) {
            return ['error' => true, 'mensaje' => 'El formato de las fechas no es valido.'];
        }

        $fechaInicio = new DateTime($datos['fecha_inicio']);
        $fechaFin = new DateTime($datos['fecha_fin']);

        if ($fechaFin < $fechaInicio) {
            return ['error' => true, 'mensaje' => 'La fecha de fin no puede ser anterior a la fecha de inicio.'];
        }

        $diasSolicitados = $fechaFin->diff($fechaInicio)->days + 1;

        if ($diasSolicitados <= 0 || $diasSolicitados > 30) {
            return ['error' => true, 'mensaje' => 'Los dias solicitados deben estar entre 1 y 30 dias.'];
        }

        if ($diasSolicitados !== (int)($datos['dias_solicitados'] ?? 0)) {
            return ['error' => true, 'mensaje' => 'El numero de dias no coincide con el rango de fechas.'];
        }

        $conexion = obtenerConexion();
        self::asegurarTabla($conexion);

        $motivo = trim($datos['motivo'] ?? '');

        try {
            // Verificar que no haya conflictos con otras solicitudes aprobadas
            $sqlVerificar = "SELECT COUNT(*) as total FROM solicitudes_vacaciones
                            WHERE id_trabajador = ?
                            AND estado = 'Aprobada'
                            AND ((fecha_inicio <= ? AND fecha_fin >= ?)
                                 OR (fecha_inicio <= ? AND fecha_fin >= ?)
                                 OR (fecha_inicio >= ? AND fecha_fin <= ?))";

            $stmt = $conexion->prepare($sqlVerificar);
            if (!$stmt) {
                return ['error' => true, 'mensaje' => 'Error al verificar conflictos.'];
            }

            $stmt->bind_param(
                'issssss',
                $idTrabajador,
                $datos['fecha_fin'],
                $datos['fecha_inicio'],
                $datos['fecha_fin'],
                $datos['fecha_inicio'],
                $datos['fecha_inicio'],
                $datos['fecha_fin']
            );

            $stmt->execute();
            $resultado = $stmt->get_result();
            $fila = $resultado->fetch_assoc();
            $stmt->close();

            if ($fila['total'] > 0) {
                return ['error' => true, 'mensaje' => 'Ya tiene otras vacaciones aprobadas en este periodo.'];
            }

            // Insertar la solicitud
            $sql = "INSERT INTO solicitudes_vacaciones 
                    (id_trabajador, fecha_inicio, fecha_fin, dias_solicitados, motivo, estado)
                    VALUES (?, ?, ?, ?, ?, 'Pendiente')";

            $stmt = $conexion->prepare($sql);
            if (!$stmt) {
                return ['error' => true, 'mensaje' => 'Error al preparar la consulta.'];
            }

            $stmt->bind_param(
                'issii',
                $idTrabajador,
                $datos['fecha_inicio'],
                $datos['fecha_fin'],
                $diasSolicitados,
                $motivo
            );

            $ok = $stmt->execute();
            $idSolicitud = $stmt->insert_id;
            $stmt->close();

            if (!$ok) {
                return ['error' => true, 'mensaje' => 'No se pudo crear la solicitud.'];
            }

            return ['error' => false, 'mensaje' => 'Solicitud de vacaciones registrada correctamente.', 'id' => $idSolicitud];
        } catch (Throwable $e) {
            return ['error' => true, 'mensaje' => 'Error al guardar la solicitud: ' . $e->getMessage()];
        } finally {
            $conexion->close();
        }
    }

    /**
     * Obtener solicitudes de vacaciones del trabajador
     */
    public static function obtenerSolicitudesTrabajador(int $idTrabajador, ?string $estado = null): array {
        $conexion = obtenerConexion();
        self::asegurarTabla($conexion);

        $condiciones = ['sv.id_trabajador = ' . (int)$idTrabajador];

        if (!empty($estado) && in_array($estado, ['Pendiente', 'Aprobada', 'Rechazada'], true)) {
            $condiciones[] = "sv.estado = '" . $conexion->real_escape_string($estado) . "'";
        }

        $where = implode(' AND ', $condiciones);

        $sql = "SELECT sv.id,
                       sv.fecha_inicio,
                       sv.fecha_fin,
                       sv.dias_solicitados,
                       sv.motivo,
                       sv.estado,
                       sv.comentario_admin,
                       sv.creado_en,
                       CONCAT(t.nombre, ' ', t.apellido1) AS procesado_por
                FROM solicitudes_vacaciones sv
                LEFT JOIN trabajadores t ON t.id = sv.procesado_por
                WHERE $where
                ORDER BY sv.creado_en DESC";

        $resultado = $conexion->query($sql);
        $solicitudes = [];

        while ($fila = $resultado->fetch_assoc()) {
            $solicitudes[] = $fila;
        }

        $conexion->close();
        return $solicitudes;
    }

    /**
     * Obtener todas las solicitudes pendientes (para admin)
     */
    public static function obtenerSolicitudesPendientes(): array {
        $conexion = obtenerConexion();
        self::asegurarTabla($conexion);

        $sql = "SELECT sv.id,
                       sv.id_trabajador,
                       sv.fecha_inicio,
                       sv.fecha_fin,
                       sv.dias_solicitados,
                       sv.motivo,
                       sv.estado,
                       sv.creado_en,
                       CONCAT(t.nombre, ' ', t.apellido1, ' ', COALESCE(t.apellido2, '')) AS nombre_trabajador,
                       t.cargo,
                       t.correo_personal
                FROM solicitudes_vacaciones sv
                INNER JOIN trabajadores t ON t.id = sv.id_trabajador
                WHERE sv.estado = 'Pendiente'
                ORDER BY sv.creado_en ASC";

        $resultado = $conexion->query($sql);
        $solicitudes = [];

        while ($fila = $resultado->fetch_assoc()) {
            $solicitudes[] = $fila;
        }

        $conexion->close();
        return $solicitudes;
    }

    /**
     * Obtener todas las solicitudes para admin (filtrable)
     */
    public static function obtenerTodasSolicitudes(?string $estado = null, ?int $idTrabajador = null): array {
        $conexion = obtenerConexion();
        self::asegurarTabla($conexion);

        $condiciones = ['1=1'];

        if (!empty($estado) && in_array($estado, ['Pendiente', 'Aprobada', 'Rechazada'], true)) {
            $condiciones[] = "sv.estado = '" . $conexion->real_escape_string($estado) . "'";
        }

        if (!empty($idTrabajador) && $idTrabajador > 0) {
            $condiciones[] = 'sv.id_trabajador = ' . (int)$idTrabajador;
        }

        $where = implode(' AND ', $condiciones);

        $sql = "SELECT sv.id,
                       sv.id_trabajador,
                       sv.fecha_inicio,
                       sv.fecha_fin,
                       sv.dias_solicitados,
                       sv.motivo,
                       sv.estado,
                       sv.comentario_admin,
                       sv.creado_en,
                       sv.fecha_procesamiento,
                       CONCAT(t.nombre, ' ', t.apellido1, ' ', COALESCE(t.apellido2, '')) AS nombre_trabajador,
                       t.cargo,
                       t.correo_personal,
                       CONCAT(ta.nombre, ' ', ta.apellido1) AS procesado_por
                FROM solicitudes_vacaciones sv
                INNER JOIN trabajadores t ON t.id = sv.id_trabajador
                LEFT JOIN trabajadores ta ON ta.id = sv.procesado_por
                WHERE $where
                ORDER BY
                    CASE WHEN sv.estado = 'Pendiente' THEN 0 ELSE 1 END,
                    sv.creado_en DESC";

        $resultado = $conexion->query($sql);
        $solicitudes = [];

        while ($fila = $resultado->fetch_assoc()) {
            $solicitudes[] = $fila;
        }

        $conexion->close();
        return $solicitudes;
    }

    public static function obtenerSolicitudPorId(int $idSolicitud): ?array {
        $conexion = obtenerConexion();
        self::asegurarTabla($conexion);

        $stmt = $conexion->prepare(
            'SELECT sv.id, sv.id_trabajador, sv.fecha_inicio, sv.fecha_fin, sv.dias_solicitados, sv.motivo, sv.estado, sv.comentario_admin, sv.creado_en
             FROM solicitudes_vacaciones sv
             WHERE sv.id = ? LIMIT 1'
        );
        if (!$stmt) {
            $conexion->close();
            return null;
        }

        $stmt->bind_param('i', $idSolicitud);
        $stmt->execute();
        $solicitud = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $conexion->close();

        return $solicitud ?: null;
    }

    /**
     * Aprobar solicitud de vacaciones
     */
    public static function aprobarSolicitud(int $idSolicitud, int $idAdmin, ?string $comentario = null): array {
        $conexion = obtenerConexion();
        self::asegurarTabla($conexion);

        try {
            $comentario = trim($comentario ?? '');

            $sql = "UPDATE solicitudes_vacaciones 
                    SET estado = 'Aprobada',
                        comentario_admin = ?,
                        procesado_por = ?,
                        fecha_procesamiento = NOW()
                    WHERE id = ?";

            $stmt = $conexion->prepare($sql);
            if (!$stmt) {
                return ['error' => true, 'mensaje' => 'Error al preparar la consulta.'];
            }

            $stmt->bind_param('sii', $comentario, $idAdmin, $idSolicitud);
            $ok = $stmt->execute();
            $stmt->close();

            if (!$ok) {
                return ['error' => true, 'mensaje' => 'No se pudo aprobar la solicitud.'];
            }

            return ['error' => false, 'mensaje' => 'Solicitud aprobada correctamente.'];
        } catch (Throwable $e) {
            return ['error' => true, 'mensaje' => 'Error al aprobar: ' . $e->getMessage()];
        } finally {
            $conexion->close();
        }
    }

    /**
     * Rechazar solicitud de vacaciones
     */
    public static function rechazarSolicitud(int $idSolicitud, int $idAdmin, string $motivo): array {
        if (empty($motivo)) {
            return ['error' => true, 'mensaje' => 'Debe proporcionar un motivo para rechazar.'];
        }

        $conexion = obtenerConexion();
        self::asegurarTabla($conexion);

        try {
            $motivo = trim($motivo);

            $sql = "UPDATE solicitudes_vacaciones 
                    SET estado = 'Rechazada',
                        comentario_admin = ?,
                        procesado_por = ?,
                        fecha_procesamiento = NOW()
                    WHERE id = ?";

            $stmt = $conexion->prepare($sql);
            if (!$stmt) {
                return ['error' => true, 'mensaje' => 'Error al preparar la consulta.'];
            }

            $stmt->bind_param('sii', $motivo, $idAdmin, $idSolicitud);
            $ok = $stmt->execute();
            $stmt->close();

            if (!$ok) {
                return ['error' => true, 'mensaje' => 'No se pudo rechazar la solicitud.'];
            }

            return ['error' => false, 'mensaje' => 'Solicitud rechazada correctamente.'];
        } catch (Throwable $e) {
            return ['error' => true, 'mensaje' => 'Error al rechazar: ' . $e->getMessage()];
        } finally {
            $conexion->close();
        }
    }

    /**
     * Obtener detalles de una solicitud
     */
    public static function obtenerSolicitud(int $idSolicitud): ?array {
        $conexion = obtenerConexion();
        self::asegurarTabla($conexion);

        $sql = "SELECT sv.id,
                       sv.id_trabajador,
                       sv.fecha_inicio,
                       sv.fecha_fin,
                       sv.dias_solicitados,
                       sv.motivo,
                       sv.estado,
                       sv.comentario_admin,
                       sv.creado_en,
                       sv.fecha_procesamiento,
                       CONCAT(t.nombre, ' ', t.apellido1, ' ', COALESCE(t.apellido2, '')) AS nombre_trabajador,
                       t.cargo,
                       CONCAT(ta.nombre, ' ', ta.apellido1) AS procesado_por
                FROM solicitudes_vacaciones sv
                INNER JOIN trabajadores t ON t.id = sv.id_trabajador
                LEFT JOIN trabajadores ta ON ta.id = sv.procesado_por
                WHERE sv.id = ?
                LIMIT 1";

        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            $conexion->close();
            return null;
        }

        $stmt->bind_param('i', $idSolicitud);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $solicitud = $resultado->fetch_assoc();
        $stmt->close();
        $conexion->close();

        return $solicitud;
    }

    /**
     * Validar formato de fecha
     */
    private static function esFechaValida(?string $fecha): bool {
        if (empty($fecha)) {
            return false;
        }
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) === 1;
    }

    /**
     * Asegurar que la tabla existe
     */
    private static function asegurarTabla(mysqli $conexion): void {
        $sql = "CREATE TABLE IF NOT EXISTS `solicitudes_vacaciones` (
                    `id`                    INT AUTO_INCREMENT PRIMARY KEY,
                    `id_trabajador`         INT NOT NULL,
                    `fecha_inicio`          DATE NOT NULL,
                    `fecha_fin`             DATE NOT NULL,
                    `dias_solicitados`      INT NOT NULL,
                    `motivo`                TEXT,
                    `estado`                ENUM('Pendiente','Aprobada','Rechazada') DEFAULT 'Pendiente',
                    `comentario_admin`      TEXT,
                    `procesado_por`         INT NULL,
                    `fecha_procesamiento`   DATETIME NULL,
                    `creado_en`             TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `actualizado_en`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX `idx_trabajador` (`id_trabajador`),
                    INDEX `idx_estado` (`estado`),
                    INDEX `idx_fechas` (`fecha_inicio`, `fecha_fin`),
                    CONSTRAINT `fk_vacaciones_trabajador`
                        FOREIGN KEY (`id_trabajador`) REFERENCES `trabajadores`(`id`) ON DELETE CASCADE,
                    CONSTRAINT `fk_vacaciones_procesado_por`
                        FOREIGN KEY (`procesado_por`) REFERENCES `trabajadores`(`id`) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $conexion->query($sql);
    }
}
