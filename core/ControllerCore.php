<?php
// 📁 core/ControllerCore.php - VERSIÓN CORREGIDA SIN DUPLICADOS

class ControllerCore
{
    protected $db;
    protected $security;
    protected $themeManager;
    protected $userModel;
    protected $notificationManager;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance()->getConnection();
        $this->security = SecurityManager::getInstance();
        $this->themeManager = new ThemeManager();
        $this->userModel = new User();
        $this->notificationManager = new NotificationManager();

        // Verificar autenticación en cada controlador (excepto AuthController)
        if (!($this instanceof AuthController)) {
            $this->checkAuth();
        }
    }

    protected function checkAuth()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
        }
    }

    // ==================== SISTEMA NUEVO DE PERMISOS DINÁMICOS ====================

    /**
     * Verifica permisos usando el PermissionManager (NUEVO SISTEMA)
     */
    protected function checkPermission($moduleKey, $action) {
        $permissionManager = PermissionManager::getInstance();
        return $permissionManager->userCan($_SESSION['user_id'], $moduleKey, $action);
    }
    
    /**
     * Verifica permisos y redirecciona si no tiene acceso (NUEVO SISTEMA)
     */
    protected function requirePermission($moduleKey, $action, $redirectTo = 'dashboard') {
        if (!$this->checkPermission($moduleKey, $action)) {
            $this->addFlashMessage('error', 'No tienes permisos para acceder a esta sección');
            $this->redirect($redirectTo);
        }
    }
    
    /**
     * Obtiene los edificios accesibles para el usuario actual (NUEVO SISTEMA)
     */
    protected function getUserAccessibleEdificios() {
        $permissionManager = PermissionManager::getInstance();
        return $permissionManager->getUserAccessibleEdificios($_SESSION['user_id']);
    }
    
    /**
     * Verifica acceso a un edificio específico (NUEVO SISTEMA)
     */
    protected function checkEdificioAccess($edificioId) {
        $permissionManager = PermissionManager::getInstance();
        if (!$permissionManager->userCanAccessEdificio($_SESSION['user_id'], $edificioId)) {
            $this->addFlashMessage('error', 'No tienes acceso a este edificio');
            $this->redirect('dashboard');
        }
    }

    // ==================== SISTEMA ANTIGUO (COMPATIBILIDAD) ====================

    /**
     * Verifica permisos - SISTEMA ANTIGUO (para compatibilidad)
     * @deprecated Usar checkPermission() en nuevo código
     */
    protected function checkPermissionLegacy($permission, $edificioId = null)
    {
        $userRole = $_SESSION['user_role'];

        if ($userRole == 1) {
            return true;
        }

        $sql = 'SELECT permissions FROM user_roles WHERE id = ?';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userRole]);
        $role = $stmt->fetch();

        if (!$role) {
            return false;
        }

        $permissions = json_decode($role['permissions'], true) ?? [];

        if (isset($permissions['all']) && $permissions['all'] === true) {
            return true;
        }

        if ($edificioId) {
            $userEdificios = $this->getUserEdificiosLegacy();
            foreach ($userEdificios as $edificio) {
                if ($edificio['id'] == $edificioId) {
                    $edificioPermissions = json_decode($edificio['permissions'] ?? '{}', true);
                    if ($this->hasPermissionInArray($edificioPermissions, $permission)) {
                        return true;
                    }
                }
            }
        }

        return $this->hasPermissionInArray($permissions, $permission);
    }

    /**
     * Obtiene edificios del usuario - SISTEMA ANTIGUO (para compatibilidad)
     * @deprecated Usar getUserAccessibleEdificios() en nuevo código
     */
    protected function getUserEdificiosLegacy($userId = null)
    {
        if (!$userId) {
            $userId = $_SESSION['user_id'];
        }

        $sql = 'SELECT e.*, uer.is_primary_admin, uer.permissions 
                FROM user_edificio_relations uer 
                JOIN edificios e ON uer.edificio_id = e.id 
                WHERE uer.user_id = ? AND e.is_active = 1 
                ORDER BY e.nombre';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);

        return array_map([$this->security, 'processDataFromDB'], $stmt->fetchAll());
    }

    /**
     * Verifica acceso a edificio - SISTEMA ANTIGUO (para compatibilidad)
     * @deprecated Usar checkEdificioAccess() en nuevo código
     */
    protected function checkEdificioAccessLegacy($edificioId)
    {
        $userEdificios = $this->getUserEdificiosLegacy($_SESSION['user_id']);
        $edificioIds = array_column($userEdificios, 'id');

        if (!in_array($edificioId, $edificioIds)) {
            $this->redirect('dashboard?error=Acceso no autorizado a este edificio');
        }
    }

    // ==================== MÉTODOS PUENTE (COMPATIBILIDAD) ====================

    /**
     * MÉTODO PUENTE: getUserEdificios - Para compatibilidad con controladores existentes
     * @deprecated Usar getUserAccessibleEdificios() en nuevo código
     */
    protected function getUserEdificios($userId = null) {
        error_log("⚠️ MÉTODO LEGACY: getUserEdificios() llamado - Considera migrar a getUserAccessibleEdificios()");
        return $this->getUserAccessibleEdificios();
    }

    // ==================== MÉTODOS COMUNES Y CORE ====================

    /**
     * PREPARA DATOS COMUNES PARA TODAS LAS VISTAS - CON PERMISOS DINÁMICOS
     */
    protected function prepareViewData($data = []) {
        error_log("🔄 ControllerCore::prepareViewData() ejecutándose");
        
        // Asegurar que UrlHelper esté disponible
        if (!class_exists('UrlHelper')) {
            require_once __DIR__.'/../utils/UrlHelper.php';
        }
        
        // Obtener permisos del usuario usando PermissionManager
        $permissionManager = PermissionManager::getInstance();
        $userPermissions = $permissionManager->getUserPermissions($_SESSION['user_id']);

        // Datos base que todas las vistas necesitan
        $baseData = [
            'url' => $this->createUrlHelperInstance(),
            'user' => $this->userModel->getUserById($_SESSION['user_id']),
            'menu' => (new Menu())->getUserMenu($_SESSION['user_id'], $_SESSION['user_role']),
            'theme' => $this->themeManager->getTheme(),
            'notifications' => $this->notificationManager->getUserNotifications($_SESSION['user_id'], true),
            'unread_count' => $this->notificationManager->getUnreadCount($_SESSION['user_id']),
            'edificios' => $this->getUserAccessibleEdificios(), // Usa el nuevo sistema
            'user_permissions' => $userPermissions, // ✅ Nuevo: permisos para vistas
            'can' => function($module, $action) use ($userPermissions) {
                return isset($userPermissions[$module][$action]) && $userPermissions[$module][$action] === true;
            }
        ];
        
        error_log("✅ Datos base preparados con permisos dinámicos");
        return array_merge($baseData, $data);
    }
    
    /**
     * CREA INSTANCIA DEL HELPER DE URLs
     */
    private function createUrlHelperInstance() {
        error_log("🔗 Creando instancia de UrlHelper para vistas");
        return new class {
            public function to($path = '') {
                $result = UrlHelper::to($path);
                return $result;
            }

            public function asset($path) {
                return UrlHelper::asset($path);
            }

            public function current() {
                return UrlHelper::current();
            }

            public function base() {
                return UrlHelper::base();
            }

            public function toWithParams($path, $params = []) {
                return UrlHelper::toWithParams($path, $params);
            }
        };
    }

    /**
     * RENDERIZA VISTA - VERSIÓN CORREGIDA
     */
    protected function renderView($viewName, $data = []) {
        error_log("🎯 INICIO renderView: $viewName");
        
        // ✅ USAR prepareViewData() que incluye $url automáticamente
        $viewData = $this->prepareViewData($data);
        
        // Extraer datos para la vista
        extract($viewData);

        // Incluir header con temas
        $themeCSS = $this->themeManager->renderThemeCSS();
        include __DIR__.'/../views/templates/header.php';

        // Incluir vista específica
        $viewPath = __DIR__."/../views/{$viewName}.php";
        if (file_exists($viewPath)) {
            error_log("📁 Incluyendo vista: $viewPath");
            include $viewPath;
        } else {
            throw new Exception("Vista no encontrada: {$viewName}");
        }

        // Incluir footer
        include __DIR__.'/../views/templates/footer.php';
        error_log("🎯 FIN renderView: $viewName");
    }

    // ==================== MÉTODOS AUXILIARES (COMPATIBLES) ====================

    protected function redirect($path, $permanent = false)
    {
        if ($permanent) {
            header('HTTP/1.1 301 Moved Permanently');
        }
        UrlHelper::redirect($path);
    }

    protected function getEdificioById($edificioId)
    {
        $this->checkEdificioAccess($edificioId); // Usa el nuevo sistema

        $sql = 'SELECT * FROM edificios WHERE id = ? AND is_active = 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$edificioId]);
        $edificio = $stmt->fetch();

        if (!$edificio) {
            throw new Exception('Edificio no encontrado');
        }

        return $this->security->processDataFromDB($edificio);
    }

    protected function jsonResponse($success, $data = [], $message = '')
    {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'data' => $data,
            'message' => $message,
            'timestamp' => time(),
        ]);
        exit;
    }

    protected function validateInput($data, $rules)
    {
        $errors = [];

        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;

            if (strpos($rule, 'required') !== false && empty($value)) {
                $errors[$field] = "El campo {$field} es requerido";
                continue;
            }

            if (strpos($rule, 'email') !== false && $value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[$field] = "El campo {$field} debe ser un email válido";
            }

            if (strpos($rule, 'rut') !== false && $value && !$this->security->validateRUT($value)) {
                $errors[$field] = 'El RUT ingresado no es válido';
            }

            if (preg_match('/min:(\d+)/', $rule, $matches)) {
                $minLength = $matches[1];
                if ($value && strlen($value) < $minLength) {
                    $errors[$field] = "El campo {$field} debe tener al menos {$minLength} caracteres";
                }
            }

            if (strpos($rule, 'numeric') !== false && $value && !is_numeric($value)) {
                $errors[$field] = "El campo {$field} debe ser numérico";
            }
        }

        return $errors;
    }

    protected function getCurrentUser()
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        return $this->userModel->getUserById($_SESSION['user_id']);
    }

    protected function addFlashMessage($type, $message)
    {
        if (!isset($_SESSION['flash_messages'])) {
            $_SESSION['flash_messages'] = [];
        }
        $_SESSION['flash_messages'][] = [
            'type' => $type,
            'message' => $message,
            'timestamp' => time(),
        ];
    }

    protected function getFlashMessages()
    {
        $messages = $_SESSION['flash_messages'] ?? [];
        unset($_SESSION['flash_messages']);

        return $messages;
    }

    protected function url($path = '')
    {
        return UrlHelper::to($path);
    }

    protected function safeHtml($value, $default = '') {
        if ($value === null) {
            return $default;
        }
        return htmlspecialchars((string)$value);
    }

    protected function getPriorityBadge($prioridad) {
        switch ($prioridad) {
            case 'urgente': return 'danger';
            case 'alta': return 'warning';
            case 'media': return 'info';
            case 'baja': return 'secondary';
            default: return 'secondary';
        }
    }

    // ==================== MÉTODOS PRIVADOS (COMPATIBILIDAD) ====================

    private function hasPermissionInArray($permissions, $requiredPermission)
    {
        list($module, $action) = explode('.', $requiredPermission);

        return isset($permissions[$module])
               && is_array($permissions[$module])
               && in_array($action, $permissions[$module]);
    }
}
?>