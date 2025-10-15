-- 📁 database/update_prorrateo_system.sql - COMPATIBLE MYSQL

-- ==================== ACTUALIZACIONES DE TABLAS EXISTENTES ====================

-- Actualizar tabla prorrateo_edificio_config para nueva estructura
ALTER TABLE `prorrateo_edificio_config` 
DROP COLUMN `pais`,
DROP COLUMN `ley_copropiedad_vigente`, 
DROP COLUMN `considerar_comerciales`,
DROP COLUMN `incremento_comercial`,
DROP COLUMN `factor_piso`,
DROP COLUMN `factor_orientacion`,
DROP COLUMN `calculo_automatico`;

-- Agregar comentarios y restricciones mejoradas
ALTER TABLE `prorrateo_edificio_config` 
MODIFY `max_variacion_porcentual` decimal(5,2) DEFAULT 20.00 
COMMENT 'Máxima variación permitida por ley entre departamentos';

ALTER TABLE `prorrateo_edificio_config`
MODIFY `config_avanzada_json` json NOT NULL 
COMMENT 'Configuración avanzada unificada en formato JSON';

-- Actualizar tabla prorrateo_strategies para nuevos campos
ALTER TABLE `prorrateo_strategies`
ADD COLUMN `is_system_strategy` tinyint(1) DEFAULT 0 
COMMENT 'Indica si es una estrategia del sistema (no editable)' AFTER `is_active`,
ADD COLUMN `version` varchar(10) DEFAULT '1.0' 
COMMENT 'Versión de la estrategia para control de cambios' AFTER `is_system_strategy`,
ADD COLUMN `compatible_paises` json DEFAULT '["CL"]' 
COMMENT 'Países compatibles con esta estrategia' AFTER `version`;

-- Agregar índices para optimización (SOLO ÍNDICES COMPATIBLES)
CREATE INDEX `idx_prorrateo_strategies_active` ON `prorrateo_strategies` (`is_active`, `is_system_strategy`);

-- Actualizar tabla gasto_prorrateo_log para nuevo flujo
ALTER TABLE `gasto_prorrateo_log`
ADD COLUMN `hash_distribucion` varchar(64) 
COMMENT 'Hash SHA256 de la distribución para integridad' AFTER `detalles_json`,
ADD COLUMN `version_calculo` varchar(10) DEFAULT '1.0' 
COMMENT 'Versión del algoritmo de cálculo usado' AFTER `hash_distribucion`,
ADD COLUMN `tiempo_calculo_ms` int 
COMMENT 'Tiempo que tomó el cálculo en milisegundos' AFTER `version_calculo`,
ADD COLUMN `metadata_json` json 
COMMENT 'Metadatos adicionales del cálculo' AFTER `tiempo_calculo_ms`;

-- Modificar estado para nuevo flujo de trabajo
ALTER TABLE `gasto_prorrateo_log` 
MODIFY `estado` enum('pendiente','pendiente_aprobacion','aprobado','rechazado','error_calculo') 
DEFAULT 'pendiente'
COMMENT 'Estado en el flujo de trabajo de prorrateo';

-- Agregar índices para consultas de prorrateo (COMPATIBLES)
CREATE INDEX `idx_prorrateo_log_estado` ON `gasto_prorrateo_log` (`estado`, `created_at`);
CREATE INDEX `idx_prorrateo_log_gasto` ON `gasto_prorrateo_log` (`gasto_comun_id`, `estado`);
CREATE INDEX `idx_prorrateo_log_estrategia` ON `gasto_prorrateo_log` (`estrategia_id`, `created_at`);

-- ==================== NUEVAS TABLAS PARA SISTEMA COMPLETO ====================

-- Tabla para historial de cambios en porcentajes de copropiedad
CREATE TABLE IF NOT EXISTS `departamento_porcentaje_historial` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `departamento_id` int unsigned NOT NULL,
  `porcentaje_anterior` decimal(5,2) NOT NULL,
  `porcentaje_nuevo` decimal(5,2) NOT NULL,
  `motivo_cambio` enum('calculo_automatico','ajuste_manual','redistribucion','correccion') NOT NULL,
  `detalles_cambio` json,
  `calculado_por_sistema` tinyint(1) DEFAULT 1,
  `created_by` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_porcentaje_historial_depto` (`departamento_id`),
  KEY `idx_porcentaje_historial_fecha` (`created_at`),
  KEY `idx_porcentaje_historial_motivo` (`motivo_cambio`),
  CONSTRAINT `fk_porcentaje_historial_departamento` 
    FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_porcentaje_historial_usuario` 
    FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Historial de cambios en porcentajes de copropiedad';

-- Tabla para plantillas de estrategias de prorrateo
CREATE TABLE IF NOT EXISTS `prorrateo_strategy_templates` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text,
  `tipo_estrategia` enum('basica','avanzada','legal','personalizada') NOT NULL DEFAULT 'basica',
  `config_template_json` json NOT NULL,
  `paises_compatibles` json DEFAULT '["CL"]',
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_strategy_template_nombre` (`nombre`),
  KEY `idx_strategy_template_tipo` (`tipo_estrategia`),
  KEY `idx_strategy_template_active` (`is_active`),
  KEY `idx_strategy_template_created_by` (`created_by`),
  CONSTRAINT `fk_strategy_template_created_by` 
    FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Plantillas predefinidas para estrategias de prorrateo';

-- Tabla para validaciones legales por país
CREATE TABLE IF NOT EXISTS `prorrateo_legal_rules` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `pais` varchar(3) NOT NULL,
  `ley_referencia` varchar(100) NOT NULL,
  `regla_tipo` enum('variacion_maxima','tratamiento_comercial','exenciones','factores') NOT NULL,
  `parametros_json` json NOT NULL,
  `descripcion` text,
  `vigente_desde` date NOT NULL,
  `vigente_hasta` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_legal_rules_pais_ley` (`pais`, `ley_referencia`, `regla_tipo`),
  KEY `idx_legal_rules_pais` (`pais`),
  KEY `idx_legal_rules_tipo` (`regla_tipo`),
  KEY `idx_legal_rules_vigencia` (`vigente_desde`, `vigente_hasta`),
  KEY `idx_legal_rules_active` (`is_active`),
  CONSTRAINT `fk_legal_rules_created_by` 
    FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Reglas legales de prorrateo por país y ley';

-- ==================== INSERCIÓN DE DATOS INICIALES ====================

-- Insertar estrategias del sistema (no editables)
INSERT IGNORE INTO `prorrateo_strategies` 
(`nombre`, `descripcion`, `tipo`, `metodo_calculo`, `config_json`, `is_active`, `is_system_strategy`, `compatible_paises`) VALUES
(
  'Porcentaje Copropiedad', 
  'Distribución según porcentajes de copropiedad establecidos', 
  'automatico', 
  'porcentaje_copropiedad', 
  '{"validar_limites": true, "considerar_exenciones": true, "redistribuir_exentos": true}',
  1, 1, '["CL", "AR", "PE", "CO", "MX"]'
),
(
  'Metros Cuadrados Útiles', 
  'Distribución proporcional a metros cuadrados útiles', 
  'automatico', 
  'metros_cuadrados', 
  '{"validar_limites": true, "superficie_considerar": "util", "considerar_comerciales": true}',
  1, 1, '["CL", "AR", "PE", "CO", "MX"]'
),
(
  'Distribución Equitativa', 
  'Distribución igualitaria entre todos los departamentos', 
  'automatico', 
  'equitativo', 
  '{"validar_limites": false, "excluir_comerciales": false, "considerar_habitados": true}',
  1, 1, '["CL", "AR", "PE", "CO", "MX"]'
),
(
  'Mixto Chileno Ley 19.537', 
  'Distribución mixta según Ley de Copropiedad Chilena', 
  'automatico', 
  'mixto', 
  '{"validar_limites": true, "peso_copropiedad": 60, "peso_metros_cuadrados": 40, "ley_aplicable": "Ley 19.537"}',
  1, 1, '["CL"]'
);

-- Insertar reglas legales para Chile
INSERT IGNORE INTO `prorrateo_legal_rules` 
(`pais`, `ley_referencia`, `regla_tipo`, `parametros_json`, `descripcion`, `vigente_desde`) VALUES
(
  'CL', 
  'Ley 19.537', 
  'variacion_maxima', 
  '{"max_variacion_porcentual": 30.00, "aplicable_desde": 100, "excepciones": []}',
  'Límite máximo de variación entre departamentos según Ley de Copropiedad',
  '2013-10-25'
),
(
  'CL', 
  'Ley 19.537', 
  'tratamiento_comercial', 
  '{"incremento_permitido": 20.00, "requiere_asamblea": true, "max_incremento": 30.00}',
  'Tratamiento para locales comerciales - incremento máximo permitido',
  '2013-10-25'
),
(
  'AR', 
  'Ley 13.512', 
  'variacion_maxima', 
  '{"max_variacion_porcentual": 25.00, "aplicable_desde": 100, "excepciones": []}',
  'Límite máximo de variación para Argentina',
  '2020-01-01'
);

-- Insertar plantillas de estrategias
INSERT IGNORE INTO `prorrateo_strategy_templates` 
(`nombre`, `descripcion`, `tipo_estrategia`, `config_template_json`, `paises_compatibles`) VALUES
(
  'Residencial Estándar',
  'Plantilla para edificios residenciales estándar',
  'basica',
  '{"metodo_calculo": "porcentaje_copropiedad", "validar_limites": true, "considerar_exenciones": true}',
  '["CL", "AR", "PE", "CO", "MX"]'
),
(
  'Mixto Residencial-Comercial',
  'Plantilla para edificios con locales comerciales',
  'avanzada',
  '{"metodo_calculo": "mixto", "peso_copropiedad": 70, "peso_metros_cuadrados": 30, "considerar_comerciales": true, "incremento_comercial": 20}',
  '["CL", "AR"]'
),
(
  'Ley Chilena Compliant',
  'Configuración compatible con Ley 19.537 de Chile',
  'legal',
  '{"metodo_calculo": "metros_cuadrados", "superficie_considerar": "util", "max_variacion_porcentual": 30, "validacion_legal_activa": true}',
  '["CL"]'
);

-- ==================== ACTUALIZACIÓN DE VISTAS (COMPATIBLE MYSQL) ====================

-- Eliminar vistas existentes si existen
DROP VIEW IF EXISTS `vw_prorrateos_pendientes`;
DROP VIEW IF EXISTS `vw_estadisticas_prorrateo`;

-- Vista mejorada para estado de prorrateos
CREATE VIEW `vw_prorrateos_pendientes` AS
SELECT 
    gpl.id,
    gpl.estado,
    gc.nombre as gasto_nombre,
    gc.monto_total,
    gc.periodo,
    e.nombre as edificio_nombre,
    ps.nombre as estrategia_nombre,
    gpl.created_at,
    DATEDIFF(NOW(), gpl.created_at) as dias_pendiente,
    (SELECT COUNT(*) FROM prorrateo_historial_modificaciones phm WHERE phm.prorrateo_log_id = gpl.id) as modificaciones
FROM gasto_prorrateo_log gpl
JOIN gastos_comunes gc ON gpl.gasto_comun_id = gc.id
JOIN edificios e ON gc.edificio_id = e.id
JOIN prorrateo_strategies ps ON gpl.estrategia_id = ps.id
WHERE gpl.estado IN ('pendiente', 'pendiente_aprobacion')
ORDER BY gpl.created_at ASC;

-- Vista para estadísticas de prorrateo
CREATE VIEW `vw_estadisticas_prorrateo` AS
SELECT 
    e.id as edificio_id,
    e.nombre as edificio_nombre,
    COUNT(DISTINCT gc.id) as total_gastos,
    COUNT(DISTINCT CASE WHEN gpl.estado = 'pendiente' THEN gpl.id END) as prorrateos_pendientes,
    COUNT(DISTINCT CASE WHEN gpl.estado = 'pendiente_aprobacion' THEN gpl.id END) as prorrateos_aprobacion,
    COUNT(DISTINCT CASE WHEN gpl.estado = 'aprobado' THEN gpl.id END) as prorrateos_aprobados,
    AVG(CASE WHEN gpl.estado = 'aprobado' THEN gpl.tiempo_calculo_ms END) as tiempo_promedio_calculo_ms,
    MAX(gpl.created_at) as ultimo_prorrateo
FROM edificios e
LEFT JOIN gastos_comunes gc ON e.id = gc.edificio_id
LEFT JOIN gasto_prorrateo_log gpl ON gc.id = gpl.gasto_comun_id
GROUP BY e.id, e.nombre;

-- ==================== PROCEDIMIENTOS ALMACENADOS CORREGIDOS ====================

-- Eliminar procedimiento existente si existe
DROP PROCEDURE IF EXISTS `sp_calcular_porcentajes_edificio`;

DELIMITER //
CREATE PROCEDURE `sp_calcular_porcentajes_edificio`(
    IN p_edificio_id INT,
    IN p_usuario_id INT,
    IN p_metodo_calculo VARCHAR(50)
)
BEGIN
    DECLARE total_metros DECIMAL(15,2);
    DECLARE depto_count INT;
    DECLARE done INT DEFAULT FALSE;
    DECLARE depto_id INT;
    DECLARE depto_metros DECIMAL(10,2);
    DECLARE nuevo_porcentaje DECIMAL(5,2);
    DECLARE cursor_deptos CURSOR FOR 
        SELECT id, metros_cuadrados 
        FROM departamentos 
        WHERE edificio_id = p_edificio_id AND is_habitado = 1;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    -- Calcular total de metros según método
    IF p_metodo_calculo = 'metros_cuadrados' THEN
        SELECT COALESCE(SUM(metros_cuadrados), 0) INTO total_metros
        FROM departamentos 
        WHERE edificio_id = p_edificio_id AND is_habitado = 1;
    ELSE
        -- Método equitativo
        SELECT COUNT(*) INTO depto_count
        FROM departamentos 
        WHERE edificio_id = p_edificio_id AND is_habitado = 1;
        
        SET total_metros = depto_count;
    END IF;
    
    -- Actualizar porcentajes
    OPEN cursor_deptos;
    read_loop: LOOP
        FETCH cursor_deptos INTO depto_id, depto_metros;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Calcular nuevo porcentaje
        IF p_metodo_calculo = 'metros_cuadrados' THEN
            SET nuevo_porcentaje = (depto_metros / total_metros) * 100;
        ELSE
            -- Método equitativo
            SET nuevo_porcentaje = (1 / depto_count) * 100;
        END IF;
        
        -- Registrar en historial si hay cambio
        INSERT INTO departamento_porcentaje_historial 
        (departamento_id, porcentaje_anterior, porcentaje_nuevo, motivo_cambio, calculado_por_sistema, created_by)
        SELECT 
            d.id, 
            d.porcentaje_copropiedad,
            nuevo_porcentaje,
            'calculo_automatico',
            1,
            p_usuario_id
        FROM departamentos d
        WHERE d.id = depto_id 
        AND ABS(COALESCE(d.porcentaje_copropiedad, 0) - nuevo_porcentaje) > 0.01;
        
        -- Actualizar departamento
        UPDATE departamentos 
        SET 
            porcentaje_copropiedad = nuevo_porcentaje,
            porcentaje_calculado_auto = 1,
            ultimo_calculo_auto = NOW(),
            metodo_calculo_utilizado = p_metodo_calculo
        WHERE id = depto_id;
        
    END LOOP;
    CLOSE cursor_deptos;
    
    -- Retornar resumen
    SELECT 
        p_edificio_id as edificio_id,
        COUNT(*) as departamentos_actualizados,
        ROUND(SUM(ABS(porcentaje_copropiedad - COALESCE(
            (SELECT porcentaje_anterior 
             FROM departamento_porcentaje_historial 
             WHERE departamento_id = departamentos.id 
             ORDER BY created_at DESC LIMIT 1), 0)
        )), 2) as variacion_total
    FROM departamentos
    WHERE edificio_id = p_edificio_id;
    
END//
DELIMITER ;

-- ==================== TRIGGERS PARA INTEGRIDAD (CORREGIDOS) ====================

-- Eliminar triggers existentes si existen
DROP TRIGGER IF EXISTS `trg_departamentos_porcentaje_before_update`;
DROP TRIGGER IF EXISTS `trg_prorrateo_log_before_insert`;

-- Trigger para validar cambios en porcentajes de copropiedad
DELIMITER //
CREATE TRIGGER `trg_departamentos_porcentaje_before_update`
BEFORE UPDATE ON `departamentos`
FOR EACH ROW
BEGIN
    -- Validar que el porcentaje esté entre 0 y 100
    IF NEW.porcentaje_copropiedad < 0 OR NEW.porcentaje_copropiedad > 100 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El porcentaje de copropiedad debe estar entre 0 y 100';
    END IF;
    
    -- Si no es cálculo automático, marcar como manual
    IF NEW.porcentaje_copropiedad != OLD.porcentaje_copropiedad AND NEW.porcentaje_calculado_auto = 1 THEN
        SET NEW.porcentaje_calculado_auto = 0;
    END IF;
END//
DELIMITER ;

-- Trigger para generar hash de distribución al crear prorrateo (CORREGIDO)
DELIMITER //
CREATE TRIGGER `trg_prorrateo_log_before_insert`
BEFORE INSERT ON `gasto_prorrateo_log`
FOR EACH ROW
BEGIN
    DECLARE distribution_data LONGTEXT; -- CAMBIADO DE TEXT A LONGTEXT
    
    -- Generar hash de la distribución para integridad
    IF NEW.detalles_json IS NOT NULL THEN
        -- Usar JSON_UNQUOTE para convertir JSON a texto
        SET distribution_data = JSON_UNQUOTE(JSON_EXTRACT(NEW.detalles_json, '$.distribucion'));
        IF distribution_data IS NOT NULL AND distribution_data != '' THEN
            SET NEW.hash_distribucion = SHA2(distribution_data, 256);
        END IF;
    END IF;
    
    -- Establecer versión por defecto
    IF NEW.version_calculo IS NULL THEN
        SET NEW.version_calculo = '1.0';
    END IF;
END//
DELIMITER ;

-- ==================== MIGRACIÓN DE DATOS EXISTENTES ====================

-- Migrar configuración existente a nueva estructura JSON
UPDATE `prorrateo_edificio_config` 
SET `config_avanzada_json` = JSON_OBJECT(
    'calculo_automatico', 1,  -- VALOR POR DEFECTO
    'pais', 'CL',             -- VALOR POR DEFECTO
    'ley_copropiedad_vigente', 'Ley 19.537', -- VALOR POR DEFECTO
    'considerar_comerciales', 1,              -- VALOR POR DEFECTO
    'incremento_comercial', 20.00,            -- VALOR POR DEFECTO
    'factor_piso', 1.00,                      -- VALOR POR DEFECTO
    'factor_orientacion', 1.00,               -- VALOR POR DEFECTO
    'factores', JSON_ARRAY('piso', 'orientacion') -- VALOR POR DEFECTO
)
WHERE `config_avanzada_json` IS NULL 
   OR `config_avanzada_json` = 'null' 
   OR `config_avanzada_json` = '{}'
   OR `config_avanzada_json` = '';

-- ==================== VERIFICACIÓN FINAL ====================

-- Consulta para verificar la migración
SELECT 
    'Configuraciones migradas' as verificado,
    COUNT(*) as total,
    SUM(JSON_VALID(config_avanzada_json)) as json_validos
FROM prorrateo_edificio_config;

SELECT 
    'Estrategias del sistema' as verificado,
    COUNT(*) as total_system_strategies
FROM prorrateo_strategies 
WHERE is_system_strategy = 1;

SELECT 
    'Reglas legales insertadas' as verificado,
    COUNT(*) as total_legal_rules
FROM prorrateo_legal_rules;
