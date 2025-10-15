<?php
// 📁 controllers/DepartamentosController.php

class DepartamentosController extends ControllerCore {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function index() {
        $userId = $_SESSION['user_id'];
        $userEdificios = $this->getUserEdificios($userId);
        
        // Obtener departamentos para los edificios del usuario
        $departamentos = $this->getDepartamentos($userEdificios);
        
        $data = [
            'departamentos' => $departamentos,
            'edificios' => $userEdificios,
            'user_name' => $_SESSION['user_name'],
            'page_title' => 'Gestión de Departamentos'
        ];
        
        $this->renderView('departamentos/index', $data);
    }
    
    public function crear() {
        $userId = $_SESSION['user_id'];
        $userEdificios = $this->getUserEdificios($userId);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->procesarCrearDepartamento($userId);
            return;
        }
        
        $data = [
            'edificios' => $userEdificios,
            'user_name' => $_SESSION['user_name'],
            'page_title' => 'Crear Departamento'
        ];
        
        $this->renderView('departamentos/crear', $data);
    }
    
    public function editar($deptoId) {
        $userId = $_SESSION['user_id'];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->procesarEditarDepartamento($deptoId, $userId);
            return;
        }
        
        // Obtener datos del departamento
        $departamento = $this->getDepartamentoById($deptoId, $userId);
        
        if (!$departamento) {
            $this->addFlashMessage('error', 'Departamento no encontrado');
            $this->redirect('departamentos');
        }
        
        $userEdificios = $this->getUserEdificios($userId);
        
        // Obtener información de cálculo automático (con manejo de error)
        $infoCalculo = $this->getInfoCalculoPorcentaje($deptoId);
        
        $data = [
            'departamento' => $departamento,
            'edificios' => $userEdificios,
            'info_calculo' => $infoCalculo,
            'user_name' => $_SESSION['user_name'],
            'page_title' => 'Editar Departamento'
        ];
        
        $this->renderView('departamentos/editar', $data);
    }
    
    public function ver($deptoId) {
        $userId = $_SESSION['user_id'];
        
        $departamento = $this->getDepartamentoById($deptoId, $userId);
        
        if (!$departamento) {
            $this->addFlashMessage('error', 'Departamento no encontrado');
            $this->redirect('departamentos');
        }
        
        // Obtener historial de prorrateo
        $historialProrrateo = $this->getHistorialProrrateoDepartamento($deptoId);
        
        // Obtener gastos y pagos del departamento
        $gastosDepartamento = $this->getGastosDepartamento($deptoId);
        
        $data = [
            'departamento' => $departamento,
            'historial_prorrateo' => $historialProrrateo,
            'gastos_departamento' => $gastosDepartamento,
            'user_name' => $_SESSION['user_name'],
            'page_title' => 'Detalle de Departamento'
        ];
        
        $this->renderView('departamentos/ver', $data);
    }
    
    public function desactivar($deptoId) {
        $userId = $_SESSION['user_id'];
        
        try {
            $departamento = $this->getDepartamentoById($deptoId, $userId);
            
            if (!$departamento) {
                $this->addFlashMessage('error', 'Departamento no encontrado');
                $this->redirect('departamentos');
            }
            
            $sql = "UPDATE departamentos SET is_habitado = 0 WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$deptoId]);
            
            $this->addFlashMessage('success', 'Departamento marcado como no habitado');
            
        } catch (Exception $e) {
            error_log("Error al desactivar departamento: " . $e->getMessage());
            $this->addFlashMessage('error', 'Error al desactivar el departamento');
        }
        
        $this->redirect('departamentos');
    }
    
    /**
     * PROCESAR CREACIÓN DE DEPARTAMENTO
     */
    private function procesarCrearDepartamento($userId) {
        try {
            $edificioId = $_POST['edificio_id'] ?? null;
            $numero = trim($_POST['numero'] ?? '');
            $piso = $_POST['piso'] ?? null;
            $metrosCuadrados = $_POST['metros_cuadrados'] ?? null;
            $orientacion = $_POST['orientacion'] ?? null;
            $dormitorios = $_POST['dormitorios'] ?? 1;
            $banos = $_POST['banos'] ?? 1;
            $estacionamientos = $_POST['estacionamientos'] ?? 0;
            $bodegas = $_POST['bodegas'] ?? 0;
            $propietarioRut = $_POST['propietario_rut'] ?? null;
            $propietarioNombre = trim($_POST['propietario_nombre'] ?? '');
            $propietarioEmail = $_POST['propietario_email'] ?? null;
            $propietarioTelefono = $_POST['propietario_telefono'] ?? null;
            $porcentajeCopropiedad = $_POST['porcentaje_copropiedad'] ?? 0;
            $observaciones = trim($_POST['observaciones'] ?? '');
            
            // Validaciones
            $errors = [];
            if (empty($edificioId)) {
                $errors[] = 'Debe seleccionar un edificio';
            }
            if (empty($numero)) {
                $errors[] = 'El número de departamento es requerido';
            }
            if (empty($propietarioNombre)) {
                $errors[] = 'El nombre del propietario es requerido';
            }
            if ($propietarioRut && !$this->security->validateRUT($propietarioRut)) {
                $errors[] = 'El RUT del propietario no es válido';
            }
            
            // Verificar que el usuario tenga acceso al edificio
            $userEdificios = $this->getUserEdificios($userId);
            $edificioIds = array_column($userEdificios, 'id');
            
            if (!in_array($edificioId, $edificioIds)) {
                $errors[] = 'No tiene permisos para gestionar este edificio';
            }
            
            // Verificar que el número de departamento no exista en el edificio
            $sqlCheck = "SELECT id FROM departamentos WHERE edificio_id = ? AND numero = ?";
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->execute([$edificioId, $numero]);
            
            if ($stmtCheck->fetch()) {
                $errors[] = 'Ya existe un departamento con ese número en el edificio';
            }
            
            if (!empty($errors)) {
                $_SESSION['form_errors'] = $errors;
                $_SESSION['form_data'] = $_POST;
                $this->redirect('departamentos/crear');
                return;
            }
            
            $this->db->beginTransaction();
            
            // Insertar departamento
            $sql = "INSERT INTO departamentos 
                    (edificio_id, numero, piso, metros_cuadrados, orientacion, dormitorios, banos, 
                     estacionamientos, bodegas, propietario_rut, propietario_nombre, propietario_email, 
                     propietario_telefono, porcentaje_copropiedad, observaciones, is_habitado) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $edificioId, $numero, $piso, $metrosCuadrados, $orientacion, $dormitorios, $banos,
                $estacionamientos, $bodegas, $propietarioRut, $propietarioNombre, $propietarioEmail,
                $propietarioTelefono, $porcentajeCopropiedad, $observaciones
            ]);
            
            $deptoId = $this->db->lastInsertId();
            
            // CALCULAR PORCENTAJE AUTOMÁTICO SI ESTÁ ACTIVADO Y EL PORCENTAJE ES 0
            if ($porcentajeCopropiedad == 0) {
                $this->calcularPorcentajeAutomatico($deptoId, $userId);
            }
            
            $this->db->commit();
            
            $this->addFlashMessage('success', 'Departamento creado exitosamente');
            $this->redirect('departamentos');
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error al crear departamento: " . $e->getMessage());
            $this->addFlashMessage('error', 'Error al crear el departamento: ' . $e->getMessage());
            $this->redirect('departamentos/crear');
        }
    }
    
    /**
     * PROCESAR EDICIÓN DE DEPARTAMENTO
     */
    private function procesarEditarDepartamento($deptoId, $userId) {
        try {
            $departamento = $this->getDepartamentoById($deptoId, $userId);
            
            if (!$departamento) {
                $this->addFlashMessage('error', 'Departamento no encontrado');
                $this->redirect('departamentos');
            }
            
            $numero = trim($_POST['numero'] ?? '');
            $piso = $_POST['piso'] ?? null;
            $metrosCuadrados = $_POST['metros_cuadrados'] ?? null;
            $orientacion = $_POST['orientacion'] ?? null;
            $dormitorios = $_POST['dormitorios'] ?? 1;
            $banos = $_POST['banos'] ?? 1;
            $estacionamientos = $_POST['estacionamientos'] ?? 0;
            $bodegas = $_POST['bodegas'] ?? 0;
            $propietarioRut = $_POST['propietario_rut'] ?? null;
            $propietarioNombre = trim($_POST['propietario_nombre'] ?? '');
            $propietarioEmail = $_POST['propietario_email'] ?? null;
            $propietarioTelefono = $_POST['propietario_telefono'] ?? null;
            $porcentajeCopropiedad = $_POST['porcentaje_copropiedad'] ?? 0;
            $observaciones = trim($_POST['observaciones'] ?? '');
            $calculoAutomatico = $_POST['calculo_automatico'] ?? '0';
            
            // Validaciones
            $errors = [];
            if (empty($numero)) {
                $errors[] = 'El número de departamento es requerido';
            }
            if (empty($propietarioNombre)) {
                $errors[] = 'El nombre del propietario es requerido';
            }
            if ($propietarioRut && !$this->security->validateRUT($propietarioRut)) {
                $errors[] = 'El RUT del propietario no es válido';
            }
            
            // Verificar que el número no esté duplicado (excluyendo este departamento)
            $sqlCheck = "SELECT id FROM departamentos WHERE edificio_id = ? AND numero = ? AND id != ?";
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->execute([$departamento['edificio_id'], $numero, $deptoId]);
            
            if ($stmtCheck->fetch()) {
                $errors[] = 'Ya existe otro departamento con ese número en el edificio';
            }
            
            if (!empty($errors)) {
                $_SESSION['form_errors'] = $errors;
                $_SESSION['form_data'] = $_POST;
                $this->redirect("departamentos/editar/$deptoId");
                return;
            }
            
            $this->db->beginTransaction();
            
            // Determinar si el porcentaje fue modificado manualmente
            $porcentajeModificadoManual = ($porcentajeCopropiedad != $departamento['porcentaje_copropiedad']);
            $calculoAutoField = $porcentajeModificadoManual ? 0 : ($departamento['porcentaje_calculado_auto'] ?? 0);
            
            // Actualizar departamento
            $sqlUpdate = "UPDATE departamentos SET 
                         numero = ?, piso = ?, metros_cuadrados = ?, orientacion = ?, 
                         dormitorios = ?, banos = ?, estacionamientos = ?, bodegas = ?,
                         propietario_rut = ?, propietario_nombre = ?, propietario_email = ?,
                         propietario_telefono = ?, porcentaje_copropiedad = ?, 
                         porcentaje_calculado_auto = ?, observaciones = ?
                         WHERE id = ?";
            
            $stmt = $this->db->prepare($sqlUpdate);
            $stmt->execute([
                $numero, $piso, $metrosCuadrados, $orientacion, $dormitorios, $banos,
                $estacionamientos, $bodegas, $propietarioRut, $propietarioNombre,
                $propietarioEmail, $propietarioTelefono, $porcentajeCopropiedad,
                $calculoAutoField, $observaciones, $deptoId
            ]);
            
            // CALCULAR PORCENTAJE AUTOMÁTICO SI ESTÁ ACTIVADO Y NO FUE MODIFICADO MANUALMENTE
            if ($calculoAutomatico === '1' && !$porcentajeModificadoManual) {
                $this->calcularPorcentajeAutomatico($deptoId, $userId);
            }
            
            $this->db->commit();
            
            $this->addFlashMessage('success', 'Departamento actualizado exitosamente');
            $this->redirect('departamentos');
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error al editar departamento: " . $e->getMessage());
            $this->addFlashMessage('error', 'Error al actualizar departamento: ' . $e->getMessage());
            $this->redirect("departamentos/editar/$deptoId");
        }
    }
    
    /**
     * CALCULAR PORCENTAJE AUTOMÁTICO PARA DEPARTAMENTO
     */
    public function calcularPorcentajeAutomatico($deptoId, $userId) {
        try {
            // Obtener datos del departamento y edificio
            $sql = "SELECT d.*, e.id as edificio_id, e.configuracion as edificio_config 
                    FROM departamentos d 
                    JOIN edificios e ON d.edificio_id = e.id 
                    WHERE d.id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$deptoId]);
            $departamento = $stmt->fetch();
            
            if (!$departamento) {
                throw new Exception("Departamento no encontrado");
            }
            
            // Verificar si el cálculo automático está activado para el edificio
            $edificioConfig = json_decode($departamento['edificio_config'] ?? '{}', true);
            $calculoAutomatico = $edificioConfig['prorrateo']['calculo_automatico'] ?? true;
            
            if (!$calculoAutomatico) {
                return [
                    'success' => true,
                    'message' => 'Cálculo automático desactivado para este edificio',
                    'porcentaje_calculado' => $departamento['porcentaje_copropiedad'] ?? 0
                ];
            }
            
            // Obtener configuración de prorrateo del edificio
            $sqlConfig = "SELECT * FROM prorrateo_edificio_config WHERE edificio_id = ?";
            $stmtConfig = $this->db->prepare($sqlConfig);
            $stmtConfig->execute([$departamento['edificio_id']]);
            $configEdificio = $stmtConfig->fetch();
            
            // Calcular porcentaje según estrategia configurada
            $resultadoCalculo = $this->calcularPorcentajeDepartamento($departamento, $configEdificio);
            $porcentajeCalculado = $resultadoCalculo['porcentaje_calculado'];
            
            // Actualizar porcentaje si es diferente al actual
            $porcentajeActual = $departamento['porcentaje_copropiedad'] ?? 0;
            
            if (abs($porcentajeCalculado - $porcentajeActual) > 0.01) {
                $sqlUpdate = "UPDATE departamentos SET 
                             porcentaje_copropiedad = ?, 
                             porcentaje_calculado_auto = 1,
                             ultimo_calculo_auto = NOW(),
                             metodo_calculo_utilizado = ?
                             WHERE id = ?";
                $stmtUpdate = $this->db->prepare($sqlUpdate);
                $stmtUpdate->execute([
                    $porcentajeCalculado, 
                    $resultadoCalculo['metodo'] ?? 'metros_cuadrados',
                    $deptoId
                ]);
                
                // Registrar en historial de cambios automáticos
                $this->registrarCambioAutomatico($deptoId, $porcentajeActual, $porcentajeCalculado, $userId, $resultadoCalculo);
                
                return [
                    'success' => true,
                    'porcentaje_anterior' => $porcentajeActual,
                    'porcentaje_calculado' => $porcentajeCalculado,
                    'metodo' => $resultadoCalculo['metodo'],
                    'metros_cuadrados' => $departamento['metros_cuadrados'],
                    'factores_aplicados' => $resultadoCalculo['factores_aplicados'] ?? null,
                    'message' => 'Porcentaje calculado automáticamente: ' . $porcentajeCalculado . '%'
                ];
            }
            
            return [
                'success' => true,
                'porcentaje_calculado' => $porcentajeActual,
                'message' => 'Porcentaje actual ya es correcto'
            ];
            
        } catch (Exception $e) {
            error_log("Error en cálculo automático de porcentaje: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * CALCULAR PORCENTAJE SEGÚN ESTRATEGIA DEL EDIFICIO
     */
    private function calcularPorcentajeDepartamento($departamento, $configEdificio) {
        // Si no hay configuración, usar metros cuadrados por defecto
        if (!$configEdificio) {
            return $this->calcularPorMetrosCuadrados($departamento);
        }
        
        $config = json_decode($configEdificio['config_json'] ?? '{}', true);
        $estrategiaId = $configEdificio['estrategia_default_id'] ?? null;
        
        // Obtener todos los departamentos del edificio para cálculo relativo
        $sqlDeptos = "SELECT id, metros_cuadrados, porcentaje_copropiedad, piso, orientacion 
                      FROM departamentos 
                      WHERE edificio_id = ? AND is_habitado = 1";
        $stmt = $this->db->prepare($sqlDeptos);
        $stmt->execute([$departamento['edificio_id']]);
        $todosDepartamentos = $stmt->fetchAll();
        
        // Usar ProrrateoManager para el cálculo
        $prorrateoManager = new ProrrateoManager();
        
        // Determinar método según estrategia
        if ($estrategiaId) {
            $estrategia = $this->obtenerEstrategia($estrategiaId);
            $metodo = $estrategia['metodo_calculo'] ?? 'metros_cuadrados';
        } else {
            $metodo = 'metros_cuadrados';
        }
        
        // Simular cálculo con monto ficticio (100 para obtener porcentajes)
        $montoFicticio = 100;
        $distribucion = [];
        
        switch ($metodo) {
            case 'metros_cuadrados':
                $distribucion = $prorrateoManager->calcularMetrosUtilesChile($todosDepartamentos, $montoFicticio, $config);
                break;
            case 'porcentaje_copropiedad':
                // Mantener porcentajes existentes
                foreach ($todosDepartamentos as $depto) {
                    $distribucion[] = [
                        'departamento_id' => $depto['id'],
                        'porcentaje_aplicado' => $depto['porcentaje_copropiedad'] ?? 0
                    ];
                }
                break;
            case 'mixto':
                $distribucion = $prorrateoManager->calcularMixtoChileno($todosDepartamentos, $montoFicticio, $config);
                break;
            default:
                $distribucion = $prorrateoManager->calcularMetrosUtilesChile($todosDepartamentos, $montoFicticio, $config);
        }
        
        // Encontrar el porcentaje calculado para este departamento
        foreach ($distribucion as $item) {
            if ($item['departamento_id'] == $departamento['id']) {
                return [
                    'porcentaje_calculado' => $item['porcentaje_aplicado'],
                    'metodo' => $metodo,
                    'factores_aplicados' => isset($item['puntos_factores']) ? 'Piso: ' . ($departamento['piso'] ?? 'N/A') . ', Orientación: ' . ($departamento['orientacion'] ?? 'N/A') : null
                ];
            }
        }
        
        // Fallback: cálculo por metros cuadrados
        return $this->calcularPorMetrosCuadrados($departamento);
    }
    
    /**
     * CÁLCULO POR METROS CUADRADOS (MÉTODO POR DEFECTO)
     */
    private function calcularPorMetrosCuadrados($departamento) {
        // Obtener total de metros del edificio
        $sqlTotal = "SELECT SUM(metros_cuadrados) as total_metros 
                     FROM departamentos 
                     WHERE edificio_id = ? AND is_habitado = 1";
        $stmt = $this->db->prepare($sqlTotal);
        $stmt->execute([$departamento['edificio_id']]);
        $result = $stmt->fetch();
        
        $totalMetros = $result['total_metros'] ?? 0;
        $metrosDepto = $departamento['metros_cuadrados'] ?? 0;
        
        if ($totalMetros > 0 && $metrosDepto > 0) {
            $porcentaje = ($metrosDepto / $totalMetros) * 100;
        } else {
            $porcentaje = 0;
        }
        
        return [
            'porcentaje_calculado' => round($porcentaje, 4),
            'metodo' => 'metros_cuadrados',
            'metros_cuadrados' => $metrosDepto,
            'total_metros_edificio' => $totalMetros
        ];
    }
    
    /**
     * RECALCULAR TODOS LOS DEPARTAMENTOS DEL EDIFICIO
     */
    public function recalcularTodoEdificio($edificioId, $userId) {
        try {
            // Obtener todos los departamentos del edificio
            $sqlDeptos = "SELECT id FROM departamentos WHERE edificio_id = ? AND is_habitado = 1";
            $stmt = $this->db->prepare($sqlDeptos);
            $stmt->execute([$edificioId]);
            $departamentos = $stmt->fetchAll();
            
            $resultados = [];
            $actualizados = 0;
            
            foreach ($departamentos as $depto) {
                $resultado = $this->calcularPorcentajeAutomatico($depto['id'], $userId);
                if ($resultado['success'] && isset($resultado['porcentaje_anterior'])) {
                    $actualizados++;
                }
                $resultados[] = $resultado;
            }
            
            return [
                'success' => true,
                'total_departamentos' => count($departamentos),
                'actualizados' => $actualizados,
                'resultados' => $resultados,
                'message' => "Recálculo completado: $actualizados de " . count($departamentos) . " departamentos actualizados"
            ];
            
        } catch (Exception $e) {
            error_log("Error al recalcular edificio: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * REGISTRAR CAMBIO AUTOMÁTICO EN HISTORIAL
     */
    private function registrarCambioAutomatico($deptoId, $valorAnterior, $valorNuevo, $userId, $infoCalculo = []) {
        try {
            // Primero intentar usar la nueva tabla prorrateo_calculo_historial
            $sql = "INSERT INTO prorrateo_calculo_historial 
                    (departamento_id, porcentaje_anterior, porcentaje_nuevo, metodo_calculo, detalles_calculo, es_automatico, created_by) 
                    VALUES (?, ?, ?, ?, ?, 1, ?)";
            
            $detallesCalculo = json_encode([
                'metodo' => $infoCalculo['metodo'] ?? 'desconocido',
                'metros_cuadrados' => $infoCalculo['metros_cuadrados'] ?? null,
                'factores_aplicados' => $infoCalculo['factores_aplicados'] ?? null
            ]);
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $deptoId,
                $valorAnterior,
                $valorNuevo,
                $infoCalculo['metodo'] ?? 'metros_cuadrados',
                $detallesCalculo,
                $userId
            ]);
        } catch (Exception $e) {
            // Fallback a la tabla antigua si la nueva no existe
            error_log("Error al registrar en historial nuevo, usando fallback: " . $e->getMessage());
            
            $sql = "INSERT INTO prorrateo_historial_modificaciones 
                    (prorrateo_log_id, campo_modificado, valor_anterior, valor_nuevo, tipo_modificacion, justificacion, created_by) 
                    VALUES (NULL, 'porcentaje_copropiedad', ?, ?, 'actualizacion_automatica', ?, ?)";
            
            $justificacion = "Cálculo automático por sistema";
            if (isset($infoCalculo['metodo'])) {
                $justificacion .= " - Método: " . $infoCalculo['metodo'];
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                json_encode(['porcentaje' => $valorAnterior]),
                json_encode(['porcentaje' => $valorNuevo, 'info_calculo' => $infoCalculo]),
                $justificacion,
                $userId
            ]);
        }
    }
    
    /**
     * OBTENER INFORMACIÓN DE CÁLCULO PARA DEPARTAMENTO (CON MANEJO DE ERROR)
     */
    private function getInfoCalculoPorcentaje($deptoId) {
        try {
            // Verificar si la columna existe antes de consultarla
            $sqlCheck = "SHOW COLUMNS FROM departamentos LIKE 'porcentaje_calculado_auto'";
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->execute();
            $columnaExiste = $stmtCheck->fetch();
            
            if (!$columnaExiste) {
                return [
                    'calculo_automatico' => true,
                    'porcentaje_calculado_auto' => 0,
                    'estrategia_activa' => 'metros_cuadrados',
                    'columna_no_existe' => true
                ];
            }
            
            $sql = "SELECT d.porcentaje_calculado_auto, 
                           e.configuracion as edificio_config,
                           pec.config_json as prorrateo_config
                    FROM departamentos d
                    JOIN edificios e ON d.edificio_id = e.id
                    LEFT JOIN prorrateo_edificio_config pec ON e.id = pec.edificio_id
                    WHERE d.id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$deptoId]);
            $result = $stmt->fetch();
            
            if (!$result) {
                return [
                    'calculo_automatico' => true,
                    'porcentaje_calculado_auto' => 0,
                    'estrategia_activa' => 'metros_cuadrados'
                ];
            }
            
            $edificioConfig = json_decode($result['edificio_config'] ?? '{}', true);
            $prorrateoConfig = json_decode($result['prorrateo_config'] ?? '{}', true);
            
            return [
                'calculo_automatico' => $edificioConfig['prorrateo']['calculo_automatico'] ?? true,
                'porcentaje_calculado_auto' => $result['porcentaje_calculado_auto'] ?? 0,
                'estrategia_activa' => $prorrateoConfig['estrategia_default'] ?? 'metros_cuadrados'
            ];
            
        } catch (Exception $e) {
            error_log("Error en getInfoCalculoPorcentaje: " . $e->getMessage());
            return [
                'calculo_automatico' => true,
                'porcentaje_calculado_auto' => 0,
                'estrategia_activa' => 'metros_cuadrados',
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * OBTENER ESTRATEGIA DE PRORRATEO
     */
    private function obtenerEstrategia($estrategiaId) {
        $sql = "SELECT * FROM prorrateo_strategies WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$estrategiaId]);
        return $stmt->fetch();
    }
    
    // ==================== MÉTODOS DE CONSULTA EXISTENTES ====================
    
    private function getDepartamentos($userEdificios) {
        if (empty($userEdificios)) {
            return [];
        }
        
        $edificioIds = array_column($userEdificios, 'id');
        $placeholders = str_repeat('?,', count($edificioIds) - 1) . '?';
        
        // Verificar si la columna porcentaje_calculado_auto existe
        try {
            $sqlCheck = "SHOW COLUMNS FROM departamentos LIKE 'porcentaje_calculado_auto'";
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->execute();
            $columnaExiste = $stmtCheck->fetch();
            
            if ($columnaExiste) {
                $sql = "SELECT d.*, 
                               e.nombre as edificio_nombre,
                               e.direccion as edificio_direccion,
                               e.comuna as edificio_comuna,
                               e.region as edificio_region,
                               d.porcentaje_calculado_auto,
                               (SELECT COUNT(*) FROM gasto_departamento gd WHERE gd.departamento_id = d.id) as total_gastos,
                               (SELECT COUNT(*) FROM pagos p WHERE p.departamento_id = d.id AND p.estado = 'pagado') as pagos_realizados
                        FROM departamentos d
                        JOIN edificios e ON d.edificio_id = e.id
                        WHERE d.edificio_id IN ($placeholders)
                        ORDER BY e.nombre, d.piso, d.numero";
            } else {
                // Si la columna no existe, usar consulta sin ella
                $sql = "SELECT d.*, 
                               e.nombre as edificio_nombre,
                               e.direccion as edificio_direccion,
                               e.comuna as edificio_comuna,
                               e.region as edificio_region,
                               0 as porcentaje_calculado_auto,
                               (SELECT COUNT(*) FROM gasto_departamento gd WHERE gd.departamento_id = d.id) as total_gastos,
                               (SELECT COUNT(*) FROM pagos p WHERE p.departamento_id = d.id AND p.estado = 'pagado') as pagos_realizados
                        FROM departamentos d
                        JOIN edificios e ON d.edificio_id = e.id
                        WHERE d.edificio_id IN ($placeholders)
                        ORDER BY e.nombre, d.piso, d.numero";
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($edificioIds);
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Error en getDepartamentos: " . $e->getMessage());
            return [];
        }
    }
    
    private function getDepartamentoById($deptoId, $userId) {
        // Verificar si la columna existe
        try {
            $sqlCheck = "SHOW COLUMNS FROM departamentos LIKE 'porcentaje_calculado_auto'";
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->execute();
            $columnaExiste = $stmtCheck->fetch();
            
            if ($columnaExiste) {
                $sql = "SELECT d.*, 
                               e.nombre as edificio_nombre,
                               e.direccion as edificio_direccion,
                               e.comuna as edificio_comuna, 
                               e.region as edificio_region,
                               d.porcentaje_calculado_auto
                        FROM departamentos d
                        JOIN edificios e ON d.edificio_id = e.id
                        JOIN user_edificio_relations uer ON e.id = uer.edificio_id
                        WHERE d.id = ? AND uer.user_id = ?";
            } else {
                $sql = "SELECT d.*, 
                               e.nombre as edificio_nombre,
                               e.direccion as edificio_direccion,
                               e.comuna as edificio_comuna, 
                               e.region as edificio_region,
                               0 as porcentaje_calculado_auto
                        FROM departamentos d
                        JOIN edificios e ON d.edificio_id = e.id
                        JOIN user_edificio_relations uer ON e.id = uer.edificio_id
                        WHERE d.id = ? AND uer.user_id = ?";
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$deptoId, $userId]);
            
            return $stmt->fetch();
            
        } catch (Exception $e) {
            error_log("Error en getDepartamentoById: " . $e->getMessage());
            return null;
        }
    }
    
    private function getHistorialProrrateoDepartamento($deptoId) {
        try {
            // Primero intentar con la nueva tabla
            $sql = "SELECT pch.*, u.nombre as usuario_nombre
                    FROM prorrateo_calculo_historial pch
                    LEFT JOIN users u ON pch.created_by = u.id
                    WHERE pch.departamento_id = ?
                    ORDER BY pch.created_at DESC
                    LIMIT 10";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$deptoId]);
            $resultados = $stmt->fetchAll();
            
            if (empty($resultados)) {
                // Fallback a la tabla antigua
                $sql = "SELECT phm.*, u.nombre as usuario_nombre
                        FROM prorrateo_historial_modificaciones phm
                        LEFT JOIN users u ON phm.created_by = u.id
                        WHERE phm.prorrateo_log_id IN (
                            SELECT gpl.id FROM gasto_prorrateo_log gpl
                            JOIN gastos_comunes gc ON gpl.gasto_comun_id = gc.id
                            JOIN gasto_departamento gd ON gc.id = gd.gasto_comun_id
                            WHERE gd.departamento_id = ?
                        )
                        ORDER BY phm.created_at DESC
                        LIMIT 10";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$deptoId]);
                $resultados = $stmt->fetchAll();
            }
            
            return $resultados;
            
        } catch (Exception $e) {
            error_log("Error en getHistorialProrrateoDepartamento: " . $e->getMessage());
            return [];
        }
    }
    
    private function getGastosDepartamento($deptoId) {
        $sql = "SELECT gc.*, gd.monto as monto_depto, gd.porcentaje as porcentaje_depto,
                p.estado as estado_pago, p.fecha_pago,
                e.nombre as edificio_nombre
                FROM gasto_departamento gd
                JOIN gastos_comunes gc ON gd.gasto_comun_id = gc.id
                JOIN edificios e ON gc.edificio_id = e.id
                LEFT JOIN pagos p ON gd.departamento_id = p.departamento_id AND gd.gasto_comun_id = p.gasto_comun_id
                WHERE gd.departamento_id = ?
                ORDER BY gc.periodo DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$deptoId]);
        
        return $stmt->fetchAll();
    }
}
?>