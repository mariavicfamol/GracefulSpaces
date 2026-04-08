<?php

require_once __DIR__ . '/../configuracion/GracefulSpacesDB.configuracion.php';

class ModeloProductoFaltante {

    public static function crearProducto(array $datos, int $idTrabajador): array {
        if (empty($datos['nombre'])) {
            return ['error' => true, 'mensaje' => 'El nombre del producto es obligatorio.'];
        }

        $conexion = obtenerConexion();
        self::asegurarTabla($conexion);

        $nombre = trim($datos['nombre']);
        $descripcion = trim($datos['descripcion'] ?? '');
        $cantidadSolicitada = max(1, (int)($datos['cantidad_solicitada'] ?? 1));

        try {
            $sql = "INSERT INTO productos_faltantes (nombre, descripcion, cantidad_solicitada, id_trabajador)
                    VALUES (?, ?, ?, ?)";

            $stmt = $conexion->prepare($sql);
            if (!$stmt) {
                return ['error' => true, 'mensaje' => 'Error al preparar la consulta.'];
            }

            $stmt->bind_param('ssii', $nombre, $descripcion, $cantidadSolicitada, $idTrabajador);
            $ok = $stmt->execute();
            $stmt->close();

            if (!$ok) {
                return ['error' => true, 'mensaje' => 'No se pudo crear el producto.'];
            }

            return ['error' => false, 'mensaje' => 'Producto registrado correctamente.'];
        } catch (Throwable $e) {
            return ['error' => true, 'mensaje' => 'Error al guardar el producto: ' . $e->getMessage()];
        } finally {
            $conexion->close();
        }
    }

    public static function obtenerProductos(?string $estado = null, ?int $idTrabajador = null): array {
        $conexion = obtenerConexion();
        self::asegurarTabla($conexion);

        $condiciones = ['1=1'];

        if (!empty($estado) && in_array($estado, ['Pendiente', 'Comprado'], true)) {
            $condiciones[] = "p.estado = '" . $conexion->real_escape_string($estado) . "'";
        }

        if (!empty($idTrabajador) && $idTrabajador > 0) {
            $condiciones[] = 'p.id_trabajador = ' . (int)$idTrabajador;
        }

        $where = implode(' AND ', $condiciones);

        $sql = "SELECT p.id,
                       p.nombre,
                       p.descripcion,
                       p.cantidad_solicitada,
                       p.estado,
                       p.fecha_solicitud,
                       p.fecha_compra,
                       CONCAT(t.nombre, ' ', t.apellido1, ' ', COALESCE(t.apellido2, '')) AS solicitante,
                       CONCAT(tc.nombre, ' ', tc.apellido1, ' ', COALESCE(tc.apellido2, '')) AS comprador
                FROM productos_faltantes p
                INNER JOIN trabajadores t ON t.id = p.id_trabajador
                LEFT JOIN trabajadores tc ON tc.id = p.comprado_por
                WHERE $where
                ORDER BY
                    CASE WHEN p.estado = 'Pendiente' THEN 0 ELSE 1 END,
                    p.fecha_solicitud DESC";

        $resultado = $conexion->query($sql);
        $productos = [];

        while ($fila = $resultado->fetch_assoc()) {
            $productos[] = $fila;
        }

        $conexion->close();
        return $productos;
    }

    public static function marcarComprrado(int $idProducto, int $idAdmin): array {
        $conexion = obtenerConexion();
        self::asegurarTabla($conexion);

        try {
            $sql = "UPDATE productos_faltantes 
                    SET estado = 'Comprado', 
                        comprado_por = ?, 
                        fecha_compra = NOW()
                    WHERE id = ?";

            $stmt = $conexion->prepare($sql);
            if (!$stmt) {
                return ['error' => true, 'mensaje' => 'Error al preparar la consulta.'];
            }

            $stmt->bind_param('ii', $idAdmin, $idProducto);
            $ok = $stmt->execute();
            $stmt->close();

            if (!$ok) {
                return ['error' => true, 'mensaje' => 'No se pudo actualizar el producto.'];
            }

            return ['error' => false, 'mensaje' => 'Producto marcado como comprado.'];
        } catch (Throwable $e) {
            return ['error' => true, 'mensaje' => 'Error al actualizar: ' . $e->getMessage()];
        } finally {
            $conexion->close();
        }
    }

    public static function eliminarProducto(int $idProducto): array {
        $conexion = obtenerConexion();
        self::asegurarTabla($conexion);

        try {
            $sql = "DELETE FROM productos_faltantes WHERE id = ? LIMIT 1";

            $stmt = $conexion->prepare($sql);
            if (!$stmt) {
                return ['error' => true, 'mensaje' => 'Error al preparar la consulta.'];
            }

            $stmt->bind_param('i', $idProducto);
            $ok = $stmt->execute();
            $stmt->close();

            if (!$ok) {
                return ['error' => true, 'mensaje' => 'No se pudo eliminar el producto.'];
            }

            return ['error' => false, 'mensaje' => 'Producto eliminado correctamente.'];
        } catch (Throwable $e) {
            return ['error' => true, 'mensaje' => 'Error al eliminar: ' . $e->getMessage()];
        } finally {
            $conexion->close();
        }
    }

    private static function asegurarTabla(mysqli $conexion): void {
        $sql = "CREATE TABLE IF NOT EXISTS `productos_faltantes` (
                    `id`                    INT AUTO_INCREMENT PRIMARY KEY,
                    `nombre`                VARCHAR(180) NOT NULL,
                    `descripcion`           TEXT,
                    `cantidad_solicitada`   INT DEFAULT 1,
                    `estado`                ENUM('Pendiente','Comprado') DEFAULT 'Pendiente',
                    `id_trabajador`         INT NOT NULL,
                    `comprado_por`          INT NULL,
                    `fecha_solicitud`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `fecha_compra`          DATETIME NULL,
                    `creado_en`             TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `actualizado_en`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX `idx_estado` (`estado`),
                    INDEX `idx_trabajador` (`id_trabajador`),
                    INDEX `idx_fecha` (`fecha_solicitud`),
                    CONSTRAINT `fk_productos_trabajador`
                        FOREIGN KEY (`id_trabajador`) REFERENCES `trabajadores`(`id`) ON DELETE CASCADE,
                    CONSTRAINT `fk_productos_comprado_por`
                        FOREIGN KEY (`comprado_por`) REFERENCES `trabajadores`(`id`) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $conexion->query($sql);
    }
}
