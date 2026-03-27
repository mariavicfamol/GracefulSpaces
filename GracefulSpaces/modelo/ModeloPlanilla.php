<?php

require_once __DIR__ . '/../configuracion/GracefulSpacesDB.configuracion.php';

class ModeloPlanilla {

    // -------------------------------------------------------
    // CREAR nueva planilla
    // -------------------------------------------------------
    public static function crearPlanilla(array $datos): array {
        $conexion = obtenerConexion();

        // Generar ID planilla
        $idPlanilla = self::generarIdPlanilla($conexion);

        $sql = "INSERT INTO planillas (
                    id_planilla, id_trabajador, periodo_inicio, periodo_fin,
                    cantidad_horas, tarifa_hora, estado, notas
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            return ['error' => true, 'mensaje' => 'Error preparando consulta: ' . $conexion->error];
        }

        $stmt->bind_param(
            'sissddss',
            $idPlanilla,
            $datos['id_trabajador'],
            $datos['periodo_inicio'],
            $datos['periodo_fin'],
            $datos['cantidad_horas'],
            $datos['tarifa_hora'],
            $datos['estado'],
            $datos['notas']
        );

        if ($stmt->execute()) {
            $stmt->close();
            $conexion->close();
            return ['error' => false, 'mensaje' => 'Planilla creada exitosamente.', 'id_planilla' => $idPlanilla];
        }

        $error = $stmt->error;
        $stmt->close();
        $conexion->close();
        return ['error' => true, 'mensaje' => 'Error al crear planilla: ' . $error];
    }

    // -------------------------------------------------------
    // OBTENER todas las planillas
    // -------------------------------------------------------
    public static function obtenerPlanillas(?int $estado = null): array {
        $conexion = obtenerConexion();

        $sql = "SELECT p.id, p.id_planilla, p.id_trabajador, 
                       CONCAT(t.nombre, ' ', t.apellido1, ' ', COALESCE(t.apellido2, '')) as trabajador,
                       p.periodo_inicio, p.periodo_fin, p.cantidad_horas, p.tarifa_hora,
                       p.monto_total, p.estado, p.notas, p.creado_en
                FROM planillas p
                INNER JOIN trabajadores t ON p.id_trabajador = t.id";

        if ($estado !== null) {
            $sql .= " WHERE p.estado = ?";
        }

        $sql .= " ORDER BY p.periodo_inicio DESC";

        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            return [];
        }

        if ($estado !== null) {
            $stmt->bind_param('s', $estado);
        }

        $stmt->execute();
        $resultado = $stmt->get_result();

        $planillas = [];
        while ($fila = $resultado->fetch_assoc()) {
            $planillas[] = $fila;
        }

        $stmt->close();
        $conexion->close();

        return $planillas;
    }

    // -------------------------------------------------------
    // OBTENER planilla por ID
    // -------------------------------------------------------
    public static function obtenerPlanillaPorId(int $id): ?array {
        $conexion = obtenerConexion();

        $sql = "SELECT p.id, p.id_planilla, p.id_trabajador, 
                       CONCAT(t.nombre, ' ', t.apellido1, ' ', COALESCE(t.apellido2, '')) as trabajador,
                       p.periodo_inicio, p.periodo_fin, p.cantidad_horas, p.tarifa_hora,
                       p.monto_total, p.estado, p.notas, p.creado_en
                FROM planillas p
                INNER JOIN trabajadores t ON p.id_trabajador = t.id
                WHERE p.id = ?";

        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('i', $id);
        $stmt->execute();
        $resultado = $stmt->get_result();

        $planilla = $resultado->fetch_assoc();
        $stmt->close();
        $conexion->close();

        return $planilla;
    }

    // -------------------------------------------------------
    // ACTUALIZAR planilla
    // -------------------------------------------------------
    public static function actualizarPlanilla(int $id, array $datos): array {
        $conexion = obtenerConexion();

        $sql = "UPDATE planillas SET
                    cantidad_horas = ?,
                    tarifa_hora = ?,
                    estado = ?,
                    notas = ?
                WHERE id = ?";

        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            return ['error' => true, 'mensaje' => 'Error preparando consulta: ' . $conexion->error];
        }

        $stmt->bind_param(
            'ddssi',
            $datos['cantidad_horas'],
            $datos['tarifa_hora'],
            $datos['estado'],
            $datos['notas'],
            $id
        );

        if ($stmt->execute()) {
            $stmt->close();
            $conexion->close();
            return ['error' => false, 'mensaje' => 'Planilla actualizada exitosamente.'];
        }

        $error = $stmt->error;
        $stmt->close();
        $conexion->close();
        return ['error' => true, 'mensaje' => 'Error al actualizar: ' . $error];
    }

    // -------------------------------------------------------
    // ELIMINAR/CANCELAR planilla
    // -------------------------------------------------------
    public static function cancelarPlanilla(int $id): array {
        $conexion = obtenerConexion();

        $sql = "UPDATE planillas SET estado = 'Cancelada' WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            return ['error' => true, 'mensaje' => 'Error preparando consulta: ' . $conexion->error];
        }

        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            $stmt->close();
            $conexion->close();
            return ['error' => false, 'mensaje' => 'Planilla cancelada exitosamente.'];
        }

        $error = $stmt->error;
        $stmt->close();
        $conexion->close();
        return ['error' => true, 'mensaje' => 'Error al cancelar: ' . $error];
    }

    // -------------------------------------------------------
    // GENERAR ID de planilla único
    // -------------------------------------------------------
    private static function generarIdPlanilla(mysqli $conexion): string {
        do {
            $idPlanilla = 'PL-' . date('Y') . '-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
            $sql = "SELECT COUNT(*) FROM planillas WHERE id_planilla = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param('s', $idPlanilla);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $row = $resultado->fetch_array();
            $existe = $row[0] > 0;
            $stmt->close();
        } while ($existe);

        return $idPlanilla;
    }

    // -------------------------------------------------------
    // OBTENER trabajadores activos (para seleccionar en formulario)
    // -------------------------------------------------------
    public static function obtenerTrabajadoresActivos(): array {
        $conexion = obtenerConexion();

        $sql = "SELECT id, nombre, apellido1, apellido2, id_empresa
                FROM trabajadores
                WHERE estado = 'Activo'
                ORDER BY nombre ASC";

        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            return [];
        }

        $stmt->execute();
        $resultado = $stmt->get_result();

        $trabajadores = [];
        while ($fila = $resultado->fetch_assoc()) {
            $trabajadores[] = $fila;
        }

        $stmt->close();
        $conexion->close();

        return $trabajadores;
    }
}
