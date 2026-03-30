<?php

require_once __DIR__ . '/../configuracion/GracefulSpacesDB.configuracion.php';

class ModeloProyecto {

    public static function crearProyecto(array $datos, array $idsColaboradores, int $idAdmin): array {
        if (empty($datos['nombre'])) {
            return ['error' => true, 'mensaje' => 'El nombre del proyecto es obligatorio.'];
        }

        if (empty($idsColaboradores)) {
            return ['error' => true, 'mensaje' => 'Debes asignar al menos un colaborador.'];
        }

        $conexion = obtenerConexion();
        self::asegurarTablas($conexion);

        $sqlProyecto = "INSERT INTO proyectos (
                            nombre,
                            detalles,
                            especificaciones,
                            horarios,
                            materiales,
                            creado_por
                        ) VALUES (?, ?, ?, ?, ?, ?)";

        $stmtProyecto = $conexion->prepare($sqlProyecto);
        if (!$stmtProyecto) {
            $conexion->close();
            return ['error' => true, 'mensaje' => 'Error al preparar la creación del proyecto.'];
        }

        $stmtProyecto->bind_param(
            'sssssi',
            $datos['nombre'],
            $datos['detalles'],
            $datos['especificaciones'],
            $datos['horarios'],
            $datos['materiales'],
            $idAdmin
        );

        $okProyecto = $stmtProyecto->execute();
        $idProyecto = (int)$conexion->insert_id;
        $stmtProyecto->close();

        if (!$okProyecto || $idProyecto <= 0) {
            $conexion->close();
            return ['error' => true, 'mensaje' => 'No se pudo crear el proyecto.'];
        }

        $sqlAsignacion = "INSERT IGNORE INTO proyecto_colaboradores (id_proyecto, id_trabajador, terminado)
                          VALUES (?, ?, 0)";
        $stmtAsignacion = $conexion->prepare($sqlAsignacion);

        if (!$stmtAsignacion) {
            $conexion->close();
            return ['error' => true, 'mensaje' => 'Proyecto creado, pero no se pudieron preparar asignaciones.'];
        }

        $totalAsignados = 0;

        foreach ($idsColaboradores as $idColaborador) {
            $idColaborador = (int)$idColaborador;
            if ($idColaborador <= 0) {
                continue;
            }

            $stmtAsignacion->bind_param('ii', $idProyecto, $idColaborador);
            if ($stmtAsignacion->execute()) {
                $totalAsignados++;
            }
        }

        $stmtAsignacion->close();
        $conexion->close();

        return [
            'error' => false,
            'mensaje' => 'Proyecto creado y colaboradores asignados correctamente.',
            'id_proyecto' => $idProyecto,
            'asignados' => $totalAsignados,
        ];
    }

    public static function obtenerColaboradoresDisponibles(): array {
        $conexion = obtenerConexion();
        self::asegurarTablas($conexion);

        $sql = "SELECT id, id_empresa, nombre, apellido1, COALESCE(apellido2, '') AS apellido2, rol, estado
                FROM trabajadores
            WHERE rol IN ('Trabajador', 'Supervisor')
            ORDER BY (estado = 'Activo') DESC, nombre ASC, apellido1 ASC";

        $resultado = $conexion->query($sql);
        $filas = [];

        while ($fila = $resultado->fetch_assoc()) {
            $filas[] = $fila;
        }

        $conexion->close();
        return $filas;
    }

    public static function obtenerProyectosAdmin(): array {
        $conexion = obtenerConexion();
        self::asegurarTablas($conexion);

        $sql = "SELECT p.id,
                       p.nombre,
                       p.detalles,
                       p.especificaciones,
                       p.horarios,
                       p.materiales,
                       p.creado_en,
                       p.estado_general,
                       COUNT(pc.id) AS total_colaboradores,
                       SUM(CASE WHEN pc.terminado = 1 THEN 1 ELSE 0 END) AS colaboradores_terminados
                FROM proyectos p
                LEFT JOIN proyecto_colaboradores pc ON pc.id_proyecto = p.id
                GROUP BY p.id
                ORDER BY p.creado_en DESC";

        $resultado = $conexion->query($sql);
        $proyectos = [];

        while ($fila = $resultado->fetch_assoc()) {
            $fila['colaboradores'] = self::obtenerColaboradoresPorProyectoInterno($conexion, (int)$fila['id']);
            $proyectos[] = $fila;
        }

        $conexion->close();
        return $proyectos;
    }

    public static function obtenerProyectosPorColaborador(int $idColaborador): array {
        $conexion = obtenerConexion();
        self::asegurarTablas($conexion);

        $sql = "SELECT p.id,
                       p.nombre,
                       p.detalles,
                       p.especificaciones,
                       p.horarios,
                       p.materiales,
                       p.estado_general,
                       p.creado_en,
                       pc.terminado,
                       pc.fecha_terminado
                FROM proyecto_colaboradores pc
                INNER JOIN proyectos p ON p.id = pc.id_proyecto
                WHERE pc.id_trabajador = ?
                ORDER BY p.creado_en DESC";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param('i', $idColaborador);
        $stmt->execute();
        $resultado = $stmt->get_result();

        $filas = [];
        while ($fila = $resultado->fetch_assoc()) {
            $filas[] = $fila;
        }

        $stmt->close();
        $conexion->close();

        return $filas;
    }

    public static function actualizarTerminadoColaborador(int $idProyecto, int $idColaborador, bool $terminado): array {
        $conexion = obtenerConexion();
        self::asegurarTablas($conexion);

        $sqlExiste = "SELECT id FROM proyecto_colaboradores
                      WHERE id_proyecto = ? AND id_trabajador = ?
                      LIMIT 1";

        $stmtExiste = $conexion->prepare($sqlExiste);
        $stmtExiste->bind_param('ii', $idProyecto, $idColaborador);
        $stmtExiste->execute();
        $asignacion = $stmtExiste->get_result()->fetch_assoc();
        $stmtExiste->close();

        if (!$asignacion) {
            $conexion->close();
            return ['error' => true, 'mensaje' => 'No tienes asignación en este proyecto.'];
        }

        $valorTerminado = $terminado ? 1 : 0;
        $fechaTerminado = $terminado ? date('Y-m-d H:i:s') : null;

        $sqlUpdate = "UPDATE proyecto_colaboradores
                      SET terminado = ?, fecha_terminado = ?
                      WHERE id_proyecto = ? AND id_trabajador = ?";

        $stmtUpdate = $conexion->prepare($sqlUpdate);
        $stmtUpdate->bind_param('isii', $valorTerminado, $fechaTerminado, $idProyecto, $idColaborador);
        $ok = $stmtUpdate->execute();
        $stmtUpdate->close();

        self::recalcularEstadoGeneralProyecto($conexion, $idProyecto);

        $conexion->close();

        if (!$ok) {
            return ['error' => true, 'mensaje' => 'No se pudo actualizar el estado del proyecto.'];
        }

        return ['error' => false, 'mensaje' => 'Estado de finalización actualizado.'];
    }

    private static function obtenerColaboradoresPorProyectoInterno(mysqli $conexion, int $idProyecto): array {
        $sql = "SELECT t.id,
                       t.id_empresa,
                       CONCAT(t.nombre, ' ', t.apellido1, ' ', COALESCE(t.apellido2, '')) AS colaborador,
                       t.rol,
                       pc.terminado
                FROM proyecto_colaboradores pc
                INNER JOIN trabajadores t ON t.id = pc.id_trabajador
                WHERE pc.id_proyecto = ?
                ORDER BY colaborador ASC";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param('i', $idProyecto);
        $stmt->execute();
        $resultado = $stmt->get_result();

        $filas = [];
        while ($fila = $resultado->fetch_assoc()) {
            $filas[] = $fila;
        }

        $stmt->close();
        return $filas;
    }

    private static function recalcularEstadoGeneralProyecto(mysqli $conexion, int $idProyecto): void {
        $sql = "SELECT COUNT(*) AS total,
                       SUM(CASE WHEN terminado = 1 THEN 1 ELSE 0 END) AS finalizados
                FROM proyecto_colaboradores
                WHERE id_proyecto = ?";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param('i', $idProyecto);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $total = (int)($res['total'] ?? 0);
        $finalizados = (int)($res['finalizados'] ?? 0);

        $estado = 'En progreso';
        if ($total > 0 && $finalizados >= $total) {
            $estado = 'Finalizado';
        }

        $stmtEstado = $conexion->prepare('UPDATE proyectos SET estado_general = ? WHERE id = ?');
        $stmtEstado->bind_param('si', $estado, $idProyecto);
        $stmtEstado->execute();
        $stmtEstado->close();
    }

    private static function asegurarTablas(mysqli $conexion): void {
        $sqlProyectos = "CREATE TABLE IF NOT EXISTS proyectos (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            nombre VARCHAR(180) NOT NULL,
                            detalles TEXT,
                            especificaciones TEXT,
                            horarios TEXT,
                            materiales TEXT,
                            estado_general ENUM('En progreso','Finalizado') DEFAULT 'En progreso',
                            creado_por INT NULL,
                            creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                            actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                            INDEX idx_estado_general (estado_general)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $sqlAsignaciones = "CREATE TABLE IF NOT EXISTS proyecto_colaboradores (
                               id INT AUTO_INCREMENT PRIMARY KEY,
                               id_proyecto INT NOT NULL,
                               id_trabajador INT NOT NULL,
                               terminado TINYINT(1) NOT NULL DEFAULT 0,
                               fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                               fecha_terminado DATETIME NULL,
                               UNIQUE KEY uniq_proyecto_trabajador (id_proyecto, id_trabajador),
                               INDEX idx_trabajador (id_trabajador),
                               CONSTRAINT fk_pc_proyecto FOREIGN KEY (id_proyecto)
                                   REFERENCES proyectos(id) ON DELETE CASCADE,
                               CONSTRAINT fk_pc_trabajador FOREIGN KEY (id_trabajador)
                                   REFERENCES trabajadores(id) ON DELETE CASCADE
                           ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $conexion->query($sqlProyectos);
        $conexion->query($sqlAsignaciones);
    }
}
