<?php
//Importa la configuración de la BD para obtener conexion y zona horaria
require_once __DIR__ . '/../configuracion/GracefulSpacesDB.configuracion.php';

//Gestina las entradas y salidas de los trabajadores
class ModeloMarcacion {

//consultas
//Busca la marcación de hoy para un trabajador en especifico
    public static function obtenerMarcacionHoy(int $idTrabajador): ?array {
        $conexion = obtenerConexion();
        self::asegurarTabla($conexion); //crea la tabla si no existe

        $hoy = self::fechaHoyVancouver(); //zona horaria Vancouver
        $sql = "SELECT * FROM marcaciones WHERE id_trabajador = ? AND fecha_marcacion = ? LIMIT 1";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param('is', $idTrabajador, $hoy);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $fila = $resultado->fetch_assoc();

        $stmt->close();
        $conexion->close();

        return $fila ?: null;
    }
//Obtiene las ultimas marcaciones del trabajador 
    public static function obtenerUltimasMarcaciones(int $idTrabajador, int $limite = 10): array {
        $conexion = obtenerConexion();
        self::asegurarTabla($conexion);
//Evita valores fuera del rango
        $limite = max(1, min($limite, 30));
        $sql = "SELECT fecha_marcacion, hora_entrada, hora_salida, estado
                FROM marcaciones
                WHERE id_trabajador = ?
                ORDER BY fecha_marcacion DESC
                LIMIT $limite";

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
    //Lista los trabajadores con al menos una marcación registrada
    public static function obtenerTrabajadoresConMarcacion(): array {
        $conexion = obtenerConexion();
        self::asegurarTabla($conexion);
        //DISTINTIC evita duplicados, CONCAT para mostrar nombre y orden 
        $sql = "SELECT DISTINCT t.id,
                               t.id_empresa,
                               CONCAT(t.nombre, ' ', t.apellido1, ' ', COALESCE(t.apellido2, '')) AS trabajador
                FROM marcaciones m
                INNER JOIN trabajadores t ON t.id = m.id_trabajador
                ORDER BY trabajador ASC";

        $resultado = $conexion->query($sql);
        $filas = [];

        while ($fila = $resultado->fetch_assoc()) {
            $filas[] = $fila;
        }

        $conexion->close();
        return $filas;
    }
    //Marcaciones filtradas para admin, con opciones de fecha y trabajador
    public static function obtenerMarcacionesParaAdmin(?int $idTrabajador = null, ?string $fechaInicio = null, ?string $fechaFin = null): array {
        $conexion = obtenerConexion();
        self::asegurarTabla($conexion);

        $condiciones = ["1=1"];

        if (!empty($idTrabajador) && $idTrabajador > 0) {
            $condiciones[] = 'm.id_trabajador = ' . (int)$idTrabajador;
        }

    //Solo si la fecha ingresada es válida
        if (self::esFechaValida($fechaInicio)) {
            $inicio = $conexion->real_escape_string($fechaInicio);
            $condiciones[] = "m.fecha_marcacion >= '$inicio'";
        }

        if (self::esFechaValida($fechaFin)) {
            $fin = $conexion->real_escape_string($fechaFin);
            $condiciones[] = "m.fecha_marcacion <= '$fin'";
        }
        //Une todos los filtros con and y ordena por fecha y hora de entrada
        $where = implode(' AND ', $condiciones);

        $sql = "SELECT m.id,
                       m.fecha_marcacion,
                       m.hora_entrada,
                       m.hora_salida,
                       m.estado,
                       t.id AS id_trabajador,
                       t.id_empresa,
                       CONCAT(t.nombre, ' ', t.apellido1, ' ', COALESCE(t.apellido2, '')) AS trabajador
                FROM marcaciones m
                INNER JOIN trabajadores t ON t.id = m.id_trabajador
                WHERE $where
                ORDER BY m.fecha_marcacion DESC, m.hora_entrada DESC";

        $resultado = $conexion->query($sql);
        $filas = [];

        while ($fila = $resultado->fetch_assoc()) {
            $filas[] = $fila;
        }

        $conexion->close();
        return $filas;
    }
//Registar entrada del trabajador
    public static function registrarEntrada(int $idTrabajador): array {
        $conexion = obtenerConexion();
        self::asegurarTabla($conexion);

        $hoy = self::fechaHoyVancouver();
        $ahora = self::fechaHoraActualVancouver();

        $marcacion = self::obtenerMarcacionInterna($conexion, $idTrabajador, $hoy);

        //Si ya hay maracación registrada hoy, no se permite duplicar
        if ($marcacion && !empty($marcacion['hora_entrada'])) {
            $conexion->close();
            return ['error' => true, 'mensaje' => 'La entrada de hoy ya fue registrada.'];
        }

        if ($marcacion) {
            //El update solo se ejecuta si hay una marcación sin hora de entrada
            $sql = "UPDATE marcaciones SET hora_entrada = ?, estado = 'Abierta' WHERE id = ?";
            $stmt = $conexion->prepare($sql);
            $id = (int)$marcacion['id'];
            $stmt->bind_param('si', $ahora, $id);
            $ok = $stmt->execute();
            $stmt->close();
        } else {
            //Si no hay marcación para hoy, se crea una nueva
            $sql = "INSERT INTO marcaciones (id_trabajador, fecha_marcacion, hora_entrada, estado)
                    VALUES (?, ?, ?, 'Abierta')";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param('iss', $idTrabajador, $hoy, $ahora);
            $ok = $stmt->execute();
            $stmt->close();
        }

        $conexion->close();

        if (!$ok) {
            return ['error' => true, 'mensaje' => 'No se pudo registrar la hora de entrada.'];
        }

        return ['error' => false, 'mensaje' => 'Hora de entrada registrada correctamente.'];
    }
    //Registra la hora de salida del trabajador, solo si ya registró entrada
    public static function registrarSalida(int $idTrabajador): array {
        $conexion = obtenerConexion();
        self::asegurarTabla($conexion);

        $hoy = self::fechaHoyVancouver();
        $ahora = self::fechaHoraActualVancouver();

        $marcacion = self::obtenerMarcacionInterna($conexion, $idTrabajador, $hoy);

        if (!$marcacion || empty($marcacion['hora_entrada'])) {
            $conexion->close();
            return ['error' => true, 'mensaje' => 'Primero debes registrar la hora de entrada.'];
        }

        if (!empty($marcacion['hora_salida'])) {
            $conexion->close();
            return ['error' => true, 'mensaje' => 'La salida de hoy ya fue registrada.'];
        }

        $sql = "UPDATE marcaciones SET hora_salida = ?, estado = 'Cerrada' WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $id = (int)$marcacion['id'];
        $stmt->bind_param('si', $ahora, $id);
        $ok = $stmt->execute();
        $stmt->close();
        $conexion->close();

        if (!$ok) {
            return ['error' => true, 'mensaje' => 'No se pudo registrar la hora de salida.'];
        }

        return ['error' => false, 'mensaje' => 'Hora de salida registrada correctamente.'];
    }
//metodos privados
//Busca una maracación sin abrir/cerrar la conexion
    private static function obtenerMarcacionInterna(mysqli $conexion, int $idTrabajador, string $fecha): ?array {
        $sql = "SELECT * FROM marcaciones WHERE id_trabajador = ? AND fecha_marcacion = ? LIMIT 1";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param('is', $idTrabajador, $fecha);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $fila = $resultado->fetch_assoc();
        $stmt->close();

        return $fila ?: null;
    }
    //crea la tabla marcaciones si aún no existe
    private static function asegurarTabla(mysqli $conexion): void {
        $sql = "CREATE TABLE IF NOT EXISTS marcaciones (
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

        $conexion->query($sql);
    }

    //valida que la fecha tenga el formato correcto
    private static function esFechaValida(?string $fecha): bool {
        if (!$fecha) {
            return false;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            return false;
        }

        [$anio, $mes, $dia] = array_map('intval', explode('-', $fecha));
        return checkdate($mes, $dia, $anio);//valida que la fecha sea real
    }

    //Retorna solo la fecha y la hora actual en Vancouver
    private static function fechaHoyVancouver(): string {
        return self::ahoraVancouver()->format('Y-m-d');
    }

    private static function fechaHoraActualVancouver(): string {
        return self::ahoraVancouver()->format('Y-m-d H:i:s');
    }
    //crea un objeto DateTimeInmutable con la zona horaria de Vancouver
    private static function ahoraVancouver(): DateTimeImmutable {
        return new DateTimeImmutable('now', new DateTimeZone(APP_TIMEZONE));
    }
}
