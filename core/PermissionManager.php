<?php
// 📁 core/PermissionManager.php

class PermissionManager {
    private static $instance = null;
    private $db;
    
    private function __construct() {
        $this->db = DatabaseConnection::getInstance()->getConnection();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new PermissionManager();
        }
        return self::$instance;
    }
    
    /**
     * Verifica si un usuario tiene permiso para una acción específica
     */
    public function userCan($userId, $moduleKey, $action) {
        // Super admin tiene todos los permisos
        $userRole = $this->getUserRole($userId);
        if ($userRole == 1) { // super_admin
            return true;
        }
        
        // Obtener permisos del rol para el módulo
        $permissions = $this->getRoleModulePermissions($userRole, $moduleKey);
        
        return isset($permissions[$action]) && $permissions[$action] === true;
    }
    
    /**
     * Obtiene todos los módulos a los que tiene acceso un usuario
     */
    public function getUserAccessibleModules($userId) {
        $userRole = $this->getUserRole($userId);
        
        if ($userRole == 1) {
            // Super admin ve todos los módulos activos
            $sql = "SELECT module_key FROM system_modules WHERE is_active = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        }
        
        // Usuario normal - módulos con permiso read
        $sql = "SELECT DISTINCT module_key FROM role_module_permissions 
                WHERE role_id = ? AND JSON_EXTRACT(permissions, '$.read') = true";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userRole]);
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Obtiene permisos detallados para un usuario
     */
    public function getUserPermissions($userId) {
        $userRole = $this->getUserRole($userId);
        $permissions = [];
        
        if ($userRole == 1) {
            // Super admin - todos los permisos en todos los módulos
            $sql = "SELECT module_key, actions FROM system_modules WHERE is_active = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            while ($module = $stmt->fetch()) {
                $actions = json_decode($module['actions'], true) ?? [];
                $permissions[$module['module_key']] = array_fill_keys($actions, true);
            }
        } else {
            // Usuario normal - permisos específicos
            $sql = "SELECT rmp.module_key, rmp.permissions 
                    FROM role_module_permissions rmp
                    JOIN system_modules sm ON rmp.module_key = sm.module_key
                    WHERE rmp.role_id = ? AND sm.is_active = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userRole]);
            
            while ($row = $stmt->fetch()) {
                $permissions[$row['module_key']] = json_decode($row['permissions'], true) ?? [];
            }
        }
        
        return $permissions;
    }
    
    /**
     * Verifica acceso a un edificio específico
     */
    public function userCanAccessEdificio($userId, $edificioId) {
        // Super admin accede a todos los edificios
        if ($this->getUserRole($userId) == 1) {
            return true;
        }
        
        // Verificar relación usuario-edificio
        $sql = "SELECT 1 FROM user_edificio_relations 
                WHERE user_id = ? AND edificio_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $edificioId]);
        
        return (bool) $stmt->fetch();
    }
    
    /**
     * Obtiene los edificios a los que tiene acceso un usuario
     */
    public function getUserAccessibleEdificios($userId) {
        // Super admin ve todos los edificios activos
        if ($this->getUserRole($userId) == 1) {
            $sql = "SELECT * FROM edificios WHERE is_active = 1 ORDER BY nombre";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        }
        
        // Usuario normal - solo edificios asignados
        $sql = "SELECT e.*, uer.is_primary_admin, uer.permissions 
                FROM user_edificio_relations uer 
                JOIN edificios e ON uer.edificio_id = e.id 
                WHERE uer.user_id = ? AND e.is_active = 1 
                ORDER BY e.nombre";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        
        return $stmt->fetchAll();
    }
    
    private function getUserRole($userId) {
        $sql = "SELECT role_id FROM users WHERE id = ? AND is_active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        
        return $result ? $result['role_id'] : null;
    }
    
    private function getRoleModulePermissions($roleId, $moduleKey) {
        $sql = "SELECT permissions FROM role_module_permissions 
                WHERE role_id = ? AND module_key = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$roleId, $moduleKey]);
        $result = $stmt->fetch();
        
        if ($result && $result['permissions']) {
            return json_decode($result['permissions'], true) ?? [];
        }
        
        return [];
    }
}
?>