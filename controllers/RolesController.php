<?php
// 📁 controllers/RolesController.php

class RolesController extends ControllerCore {
    
    public function __construct() {
        parent::__construct();
        // Solo super_admin puede gestionar roles
        if (!$this->checkPermission('roles', 'read')) {
            $this->addFlashMessage('error', 'No tienes permisos para acceder a esta sección');
            $this->redirect('dashboard');
        }
    }
    
    public function index() {
        // Verificar permiso de lectura
        if (!$this->checkPermission('roles', 'read')) {
            $this->addFlashMessage('error', 'No tienes permisos para ver roles');
            $this->redirect('dashboard');
        }
        
        $roles = $this->getAllRolesWithPermissions();
        $modules = $this->getAllSystemModules();
        
        $data = [
            'roles' => $roles,
            'modules' => $modules,
            'user_name' => $_SESSION['user_name'],
            'flash_messages' => $this->getFlashMessages()
        ];
        
        $this->renderView('roles/index', $data);
    }
    
    public function editar($roleId) {
        // Verificar permiso de escritura
        if (!$this->checkPermission('roles', 'write')) {
            $this->addFlashMessage('error', 'No tienes permisos para editar roles');
            $this->redirect('roles');
        }
        
        $role = $this->getRoleById($roleId);
        
        if (!$role) {
            $this->addFlashMessage('error', 'Rol no encontrado');
            $this->redirect('roles');
        }
        
        // No permitir editar roles del sistema no editables
        if ($role['is_system_role'] && !$role['is_editable']) {
            $this->addFlashMessage('error', 'Este rol del sistema no puede ser editado');
            $this->redirect('roles');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->actualizarPermisosRol($roleId, $_POST);
        }
        
        $modules = $this->getAllSystemModules();
        $rolePermissions = $this->getRolePermissions($roleId);
        
        $data = [
            'role' => $role,
            'modules' => $modules,
            'role_permissions' => $rolePermissions,
            'user_name' => $_SESSION['user_name'],
            'flash_messages' => $this->getFlashMessages()
        ];
        
        $this->renderView('roles/editar', $data);
    }
    
    public function crear() {
        // Verificar permiso de escritura
        if (!$this->checkPermission('roles', 'write')) {
            $this->addFlashMessage('error', 'No tienes permisos para crear roles');
            $this->redirect('roles');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->crearRol($_POST);
        }
        
        $modules = $this->getAllSystemModules();
        
        $data = [
            'modules' => $modules,
            'user_name' => $_SESSION['user_name'],
            'flash_messages' => $this->getFlashMessages()
        ];
        
        $this->renderView('roles/crear', $data);
    }
    
    private function crearRol($data) {
        $errors = $this->validateInput($data, [
            'role_name' => 'required|min:3',
            'role_description' => 'required|min:10'
        ]);
        
        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->addFlashMessage('error', $error);
            }
            $this->redirect('roles/crear');
        }
        
        try {
            $this->db->beginTransaction();
            
            // Verificar que el nombre no exista
            $sqlCheck = "SELECT id FROM user_roles WHERE role_name = ?";
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->execute([$data['role_name']]);
            
            if ($stmtCheck->fetch()) {
                $this->addFlashMessage('error', 'Ya existe un rol con ese nombre');
                $this->redirect('roles/crear');
            }
            
            // Crear rol
            $sql = "INSERT INTO user_roles (role_name, role_description, permissions, is_system_role, is_editable) 
                    VALUES (?, ?, '{}', 0, 1)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$data['role_name'], $data['role_description']]);
            
            $roleId = $this->db->lastInsertId();
            
            // Asignar permisos si se proporcionaron
            if (isset($data['permissions']) && is_array($data['permissions'])) {
                $this->actualizarPermisosRol($roleId, $data);
            }
            
            $this->db->commit();
            $this->addFlashMessage('success', 'Rol creado exitosamente');
            $this->redirect('roles');
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error al crear rol: " . $e->getMessage());
            $this->addFlashMessage('error', 'Error al crear el rol: ' . $e->getMessage());
            $this->redirect('roles/crear');
        }
    }
    
    private function actualizarPermisosRol($roleId, $data) {
        try {
            $this->db->beginTransaction();
            
            // Eliminar permisos existentes
            $sqlDelete = "DELETE FROM role_module_permissions WHERE role_id = ?";
            $stmtDelete = $this->db->prepare($sqlDelete);
            $stmtDelete->execute([$roleId]);
            
            // Insertar nuevos permisos
            if (isset($data['permissions']) && is_array($data['permissions'])) {
                $sqlInsert = "INSERT INTO role_module_permissions (role_id, module_key, permissions) VALUES (?, ?, ?)";
                $stmtInsert = $this->db->prepare($sqlInsert);
                
                foreach ($data['permissions'] as $moduleKey => $modulePermissions) {
                    if (!empty($modulePermissions) && is_array($modulePermissions)) {
                        $permissionsJson = json_encode(array_fill_keys($modulePermissions, true));
                        $stmtInsert->execute([$roleId, $moduleKey, $permissionsJson]);
                    }
                }
            }
            
            $this->db->commit();
            $this->addFlashMessage('success', 'Permisos actualizados exitosamente');
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error al actualizar permisos: " . $e->getMessage());
            $this->addFlashMessage('error', 'Error al actualizar permisos: ' . $e->getMessage());
        }
    }
    
    private function getAllRolesWithPermissions() {
        $sql = "SELECT ur.*, 
                COUNT(rmp.id) as total_modules,
                COUNT(CASE WHEN rmp.permissions IS NOT NULL THEN 1 END) as modules_with_permissions
                FROM user_roles ur
                LEFT JOIN role_module_permissions rmp ON ur.id = rmp.role_id
                GROUP BY ur.id
                ORDER BY ur.is_system_role DESC, ur.role_name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    private function getRolePermissions($roleId) {
        $sql = "SELECT module_key, permissions FROM role_module_permissions WHERE role_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$roleId]);
        
        $permissions = [];
        while ($row = $stmt->fetch()) {
            $permissions[$row['module_key']] = json_decode($row['permissions'], true) ?? [];
        }
        
        return $permissions;
    }
    
    private function getAllSystemModules() {
        $sql = "SELECT * FROM system_modules WHERE is_active = 1 ORDER BY parent_module IS NULL DESC, module_name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        $modules = [];
        while ($row = $stmt->fetch()) {
            if ($row['parent_module']) {
                $modules[$row['parent_module']]['children'][] = $row;
            } else {
                $modules[$row['module_key']] = $row;
                $modules[$row['module_key']]['children'] = [];
            }
        }
        
        return $modules;
    }
    
    private function getRoleById($roleId) {
        $sql = "SELECT * FROM user_roles WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$roleId]);
        return $stmt->fetch();
    }
}
?>