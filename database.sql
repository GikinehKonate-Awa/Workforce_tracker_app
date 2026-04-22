-- =====================================================
-- BASE DE DATOS WORKFORCE TRACKER
-- Sistema de control de presencia empresarial
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+02:00";

-- -----------------------------------------------------
-- TABLA DEPARTAMENTOS
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `departamentos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL,
  `descripcion` TEXT NULL,
  `color` VARCHAR(7) DEFAULT '#3498db',
  `activo` TINYINT(1) DEFAULT 1,
  `fecha_creacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre_unique` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- TABLA EMPLEADOS
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `empleados` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `departamento_id` INT UNSIGNED NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `nombre` VARCHAR(100) NOT NULL,
  `apellidos` VARCHAR(150) NOT NULL,
  `dni` VARCHAR(20) NULL,
  `telefono` VARCHAR(20) NULL,
  `foto_perfil` VARCHAR(255) NULL DEFAULT 'default.png',
  `rol` ENUM('empleado', 'jefe_departamento', 'admin') NOT NULL DEFAULT 'empleado',
  `modalidad` ENUM('presencial', 'teletrabajo', 'hibrido') NOT NULL DEFAULT 'presencial',
  `fecha_contratacion` DATE NULL,
  `horario_entrada` TIME DEFAULT '08:00:00',
  `horario_salida` TIME DEFAULT '17:00:00',
  `activo` TINYINT(1) DEFAULT 1,
  `fecha_creacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `ultimo_acceso` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_unique` (`email`),
  INDEX `fk_empleados_departamento_idx` (`departamento_id` ASC),
  CONSTRAINT `fk_empleados_departamento`
    FOREIGN KEY (`departamento_id`)
    REFERENCES `departamentos` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- TABLA FICHAJES
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fichajes` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `empleado_id` INT UNSIGNED NOT NULL,
  `fecha` DATE NOT NULL,
  `hora_entrada` TIME NULL,
  `hora_salida` TIME NULL,
  `ip_entrada` VARCHAR(45) NULL,
  `ip_salida` VARCHAR(45) NULL,
  `vpn_entrada` TINYINT(1) DEFAULT 0,
  `vpn_salida` TINYINT(1) DEFAULT 0,
  `es_manual` TINYINT(1) DEFAULT 0,
  `comentario_manual` TEXT NULL,
  `estado` ENUM('pendiente', 'aprobado', 'rechazado') DEFAULT 'aprobado',
  `revisado_por` INT UNSIGNED NULL,
  `fecha_revision` DATETIME NULL,
  `comentario_revision` TEXT NULL,
  `fecha_creacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `fk_fichajes_empleado_idx` (`empleado_id` ASC),
  INDEX `idx_fichajes_fecha` (`fecha` ASC),
  CONSTRAINT `fk_fichajes_empleado`
    FOREIGN KEY (`empleado_id`)
    REFERENCES `empleados` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- TABLA PROYECTOS
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `proyectos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `departamento_id` INT UNSIGNED NOT NULL,
  `nombre` VARCHAR(150) NOT NULL,
  `descripcion` TEXT NULL,
  `fecha_inicio` DATE NULL,
  `fecha_fin` DATE NULL,
  `horas_estimadas` INT DEFAULT 0,
  `estado` ENUM('planificado', 'en_curso', 'finalizado', 'cancelado') DEFAULT 'en_curso',
  `fecha_creacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `fk_proyectos_departamento_idx` (`departamento_id` ASC),
  CONSTRAINT `fk_proyectos_departamento`
    FOREIGN KEY (`departamento_id`)
    REFERENCES `departamentos` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- TABLA EMPLEADOS_PROYECTOS
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `empleados_proyectos` (
  `empleado_id` INT UNSIGNED NOT NULL,
  `proyecto_id` INT UNSIGNED NOT NULL,
  `horas_asignadas` INT DEFAULT 0,
  `rol_proyecto` VARCHAR(100) NULL,
  `fecha_asignacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`empleado_id`, `proyecto_id`),
  CONSTRAINT `fk_ep_empleado`
    FOREIGN KEY (`empleado_id`)
    REFERENCES `empleados` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_ep_proyecto`
    FOREIGN KEY (`proyecto_id`)
    REFERENCES `proyectos` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- TABLA HORAS_EXTRAS
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `horas_extras` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `empleado_id` INT UNSIGNED NOT NULL,
  `fecha` DATE NOT NULL,
  `horas` DECIMAL(4,2) NOT NULL,
  `motivo` TEXT NOT NULL,
  `estado` ENUM('pendiente', 'aprobada', 'rechazada') DEFAULT 'pendiente',
  `aprobado_por` INT UNSIGNED NULL,
  `fecha_aprobacion` DATETIME NULL,
  `comentario_aprobacion` TEXT NULL,
  `fecha_creacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `fk_horasextras_empleado_idx` (`empleado_id` ASC),
  CONSTRAINT `fk_horasextras_empleado`
    FOREIGN KEY (`empleado_id`)
    REFERENCES `empleados` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- TABLA NOTIFICACIONES
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `notificaciones` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `empleado_id` INT UNSIGNED NOT NULL,
  `titulo` VARCHAR(200) NOT NULL,
  `mensaje` TEXT NOT NULL,
  `tipo` ENUM('info', 'aviso', 'alerta', 'sistema') DEFAULT 'info',
  `leida` TINYINT(1) DEFAULT 0,
  `fecha_creacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `fecha_leida` DATETIME NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_notificaciones_empleado_idx` (`empleado_id` ASC),
  CONSTRAINT `fk_notificaciones_empleado`
    FOREIGN KEY (`empleado_id`)
    REFERENCES `empleados` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- TABLA NOMINAS
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `nominas` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `empleado_id` INT UNSIGNED NOT NULL,
  `mes` TINYINT UNSIGNED NOT NULL,
  `anio` SMALLINT UNSIGNED NOT NULL,
  `horas_trabajadas` DECIMAL(6,2) DEFAULT 0,
  `horas_extras` DECIMAL(6,2) DEFAULT 0,
  `importe_bruto` DECIMAL(10,2) NOT NULL,
  `importe_neto` DECIMAL(10,2) NOT NULL,
  `documento_path` VARCHAR(255) NULL,
  `estado` ENUM('generada', 'enviada', 'pagada') DEFAULT 'generada',
  `fecha_creacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_nomina_mesanio` (`empleado_id`, `mes`, `anio`),
  CONSTRAINT `fk_nominas_empleado`
    FOREIGN KEY (`empleado_id`)
    REFERENCES `empleados` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- TABLA HORARIOS
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `horarios` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `empleado_id` INT UNSIGNED NOT NULL,
  `dia_semana` TINYINT UNSIGNED NOT NULL COMMENT '1=Lunes, 7=Domingo',
  `hora_entrada` TIME NOT NULL,
  `hora_salida` TIME NOT NULL,
  `es_teletrabajo` TINYINT(1) DEFAULT 0,
  `activo` TINYINT(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_horario_dia` (`empleado_id`, `dia_semana`),
  CONSTRAINT `fk_horarios_empleado`
    FOREIGN KEY (`empleado_id`)
    REFERENCES `empleados` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- TABLA SOLICITUDES
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `solicitudes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `empleado_id` INT UNSIGNED NOT NULL,
  `tipo_solicitud` ENUM('cambio_modalidad', 'dia_libre', 'horas_extras', 'otro') NOT NULL,
  `fecha_solicitud` DATE NOT NULL,
  `datos` TEXT NULL,
  `motivo` TEXT NOT NULL,
  `estado` ENUM('pendiente', 'aprobada', 'rechazada') DEFAULT 'pendiente',
  `procesado_por` INT UNSIGNED NULL,
  `fecha_procesado` DATETIME NULL,
  `comentario` TEXT NULL,
  `fecha_creacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_solicitudes_empleado`
    FOREIGN KEY (`empleado_id`)
    REFERENCES `empleados` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- TABLA LOGS
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `logs_sistema` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `empleado_id` INT UNSIGNED NULL,
  `accion` VARCHAR(100) NOT NULL,
  `descripcion` TEXT NULL,
  `ip` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `fecha` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;