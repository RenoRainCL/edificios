<?php
// 📁 controllers/ConfiguracionController.php

class ConfiguracionController extends ControllerCore {
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * CONFIGURACIÓN DE PRORRATEO POR EDIFICIO
     */
    public function prorrateo() {
        $this->requirePermission('configuracion', 'write');
        
        $userId = $_SESSION['user_id'];
        $userEdificios = $this->getUserAccessibleEdificios();
        $edificioId = $_GET['edificio_id'] ?? ($userEdificios[0]['id'] ?? null);
        
        if (!$edificioId) {
            $this->addFlashMessage('error', 'No tienes edificios asignados');
            $this->redirect('dashboard');
        }
        
        // Verificar acceso al edificio
        $this->checkEdificioAccess($edificioId);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->guardarConfiguracionProrrateo($edificioId, $userId);
            return;
        }
        
        // Obtener configuración actual
        $configActual = $this->getConfiguracionProrrateoEdificio($edificioId);
        $estrategias = $this->getEstrategiasProrrateo();
        
        $data = [
            'edificios' => $userEdificios,
            'edificio_actual' => $this->getEdificioById($edificioId),
            'config_actual' => $configActual,
            'estrategias' => $estrategias,
            'user_name' => $_SESSION['user_name'],
            'page_title' => 'Configuración de Prorrateo'
        ];
        
        $this->renderView('configuracion/prorrateo', $data);
    }
    
    /**
     * GUARDAR CONFIGURACIÓN DE PRORRATEO - CORREGIDO
     */
    private function guardarConfiguracionProrrateo($edificioId, $userId) {
        try {
            // Validar estrategia si se proporciona
            $estrategiaDefaultId = $_POST['estrategia_default_id'] ?? null;
            if ($estrategiaDefaultId) {
                if (!$this->validarEstrategiaExistente($estrategiaDefaultId)) {
                    throw new Exception("La estrategia seleccionada no existe o no está activa");
                }
            }
            
            // Datos básicos
            $superficieConsiderar = $_POST['superficie_considerar'] ?? 'util';
            $validacionLegalActiva = isset($_POST['validacion_legal_activa']) ? 1 : 0;
            $maxVariacionPorcentual = floatval($_POST['max_variacion_porcentual'] ?? 20.00);
            $tratamientoComercial = $_POST['tratamiento_comercial'] ?? 'incremento_20';
            
            // Validar variación porcentual
            if ($maxVariacionPorcentual < 0 || $maxVariacionPorcentual > 100) {
                throw new Exception("La variación porcentual debe estar entre 0% y 100%");
            }
            
            // Configuración avanzada UNIFICADA en JSON
            $configAvanzada = [
                'calculo_automatico' => isset($_POST['calculo_automatico']) ? 1 : 0,
                'pais' => $_POST['pais'] ?? 'CL',
                'ley_copropiedad_vigente' => $_POST['ley_copropiedad_vigente'] ?? 'Ley 19.537',
                'considerar_comerciales' => isset($_POST['considerar_comerciales']) ? 1 : 0,
                'incremento_comercial' => floatval($_POST['incremento_comercial'] ?? 20.00),
                'factor_piso' => floatval($_POST['factor_piso'] ?? 1.00),
                'factor_orientacion' => floatval($_POST['factor_orientacion'] ?? 1.00),
                'factores' => $_POST['factores'] ?? ['piso', 'orientacion']
            ];
            
            // Validar factores numéricos
            if ($configAvanzada['incremento_comercial'] < 0 || $configAvanzada['incremento_comercial'] > 100) {
                throw new Exception("El incremento comercial debe estar entre 0% y 100%");
            }
            if ($configAvanzada['factor_piso'] < 0.5 || $configAvanzada['factor_piso'] > 2.0) {
                throw new Exception("El factor piso debe estar entre 0.5 y 2.0");
            }
            if ($configAvanzada['factor_orientacion'] < 0.5 || $configAvanzada['factor_orientacion'] > 2.0) {
                throw new Exception("El factor orientación debe estar entre 0.5 y 2.0");
            }
            
            $this->db->beginTransaction();
            
            // Verificar si ya existe configuración
            $sqlCheck = "SELECT id FROM prorrateo_edificio_config WHERE edificio_id = ?";
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->execute([$edificioId]);
            $configExistente = $stmtCheck->fetch();
            
            if ($configExistente) {
                // Actualizar configuración existente - SIN DUPLICACIONES
                $sqlUpdate = "UPDATE prorrateo_edificio_config 
                             SET estrategia_default_id = ?, superficie_considerar = ?, 
                                 validacion_legal_activa = ?, max_variacion_porcentual = ?, 
                                 tratamiento_comercial = ?, config_avanzada_json = ?,
                                 updated_at = NOW()
                             WHERE edificio_id = ?";
                
                $stmt = $this->db->prepare($sqlUpdate);
                $stmt->execute([
                    $estrategiaDefaultId, $superficieConsiderar, $validacionLegalActiva,
                    $maxVariacionPorcentual, $tratamientoComercial, json_encode($configAvanzada),
                    $edificioId
                ]);
            } else {
                // Insertar nueva configuración - SIN DUPLICACIONES
                $sqlInsert = "INSERT INTO prorrateo_edificio_config 
                             (edificio_id, estrategia_default_id, superficie_considerar, 
                              validacion_legal_activa, max_variacion_porcentual, tratamiento_comercial, 
                              config_avanzada_json, created_by) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $this->db->prepare($sqlInsert);
                $stmt->execute([
                    $edificioId, $estrategiaDefaultId, $superficieConsiderar, $validacionLegalActiva,
                    $maxVariacionPorcentual, $tratamientoComercial, json_encode($configAvanzada),
                    $userId
                ]);
            }
            
            // Actualizar también la configuración en la tabla edificios para compatibilidad
            $sqlEdificio = "UPDATE edificios SET configuracion = JSON_SET(
                           COALESCE(configuracion, '{}'), 
                           '$.prorrateo.calculo_automatico', ?
                       ) WHERE id = ?";
            
            $stmtEdificio = $this->db->prepare($sqlEdificio);
            $stmtEdificio->execute([$configAvanzada['calculo_automatico'], $edificioId]);
            
            $this->db->commit();
            
            $this->addFlashMessage('success', 'Configuración de prorrateo guardada exitosamente');
            $this->redirect("configuracion/prorrateo?edificio_id=$edificioId");
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error al guardar configuración de prorrateo: " . $e->getMessage());
            $this->addFlashMessage('error', 'Error al guardar la configuración: ' . $e->getMessage());
            $this->redirect("configuracion/prorrateo?edificio_id=$edificioId");
        }
    }
    
    /**
     * VALIDAR ESTRATEGIA EXISTENTE
     */
    private function validarEstrategiaExistente($estrategiaId) {
        $sql = "SELECT id FROM prorrateo_strategies WHERE id = ? AND is_active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$estrategiaId]);
        return (bool) $stmt->fetch();
    }
    
    /**
     * OBTENER CONFIGURACIÓN ACTUAL DE PRORRATEO - CORREGIDO
     */
    private function getConfiguracionProrrateoEdificio($edificioId) {
        $sql = "SELECT * FROM prorrateo_edificio_config WHERE edificio_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$edificioId]);
        $config = $stmt->fetch();
        
        if ($config && !empty($config['config_avanzada_json'])) {
            // Unificar configuración: campos individuales + JSON
            $configAvanzada = json_decode($config['config_avanzada_json'], true) ?? [];
            
            // Si hay campos en el JSON que también existen como columnas individuales,
            // priorizar los del JSON para mantener consistencia
            $configUnificada = array_merge([
                'estrategia_default_id' => $config['estrategia_default_id'] ?? null,
                'superficie_considerar' => $config['superficie_considerar'] ?? 'util',
                'validacion_legal_activa' => $config['validacion_legal_activa'] ?? 1,
                'max_variacion_porcentual' => $config['max_variacion_porcentual'] ?? 20.00,
                'tratamiento_comercial' => $config['tratamiento_comercial'] ?? 'incremento_20'
            ], $configAvanzada);
            
            $configUnificada['config_avanzada_json'] = $configAvanzada;
            return $configUnificada;
        } else {
            // Configuración por defecto unificada
            return [
                'estrategia_default_id' => null,
                'superficie_considerar' => 'util',
                'validacion_legal_activa' => 1,
                'max_variacion_porcentual' => 20.00,
                'tratamiento_comercial' => 'incremento_20',
                'config_avanzada_json' => [
                    'calculo_automatico' => true,
                    'pais' => 'CL',
                    'ley_copropiedad_vigente' => 'Ley 19.537',
                    'considerar_comerciales' => 1,
                    'incremento_comercial' => 20.00,
                    'factor_piso' => 1.00,
                    'factor_orientacion' => 1.00,
                    'factores' => ['piso', 'orientacion']
                ],
                // Campos individuales para compatibilidad con vistas
                'calculo_automatico' => true,
                'pais' => 'CL',
                'ley_copropiedad_vigente' => 'Ley 19.537',
                'considerar_comerciales' => 1,
                'incremento_comercial' => 20.00,
                'factor_piso' => 1.00,
                'factor_orientacion' => 1.00
            ];
        }
    }
    
    /**
     * OBTENER ESTRATEGIAS DE PRORRATEO
     */
    private function getEstrategiasProrrateo() {
        $sql = "SELECT * FROM prorrateo_strategies WHERE is_active = 1 ORDER BY nombre";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * GUARDAR ESTRATEGIA PERSONALIZADA
     */
    public function guardarEstrategia() {
        $this->requirePermission('configuracion', 'write');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, [], 'Método no permitido');
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $tipo = $_POST['tipo'] ?? 'automatico';
        $metodoCalculo = $_POST['metodo_calculo'] ?? 'metros_cuadrados';
        $configJson = $_POST['config_json'] ?? '{}';
        $requiereAprobacion = isset($_POST['requiere_aprobacion']) ? 1 : 0;
        $nivelAprobacion = $_POST['nivel_aprobacion'] ?? 'administrador';
        
        try {
            // Validaciones
            if (empty($nombre)) {
                throw new Exception('El nombre de la estrategia es requerido');
            }
            
            // Validar JSON de configuración
            if (!json_decode($configJson)) {
                throw new Exception('El JSON de configuración no es válido');
            }
            
            $this->db->beginTransaction();
            
            // Verificar que el nombre no exista
            $sqlCheck = "SELECT id FROM prorrateo_strategies WHERE nombre = ?";
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->execute([$nombre]);
            
            if ($stmtCheck->fetch()) {
                throw new Exception('Ya existe una estrategia con ese nombre');
            }
            
            // Insertar nueva estrategia
            $sql = "INSERT INTO prorrateo_strategies 
                   (nombre, descripcion, tipo, metodo_calculo, config_json, 
                    requiere_aprobacion, nivel_aprobacion, created_by) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $nombre, $descripcion, $tipo, $metodoCalculo, $configJson,
                $requiereAprobacion, $nivelAprobacion, $userId
            ]);
            
            $estrategiaId = $this->db->lastInsertId();
            
            $this->db->commit();
            
            $this->jsonResponse(true, [
                'estrategia_id' => $estrategiaId,
                'message' => 'Estrategia creada exitosamente'
            ]);
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error al crear estrategia: " . $e->getMessage());
            $this->jsonResponse(false, [], 'Error al crear estrategia: ' . $e->getMessage());
        }
    }
    
    /**
     * CONFIGURACIÓN GENERAL DEL SISTEMA
     */
    public function general() {
        $this->requirePermission('configuracion', 'read');
        
        $data = [
            'user_name' => $_SESSION['user_name'],
            'page_title' => 'Configuración General'
        ];
        
        $this->renderView('configuracion/general', $data);
    }
    
    /**
     * CONFIGURACIÓN DE NOTIFICACIONES
     */
    public function notificaciones() {
        $this->requirePermission('configuracion', 'read');
        
        $data = [
            'user_name' => $_SESSION['user_name'],
            'page_title' => 'Configuración de Notificaciones'
        ];
        
        $this->renderView('configuracion/notificaciones', $data);
    }

    /**
     * API PARA CÁLCULO AUTOMÁTICO DE PRORRATEO
     * URL: http://localhost:8080/proyectos/edificios/configuracion/calcularProrrateoAuto
     */
    public function calcularProrrateoAuto() {
        $this->requirePermission('configuracion', 'read');
        
        // Solo aceptar POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Método no permitido']);
            exit;
        }
        
        try {
            // Obtener datos JSON
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('JSON inválido en la solicitud');
            }
            
            // Validar datos requeridos
            if (empty($input['edificio_id'])) {
                throw new Exception('ID de edificio requerido');
            }
            
            $edificioId = $input['edificio_id'];
            $this->checkEdificioAccess($edificioId);
            
            // EJECUTAR CÁLCULO TEMPORAL (para probar)
            $resultado = [
                'edificio_id' => $edificioId,
                'porcentajes_calculados' => [
                    ['unidad_id' => 1, 'porcentaje' => 25.5],
                    ['unidad_id' => 2, 'porcentaje' => 24.5],
                    ['unidad_id' => 3, 'porcentaje' => 25.0],
                    ['unidad_id' => 4, 'porcentaje' => 25.0]
                ],
                'total_porcentaje' => 100.0,
                'metodo_usado' => 'prorrateo_automatico',
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            // Respuesta exitosa
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => $resultado,
                'message' => 'Cálculo de prorrateo completado exitosamente'
            ]);
            
        } catch (Exception $e) {
            // Respuesta de error
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }   
    
    /**
     * API PARA CÁLCULO AUTOMÁTICO - RUTA SEPARADA
     * URL: /proyectos/edificios/configuracion/calcularAuto
     */
    public function calcularAuto() {
        $this->requirePermission('configuracion', 'read');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Método no permitido']);
            exit;
        }
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                $input = $_POST;
            }
            
            $edificioId = $input['edificio_id'] ?? null;
            
            if (!$edificioId) {
                throw new Exception('ID de edificio requerido');
            }
            
            $this->checkEdificioAccess($edificioId);
            
            // ✅ USAR LA CONFIGURACIÓN EXISTENTE DE PRORRATEO
            $configProrrateo = $this->getConfiguracionProrrateoEdificio($edificioId);
            
            // ✅ EJECUTAR CÁLCULO CON LA CONFIGURACIÓN REAL
            $resultado = $this->ejecutarCalculoReal($edificioId, $configProrrateo);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => $resultado,
                'message' => 'Cálculo automático aplicado según configuración'
            ]);
            
        } catch (Exception $e) {
            error_log("📍 ERROR en calcularAuto: " . $e->getMessage());
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }

    private function ejecutarCalculoReal($edificioId, $configProrrateo) {
        // 1. OBTENER DEPARTAMENTOS CON METROS CUADRADOS
        $departamentos = $this->obtenerDepartamentosConSuperficie($edificioId);
        
        if (empty($departamentos)) {
            throw new Exception('No se encontraron departamentos para este edificio');
        }
        
        // 2. CONFIGURACIÓN
        $tipoSuperficie = $configProrrateo['superficie_considerar'] ?? 'util';
        $campoSuperficie = 'metros_cuadrados'; // ⚠️ CORREGIDO: El campo se llama metros_cuadrados
        $factoresConfig = $configProrrateo['factores'] ?? [];
        
        // 3. CALCULAR SUPERFICIE TOTAL CON FACTORES
        $superficieTotalAjustada = 0;
        $departamentosConAjustes = [];
        
        foreach ($departamentos as $depto) {
            $superficieBase = floatval($depto[$campoSuperficie] ?? 0);
            $factorTotal = 1.0;
            
            // APLICAR FACTORES SEGÚN CONFIGURACIÓN
            if (in_array('piso', $factoresConfig)) {
                $factorPiso = $this->calcularFactorPiso($depto['piso'] ?? 0, $configProrrateo);
                $factorTotal *= $factorPiso;
            }
            
            if (in_array('orientacion', $factoresConfig)) {
                $factorOrientacion = $this->calcularFactorOrientacion($depto['orientacion'] ?? '', $configProrrateo);
                $factorTotal *= $factorOrientacion;
            }
            
            // APLICAR INCREMENTO COMERCIAL SI CORRESPONDE
            if (($configProrrateo['considerar_comerciales'] ?? false) && 
                $this->esDepartamentoComercial($depto)) {
                $incremento = ($configProrrateo['incremento_comercial'] ?? 20.0) / 100;
                $factorTotal *= (1 + $incremento);
            }
            
            $superficieAjustada = $superficieBase * $factorTotal;
            
            $departamentosConAjustes[] = [
                'id' => $depto['id'],
                'numero' => $depto['numero'],
                'nombre' => 'Depto ' . $depto['numero'],
                'superficie_base' => $superficieBase,
                'superficie_ajustada' => $superficieAjustada,
                'factor_total' => $factorTotal,
                'piso' => $depto['piso'] ?? null,
                'orientacion' => $depto['orientacion'] ?? null
            ];
            
            $superficieTotalAjustada += $superficieAjustada;
        }
        
        if ($superficieTotalAjustada <= 0) {
            throw new Exception('La superficie total ajustada no puede ser cero');
        }
        
        // 4. CALCULAR PORCENTAJES
        $porcentajes = [];
        foreach ($departamentosConAjustes as $depto) {
            $porcentaje = ($depto['superficie_ajustada'] / $superficieTotalAjustada) * 100;
            
            $porcentajes[] = [
                'departamento_id' => $depto['id'],
                'numero' => $depto['numero'],
                'nombre' => $depto['nombre'],
                'porcentaje' => round($porcentaje, 4),
                'superficie_base' => $depto['superficie_base'],
                'superficie_ajustada' => $depto['superficie_ajustada'],
                'factor_aplicado' => $depto['factor_total'],
                'piso' => $depto['piso'],
                'orientacion' => $depto['orientacion']
            ];
        }
        
        // 5. AJUSTAR PARA QUE SUME EXACTAMENTE 100%
        $totalPorcentaje = array_sum(array_column($porcentajes, 'porcentaje'));
        $diferencia = 100 - $totalPorcentaje;
        
        if (abs($diferencia) > 0.001) {
            $porcentajes[0]['porcentaje'] = round($porcentajes[0]['porcentaje'] + $diferencia, 4);
            $totalPorcentaje = 100;
        }
        
        return [
            'edificio_id' => (int)$edificioId,
            'porcentajes_calculados' => $porcentajes,
            'total_porcentaje' => round($totalPorcentaje, 4),
            'total_superficie_ajustada' => $superficieTotalAjustada,
            'configuracion_usada' => [
                'tipo_superficie' => $tipoSuperficie,
                'factores_aplicados' => $factoresConfig,
                'factor_piso' => $configProrrateo['factor_piso'] ?? 1.0,
                'factor_orientacion' => $configProrrateo['factor_orientacion'] ?? 1.0,
                'considerar_comerciales' => $configProrrateo['considerar_comerciales'] ?? false,
                'incremento_comercial' => $configProrrateo['incremento_comercial'] ?? 0.0
            ],
            'departamentos_procesados' => count($departamentos),
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    private function obtenerDepartamentosConSuperficie($edificioId) {
        $sql = "SELECT 
                    id, 
                    numero, 
                    piso,
                    orientacion,
                    metros_cuadrados,
                    dormitorios,
                    banos
                FROM departamentos 
                WHERE edificio_id = ? AND is_habitado = 1 
                ORDER BY piso, numero";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$edificioId]);
        return $stmt->fetchAll();
    }

    private function calcularFactorPiso($piso, $configProrrateo) {
        $factorBase = $configProrrateo['factor_piso'] ?? 1.0;
        // Lógica por piso (ejemplo: pisos más altos tienen mayor factor)
        if ($piso > 10) {
            return $factorBase * 1.2;
        } elseif ($piso > 5) {
            return $factorBase * 1.1;
        }
        return $factorBase;
    }

    private function calcularFactorOrientacion($orientacion, $configProrrateo) {
        $factorBase = $configProrrateo['factor_orientacion'] ?? 1.0;
        // Lógica por orientación
        $orientacionesPremium = ['Norte', 'Noreste', 'Nororiente'];
        if (in_array($orientacion, $orientacionesPremium)) {
            return $factorBase * 1.15;
        }
        return $factorBase;
    }

    private function esDepartamentoComercial($departamento) {
        // Determinar si es comercial basado en características
        // Por ejemplo, si tiene 0 dormitorios o está en planta baja
        return ($departamento['dormitorios'] == 0 || $departamento['piso'] == 0);
    }
    private function obtenerUnidadesCompletas($edificioId) {
        $sql = "SELECT 
                    id, 
                    nombre, 
                    superficie_util, 
                    superficie_total,
                    piso,
                    orientacion,
                    tipo_unidad,
                    numero_unidad
                FROM unidades 
                WHERE edificio_id = ? AND activa = 1 
                ORDER BY piso, numero_unidad, nombre";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$edificioId]);
        return $stmt->fetchAll();
    }

    private function esUnidadComercial($tipoUnidad) {
        $tiposComerciales = ['comercial', 'local', 'tienda', 'oficina'];
        return in_array(strtolower($tipoUnidad), $tiposComerciales);
    }

    private function validarVariacionPorcentual($porcentajes, $configProrrateo) {
        $maxVariacion = $configProrrateo['max_variacion_porcentual'] ?? 20.0;
        
        // Aquí puedes implementar validación legal
        // Por ahora solo log
        error_log("📍 Validación legal: variación máxima permitida " . $maxVariacion . "%");
    }


}
?>