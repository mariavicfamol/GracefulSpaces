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
    `correo_corporativo`    VARCHAR(150),
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
-- Tabla de Planillas
-- ============================================================
CREATE TABLE IF NOT EXISTS `planillas` (
    `id`                    INT AUTO_INCREMENT PRIMARY KEY,
    `id_planilla`           VARCHAR(20) UNIQUE,
    `id_trabajador`         INT NOT NULL,
    `periodo_inicio`        DATE NOT NULL,
    `periodo_fin`           DATE NOT NULL,
    `cantidad_horas`        DECIMAL(8, 2) NOT NULL,
    `tarifa_hora`           DECIMAL(10, 2) NOT NULL,
    `monto_total`           DECIMAL(12, 2) GENERATED ALWAYS AS (cantidad_horas * tarifa_hora) STORED,
    `estado`                ENUM('Pendiente','Aprobada','Pagada','Cancelada') DEFAULT 'Pendiente',
    `notas`                 TEXT,
    `creado_en`             TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `actualizado_en`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`id_trabajador`) REFERENCES `trabajadores`(`id`) ON DELETE CASCADE,
    INDEX idx_trabajador (id_trabajador),
    INDEX idx_periodo (periodo_inicio, periodo_fin),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
