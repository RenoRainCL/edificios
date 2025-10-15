-- 📁 database/create_database.sql
-- SISTEMA DE ADMINISTRACIÓN DE EDIFICIOS CHILE
-- Script de creación de base de datos completo

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- Crear base de datos si no existe
CREATE DATABASE IF NOT EXISTS `admin_edificios_chile` 
DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `admin_edificios_chile`;

-- TABLA DE CONFIGURACIÓN DEL SISTEMA
CREATE TABLE IF NOT EXISTS `system_config` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `config_key` VARCHAR(255) NOT NULL UNIQUE,
    `config_value` TEXT NULL,
    `config_type` ENUM('string', 'int', 'bool', 'json') DEFAULT 'string',
    `description` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_config_key` (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLA DE TEMAS
CREATE TABLE IF NOT EXISTS `themes` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `description` VARCHAR(255) NULL,
    `is_active` TINYINT(1) DEFAULT 0,
    `colors` JSON NOT NULL,
    `created_by` INT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_theme_active` (`is_active`),
    INDEX `idx_theme_created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLA DE ROLES DE USUARIO
CREATE TABLE IF NOT EXISTS `user_roles` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `role_name` VARCHAR(100) NOT NULL,
    `role_description` TEXT NULL,
    `permissions` JSON NOT NULL,
    `is_system_role` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_role_name` (`role_name`),
    INDEX `idx_role_system` (`is_system_role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLA DE USUARIOS
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `rut` VARCHAR(12) NOT NULL UNIQUE,
    `nombre` VARCHAR(255) NOT NULL,
    `apellido` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `telefono` VARCHAR(20) NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `role_id` INT UNSIGNED NOT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `last_login` TIMESTAMP NULL,
    `password_reset_token` VARCHAR(100) NULL,
    `password_reset_expires` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_user_rut` (`rut`),
    UNIQUE KEY `uk_user_email` (`email`),
    INDEX `idx_user_role` (`role_id`),
    INDEX `idx_user_active` (`is_active`),
    INDEX `idx_user_last_login` (`last_login`),
    FOREIGN KEY (`role_id`) 
        REFERENCES `user_roles` (`id`)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLA DE EDIFICIOS
CREATE TABLE IF NOT EXISTS `edificios` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(255) NOT NULL,
    `direccion` TEXT NOT NULL,
    `comuna` VARCHAR(100) NOT NULL,
    `region` VARCHAR(100) NOT NULL,
    `rut_administrador` VARCHAR(12) NULL,
    `telefono_administrador` VARCHAR(20) NULL,
    `email_administrador` VARCHAR(255) NULL,
    `total_departamentos` INT UNSIGNED DEFAULT 0,
    `total_pisos` INT UNSIGNED DEFAULT 0,
    `fecha_construccion` DATE NULL,
    `reglamento_copropiedad` TEXT NULL,
    `configuracion` JSON NULL,
    `theme_id` INT UNSIGNED NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_edificio_comuna` (`comuna`),
    INDEX `idx_edificio_region` (`region`),
    INDEX `idx_edificio_active` (`is_active`),
    INDEX `idx_edificio_theme` (`theme_id`),
    FOREIGN KEY (`theme_id`) 
        REFERENCES `themes` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLA DE RELACIÓN USUARIOS-EDIFICIOS (Multi-administración)
CREATE TABLE IF NOT EXISTS `user_edificio_relations` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `edificio_id` INT UNSIGNED NOT NULL,
    `permissions` JSON NULL,
    `is_primary_admin` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_user_edificio` (`user_id`, `edificio_id`),
    INDEX `idx_user_edificio_user` (`user_id`),
    INDEX `idx_user_edificio_edificio` (`edificio_id`),
    INDEX `idx_primary_admin` (`is_primary_admin`),
    FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (`edificio_id`) 
        REFERENCES `edificios` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLA DE MENÚS DINÁMICOS
CREATE TABLE IF NOT EXISTS `menu_items` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `parent_id` INT UNSIGNED NULL,
    `menu_key` VARCHAR(100) NOT NULL UNIQUE,
    `menu_text` VARCHAR(255) NOT NULL,
    `menu_icon` VARCHAR(100) NULL,
    `menu_url` VARCHAR(255) NULL,
    `menu_order` INT DEFAULT 0,
    `required_permissions` JSON NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_menu_parent` (`parent_id`),
    INDEX `idx_menu_order` (`menu_order`),
    INDEX `idx_menu_active` (`is_active`),
    FOREIGN KEY (`parent_id`) 
        REFERENCES `menu_items` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLA DE DEPARTAMENTOS
CREATE TABLE IF NOT EXISTS `departamentos` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `edificio_id` INT UNSIGNED NOT NULL,
    `numero` VARCHAR(20) NOT NULL,
    `piso` INT UNSIGNED NULL,
    `metros_cuadrados` DECIMAL(10,2) NULL,
    `orientacion` VARCHAR(50) NULL,
    `dormitorios` INT UNSIGNED DEFAULT 1,
    `banos` INT UNSIGNED DEFAULT 1,
    `estacionamientos` INT UNSIGNED DEFAULT 0,
    `bodegas` INT UNSIGNED DEFAULT 0,
    `propietario_rut` VARCHAR(12) NULL,
    `propietario_nombre` VARCHAR(255) NULL,
    `propietario_email` VARCHAR(255) NULL,
    `propietario_telefono` VARCHAR(20) NULL,
    `arrendatario_rut` VARCHAR(12) NULL,
    `arrendatario_nombre` VARCHAR(255) NULL,
    `arrendatario_email` VARCHAR(255) NULL,
    `arrendatario_telefono` VARCHAR(20) NULL,
    `porcentaje_copropiedad` DECIMAL(5,2) DEFAULT 0.00,
    `is_habitado` TINYINT(1) DEFAULT 1,
    `observaciones` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_edificio_depto` (`edificio_id`, `numero`),
    INDEX `idx_depto_edificio` (`edificio_id`),
    INDEX `idx_depto_piso` (`piso`),
    INDEX `idx_depto_habitado` (`is_habitado`),
    INDEX `idx_depto_propietario_rut` (`propietario_rut`),
    FOREIGN KEY (`edificio_id`) 
        REFERENCES `edificios` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLA DE GASTOS COMUNES
CREATE TABLE IF NOT EXISTS `gastos_comunes` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `edificio_id` INT UNSIGNED NOT NULL,
    `nombre` VARCHAR(255) NOT NULL,
    `descripcion` TEXT NULL,
    `monto_total` DECIMAL(15,2) NOT NULL,
    `periodo` DATE NOT NULL,
    `fecha_vencimiento` DATE NOT NULL,
    `fecha_emision` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `estado` ENUM('pendiente', 'emitido', 'vencido', 'cerrado') DEFAULT 'pendiente',
    `detalles` JSON NULL,
    `created_by` INT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_gasto_edificio_periodo` (`edificio_id`, `periodo`),
    INDEX `idx_gasto_edificio` (`edificio_id`),
    INDEX `idx_gasto_periodo` (`periodo`),
    INDEX `idx_gasto_estado` (`estado`),
    INDEX `idx_gasto_vencimiento` (`fecha_vencimiento`),
    INDEX `idx_gasto_created_by` (`created_by`),
    FOREIGN KEY (`edificio_id`) 
        REFERENCES `edificios` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (`created_by`) 
        REFERENCES `users` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLA DE DETALLE DE GASTOS POR DEPARTAMENTO
CREATE TABLE IF NOT EXISTS `gasto_departamento` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `gasto_comun_id` INT UNSIGNED NOT NULL,
    `departamento_id` INT UNSIGNED NOT NULL,
    `monto` DECIMAL(15,2) NOT NULL,
    `porcentaje` DECIMAL(5,2) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_gasto_depto` (`gasto_comun_id`, `departamento_id`),
    INDEX `idx_gasto_detalle_gasto` (`gasto_comun_id`),
    INDEX `idx_gasto_detalle_depto` (`departamento_id`),
    FOREIGN KEY (`gasto_comun_id`) 
        REFERENCES `gastos_comunes` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (`departamento_id`) 
        REFERENCES `departamentos` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLA DE PAGOS
CREATE TABLE IF NOT EXISTS `pagos` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `departamento_id` INT UNSIGNED NOT NULL,
    `gasto_comun_id` INT UNSIGNED NOT NULL,
    `monto` DECIMAL(15,2) NOT NULL,
    `fecha_pago` TIMESTAMP NULL,
    `metodo_pago` ENUM('transferencia', 'efectivo', 'cheque', 'webpay', 'debito_automatico') DEFAULT 'transferencia',
    `estado` ENUM('pendiente', 'pagado', 'atrasado', 'judicial', 'anulado') DEFAULT 'pendiente',
    `numero_comprobante` VARCHAR(100) NULL,
    `referencia_bancaria` VARCHAR(100) NULL,
    `observaciones` TEXT NULL,
    `created_by` INT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_pago_depto_gasto` (`departamento_id`, `gasto_comun_id`),
    INDEX `idx_pago_departamento` (`departamento_id`),
    INDEX `idx_pago_gasto` (`gasto_comun_id`),
    INDEX `idx_pago_estado` (`estado`),
    INDEX `idx_pago_fecha` (`fecha_pago`),
    INDEX `idx_pago_metodo` (`metodo_pago`),
    INDEX `idx_pago_created_by` (`created_by`),
    FOREIGN KEY (`departamento_id`) 
        REFERENCES `departamentos` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (`gasto_comun_id`) 
        REFERENCES `gastos_comunes` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (`created_by`) 
        REFERENCES `users` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLA DE MANTENIMIENTO
CREATE TABLE IF NOT EXISTS `mantenimientos` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `edificio_id` INT UNSIGNED NOT NULL,
    `tipo` ENUM('preventivo', 'correctivo', 'urgente', 'mejora') NOT NULL,
    `titulo` VARCHAR(255) NOT NULL,
    `descripcion` TEXT NULL,
    `area` VARCHAR(100) NULL,
    `prioridad` ENUM('baja', 'media', 'alta', 'urgente') DEFAULT 'media',
    `fecha_solicitud` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `fecha_programada` DATE NULL,
    `fecha_completada` TIMESTAMP NULL,
    `estado` ENUM('pendiente', 'en_proceso', 'completado', 'cancelado') DEFAULT 'pendiente',
    `costo_estimado` DECIMAL(15,2) NULL,
    `costo_real` DECIMAL(15,2) NULL,
    `proveedor` VARCHAR(255) NULL,
    `contacto_proveedor` VARCHAR(255) NULL,
    `observaciones` TEXT NULL,
    `created_by` INT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_mantenimiento_edificio` (`edificio_id`),
    INDEX `idx_mantenimiento_tipo` (`tipo`),
    INDEX `idx_mantenimiento_prioridad` (`prioridad`),
    INDEX `idx_mantenimiento_estado` (`estado`),
    INDEX `idx_mantenimiento_fecha_programada` (`fecha_programada`),
    INDEX `idx_mantenimiento_created_by` (`created_by`),
    FOREIGN KEY (`edificio_id`) 
        REFERENCES `edificios` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (`created_by`) 
        REFERENCES `users` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLA DE AMENITIES
CREATE TABLE IF NOT EXISTS `amenities` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `edificio_id` INT UNSIGNED NOT NULL,
    `nombre` VARCHAR(255) NOT NULL,
    `tipo` ENUM('gimnasio', 'piscina', 'quincho', 'sala_eventos', 'lavanderia', 'juegos_infantiles', 'terraza', 'otro') NOT NULL,
    `descripcion` TEXT NULL,
    `capacidad` INT UNSIGNED NULL,
    `horario_apertura` TIME NULL,
    `horario_cierre` TIME NULL,
    `reglas_uso` TEXT NULL,
    `costo_uso` DECIMAL(10,2) DEFAULT 0.00,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_amenity_edificio` (`edificio_id`),
    INDEX `idx_amenity_tipo` (`tipo`),
    INDEX `idx_amenity_active` (`is_active`),
    FOREIGN KEY (`edificio_id`) 
        REFERENCES `edificios` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLA DE RESERVAS
CREATE TABLE IF NOT EXISTS `reservas` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `amenity_id` INT UNSIGNED NOT NULL,
    `departamento_id` INT UNSIGNED NOT NULL,
    `fecha_reserva` DATE NOT NULL,
    `hora_inicio` TIME NOT NULL,
    `hora_fin` TIME NOT NULL,
    `estado` ENUM('pendiente', 'confirmada', 'cancelada', 'completada', 'rechazada') DEFAULT 'pendiente',
    `motivo` VARCHAR(255) NULL,
    `numero_asistentes` INT UNSIGNED DEFAULT 1,
    `costo_total` DECIMAL(10,2) DEFAULT 0.00,
    `observaciones` TEXT NULL,
    `created_by` INT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_reserva_amenity_hora` (`amenity_id`, `fecha_reserva`, `hora_inicio`),
    INDEX `idx_reserva_amenity` (`amenity_id`),
    INDEX `idx_reserva_departamento` (`departamento_id`),
    INDEX `idx_reserva_fecha` (`fecha_reserva`),
    INDEX `idx_reserva_estado` (`estado`),
    INDEX `idx_reserva_created_by` (`created_by`),
    FOREIGN KEY (`amenity_id`) 
        REFERENCES `amenities` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (`departamento_id`) 
        REFERENCES `departamentos` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (`created_by`) 
        REFERENCES `users` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLA DE COMUNICACIONES
CREATE TABLE IF NOT EXISTS `comunicaciones` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `edificio_id` INT UNSIGNED NOT NULL,
    `tipo` ENUM('general', 'urgente', 'mantenimiento', 'finanzas', 'legal', 'seguridad') NOT NULL,
    `titulo` VARCHAR(255) NOT NULL,
    `contenido` TEXT NOT NULL,
    `destinatarios` JSON NULL,
    `fecha_envio` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `fecha_expiracion` DATE NULL,
    `is_important` TINYINT(1) DEFAULT 0,
    `requires_acknowledgment` TINYINT(1) DEFAULT 0,
    `created_by` INT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_comunicacion_edificio` (`edificio_id`),
    INDEX `idx_comunicacion_tipo` (`tipo`),
    INDEX `idx_comunicacion_fecha_envio` (`fecha_envio`),
    INDEX `idx_comunicacion_important` (`is_important`),
    INDEX `idx_comunicacion_created_by` (`created_by`),
    FOREIGN KEY (`edificio_id`) 
        REFERENCES `edificios` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (`created_by`) 
        REFERENCES `users` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLA DE ACUSES DE RECIBO DE COMUNICACIONES
CREATE TABLE IF NOT EXISTS `comunicacion_acknowledgments` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `comunicacion_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `fecha_lectura` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `ip_address` VARCHAR(45) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_ack_comunicacion_user` (`comunicacion_id`, `user_id`),
    INDEX `idx_ack_comunicacion` (`comunicacion_id`),
    INDEX `idx_ack_user` (`user_id`),
    FOREIGN KEY (`comunicacion_id`) 
        REFERENCES `comunicaciones` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLA DE DOCUMENTOS LEGALES
CREATE TABLE IF NOT EXISTS `documentos_legales` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `edificio_id` INT UNSIGNED NOT NULL,
    `tipo_documento` ENUM('reglamento', 'acta', 'balance', 'contrato', 'seguro', 'permiso', 'otro') NOT NULL,
    `nombre` VARCHAR(255) NOT NULL,
    `descripcion` TEXT NULL,
    `archivo_path` VARCHAR(500) NULL,
    `google_drive_id` VARCHAR(255) NULL,
    `fecha_documento` DATE NULL,
    `fecha_vencimiento` DATE NULL,
    `is_public` TINYINT(1) DEFAULT 0,
    `created_by` INT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_documento_edificio` (`edificio_id`),
    INDEX `idx_documento_tipo` (`tipo_documento`),
    INDEX `idx_documento_fecha` (`fecha_documento`),
    INDEX `idx_documento_public` (`is_public`),
    INDEX `idx_documento_created_by` (`created_by`),
    FOREIGN KEY (`edificio_id`) 
        REFERENCES `edificios` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (`created_by`) 
        REFERENCES `users` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLA DE NOTIFICACIONES
CREATE TABLE IF NOT EXISTS `notificaciones` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `tipo` ENUM('info', 'warning', 'error', 'success', 'urgent') DEFAULT 'info',
    `titulo` VARCHAR(255) NOT NULL,
    `mensaje` TEXT NOT NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `related_entity_type` VARCHAR(50) NULL,
    `related_entity_id` INT UNSIGNED NULL,
    `fecha_lectura` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_notification_user` (`user_id`),
    INDEX `idx_notification_type` (`tipo`),
    INDEX `idx_notification_read` (`is_read`),
    INDEX `idx_notification_related` (`related_entity_type`, `related_entity_id`),
    INDEX `idx_notification_created` (`created_at`),
    FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLA DE AUDITORÍA
CREATE TABLE IF NOT EXISTS `audit_log` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NULL,
    `action` VARCHAR(100) NOT NULL,
    `entity_type` VARCHAR(100) NOT NULL,
    `entity_id` INT UNSIGNED NULL,
    `old_values` JSON NULL,
    `new_values` JSON NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_audit_user` (`user_id`),
    INDEX `idx_audit_action` (`action`),
    INDEX `idx_audit_entity` (`entity_type`, `entity_id`),
    INDEX `idx_audit_created` (`created_at`),
    FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- VISTAS PARA INFORMES

-- Vista de Estado de Pagos por Edificio
CREATE OR REPLACE VIEW `vw_estado_pagos_edificio` AS
SELECT 
    e.id as edificio_id,
    e.nombre as edificio_nombre,
    gc.periodo,
    COUNT(DISTINCT d.id) as total_departamentos,
    COUNT(DISTINCT CASE WHEN p.estado = 'pagado' THEN d.id END) as deptos_pagados,
    COUNT(DISTINCT CASE WHEN p.estado IN ('pendiente', 'atrasado') THEN d.id END) as deptos_pendientes,
    SUM(gd.monto) as monto_total,
    SUM(CASE WHEN p.estado = 'pagado' THEN p.monto ELSE 0 END) as monto_recaudado,
    ROUND((SUM(CASE WHEN p.estado = 'pagado' THEN p.monto ELSE 0 END) / SUM(gd.monto)) * 100, 2) as porcentaje_recaudado
FROM edificios e
JOIN departamentos d ON e.id = d.edificio_id
JOIN gastos_comunes gc ON e.id = gc.edificio_id
JOIN gasto_departamento gd ON gc.id = gd.gasto_comun_id AND d.id = gd.departamento_id
LEFT JOIN pagos p ON d.id = p.departamento_id AND gc.id = p.gasto_comun_id
GROUP BY e.id, e.nombre, gc.periodo;

-- Vista de Mantenimientos Pendientes
CREATE OR REPLACE VIEW `vw_mantenimientos_pendientes` AS
SELECT 
    e.id as edificio_id,
    e.nombre as edificio_nombre,
    m.tipo,
    m.titulo,
    m.prioridad,
    m.fecha_solicitud,
    m.fecha_programada,
    DATEDIFF(CURRENT_DATE, m.fecha_solicitud) as dias_pendiente,
    u.nombre as solicitante
FROM mantenimientos m
JOIN edificios e ON m.edificio_id = e.id
LEFT JOIN users u ON m.created_by = u.id
WHERE m.estado IN ('pendiente', 'en_proceso')
ORDER BY 
    CASE m.prioridad 
        WHEN 'urgente' THEN 1
        WHEN 'alta' THEN 2
        WHEN 'media' THEN 3
        WHEN 'baja' THEN 4
    END,
    m.fecha_solicitud ASC;

-- Vista de Reservas del Día
CREATE OR REPLACE VIEW `vw_reservas_hoy` AS
SELECT 
    r.id,
    a.nombre as amenity_nombre,
    d.numero as departamento_numero,
    r.fecha_reserva,
    r.hora_inicio,
    r.hora_fin,
    r.estado,
    e.nombre as edificio_nombre
FROM reservas r
JOIN amenities a ON r.amenity_id = a.id
JOIN departamentos d ON r.departamento_id = d.id
JOIN edificios e ON a.edificio_id = e.id
WHERE r.fecha_reserva = CURDATE()
ORDER BY r.hora_inicio;

-- Vista de Comunicaciones No Leídas
CREATE OR REPLACE VIEW `vw_comunicaciones_no_leidas` AS
SELECT 
    c.id,
    c.titulo,
    c.tipo,
    c.fecha_envio,
    e.nombre as edificio_nombre,
    u.nombre as creador_nombre,
    COUNT(ca.id) as leidos,
    (SELECT COUNT(*) FROM user_edificio_relations uer WHERE uer.edificio_id = c.edificio_id) as total_usuarios
FROM comunicaciones c
JOIN edificios e ON c.edificio_id = e.id
LEFT JOIN users u ON c.created_by = u.id
LEFT JOIN comunicacion_acknowledgments ca ON c.id = ca.comunicacion_id
WHERE c.requires_acknowledgment = 1
GROUP BY c.id, c.titulo, c.tipo, c.fecha_envio, e.nombre, u.nombre;

-- INSERCIÓN DE DATOS INICIALES

-- Roles del sistema
INSERT IGNORE INTO `user_roles` (`id`, `role_name`, `role_description`, `permissions`, `is_system_role`) VALUES
(1, 'super_admin', 'Administrador del sistema completo', '{"all": true, "system": true}', 1),
(2, 'administrador', 'Administrador de edificios', '{"edificios": ["read", "write", "delete"], "finanzas": ["read", "write", "delete"], "mantenimiento": ["read", "write", "delete"], "comunicaciones": ["read", "write", "delete"], "documentos": ["read", "write", "delete"], "amenities": ["read", "write", "delete"]}', 0),
(3, 'conserje', 'Personal de conserjería', '{"mantenimiento": ["read", "write"], "reservas": ["read", "write", "delete"], "comunicaciones": ["read"]}', 0),
(4, 'residente', 'Residente del edificio', '{"perfil": ["read", "write"], "pagos": ["read", "pay"], "reservas": ["read", "write"], "comunicaciones": ["read"], "documentos": ["read"]}', 0),
(5, 'comite', 'Miembro del comité de administración', '{"finanzas": ["read"], "mantenimiento": ["read"], "comunicaciones": ["read", "write"], "documentos": ["read"]}', 0);

-- Menús del sistema
INSERT IGNORE INTO `menu_items` (`id`, `parent_id`, `menu_key`, `menu_text`, `menu_icon`, `menu_url`, `menu_order`, `required_permissions`, `is_active`) VALUES
(1, NULL, 'dashboard', 'Dashboard', 'bi-speedometer2', '/dashboard', 1, '{"dashboard": ["read"]}', 1),
(2, NULL, 'finanzas', 'Finanzas', 'bi-cash-coin', '#', 2, '{"finanzas": ["read"]}', 1),
(3, 2, 'gastos_comunes', 'Gastos Comunes', 'bi-receipt', '/finanzas/gastos-comunes', 1, '{"finanzas": ["read"]}', 1),
(4, 2, 'pagos', 'Estado de Pagos', 'bi-credit-card', '/finanzas/pagos', 2, '{"finanzas": ["read"]}', 1),
(5, 2, 'balances', 'Balances', 'bi-graph-up', '/finanzas/balances', 3, '{"finanzas": ["read", "write"]}', 1),
(6, 2, 'reportes', 'Reportes Financieros', 'bi-file-earmark-text', '/finanzas/reportes', 4, '{"finanzas": ["read", "write"]}', 1),
(7, NULL, 'mantenimiento', 'Mantenimiento', 'bi-tools', '#', 3, '{"mantenimiento": ["read"]}', 1),
(8, 7, 'solicitudes', 'Solicitudes', 'bi-clipboard-plus', '/mantenimiento/solicitudes', 1, '{"mantenimiento": ["read", "write"]}', 1),
(9, 7, 'programacion', 'Programación', 'bi-calendar-event', '/mantenimiento/programacion', 2, '{"mantenimiento": ["read", "write"]}', 1),
(10, 7, 'historial', 'Historial', 'bi-clock-history', '/mantenimiento/historial', 3, '{"mantenimiento": ["read"]}', 1),
(11, NULL, 'amenities', 'Amenities', 'bi-building', '#', 4, '{"reservas": ["read"]}', 1),
(12, 11, 'reservas', 'Reservas', 'bi-calendar-check', '/amenities/reservas', 1, '{"reservas": ["read", "write"]}', 1),
(13, 11, 'espacios', 'Espacios', 'bi-house-door', '/amenities/espacios', 2, '{"reservas": ["read"]}', 1),
(14, NULL, 'comunicaciones', 'Comunicaciones', 'bi-megaphone', '/comunicaciones', 5, '{"comunicaciones": ["read"]}', 1),
(15, NULL, 'documentos', 'Documentos', 'bi-folder', '/documentos', 6, '{"documentos": ["read"]}', 1),
(16, NULL, 'residentes', 'Residentes', 'bi-people', '/residentes', 7, '{"edificios": ["read"]}', 1),
(17, NULL, 'configuracion', 'Configuración', 'bi-gear', '/configuracion', 8, '{"configuracion": ["read", "write"]}', 1);

-- Temas predefinidos
INSERT IGNORE INTO `themes` (`id`, `name`, `description`, `is_active`, `colors`) VALUES
(1, 'tech_blue', 'Tema tecnológico azul - Principal', 1, '{"primary": "#2c3e50", "secondary": "#3498db", "success": "#27ae60", "warning": "#f39c12", "danger": "#e74c3c", "light": "#ecf0f1", "dark": "#34495e", "info": "#17a2b8"}'),
(2, 'professional_green', 'Tema profesional verde', 0, '{"primary": "#16a085", "secondary": "#1abc9c", "success": "#27ae60", "warning": "#f39c12", "danger": "#c0392b", "light": "#ecf0f1", "dark": "#2c3e50", "info": "#2980b9"}'),
(3, 'modern_dark', 'Tema moderno oscuro', 0, '{"primary": "#34495e", "secondary": "#7f8c8d", "success": "#27ae60", "warning": "#f39c12", "danger": "#e74c3c", "light": "#bdc3c7", "dark": "#2c3e50", "info": "#3498db"}');

-- Configuración del sistema
INSERT IGNORE INTO `system_config` (`config_key`, `config_value`, `config_type`, `description`) VALUES
('ley_copropiedad_activa', 'true', 'bool', 'Habilita cumplimiento Ley de Copropiedad'),
('dias_vencimiento_gastos', '10', 'int', 'Días de gracia para pagos de gastos comunes'),
('notificar_mantenimientos', 'true', 'bool', 'Notificar mantenimientos pendientes'),
('requerir_actas_reunion', 'true', 'bool', 'Requerir actas de reunión del comité'),
('max_reservas_semana', '2', 'int', 'Máximo de reservas por departamento por semana'),
('moneda_sistema', 'CLP', 'string', 'Moneda utilizada en el sistema'),
('pais_sistema', 'Chile', 'string', 'País del sistema'),
('rut_empresa', '76.123.456-7', 'string', 'RUT de la empresa administradora'),
('nombre_empresa', 'AdminEdificios Chile SpA', 'string', 'Nombre de la empresa administradora'),
('version_sistema', '1.0.0', 'string', 'Versión del sistema');

-- Usuario super administrador por defecto (password: Admin123!)
INSERT IGNORE INTO `users` (`id`, `rut`, `nombre`, `apellido`, `email`, `telefono`, `password_hash`, `role_id`) VALUES
(1, '12.345.678-9', 'Admin', 'Sistema', 'admin@sistemaedificios.cl', '+56912345678', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

CREATE TABLE IF NOT EXISTS `amenity_imagenes` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `amenity_id` INT UNSIGNED NOT NULL,
    `nombre_archivo` VARCHAR(255),
    `ruta_archivo` VARCHAR(500),
    `orden` INT DEFAULT 0,
    `is_principal` BOOLEAN DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_amenity_imagen_amenity` (`amenity_id`),
    INDEX `idx_amenity_imagen_orden` (`orden`),
    FOREIGN KEY (`amenity_id`) 
        REFERENCES `amenities` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabla de configuración jerárquica
CREATE TABLE IF NOT EXISTS `amenity_config` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nivel` ENUM('global', 'edificio', 'amenity') NOT NULL,
    `entidad_id` INT UNSIGNED NULL,
    `configuracion` JSON NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_config_nivel_entidad` (`nivel`, `entidad_id`),
    INDEX `idx_config_nivel` (`nivel`),
    INDEX `idx_config_entidad` (`entidad_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Ampliar tabla amenities con horarios complejos
ALTER TABLE `amenities` 
ADD COLUMN `horario_lunes_apertura` TIME NULL,
ADD COLUMN `horario_lunes_cierre` TIME NULL,
ADD COLUMN `horario_sabado_apertura` TIME NULL,
ADD COLUMN `horario_sabado_cierre` TIME NULL,
ADD COLUMN `horario_domingo_apertura` TIME NULL,
ADD COLUMN `horario_domingo_cierre` TIME NULL,
ADD COLUMN `horario_verano_inicio` DATE NULL,
ADD COLUMN `horario_verano_fin` DATE NULL,
ADD COLUMN `horario_verano_apertura` TIME NULL,
ADD COLUMN `horario_verano_cierre` TIME NULL,
ADD COLUMN `horario_invierno_inicio` DATE NULL,
ADD COLUMN `horario_invierno_fin` DATE NULL,
ADD COLUMN `horario_invierno_apertura` TIME NULL,
ADD COLUMN `horario_invierno_cierre` TIME NULL,
ADD COLUMN `requiere_aprobacion` BOOLEAN DEFAULT 1,
ADD COLUMN `max_reservas_semana` INT UNSIGNED DEFAULT 2,
ADD COLUMN `max_reservas_mismo_dia` INT UNSIGNED DEFAULT 1,
ADD COLUMN `antelacion_maxima_dias` INT UNSIGNED DEFAULT 30,
ADD COLUMN `duracion_minima_reserva` INT UNSIGNED DEFAULT 60,
ADD COLUMN `duracion_maxima_reserva` INT UNSIGNED DEFAULT 240,
ADD COLUMN `bloques_horarios` JSON NULL;

-- 4. Tabla de bloqueos temporales
CREATE TABLE IF NOT EXISTS `amenity_bloqueos` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `amenity_id` INT UNSIGNED NOT NULL,
    `fecha_inicio` DATE NOT NULL,
    `fecha_fin` DATE NOT NULL,
    `hora_inicio` TIME NULL,
    `hora_fin` TIME NULL,
    `motivo` VARCHAR(255) NOT NULL,
    `created_by` INT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_bloqueo_amenity` (`amenity_id`),
    INDEX `idx_bloqueo_fechas` (`fecha_inicio`, `fecha_fin`),
    FOREIGN KEY (`amenity_id`) 
        REFERENCES `amenities` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (`created_by`) 
        REFERENCES `users` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Insertar configuración global por defecto
INSERT IGNORE INTO `amenity_config` (`nivel`, `entidad_id`, `configuracion`) VALUES
('global', NULL, '{
    "max_horas_por_reserva": 4,
    "dias_anticipacion_reserva": 7,
    "notificar_conflictos": true,
    "auto_aprobar_reservas": false,
    "permisos_reserva": ["propietario", "arrendatario"],
    "horario_global_apertura": "08:00",
    "horario_global_cierre": "22:00"
}');

-- 6. Actualizar menús para nuevo módulo
UPDATE `menu_items` 
SET `menu_text` = 'Gestión Amenities', `menu_icon` = 'bi-building-gear'
WHERE `menu_key` = 'espacios';

INSERT IGNORE INTO `menu_items` (`parent_id`, `menu_key`, `menu_text`, `menu_icon`, `menu_url`, `menu_order`, `required_permissions`, `is_active`) VALUES
(11, 'configuracion_amenities', 'Configuración', 'bi-gear', '/amenities/configuracion', 3, '{"reservas": ["write", "delete"]}', 1);

-- 📁 Actualización de menús para integración completa

-- Actualizar menú existente de Amenities
UPDATE `menu_items` 
SET `menu_url` = '/amenities/gestionar', 
    `menu_text` = 'Gestión Amenities',
    `menu_icon` = 'bi-building-gear'
WHERE `menu_key` = 'espacios';

-- Agregar nuevo menú de Configuración Amenities
INSERT IGNORE INTO `menu_items` 
(`parent_id`, `menu_key`, `menu_text`, `menu_icon`, `menu_url`, `menu_order`, `required_permissions`, `is_active`) 
VALUES 
(11, 'configuracion_amenities', 'Configuración', 'bi-gear', '/amenities/configuracion', 3, '{"reservas": ["write", "delete"]}', 1);

-- 📁 database/update_permissions_system.sql

-- 1. Modificar tabla user_roles existente
ALTER TABLE `user_roles` 
MODIFY COLUMN `permissions` JSON NOT NULL DEFAULT '{}',
ADD COLUMN `is_editable` BOOLEAN DEFAULT 1,
ADD COLUMN `module_access` JSON NULL;

-- 2. Nueva tabla para módulos del sistema
CREATE TABLE IF NOT EXISTS `system_modules` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `module_key` VARCHAR(100) NOT NULL UNIQUE,
    `module_name` VARCHAR(255) NOT NULL,
    `module_description` TEXT NULL,
    `module_icon` VARCHAR(100) NULL,
    `parent_module` VARCHAR(100) NULL,
    `is_active` BOOLEAN DEFAULT 1,
    `actions` JSON NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_module_key` (`module_key`),
    INDEX `idx_module_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Tabla para permisos granular por rol-módulo
CREATE TABLE IF NOT EXISTS `role_module_permissions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `role_id` INT UNSIGNED NOT NULL,
    `module_key` VARCHAR(100) NOT NULL,
    `permissions` JSON NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_role_module` (`role_id`, `module_key`),
    INDEX `idx_role_permissions` (`role_id`),
    FOREIGN KEY (`role_id`) 
        REFERENCES `user_roles` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Insertar módulos del sistema
INSERT IGNORE INTO `system_modules` 
(`module_key`, `module_name`, `module_description`, `module_icon`, `parent_module`, `actions`) VALUES
-- MÓDULO PRINCIPAL: AMENITIES
('amenities', 'Gestión de Amenities', 'Administración completa de espacios comunes', 'bi-building-gear', NULL, '["read", "write", "delete", "configure"]'),
('amenities.images', 'Imágenes de Amenities', 'Gestión de galería de imágenes', 'bi-images', 'amenities', '["read", "write", "delete"]'),
('amenities.config', 'Configuración Amenities', 'Horarios, reglas y parámetros', 'bi-gear', 'amenities', '["read", "write"]'),
('amenities.blocking', 'Bloqueos Temporales', 'Gestión de bloqueos de amenities', 'bi-calendar-x', 'amenities', '["read", "write", "delete"]'),

-- MÓDULO PRINCIPAL: RESERVAS
('reservas', 'Sistema de Reservas', 'Gestión completa de reservas', 'bi-calendar-check', NULL, '["read", "write", "delete", "approve", "cancel"]'),
('reservas.calendario', 'Calendario de Reservas', 'Vista calendario interactivo', 'bi-calendar-week', 'reservas', '["read"]'),
('reservas.aprobaciones', 'Aprobación de Reservas', 'Panel de aprobaciones pendientes', 'bi-clipboard-check', 'reservas', '["read", "approve", "reject"]'),
('reservas.mis_reservas', 'Mis Reservas', 'Gestión de reservas personales', 'bi-list-check', 'reservas', '["read", "write", "cancel"]'),

-- MÓDULO PRINCIPAL: PAGOS
('pagos', 'Sistema de Pagos', 'Gestión de pagos de amenities', 'bi-credit-card', NULL, '["read", "write", "confirm", "cancel", "report"]'),
('pagos.webpay', 'Pagos Webpay', 'Integración con Transbank', 'bi-credit-card-2-front', 'pagos', '["read", "process"]'),
('pagos.transferencias', 'Transferencias Bancarias', 'Gestión de transferencias', 'bi-bank', 'pagos', '["read", "confirm", "reject"]'),
('pagos.presencial', 'Pagos Presenciales', 'Registro de pagos en sitio', 'bi-cash', 'pagos', '["read", "register"]'),

-- MÓDULOS EXISTENTES ACTUALIZADOS
('edificios', 'Gestión de Edificios', 'Administración de edificios', 'bi-building', NULL, '["read", "write", "delete", "configure"]'),
('finanzas', 'Sistema Financiero', 'Gestión de gastos comunes y finanzas', 'bi-cash-coin', NULL, '["read", "write", "delete", "report"]'),
('mantenimiento', 'Sistema de Mantenimiento', 'Gestión de mantenimientos', 'bi-tools', NULL, '["read", "write", "delete", "assign"]'),
('comunicaciones', 'Sistema de Comunicaciones', 'Gestión de comunicaciones', 'bi-megaphone', NULL, '["read", "write", "delete", "publish"]'),
('documentos', 'Gestión Documental', 'Administración de documentos', 'bi-folder', NULL, '["read", "write", "delete", "share"]'),
('usuarios', 'Gestión de Usuarios', 'Administración de usuarios y roles', 'bi-people', NULL, '["read", "write", "delete", "assign_roles"]'),
('roles', 'Gestión de Roles', 'Configuración de roles y permisos', 'bi-shield-check', NULL, '["read", "write", "delete", "assign_permissions"]'),

-- MÓDULOS DEL SISTEMA BASE
('dashboard', 'Dashboard', 'Panel de control principal', 'bi-speedometer2', NULL, '["read"]'),
('configuracion', 'Configuración Sistema', 'Configuración global del sistema', 'bi-gear-fill', NULL, '["read", "write"]');

-- 5. Actualizar roles existentes para ser editables (excepto super_admin)
UPDATE `user_roles` SET 
`is_editable` = CASE 
    WHEN `id` = 1 THEN 0  -- super_admin no editable
    ELSE 1 
END,
`permissions` = '{}';

-- 6. Insertar permisos por defecto para roles existentes
INSERT IGNORE INTO `role_module_permissions` (`role_id`, `module_key`, `permissions`) VALUES
-- SUPER_ADMIN - TODOS LOS PERMISOS
(1, 'dashboard', '{"read": true}'),
(1, 'edificios', '{"read": true, "write": true, "delete": true, "configure": true}'),
(1, 'amenities', '{"read": true, "write": true, "delete": true, "configure": true}'),
(1, 'reservas', '{"read": true, "write": true, "delete": true, "approve": true, "cancel": true}'),
(1, 'pagos', '{"read": true, "write": true, "confirm": true, "cancel": true, "report": true}'),
(1, 'finanzas', '{"read": true, "write": true, "delete": true, "report": true}'),
(1, 'mantenimiento', '{"read": true, "write": true, "delete": true, "assign": true}'),
(1, 'comunicaciones', '{"read": true, "write": true, "delete": true, "publish": true}'),
(1, 'documentos', '{"read": true, "write": true, "delete": true, "share": true}'),
(1, 'usuarios', '{"read": true, "write": true, "delete": true, "assign_roles": true}'),
(1, 'roles', '{"read": true, "write": true, "delete": true, "assign_permissions": true}'),
(1, 'configuracion', '{"read": true, "write": true}'),

-- ADMINISTRADOR
(2, 'dashboard', '{"read": true}'),
(2, 'edificios', '{"read": true, "write": true, "configure": true}'),
(2, 'amenities', '{"read": true, "write": true, "delete": true, "configure": true}'),
(2, 'reservas', '{"read": true, "write": true, "delete": true, "approve": true, "cancel": true}'),
(2, 'pagos', '{"read": true, "write": true, "confirm": true, "report": true}'),
(2, 'finanzas', '{"read": true, "write": true, "report": true}'),
(2, 'mantenimiento', '{"read": true, "write": true, "assign": true}'),
(2, 'comunicaciones', '{"read": true, "write": true, "publish": true}'),
(2, 'documentos', '{"read": true, "write": true, "share": true}'),
(2, 'usuarios', '{"read": true, "write": true}'),

-- CONSERJE
(3, 'dashboard', '{"read": true}'),
(3, 'amenities', '{"read": true}'),
(3, 'reservas', '{"read": true, "approve": true, "cancel": true}'),
(3, 'pagos', '{"read": true, "register": true}'),
(3, 'mantenimiento', '{"read": true, "write": true}'),

-- RESIDENTE
(4, 'dashboard', '{"read": true}'),
(4, 'amenities', '{"read": true}'),
(4, 'reservas', '{"read": true, "write": true, "cancel": true}'),
(4, 'pagos', '{"read": true, "process": true}'),

-- COMITE
(5, 'dashboard', '{"read": true}'),
(5, 'amenities', '{"read": true}'),
(5, 'reservas', '{"read": true}'),
(5, 'pagos', '{"read": true, "report": true}'),
(5, 'finanzas', '{"read": true, "report": true}'),
(5, 'documentos', '{"read": true}');

-- 7. Actualizar menús del sistema
UPDATE `menu_items` SET `required_permissions` = '{"dashboard": ["read"]}' WHERE `menu_key` = 'dashboard';
UPDATE `menu_items` SET `required_permissions` = '{"edificios": ["read"]}' WHERE `menu_key` = 'edificios';
UPDATE `menu_items` SET `required_permissions` = '{"amenities": ["read"]}' WHERE `menu_key` = 'espacios';
UPDATE `menu_items` SET `required_permissions` = '{"reservas": ["read"]}' WHERE `menu_key` = 'reservas';
UPDATE `menu_items` SET `required_permissions` = '{"finanzas": ["read"]}' WHERE `menu_key` = 'finanzas';
UPDATE `menu_items` SET `required_permissions` = '{"mantenimiento": ["read"]}' WHERE `menu_key` = 'mantenimiento';
UPDATE `menu_items` SET `required_permissions` = '{"comunicaciones": ["read"]}' WHERE `menu_key` = 'comunicaciones';
UPDATE `menu_items` SET `required_permissions` = '{"documentos": ["read"]}' WHERE `menu_key` = 'documentos';
UPDATE `menu_items` SET `required_permissions` = '{"usuarios": ["read"]}' WHERE `menu_key` = 'residentes';
UPDATE `menu_items` SET `required_permissions` = '{"configuracion": ["read"]}' WHERE `menu_key` = 'configuracion';

-- Agregar menú para gestión de roles
INSERT IGNORE INTO `menu_items` 
(`parent_id`, `menu_key`, `menu_text`, `menu_icon`, `menu_url`, `menu_order`, `required_permissions`, `is_active`) 
VALUES 
(17, 'gestion_roles', 'Gestión de Roles', 'bi-shield-check', '/roles', 2, '{"roles": ["read"]}', 1);

-- ==================== SISTEMA DE PRORRATEO - ESTRUCTURA COMPLETA ====================

-- 1. TABLA DE ESTRATEGIAS DE PRORRATEO CONFIGURABLES
CREATE TABLE IF NOT EXISTS `prorrateo_strategies` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(100) NOT NULL,
    `descripcion` TEXT NULL,
    `tipo` ENUM('automatico', 'manual', 'mixto') DEFAULT 'automatico',
    `metodo_calculo` ENUM('porcentaje_copropiedad', 'metros_cuadrados', 'equitativo', 'personalizado', 'mixto') NOT NULL,
    `config_json` JSON NOT NULL COMMENT 'Configuración específica del método',
    `limites_legales_json` JSON NOT NULL COMMENT 'Límites según ley chilena',
    `requiere_aprobacion` BOOLEAN DEFAULT 1,
    `nivel_aprobacion` ENUM('administrador', 'comite', 'asamblea') DEFAULT 'administrador',
    `is_active` BOOLEAN DEFAULT 1,
    `created_by` INT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_prorrateo_activo` (`is_active`),
    INDEX `idx_prorrateo_tipo` (`tipo`),
    INDEX `idx_prorrateo_metodo` (`metodo_calculo`),
    FOREIGN KEY (`created_by`) 
        REFERENCES `users` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. TABLA DE CONFIGURACIÓN DE MÉTRICAS POR EDIFICIO
CREATE TABLE IF NOT EXISTS `prorrateo_edificio_config` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `edificio_id` INT UNSIGNED NOT NULL,
    `estrategia_default_id` INT UNSIGNED NULL,
    `superficie_considerar` ENUM('util', 'total', 'escritura', 'mixta') DEFAULT 'util',
    `validacion_legal_activa` BOOLEAN DEFAULT 1,
    `max_variacion_porcentual` DECIMAL(5,2) DEFAULT 20.00 COMMENT 'Máxima variación permitida por ley',
    `tratamiento_comercial` ENUM('igual', 'incremento_10', 'incremento_20', 'incremento_30', 'personalizado') DEFAULT 'incremento_20',
    `config_avanzada_json` JSON NULL,
    `created_by` INT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_edificio_config` (`edificio_id`),
    INDEX `idx_config_edificio` (`edificio_id`),
    INDEX `idx_config_estrategia` (`estrategia_default_id`),
    FOREIGN KEY (`edificio_id`) 
        REFERENCES `edificios` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (`estrategia_default_id`) 
        REFERENCES `prorrateo_strategies` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    FOREIGN KEY (`created_by`) 
        REFERENCES `users` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. TABLA DE REGISTRO DE PRORRATEOS APLICADOS CON VERSIONADO
CREATE TABLE IF NOT EXISTS `gasto_prorrateo_log` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `gasto_comun_id` INT UNSIGNED NOT NULL,
    `estrategia_id` INT UNSIGNED NOT NULL,
    `version` INT UNSIGNED DEFAULT 1,
    `detalles_calculo_json` JSON NOT NULL COMMENT 'Detalles completos del cálculo',
    `distribucion_final_json` JSON NOT NULL COMMENT 'Distribución final aplicada',
    `estado` ENUM('borrador', 'pendiente_aprobacion', 'aprobado', 'rechazado', 'revision_legal') DEFAULT 'borrador',
    `justificacion_cambios` TEXT NULL,
    `validacion_legal_json` JSON NULL COMMENT 'Resultado validación LegalChileManager',
    `hash_distribucion` VARCHAR(64) NULL COMMENT 'Hash para control de integridad',
    `created_by` INT UNSIGNED NULL,
    `approved_by` INT UNSIGNED NULL,
    `approved_at` TIMESTAMP NULL,
    `rejected_by` INT UNSIGNED NULL,
    `rejection_reason` TEXT NULL,
    `rejected_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_gasto_version` (`gasto_comun_id`, `version`),
    INDEX `idx_prorrateo_gasto` (`gasto_comun_id`),
    INDEX `idx_prorrateo_estrategia` (`estrategia_id`),
    INDEX `idx_prorrateo_estado` (`estado`),
    INDEX `idx_prorrateo_created` (`created_at`),
    INDEX `idx_prorrateo_approved` (`approved_at`),
    FOREIGN KEY (`gasto_comun_id`) 
        REFERENCES `gastos_comunes` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (`estrategia_id`) 
        REFERENCES `prorrateo_strategies` (`id`)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    FOREIGN KEY (`created_by`) 
        REFERENCES `users` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    FOREIGN KEY (`approved_by`) 
        REFERENCES `users` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    FOREIGN KEY (`rejected_by`) 
        REFERENCES `users` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. TABLA DE EXENCIONES Y CASOS ESPECIALES SEGÚN LEY CHILENA
CREATE TABLE IF NOT EXISTS `departamento_exenciones` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `departamento_id` INT UNSIGNED NOT NULL,
    `tipo_exencion` ENUM('total', 'parcial', 'temporal', 'comercial', 'reparacion', 'deshabitado') NOT NULL,
    `porcentaje_exencion` DECIMAL(5,2) DEFAULT 0.00,
    `motivo` TEXT NOT NULL,
    `periodo_inicio` DATE NOT NULL,
    `periodo_fin` DATE NULL,
    `documento_respaldo` VARCHAR(500) NULL,
    `estado` ENUM('pendiente', 'aprobado', 'rechazado', 'expirado') DEFAULT 'pendiente',
    `aprobado_por` INT UNSIGNED NULL,
    `fecha_aprobacion` TIMESTAMP NULL,
    `created_by` INT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_exencion_departamento` (`departamento_id`),
    INDEX `idx_exencion_tipo` (`tipo_exencion`),
    INDEX `idx_exencion_periodo` (`periodo_inicio`, `periodo_fin`),
    INDEX `idx_exencion_estado` (`estado`),
    FOREIGN KEY (`departamento_id`) 
        REFERENCES `departamentos` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (`created_by`) 
        REFERENCES `users` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    FOREIGN KEY (`aprobado_por`) 
        REFERENCES `users` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. TABLA DE HISTÓRICO DE MODIFICACIONES DE DISTRIBUCIÓN
CREATE TABLE IF NOT EXISTS `prorrateo_historial_modificaciones` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `prorrateo_log_id` BIGINT UNSIGNED NOT NULL,
    `campo_modificado` VARCHAR(100) NOT NULL,
    `valor_anterior` JSON NULL,
    `valor_nuevo` JSON NOT NULL,
    `tipo_modificacion` ENUM('creacion', 'actualizacion', 'aprobacion', 'rechazo', 'redistribucion') NOT NULL,
    `justificacion` TEXT NOT NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` TEXT NULL,
    `created_by` INT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_historial_prorrateo` (`prorrateo_log_id`),
    INDEX `idx_historial_tipo` (`tipo_modificacion`),
    INDEX `idx_historial_created` (`created_at`),
    FOREIGN KEY (`prorrateo_log_id`) 
        REFERENCES `gasto_prorrateo_log` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (`created_by`) 
        REFERENCES `users` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. COLUMNAS ADICIONALES EN TABLAS EXISTENTES
ALTER TABLE `gastos_comunes` 
ADD COLUMN `estrategia_prorrateo_id` INT UNSIGNED NULL AFTER `estado`,
ADD COLUMN `requiere_aprobacion_prorrateo` BOOLEAN DEFAULT 0 AFTER `estrategia_prorrateo_id`,
ADD COLUMN `distribucion_confirmada` BOOLEAN DEFAULT 0 AFTER `requiere_aprobacion_prorrateo`,
ADD COLUMN `hash_distribucion_actual` VARCHAR(64) NULL AFTER `distribucion_confirmada`,
ADD INDEX `idx_gasto_estrategia` (`estrategia_prorrateo_id`),
ADD CONSTRAINT `fk_gasto_estrategia`
    FOREIGN KEY (`estrategia_prorrateo_id`)
    REFERENCES `prorrateo_strategies` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;

ALTER TABLE `gasto_departamento`
ADD COLUMN `es_exento` BOOLEAN DEFAULT 0 AFTER `porcentaje`,
ADD COLUMN `motivo_exencion` VARCHAR(255) NULL AFTER `es_exento`,
ADD COLUMN `porcentaje_original` DECIMAL(5,2) NULL AFTER `motivo_exencion`,
ADD COLUMN `exencion_id` INT UNSIGNED NULL AFTER `porcentaje_original`,
ADD INDEX `idx_gasto_depto_exento` (`es_exento`),
ADD INDEX `idx_gasto_depto_exencion` (`exencion_id`),
ADD CONSTRAINT `fk_gasto_depto_exencion`
    FOREIGN KEY (`exencion_id`)
    REFERENCES `departamento_exenciones` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;

-- ==================== DATOS INICIALES - ESTRATEGIAS PREDEFINIDAS ====================

-- Estrategias de prorrateo según ley chilena
INSERT IGNORE INTO `prorrateo_strategies` 
(`id`, `nombre`, `descripcion`, `tipo`, `metodo_calculo`, `config_json`, `limites_legales_json`, `requiere_aprobacion`, `nivel_aprobacion`) VALUES
(1, 'Porcentaje Copropiedad Estándar', 'Distribución según porcentaje de copropiedad establecido en escrituras', 'automatico', 'porcentaje_copropiedad', 
 '{"considerar_exenciones": true, "redistribuir_exentos": true, "validar_limites": true}',
 '{"max_variacion_porcentual": 20.00, "tratamiento_comercial": "incremento_20", "exencion_deshabitados": 50}',
 0, 'administrador'),

(2, 'Metros Cuadrados Útiles', 'Distribución proporcional a metros cuadrados útiles de cada departamento', 'automatico', 'metros_cuadrados',
 '{"superficie_considerar": "util", "excluir_areas_comunes": true, "redondeo_decimales": 2}',
 '{"max_variacion_porcentual": 15.00, "min_metros_cuadrados": 20, "validar_escrituras": true}',
 1, 'comite'),

(3, 'Distribución Equitativa', 'Distribución igualitaria entre todos los departamentos', 'automatico', 'equitativo',
 '{"excluir_comerciales": false, "considerar_habitados": true, "minimo_departamentos": 2}',
 '{"max_variacion_porcentual": 0.00, "aplica_para": "gastos_generales"}',
 1, 'administrador'),

(4, 'Mixto Copropiedad-Metros', 'Combinación entre porcentaje de copropiedad y metros cuadrados', 'mixto', 'mixto',
 '{"peso_copropiedad": 60, "peso_metros_cuadrados": 40, "superficie_considerar": "util"}',
 '{"max_variacion_porcentual": 18.00, "validar_pesos": true}',
 1, 'comite'),

(5, 'Manual con Validación Legal', 'Distribución manual con validación automática de límites legales', 'manual', 'personalizado',
 '{"validacion_automatica": true, "mostrar_alertas_legales": true, "sugerir_ajustes": true}',
 '{"max_variacion_porcentual": 20.00, "alertar_excesos": true}',
 1, 'asamblea');

-- Configuración de módulo en system_modules
INSERT IGNORE INTO `system_modules` 
(`module_key`, `module_name`, `module_description`, `module_icon`, `parent_module`, `actions`) VALUES
('prorrateo', 'Sistema de Prorrateo', 'Gestión de distribución de gastos comunes', 'bi-calculator', 'finanzas', 
 '["read", "write", "delete", "configure", "approve", "validate_legal"]'),
('prorrateo.estrategias', 'Estrategias de Prorrateo', 'Configuración de métodos de distribución', 'bi-gear', 'prorrateo', 
 '["read", "write", "delete", "assign"]'),
('prorrateo.exenciones', 'Gestión de Exenciones', 'Administración de casos especiales y exenciones', 'bi-shield-check', 'prorrateo', 
 '["read", "write", "delete", "approve"]'),
('prorrateo.aprobaciones', 'Aprobación de Distribuciones', 'Panel de revisión y aprobación de prorrateos', 'bi-clipboard-check', 'prorrateo', 
 '["read", "approve", "reject", "review"]');

-- Actualizar permisos de roles existentes
INSERT IGNORE INTO `role_module_permissions` (`role_id`, `module_key`, `permissions`) VALUES
(1, 'prorrateo', '{"read": true, "write": true, "delete": true, "configure": true, "approve": true, "validate_legal": true}'),
(1, 'prorrateo.estrategias', '{"read": true, "write": true, "delete": true, "assign": true}'),
(1, 'prorrateo.exenciones', '{"read": true, "write": true, "delete": true, "approve": true}'),
(1, 'prorrateo.aprobaciones', '{"read": true, "approve": true, "reject": true, "review": true}'),

(2, 'prorrateo', '{"read": true, "write": true, "configure": true, "approve": true, "validate_legal": true}'),
(2, 'prorrateo.estrategias', '{"read": true, "write": true, "assign": true}'),
(2, 'prorrateo.exenciones', '{"read": true, "write": true, "approve": true}'),
(2, 'prorrateo.aprobaciones', '{"read": true, "approve": true, "reject": true}'),

(5, 'prorrateo', '{"read": true, "approve": true, "validate_legal": true}'),
(5, 'prorrateo.aprobaciones', '{"read": true, "approve": true, "review": true}');

-- Menús para el sistema de prorrateo
INSERT IGNORE INTO `menu_items` 
(`parent_id`, `menu_key`, `menu_text`, `menu_icon`, `menu_url`, `menu_order`, `required_permissions`, `is_active`) VALUES
(2, 'prorrateo_gastos', 'Prorrateo Gastos', 'bi-calculator', '/finanzas/prorrateo', 5, '{"prorrateo": ["read"]}', 1),
(17, 'config_prorrateo', 'Config. Prorrateo', 'bi-gear', '/configuracion/prorrateo', 6, '{"prorrateo": ["configure"]}', 1);
