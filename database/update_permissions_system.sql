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
