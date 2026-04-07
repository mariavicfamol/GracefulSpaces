-- ============================================================
-- GracefulSpaces Workers - Script de Base de Datos
-- Ejecuta esto una sola vez en tu base de datos MySQL
-- ============================================================

CREATE TABLE IF NOT EXISTS `trabajadores` (
    `id`                    INT AUTO_INCREMENT PRIMARY KEY,
    `id_empresa`            VARCHAR(20) UNIQUE,
    -- Información personal
    `nombre`                VARCHAR(100) NOT NULL,
    `apellido1`             VARCHAR(100) NOT NULL,
    `apellido2`             VARCHAR(100),
    `tipo_documento`        ENUM('Cedula','Pasaporte','DIMEX') DEFAULT 'Cedula',
    `numero_identificacion` VARCHAR(20),
    `fecha_nacimiento`      DATE,
    `sexo`                  ENUM('Hombre','Mujer','Prefiero no decir') DEFAULT 'Prefiero no decir',
    `genero`                VARCHAR(100),
    `nacionalidad`          VARCHAR(50),
    -- Información laboral
    `cargo`                 ENUM('Administrador','Supervisor','Trabajador') DEFAULT 'Trabajador',
    `tipo_contrato`         ENUM('Tiempo completo','Medio tiempo','Turno rotativo','Turno nocturno','Trabajo remoto','Trabajo temporal') DEFAULT 'Tiempo completo',
    `fecha_ingreso`         DATE,
    -- Contacto
    `correo_personal`       VARCHAR(150),
    `telefono`              VARCHAR(20),
    `contacto_emergencia`   VARCHAR(150),
    `telefono_emergencia`   VARCHAR(20),
    `direccion`             TEXT,
    -- Acceso al sistema
    `login_usuario`         VARCHAR(100) UNIQUE NOT NULL,
    `password_hash`         VARCHAR(255) NOT NULL,
    `rol`                   ENUM('Administrador Total','Administrador','Supervisor','Trabajador') DEFAULT 'Trabajador',
    `estado`                ENUM('Activo','Inactivo') DEFAULT 'Activo',
    `foto_perfil`           VARCHAR(255),
    -- Auditoría
    `creado_en`             TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `actualizado_en`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Usuario administrador inicial (contraseña: Admin2026!)
INSERT IGNORE INTO `trabajadores`
    (id_empresa, nombre, apellido1, login_usuario, password_hash, rol, estado, cargo, tipo_contrato, fecha_ingreso)
VALUES
    ('GS-2026-001', 'Admin', 'Sistema', 'admin@gracefulspaces.com',
     '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
     'Administrador Total', 'Activo', 'Administrador', 'Tiempo completo', CURDATE());

-- ============================================================
-- Tabla de Marcaciones (entrada/salida de trabajadores)
-- ============================================================
CREATE TABLE IF NOT EXISTS `marcaciones` (
    `id`                    INT AUTO_INCREMENT PRIMARY KEY,
    `id_trabajador`         INT NOT NULL,
    `fecha_marcacion`       DATE NOT NULL,
    `hora_entrada`          DATETIME NULL,
    `hora_salida`           DATETIME NULL,
    `estado`                ENUM('Abierta','Cerrada') DEFAULT 'Abierta',
    `creado_en`             TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `actualizado_en`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uniq_trabajador_fecha` (`id_trabajador`, `fecha_marcacion`),
    INDEX `idx_fecha_marcacion` (`fecha_marcacion`),
    CONSTRAINT `fk_marcaciones_trabajador`
        FOREIGN KEY (`id_trabajador`) REFERENCES `trabajadores`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Tablas de Planillas Mensuales
-- ============================================================
CREATE TABLE IF NOT EXISTS `planillas_mensuales` (
    `id`                    INT AUTO_INCREMENT PRIMARY KEY,
    `id_trabajador`         INT NOT NULL,
    `anio`                  INT NOT NULL,
    `mes`                   INT NOT NULL,
    `tarifa_hora`           DECIMAL(10, 2) NOT NULL,
    `horas_totales`         DECIMAL(10, 2) NOT NULL DEFAULT 0,
    `monto_total`           DECIMAL(12, 2) NOT NULL DEFAULT 0,
    `fecha_generacion`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `aprobada`              TINYINT(1) NOT NULL DEFAULT 0,
    `aprobado_por`          INT NULL,
    `fecha_aprobacion`      DATETIME NULL,
    `creado_por`            INT NULL,
    UNIQUE KEY `uniq_planilla_mes` (`id_trabajador`, `anio`, `mes`),
    INDEX `idx_periodo` (`anio`, `mes`),
    INDEX `idx_aprobada` (`aprobada`),
    CONSTRAINT `fk_planillas_trabajador`
        FOREIGN KEY (`id_trabajador`) REFERENCES `trabajadores`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `planilla_detalles` (
    `id`                    INT AUTO_INCREMENT PRIMARY KEY,
    `id_planilla`           INT NOT NULL,
    `fecha_marcacion`       DATE NOT NULL,
    `hora_entrada`          DATETIME NULL,
    `hora_salida`           DATETIME NULL,
    `horas_laboradas`       DECIMAL(10, 2) NOT NULL DEFAULT 0,
    INDEX `idx_planilla` (`id_planilla`),
    INDEX `idx_fecha_detalle` (`fecha_marcacion`),
    CONSTRAINT `fk_detalle_planilla`
        FOREIGN KEY (`id_planilla`) REFERENCES `planillas_mensuales`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Tablas de Proyectos
-- ============================================================
CREATE TABLE IF NOT EXISTS `proyectos` (
    `id`                    INT AUTO_INCREMENT PRIMARY KEY,
    `nombre`                VARCHAR(180) NOT NULL,
    `detalles`              TEXT,
    `especificaciones`      TEXT,
    `horarios`              TEXT,
    `materiales`            TEXT,
    `estado_general`        ENUM('En progreso','Finalizado') DEFAULT 'En progreso',
    `creado_por`            INT NULL,
    `creado_en`             TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `actualizado_en`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_estado_general` (`estado_general`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `proyecto_colaboradores` (
    `id`                    INT AUTO_INCREMENT PRIMARY KEY,
    `id_proyecto`           INT NOT NULL,
    `id_trabajador`         INT NOT NULL,
    `terminado`             TINYINT(1) NOT NULL DEFAULT 0,
    `fecha_asignacion`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `fecha_terminado`       DATETIME NULL,
    UNIQUE KEY `uniq_proyecto_trabajador` (`id_proyecto`, `id_trabajador`),
    INDEX `idx_trabajador` (`id_trabajador`),
    CONSTRAINT `fk_pc_proyecto`
        FOREIGN KEY (`id_proyecto`) REFERENCES `proyectos`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pc_trabajador`
        FOREIGN KEY (`id_trabajador`) REFERENCES `trabajadores`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Tabla de Productos Faltantes
-- ============================================================
CREATE TABLE IF NOT EXISTS `productos_faltantes` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

