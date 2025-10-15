<?php
// 📁 controllers/ApiController.php - VERSIÓN COMPLETA

class ApiController {
    private $security;
    private $db;
    
    public function __construct() {
        $this->security = SecurityManager::getInstance();
        $this->db = DatabaseConnection::getInstance()->getConnection();
        header('Content-Type: application/json');
        $this->checkAuth();
    }
    
    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $path = str_replace('/api/', '', $path);
        
        // 🎯 ENDPOINTS ORGANIZADOS POR MÓDULOS
        switch ($path) {
            // ==================== MÓDULO DEPARTAMENTOS ====================
            case 'departamentos/calcular-porcentaje':
                if ($method === 'GET') return $this->calcularPorcentajeDepartamento();
                break;
                
            case 'edificios/recalcular-prorrateo':
                if ($method === 'POST') return $this->recalcularProrrateoEdificio();
                break;
                
            // ==================== MÓDULO PRORRATEO ====================
            case 'prorrateo/calcular':
                if ($method === 'POST') return $this->calcularProrrateo();
                break;
                
            case 'prorrateo/aprobar':
                if ($method === 'POST') return $this->aprobarProrrateo();
                break;
                
            // ==================== MÓDULO FINANZAS ====================    
            case 'finanzas/gastos-comunes':
                if ($method === 'GET') return $this->getGastosComunes();
                if ($method === 'POST') return $this->crearGastoComun();
                break;
                
            // ==================== MÓDULO MANTENIMIENTO ====================
            case 'mantenimiento/solicitudes':
                if ($method === 'GET') return $this->getSolicitudesMantenimiento();
                if ($method === 'POST') return $this->crearSolicitudMantenimiento();
                break;
                
            // ==================== MÓDULO AMENITIES ====================
            case 'amenities/reservas':
                if ($method === 'GET') return $this->getReservas();
                if ($method === 'POST') return $this->crearReserva();
                break;
                
            // ==================== MÓDULO LEGAL ====================
            case 'legal/cumplimiento':
                if ($method === 'GET') return $this->getCumplimientoLegal();
                break;
                
            // ==================== MÓDULO SISTEMA ====================
            case 'menu':
                if ($method === 'GET') return $this->getMenu();
                break;
                
            case 'edificios':
                if ($method === 'GET') return $this->getEdificios();
                break;
                
            default:
                return $this->handleNotFound();
        }
    }
    
    // ==================== MÉTODOS DEPARTAMENTOS - NUEVOS ====================
    
    /**
     * CALCULAR PORCENTAJE PARA DEPARTAMENTO INDIVIDUAL
     * ✅ ENDPOINT FALTANTE - SOLUCIÓN AL ERROR
     */
    public function calcularPorcentajeDepartamento() {
        $this->checkPermission('prorrateo', 'read');
        
        $deptoId = $_GET['depto_id'] ?? null;
        
        if (!$deptoId) {
            return $this->jsonResponse(false, [], 'ID de departamento requerido');
        }
        
        try {
            // Usar la lógica existente de DepartamentosController
            $departamentosController = new DepartamentosController();
            $resultado = $departamentosController->calcularPorcentajeAutomatico($deptoId, $_SESSION['user_id']);
            
            return $this->jsonResponse(
                $resultado['success'] ?? false, 
                $resultado, 
                $resultado['message'] ?? ''
            );
            
        } catch (Exception $e) {
            error_log("Error en API calcularPorcentajeDepartamento: " . $e->getMessage());
            return $this->jsonResponse(false, [], 'Error interno: ' . $e->getMessage());
        }
    }
    
    /**
     * RECALCULAR TODO EL EDIFICIO
     * ✅ ENDPOINT FALTANTE - SOLUCIÓN AL ERROR
     */
    public function recalcularProrrateoEdificio() {
        $this->checkPermission('prorrateo', 'write');
        
        $data = json_decode(file_get_contents('php://input'), true);
        $edificioId = $data['edificio_id'] ?? $_POST['edificio_id'] ?? null;
        
        if (!$edificioId) {
            return $this->jsonResponse(false, [], 'ID de edificio requerido');
        }
        
        try {
            // Verificar permisos de acceso al edificio
            $this->checkEdificioAccess($edificioId);
            
            // Usar la lógica existente de DepartamentosController
            $departamentosController = new DepartamentosController();
            $resultado = $departamentosController->recalcularTodoEdificio($edificioId, $_SESSION['user_id']);
            
            return $this->jsonResponse(
                $resultado['success'] ?? false, 
                $resultado, 
                $resultado['message'] ?? ''
            );
            
        } catch (Exception $e) {
            error_log("Error en API recalcularProrrateoEdificio: " . $e->getMessage());
            return $this->jsonResponse(false, [], 'Error interno: ' . $e->getMessage());
        }
    }
    
    // ==================== MÉTODOS EXISTENTES (MANTENIDOS) ====================
    
    private function getMenu() {
        $menuModel = new Menu();
        $menu = $menuModel->getUserMenu($_SESSION['user_id'], $_SESSION['user_role']);
        echo json_encode(['success' => true, 'data' => $menu]);
    }
    
    private function getGastosComunes() {
        $edificioId = $_GET['edificio_id'] ?? null;
        $periodo = $_GET['periodo'] ?? date('Y-m');
        
        if (!$edificioId) {
            return $this->jsonResponse(false, [], 'ID de edificio requerido');
        }
        
        $finanzasController = new FinanzasController();
        $gastos = $finanzasController->getGastosComunes($edificioId, $periodo);
        
        return $this->jsonResponse(true, $gastos);
    }
    
    private function calcularProrrateo() {
        $this->checkPermission('prorrateo', 'write');
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$this->validateRequired($data, ['gasto_id', 'estrategia_id'])) {
            return $this->jsonResponse(false, [], 'Datos incompletos: gasto_id y estrategia_id requeridos');
        }
        
        try {
            $prorrateoManager = new ProrrateoManager();
            $resultado = $prorrateoManager->calcularDistribucionAutomatica(
                $data['gasto_id'],
                $data['estrategia_id'],
                $_SESSION['user_id']
            );
            
            if ($resultado['success']) {
                return $this->jsonResponse(true, $resultado, 'Cálculo de prorrateo completado');
            } else {
                return $this->jsonResponse(false, [], $resultado['error'] ?? 'Error en el cálculo');
            }
            
        } catch (Exception $e) {
            error_log("Error en API calcularProrrateo: " . $e->getMessage());
            return $this->jsonResponse(false, [], 'Error interno del sistema: ' . $e->getMessage());
        }
    }
    
    private function aprobarProrrateo() {
        $this->checkPermission('prorrateo', 'approve');
        
        $data = json_decode(file_get_contents('php://input'), true);
        $prorrateoLogId = $data['prorrateo_log_id'] ?? null;
        $justificacion = $data['justificacion'] ?? null;
        
        if (!$prorrateoLogId) {
            return $this->jsonResponse(false, [], 'ID de registro de prorrateo requerido');
        }
        
        try {
            $prorrateoManager = new ProrrateoManager();
            $resultado = $prorrateoManager->aprobarProrrateo(
                $prorrateoLogId, 
                $_SESSION['user_id'], 
                $justificacion
            );
            
            if ($resultado['success']) {
                return $this->jsonResponse(true, $resultado, 'Prorrateo aprobado exitosamente');
            } else {
                return $this->jsonResponse(false, [], $resultado['error'] ?? 'Error al aprobar prorrateo');
            }
            
        } catch (Exception $e) {
            error_log("Error en API aprobarProrrateo: " . $e->getMessage());
            return $this->jsonResponse(false, [], 'Error interno del sistema: ' . $e->getMessage());
        }
    }
    
    private function getCumplimientoLegal() {
        $edificioId = $_GET['edificio_id'] ?? null;
        
        if (!$edificioId) {
            return $this->jsonResponse(false, [], 'ID de edificio requerido');
        }
        
        $legalManager = new LegalChileManager();
        $cumplimiento = $legalManager->verificarCumplimientoLeyCopropiedad($edificioId);
        $proteccionDatos = $legalManager->verificarProteccionDatos($edificioId);
        
        return $this->jsonResponse(true, [
            'ley_copropiedad' => $cumplimiento,
            'proteccion_datos' => $proteccionDatos
        ]);
    }
    
    private function getEdificios() {
        $permissionManager = PermissionManager::getInstance();
        $edificios = $permissionManager->getUserAccessibleEdificios($_SESSION['user_id']);
        return $this->jsonResponse(true, $edificios);
    }
    
    // ==================== MÉTODOS AUXILIARES MEJORADOS ====================
    
    private function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'No autorizado']);
            exit();
        }
    }
    
    private function checkPermission($module, $action) {
        $permissionManager = PermissionManager::getInstance();
        if (!$permissionManager->userCan($_SESSION['user_id'], $module, $action)) {
            http_response_code(403);
            echo json_encode(['error' => 'Sin permisos para esta acción']);
            exit();
        }
    }
    
    private function checkEdificioAccess($edificioId) {
        $permissionManager = PermissionManager::getInstance();
        if (!$permissionManager->userCanAccessEdificio($_SESSION['user_id'], $edificioId)) {
            http_response_code(403);
            echo json_encode(['error' => 'Sin acceso a este edificio']);
            exit();
        }
    }
    
    private function validateRequired($data, $requiredFields) {
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return false;
            }
        }
        return true;
    }
    
    private function jsonResponse($success, $data = [], $message = '') {
        echo json_encode([
            'success' => $success,
            'data' => $data,
            'message' => $message,
            'timestamp' => time()
        ]);
        exit;
    }
    
    private function handleNotFound() {
        http_response_code(404);
        echo json_encode([
            'error' => 'Endpoint no encontrado',
            'path' => parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH),
            'method' => $_SERVER['REQUEST_METHOD']
        ]);
        exit;
    }
    
    // ==================== MÉTODOS PENDIENTES DE IMPLEMENTACIÓN ====================
    
    private function crearSolicitudMantenimiento() {
        // TODO: Implementar creación de solicitud de mantenimiento vía API
        http_response_code(501);
        echo json_encode(['error' => 'Método no implementado']);
    }
    
    private function getSolicitudesMantenimiento() {
        // TODO: Implementar obtención de solicitudes de mantenimiento vía API
        http_response_code(501);
        echo json_encode(['error' => 'Método no implementado']);
    }
    
    private function crearGastoComun() {
        // TODO: Implementar creación de gasto común vía API
        http_response_code(501);
        echo json_encode(['error' => 'Método no implementado']);
    }
    
    private function getReservas() {
        // TODO: Implementar obtención de reservas vía API
        http_response_code(501);
        echo json_encode(['error' => 'Método no implementado']);
    }
    
    private function crearReserva() {
        // TODO: Implementar creación de reserva vía API
        http_response_code(501);
        echo json_encode(['error' => 'Método no implementado']);
    }
}
?>