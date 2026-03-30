<?php

require_once __DIR__ . '/../configuracion/GracefulSpacesDB.configuracion.php';

class ModeloPlanilla {

    public static function generarPlanillasMensuales(int $anio, int $mes, float $tarifaHora, int $idGenerador): array {
        if ($anio < 2000 || $anio > 2100) {
            return ['error' => true, 'mensaje' => 'El año indicado no es válido.'];
        }

        if ($mes < 1 || $mes > 12) {
            return ['error' => true, 'mensaje' => 'El mes indicado no es válido.'];
        }

        if ($tarifaHora <= 0) {
            return ['error' => true, 'mensaje' => 'La tarifa por hora debe ser mayor a 0.'];
        }

        $conexion = obtenerConexion();
        self::asegurarTablas($conexion);

        $trabajadores = self::obtenerTrabajadoresActivosInterno($conexion);
        $generadas = 0;

        foreach ($trabajadores as $trabajador) {
            $idTrabajador = (int)$trabajador['id'];
            $marcaciones = self::obtenerMarcacionesDelMesInterno($conexion, $idTrabajador, $anio, $mes);

            $horasTotales = 0.0;
            foreach ($marcaciones as $marcacion) {
                $horasTotales += self::calcularHorasLaboradas($marcacion['hora_entrada'] ?? null, $marcacion['hora_salida'] ?? null);
            }

            $horasTotales = round($horasTotales, 2);
            $montoTotal = round($horasTotales * $tarifaHora, 2);

            $sqlPlanilla = "INSERT INTO planillas_mensuales
                            (id_trabajador, anio, mes, tarifa_hora, horas_totales, monto_total, creado_por)
                            VALUES (?, ?, ?, ?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE
                                tarifa_hora = VALUES(tarifa_hora),
                                horas_totales = VALUES(horas_totales),
                                monto_total = VALUES(monto_total),
                                creado_por = VALUES(creado_por),
                                fecha_generacion = CURRENT_TIMESTAMP";

            $stmtPlanilla = $conexion->prepare($sqlPlanilla);
            $stmtPlanilla->bind_param('iiidddi', $idTrabajador, $anio, $mes, $tarifaHora, $horasTotales, $montoTotal, $idGenerador);
            $okPlanilla = $stmtPlanilla->execute();
            $stmtPlanilla->close();

            if (!$okPlanilla) {
                continue;
            }

            $idPlanilla = (int)$conexion->insert_id;
            if ($idPlanilla === 0) {
                $stmtId = $conexion->prepare('SELECT id FROM planillas_mensuales WHERE id_trabajador = ? AND anio = ? AND mes = ? LIMIT 1');
                $stmtId->bind_param('iii', $idTrabajador, $anio, $mes);
                $stmtId->execute();
                $resId = $stmtId->get_result();
                $rowId = $resId->fetch_assoc();
                $stmtId->close();
                $idPlanilla = (int)($rowId['id'] ?? 0);
            }

            if ($idPlanilla <= 0) {
                continue;
            }

            $stmtDelete = $conexion->prepare('DELETE FROM planilla_detalles WHERE id_planilla = ?');
            $stmtDelete->bind_param('i', $idPlanilla);
            $stmtDelete->execute();
            $stmtDelete->close();

            if (!empty($marcaciones)) {
                $stmtDetalle = $conexion->prepare(
                    'INSERT INTO planilla_detalles (id_planilla, fecha_marcacion, hora_entrada, hora_salida, horas_laboradas) VALUES (?, ?, ?, ?, ?)'
                );

                foreach ($marcaciones as $marcacion) {
                    $fecha = $marcacion['fecha_marcacion'];
                    $horaEntrada = $marcacion['hora_entrada'] ?: null;
                    $horaSalida = $marcacion['hora_salida'] ?: null;
                    $horas = self::calcularHorasLaboradas($horaEntrada, $horaSalida);

                    $stmtDetalle->bind_param('isssd', $idPlanilla, $fecha, $horaEntrada, $horaSalida, $horas);
                    $stmtDetalle->execute();
                }

                $stmtDetalle->close();
            }

            $generadas++;
        }

        $conexion->close();

        return [
            'error' => false,
            'mensaje' => 'Planillas generadas/actualizadas correctamente.',
            'total_empleados' => count($trabajadores),
            'total_planillas' => $generadas,
        ];
    }

    public static function obtenerTrabajadoresActivos(): array {
        $conexion = obtenerConexion();
        self::asegurarTablas($conexion);

        $trabajadores = self::obtenerTrabajadoresActivosInterno($conexion);
        $conexion->close();

        return $trabajadores;
    }

    public static function obtenerPlanillasAdmin(?int $anio = null, ?int $mes = null, ?int $idTrabajador = null): array {
        $conexion = obtenerConexion();
        self::asegurarTablas($conexion);

        $condiciones = ['1=1'];

        if (!empty($anio) && $anio > 0) {
            $condiciones[] = 'p.anio = ' . (int)$anio;
        }

        if (!empty($mes) && $mes > 0) {
            $condiciones[] = 'p.mes = ' . (int)$mes;
        }

        if (!empty($idTrabajador) && $idTrabajador > 0) {
            $condiciones[] = 'p.id_trabajador = ' . (int)$idTrabajador;
        }

        $where = implode(' AND ', $condiciones);

        $sql = "SELECT p.id,
                       p.id_trabajador,
                       p.anio,
                       p.mes,
                       p.tarifa_hora,
                       p.horas_totales,
                       p.monto_total,
                       p.fecha_generacion,
                       t.id_empresa,
                       CONCAT(t.nombre, ' ', t.apellido1, ' ', COALESCE(t.apellido2, '')) AS trabajador
                FROM planillas_mensuales p
                INNER JOIN trabajadores t ON t.id = p.id_trabajador
                WHERE $where
                ORDER BY p.anio DESC, p.mes DESC, trabajador ASC";

        $resultado = $conexion->query($sql);
        $filas = [];

        while ($fila = $resultado->fetch_assoc()) {
            $filas[] = $fila;
        }

        $conexion->close();
        return $filas;
    }

    public static function obtenerPlanillasPorTrabajador(int $idTrabajador): array {
        $conexion = obtenerConexion();
        self::asegurarTablas($conexion);

        $sql = "SELECT p.id,
                       p.id_trabajador,
                       p.anio,
                       p.mes,
                       p.tarifa_hora,
                       p.horas_totales,
                       p.monto_total,
                       p.fecha_generacion
                FROM planillas_mensuales p
                WHERE p.id_trabajador = ?
                ORDER BY p.anio DESC, p.mes DESC";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param('i', $idTrabajador);
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

    public static function obtenerPlanillaConDetalles(int $idPlanilla): ?array {
        $conexion = obtenerConexion();
        self::asegurarTablas($conexion);

        $sqlCabecera = "SELECT p.id,
                               p.id_trabajador,
                               p.anio,
                               p.mes,
                               p.tarifa_hora,
                               p.horas_totales,
                               p.monto_total,
                               p.fecha_generacion,
                               t.id_empresa,
                               CONCAT(t.nombre, ' ', t.apellido1, ' ', COALESCE(t.apellido2, '')) AS trabajador
                        FROM planillas_mensuales p
                        INNER JOIN trabajadores t ON t.id = p.id_trabajador
                        WHERE p.id = ?
                        LIMIT 1";

        $stmtCabecera = $conexion->prepare($sqlCabecera);
        $stmtCabecera->bind_param('i', $idPlanilla);
        $stmtCabecera->execute();
        $cabecera = $stmtCabecera->get_result()->fetch_assoc();
        $stmtCabecera->close();

        if (!$cabecera) {
            $conexion->close();
            return null;
        }

        $sqlDetalles = "SELECT fecha_marcacion, hora_entrada, hora_salida, horas_laboradas
                        FROM planilla_detalles
                        WHERE id_planilla = ?
                        ORDER BY fecha_marcacion ASC";

        $stmtDetalles = $conexion->prepare($sqlDetalles);
        $stmtDetalles->bind_param('i', $idPlanilla);
        $stmtDetalles->execute();
        $resultadoDetalles = $stmtDetalles->get_result();

        $detalles = [];
        while ($fila = $resultadoDetalles->fetch_assoc()) {
            $detalles[] = $fila;
        }

        $stmtDetalles->close();
        $conexion->close();

        $cabecera['detalles'] = $detalles;
        return $cabecera;
    }

    private static function obtenerTrabajadoresActivosInterno(mysqli $conexion): array {
        $sql = "SELECT id, id_empresa, nombre, apellido1, COALESCE(apellido2, '') AS apellido2
                FROM trabajadores
                WHERE estado = 'Activo'
                ORDER BY nombre ASC, apellido1 ASC";

        $resultado = $conexion->query($sql);
        $filas = [];

        while ($fila = $resultado->fetch_assoc()) {
            $filas[] = $fila;
        }

        return $filas;
    }

    private static function obtenerMarcacionesDelMesInterno(mysqli $conexion, int $idTrabajador, int $anio, int $mes): array {
        $sql = "SELECT fecha_marcacion, hora_entrada, hora_salida
                FROM marcaciones
                WHERE id_trabajador = ?
                  AND YEAR(fecha_marcacion) = ?
                  AND MONTH(fecha_marcacion) = ?
                ORDER BY fecha_marcacion ASC";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param('iii', $idTrabajador, $anio, $mes);
        $stmt->execute();
        $resultado = $stmt->get_result();

        $filas = [];
        while ($fila = $resultado->fetch_assoc()) {
            $filas[] = $fila;
        }

        $stmt->close();
        return $filas;
    }

    private static function calcularHorasLaboradas(?string $entrada, ?string $salida): float {
        if (!$entrada || !$salida) {
            return 0.0;
        }

        try {
            $dtEntrada = new DateTime($entrada);
            $dtSalida = new DateTime($salida);

            if ($dtSalida < $dtEntrada) {
                return 0.0;
            }

            $segundos = $dtSalida->getTimestamp() - $dtEntrada->getTimestamp();
            return round($segundos / 3600, 2);
        } catch (Throwable $e) {
            return 0.0;
        }
    }

    private static function asegurarTablas(mysqli $conexion): void {
        $sqlMarcaciones = "CREATE TABLE IF NOT EXISTS marcaciones (
                               id INT AUTO_INCREMENT PRIMARY KEY,
                               id_trabajador INT NOT NULL,
                               fecha_marcacion DATE NOT NULL,
                               hora_entrada DATETIME NULL,
                               hora_salida DATETIME NULL,
                               estado ENUM('Abierta','Cerrada') DEFAULT 'Abierta',
                               creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                               actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                               UNIQUE KEY uniq_trabajador_fecha (id_trabajador, fecha_marcacion),
                               INDEX idx_fecha (fecha_marcacion),
                               CONSTRAINT fk_marcaciones_trabajador FOREIGN KEY (id_trabajador)
                                   REFERENCES trabajadores(id) ON DELETE CASCADE
                           ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $sqlPlanillas = "CREATE TABLE IF NOT EXISTS planillas_mensuales (
                             id INT AUTO_INCREMENT PRIMARY KEY,
                             id_trabajador INT NOT NULL,
                             anio INT NOT NULL,
                             mes INT NOT NULL,
                             tarifa_hora DECIMAL(10,2) NOT NULL,
                             horas_totales DECIMAL(10,2) NOT NULL DEFAULT 0,
                             monto_total DECIMAL(12,2) NOT NULL DEFAULT 0,
                             fecha_generacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                             creado_por INT NULL,
                             UNIQUE KEY uniq_planilla_mes (id_trabajador, anio, mes),
                             INDEX idx_periodo (anio, mes),
                             CONSTRAINT fk_planillas_trabajador FOREIGN KEY (id_trabajador)
                                 REFERENCES trabajadores(id) ON DELETE CASCADE
                         ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $sqlDetalles = "CREATE TABLE IF NOT EXISTS planilla_detalles (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            id_planilla INT NOT NULL,
                            fecha_marcacion DATE NOT NULL,
                            hora_entrada DATETIME NULL,
                            hora_salida DATETIME NULL,
                            horas_laboradas DECIMAL(10,2) NOT NULL DEFAULT 0,
                            INDEX idx_planilla (id_planilla),
                            INDEX idx_fecha_detalle (fecha_marcacion),
                            CONSTRAINT fk_detalle_planilla FOREIGN KEY (id_planilla)
                                REFERENCES planillas_mensuales(id) ON DELETE CASCADE
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $conexion->query($sqlMarcaciones);
        $conexion->query($sqlPlanillas);
        $conexion->query($sqlDetalles);
    }
}
