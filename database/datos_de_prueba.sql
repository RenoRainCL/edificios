-- ==================== DEPARTAMENTOS PARA EDIFICIO 1 ====================
INSERT INTO departamentos (edificio_id, numero, piso, metros_cuadrados, orientacion, dormitorios, banos, estacionamientos, bodegas, propietario_rut, propietario_nombre, propietario_email, propietario_telefono, porcentaje_copropiedad, is_habitado) VALUES
-- Piso 2
(1, '202', 2, 65.00, 'Sur', 2, 2, 1, 0, '16.543.210-1', 'Carlos López', 'clopez@depto.cl', '+56992345678', 1.15, 1),
(1, '203', 2, 75.00, 'Norte', 3, 2, 1, 1, '12.345.678-9', 'Ana Martínez', 'amartinez@depto.cl', '+56993456789', 1.35, 1),

-- Piso 3
(1, '301', 3, 80.00, 'Norte', 3, 2, 1, 1, '17.890.123-4', 'Roberto Silva', 'rsilva@depto.cl', '+56994567890', 1.45, 1),
(1, '302', 3, 70.00, 'Sur', 3, 2, 1, 0, '15.678.901-2', 'María González', 'mgonzalez@depto.cl', '+56995678901', 1.25, 1),
(1, '303', 3, 85.00, 'Norte', 4, 2, 2, 1, '14.567.890-1', 'Pedro Rodríguez', 'prodriguez@depto.cl', '+56996789012', 1.55, 1),

-- Piso 4
(1, '401', 4, 90.00, 'Norte', 4, 3, 2, 1, '18.901.234-5', 'Laura Fernández', 'lfernandez@depto.cl', '+56997890123', 1.65, 1),
(1, '402', 4, 75.00, 'Sur', 3, 2, 1, 1, '19.012.345-6', 'Diego Morales', 'dmorales@depto.cl', '+56998901234', 1.35, 1),
(1, '403', 4, 95.00, 'Norte', 4, 3, 2, 1, '20.123.456-7', 'Carmen Vargas', 'cvargas@depto.cl', '+56999012345', 1.75, 1),

-- Local Comercial (exento parcialmente)
(1, 'L01', 1, 120.00, 'Norte', 0, 1, 0, 0, '21.234.567-8', 'Comercial Ltda.', 'comercial@empresa.cl', '+56999123456', 2.50, 1);
-- ==================== DEPARTAMENTOS PARA EDIFICIO 2 ====================
INSERT INTO departamentos (edificio_id, numero, piso, metros_cuadrados, orientacion, dormitorios, banos, estacionamientos, bodegas, propietario_rut, propietario_nombre, propietario_email, propietario_telefono, porcentaje_copropiedad, is_habitado) VALUES
-- Piso 1
(2, '101', 1, 60.00, 'Norte', 2, 1, 1, 0, '22.345.678-9', 'Juan Pérez', 'jperez@depto.cl', '+56999234567', 1.10, 1),
(2, '102', 1, 55.00, 'Sur', 1, 1, 0, 0, '23.456.789-0', 'Sofia Rojas', 'srojas@depto.cl', '+56999345678', 1.00, 1),

-- Piso 2  
(2, '201', 2, 65.00, 'Norte', 2, 1, 1, 0, '24.567.890-1', 'Miguel Torres', 'mtorres@depto.cl', '+56999456789', 1.20, 1),
(2, '202', 2, 70.00, 'Sur', 2, 2, 1, 0, '25.678.901-2', 'Elena Castro', 'ecastro@depto.cl', '+56999567890', 1.30, 1),

-- Piso 3
(2, '301', 3, 75.00, 'Norte', 3, 2, 1, 1, '26.789.012-3', 'Andrés Navarro', 'anavarro@depto.cl', '+56999678901', 1.40, 1),
(2, '302', 3, 80.00, 'Sur', 3, 2, 1, 1, '27.890.123-4', 'Patricia Soto', 'psoto@depto.cl', '+56999789012', 1.50, 1),

-- Local Comercial
(2, 'LC1', 1, 100.00, 'Norte', 0, 1, 0, 0, '28.901.234-5', 'Restaurant S.A.', 'info@restaurant.cl', '+56999890123', 2.00, 1),
(2, 'LC2', 1, 85.00, 'Sur', 0, 1, 0, 0, '29.012.345-6', 'Farmacia Ltda.', 'contacto@farmacia.cl', '+56999901234', 1.70, 1);
-- ==================== GASTOS COMUNES - EDIFICIO 1 ====================
INSERT INTO gastos_comunes (edificio_id, nombre, descripcion, monto_total, periodo, fecha_vencimiento, estado, created_by) VALUES
(1, 'Gasto Común Octubre 2024', 'Gastos comunes del mes de octubre incluyendo mantenimiento ascensores y áreas comunes', 1850000.00, '2024-10-01', '2024-11-10', 'pendiente', 1),
(1, 'Gasto Común Septiembre 2024', 'Gastos comunes mes de septiembre - gastos extraordinarios por reparación tuberías', 1650000.00, '2024-09-01', '2024-10-10', 'emitido', 1),
(1, 'Gasto Común Agosto 2024', 'Gastos regulares del mes de agosto', 1520000.00, '2024-08-01', '2024-09-10', 'cerrado', 1);

-- ==================== GASTOS COMUNES - EDIFICIO 2 ====================
INSERT INTO gastos_comunes (edificio_id, nombre, descripcion, monto_total, periodo, fecha_vencimiento, estado, created_by) VALUES
(2, 'Gasto Común Octubre 2024', 'Gastos comunes mensuales - incluye seguridad 24/7', 2200000.00, '2024-10-01', '2024-11-15', 'pendiente', 1),
(2, 'Gasto Común Septiembre 2024', 'Gastos regulares del mes', 1950000.00, '2024-09-01', '2024-10-15', 'emitido', 1);
-- ==================== CONFIGURACIÓN PRORRATEO EDIFICIOS ====================
INSERT INTO prorrateo_edificio_config (edificio_id, estrategia_default_id, superficie_considerar, validacion_legal_activa, max_variacion_porcentual, tratamiento_comercial, config_avanzada_json, created_by) VALUES
(1, 1, 'util', 1, 20.00, 'incremento_20', '{"calculo_automatico": true, "pais": "CL", "ley_copropiedad_vigente": "Ley 19.537", "considerar_comerciales": 1, "incremento_comercial": 20.00, "factor_piso": 1.05, "factor_orientacion": 1.02, "factores": ["piso", "orientacion"]}', 1),
(2, 2, 'util', 1, 25.00, 'incremento_30', '{"calculo_automatico": true, "pais": "CL", "ley_copropiedad_vigente": "Ley 19.537", "considerar_comerciales": 1, "incremento_comercial": 30.00, "factor_piso": 1.03, "factor_orientacion": 1.01, "factores": ["piso"]}', 1);
-- ==================== EXENCIONES PARA PRUEBAS ====================
INSERT INTO departamento_exenciones (departamento_id, tipo_exencion, porcentaje_exencion, motivo, periodo_inicio, periodo_fin, documento_respaldo, created_by) VALUES
-- Exención total temporal (departamento en remodelación)
(3, 'temporal', 100.00, 'Remodelación autorizada por comité - Sin ocupación temporal', '2024-10-01', '2024-12-31', 'resolucion_remodelacion_2024.pdf', 1),

-- Exención parcial permanente (adulto mayor)
(5, 'parcial', 50.00, 'Propietario adulto mayor con resolución de beneficio', '2024-01-01', NULL, 'certificado_adulto_mayor.pdf', 1),

-- Exención comercial (tratamiento diferenciado)
(10, 'comercial', 0.00, 'Local comercial - tratamiento diferenciado según reglamento', '2024-01-01', NULL, 'reglamento_copropiedad.pdf', 1);
-- ==================== ESTRATEGIAS DE PRORRATEO ====================
INSERT INTO prorrateo_strategies (nombre, descripcion, tipo, metodo_calculo, config_json, is_active, is_system_strategy, version, compatible_paises, created_by) VALUES
('Copropiedad Estándar', 'Distribución según porcentaje de copropiedad registrado', 'automatico', 'porcentaje_copropiedad', '{"validar_limites": true, "considerar_exenciones": true, "redistribuir_exentos": true}', 1, 1, '1.0', '["CL", "AR", "PE"]', 1),
('Metros Cuadrados', 'Distribución proporcional a metros cuadrados útiles', 'automatico', 'metros_cuadrados', '{"validar_limites": true, "tipo_superficie": "util", "ajuste_comercial": true}', 1, 1, '1.0', '["CL", "AR", "PE"]', 1),
('Equitativo Simple', 'Distribución equitativa entre todos los departamentos', 'automatico', 'equitativo', '{"excluir_comerciales": false, "considerar_habitados": true}', 1, 1, '1.0', '["CL", "AR", "PE", "CO", "MX"]', 1),
('Mixto Copropiedad-Metros', 'Distribución 50% copropiedad - 50% metros cuadrados', 'automatico', 'mixto', '{"peso_copropiedad": 50, "peso_metros_cuadrados": 50, "validar_limites": true}', 1, 0, '1.0', '["CL"]', 1);

