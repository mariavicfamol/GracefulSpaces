<?php

require_once __DIR__ . '/../configuracion/GracefulSpacesDB.configuracion.php';

class ModeloNotificacion {

    public static function crearNotificacion(int $idTrabajador, string $mensaje, string $tipo = 'general'): bool {
        if ($idTrabajador <= 0 || trim($mensaje) === '') {
            return false;
        }

        $conexion = obtenerConexion();
        self::asegurarTabla($conexion);

        $mensaje = mb_substr(trim($mensaje), 0, 255);
        $tipo = mb_substr(trim($tipo), 0, 50);

        $stmt = $conexion->prepare(
            'INSERT INTO notificaciones (id_trabajador, mensaje, tipo) VALUES (?, ?, ?)'
        );
        if (!$stmt) {
            $conexion->close();
            return false;
        }

        $stmt->bind_param('iss', $idTrabajador, $mensaje, $tipo);
        $exito = $stmt->execute();
        $stmt->close();
        $conexion->close();

        return $exito;
    }

    public static function enviarAAdmins(string $mensaje, string $tipo = 'admin'): int {
        $conexion = obtenerConexion();
        self::asegurarTabla($conexion);

        $admins = self::obtenerAdministradoresActivos($conexion);
        if (empty($admins)) {
            $conexion->close();
            return 0;
        }

        $mensaje = mb_substr(trim($mensaje), 0, 255);
        $tipo = mb_substr(trim($tipo), 0, 50);

        $stmt = $conexion->prepare(
            'INSERT INTO notificaciones (id_trabajador, mensaje, tipo) VALUES (?, ?, ?)'
        );
        if (!$stmt) {
            $conexion->close();
            return 0;
        }

        $insertados = 0;
        foreach ($admins as $adminId) {
            $stmt->bind_param('iss', $adminId, $mensaje, $tipo);
            if ($stmt->execute()) {
                $insertados++;
            }
        }

        $stmt->close();
        $conexion->close();
        return $insertados;
    }

    public static function obtenerNotificaciones(int $idTrabajador, bool $soloNoLeidas = false): array {
        if ($idTrabajador <= 0) {
            return [];
        }

        $conexion = obtenerConexion();
        self::asegurarTabla($conexion);

        $sql = 'SELECT id, mensaje, tipo, leido, fecha FROM notificaciones WHERE id_trabajador = ?';
        if ($soloNoLeidas) {
            $sql .= ' AND leido = 0';
        }
        $sql .= ' ORDER BY fecha DESC';

        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            $conexion->close();
            return [];
        }

        $stmt->bind_param('i', $idTrabajador);
        $stmt->execute();
        $resultado = $stmt->get_result();

        $notificaciones = [];
        while ($fila = $resultado->fetch_assoc()) {
            $fila['leido'] = (bool)$fila['leido'];
            $notificaciones[] = $fila;
        }

        $stmt->close();
        $conexion->close();

        return $notificaciones;
    }

    public static function contarNotificacionesNoLeidas(int $idTrabajador): int {
        if ($idTrabajador <= 0) {
            return 0;
        }

        $conexion = obtenerConexion();
        self::asegurarTabla($conexion);

        $stmt = $conexion->prepare('SELECT COUNT(*) AS total FROM notificaciones WHERE id_trabajador = ? AND leido = 0');
        if (!$stmt) {
            $conexion->close();
            return 0;
        }

        $stmt->bind_param('i', $idTrabajador);
        $stmt->execute();
        $resultado = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $conexion->close();

        return (int)($resultado['total'] ?? 0);
    }

    public static function marcarLeida(int $idNotificacion, int $idTrabajador): bool {
        if ($idNotificacion <= 0 || $idTrabajador <= 0) {
            return false;
        }

        $conexion = obtenerConexion();
        self::asegurarTabla($conexion);

        $stmt = $conexion->prepare('UPDATE notificaciones SET leido = 1 WHERE id = ? AND id_trabajador = ?');
        if (!$stmt) {
            $conexion->close();
            return false;
        }

        $stmt->bind_param('ii', $idNotificacion, $idTrabajador);
        $exito = $stmt->execute();
        $stmt->close();
        $conexion->close();

        return $exito;
    }

    public static function marcarTodasLeidas(int $idTrabajador): bool {
        if ($idTrabajador <= 0) {
            return false;
        }

        $conexion = obtenerConexion();
        self::asegurarTabla($conexion);

        $stmt = $conexion->prepare('UPDATE notificaciones SET leido = 1 WHERE id_trabajador = ? AND leido = 0');
        if (!$stmt) {
            $conexion->close();
            return false;
        }

        $stmt->bind_param('i', $idTrabajador);
        $exito = $stmt->execute();
        $stmt->close();
        $conexion->close();

        return $exito;
    }

    private static function obtenerAdministradoresActivos(mysqli $conexion): array {
        $sql = "SELECT id FROM trabajadores WHERE rol IN ('Administrador Total', 'Administrador') AND estado = 'Activo'";
        $resultado = $conexion->query($sql);
        $admins = [];

        while ($fila = $resultado->fetch_assoc()) {
            $admins[] = (int)$fila['id'];
        }

        return $admins;
    }

    private static function asegurarTabla(mysqli $conexion): void {
        $sql = "CREATE TABLE IF NOT EXISTS notificaciones (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    id_trabajador INT NOT NULL,
                    mensaje VARCHAR(255) NOT NULL,
                    tipo VARCHAR(50) DEFAULT 'general',
                    leido TINYINT(1) NOT NULL DEFAULT 0,
                    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_trabajador (id_trabajador),
                    CONSTRAINT fk_notificaciones_trabajador
                        FOREIGN KEY (id_trabajador) REFERENCES trabajadores(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $conexion->query($sql);
    }
}
