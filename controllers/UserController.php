<?php
// 📁 controllers/UserController.php - VERSIÓN CORREGIDA Y MIGRADA

class UserController extends ControllerCore {
    
    public function __construct() {
        parent::__construct();
        // ✅ USAR NUEVO SISTEMA DE PERMISOS
        $this->requirePermission('usuarios', 'read');
    }
    
    public function index() {
        // ✅ VERIFICAR PERMISO DE LECTURA
        if (!$this->checkPermission('usuarios', 'read')) {
            $this->addFlashMessage('error', 'No tienes permisos para ver usuarios');
            $this->redirect('dashboard');
        }
        
        $users = $this->getAllUsers();
        
        $data = [
            'users' => $users,
            'user_name' => $_SESSION['user_name'],
            'flash_messages' => $this->getFlashMessages()
        ];
        
        $this->renderView('users/index', $data);
    }
    
    public function crear() {
        // ✅ VERIFICAR PERMISO DE ESCRITURA
        if (!$this->checkPermission('usuarios', 'write')) {
            $this->addFlashMessage('error', 'No tienes permisos para crear usuarios');
            $this->redirect('usuarios');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->crearUsuario($_POST);
        }
        
        $data = [
            'user_name' => $_SESSION['user_name'],
            'edificios' => $this->getUserAccessibleEdificios(), // ✅ NUEVO SISTEMA
            'roles' => $this->getAllRoles(),
            'flash_messages' => $this->getFlashMessages()
        ];
        
        $this->renderView('users/crear', $data);
    }
    
    public function editar($userId) {
        // ✅ VERIFICAR PERMISO DE ESCRITURA
        if (!$this->checkPermission('usuarios', 'write')) {
            $this->addFlashMessage('error', 'No tienes permisos para editar usuarios');
            $this->redirect('usuarios');
        }
        
        $user = $this->getUserById($userId);
        
        if (!$user) {
            $this->addFlashMessage('error', 'Usuario no encontrado');
            $this->redirect('usuarios');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->actualizarUsuario($userId, $_POST);
        }
        
        $data = [
            'user' => $user,
            'user_edificios' => $this->getUserEdificiosRelations($userId), // ✅ MÉTODO ACTUALIZADO
            'edificios' => $this->getUserAccessibleEdificios(), // ✅ NUEVO SISTEMA
            'roles' => $this->getAllRoles(),
            'user_name' => $_SESSION['user_name'],
            'flash_messages' => $this->getFlashMessages()
        ];
        
        $this->renderView('users/editar', $data);
    }
    
    public function desactivar($userId) {
        // ✅ VERIFICAR PERMISO DE ELIMINACIÓN
        if (!$this->checkPermission('usuarios', 'delete')) {
            $this->jsonResponse(false, [], 'No tienes permisos para desactivar usuarios');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->desactivarUsuario($userId);
        }
    }
    
    private function crearUsuario($data) {
        $errors = $this->validateInput($data, [
            'email' => 'required|email',
            'nombre' => 'required|min:3',
            'apellido' => 'required|min:3',
            'password' => 'required|min:6',
            'role_id' => 'required|numeric'
        ]);
        
        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->addFlashMessage('error', $error);
            }
            $this->redirect('usuarios/crear');
        }
        
        try {
            $sqlCheck = "SELECT id FROM users WHERE email = ?";
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->execute([$data['email']]);
            
            if ($stmtCheck->fetch()) {
                $this->addFlashMessage('error', 'El email ya está registrado');
                $this->redirect('usuarios/crear');
            }

            if (!empty($data['rut'])) {
                $sqlCheckRut = "SELECT id FROM users WHERE rut = ?";
                $stmtCheckRut = $this->db->prepare($sqlCheckRut);
                $stmtCheckRut->execute([$data['rut']]);
                
                if ($stmtCheckRut->fetch()) {
                    $this->addFlashMessage('error', 'El RUT ya está registrado');
                    $this->redirect('usuarios/crear');
                }
            }
            
            $sql = "INSERT INTO users (rut, nombre, apellido, email, telefono, password_hash, role_id, is_active, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['rut'] ?? null,
                $data['nombre'],
                $data['apellido'],
                $data['email'],
                $data['telefono'] ?? null,
                password_hash($data['password'], PASSWORD_DEFAULT),
                $data['role_id']
            ]);
            
            $userId = $this->db->lastInsertId();
            
            if (isset($data['edificios']) && is_array($data['edificios'])) {
                foreach ($data['edificios'] as $edificioId) {
                    $this->asignarEdificioUsuario($userId, $edificioId, false);
                }
            }
            
            $this->addFlashMessage('success', 'Usuario creado exitosamente');
            $this->redirect('usuarios');
            
        } catch (Exception $e) {
            error_log("Error al crear usuario: " . $e->getMessage());
            $this->addFlashMessage('error', 'Error al crear el usuario: ' . $e->getMessage());
            $this->redirect('usuarios/crear');
        }
    }
    
    private function actualizarUsuario($userId, $data) {
        $errors = $this->validateInput($data, [
            'email' => 'required|email',
            'nombre' => 'required|min:3',
            'apellido' => 'required|min:3',
            'role_id' => 'required|numeric'
        ]);
        
        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->addFlashMessage('error', $error);
            }
            $this->redirect('usuarios/editar/' . $userId);
        }
        
        try {
            // Verificar si el email ya existe en otro usuario
            $sqlCheck = "SELECT id FROM users WHERE email = ? AND id != ?";
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->execute([$data['email'], $userId]);
            
            if ($stmtCheck->fetch()) {
                $this->addFlashMessage('error', 'El email ya está registrado por otro usuario');
                $this->redirect('usuarios/editar/' . $userId);
            }

            // Verificar RUT si se proporcionó
            if (!empty($data['rut'])) {
                $sqlCheckRut = "SELECT id FROM users WHERE rut = ? AND id != ?";
                $stmtCheckRut = $this->db->prepare($sqlCheckRut);
                $stmtCheckRut->execute([$data['rut'], $userId]);
                
                if ($stmtCheckRut->fetch()) {
                    $this->addFlashMessage('error', 'El RUT ya está registrado por otro usuario');
                    $this->redirect('usuarios/editar/' . $userId);
                }
            }
            
            // Construir query de actualización
            $updateFields = [
                'email = ?',
                'nombre = ?',
                'apellido = ?',
                'rut = ?',
                'telefono = ?',
                'role_id = ?'
            ];
            $params = [
                $data['email'],
                $data['nombre'],
                $data['apellido'],
                $data['rut'] ?? null,
                $data['telefono'] ?? null,
                $data['role_id']
            ];
            
            // Si se proporcionó nueva contraseña
            if (!empty($data['password'])) {
                $updateFields[] = 'password_hash = ?';
                $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            
            $params[] = $userId;
            
            $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            // Actualizar asignación de edificios
            $this->actualizarEdificiosUsuario($userId, $data['edificios'] ?? []);
            
            $this->addFlashMessage('success', 'Usuario actualizado exitosamente');
            $this->redirect('usuarios');
            
        } catch (Exception $e) {
            error_log("Error al actualizar usuario: " . $e->getMessage());
            $this->addFlashMessage('error', 'Error al actualizar el usuario: ' . $e->getMessage());
            $this->redirect('usuarios/editar/' . $userId);
        }
    }
    
    private function desactivarUsuario($userId) {
        try {
            // No permitir desactivarse a sí mismo
            if ($userId == $_SESSION['user_id']) {
                echo json_encode(['success' => false, 'message' => 'No puedes desactivar tu propia cuenta']);
                return;
            }
            
            $sql = "UPDATE users SET is_active = 0 WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            
            echo json_encode(['success' => true, 'message' => 'Usuario desactivado exitosamente']);
            
        } catch (Exception $e) {
            error_log("Error al desactivar usuario: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error al desactivar el usuario']);
        }
    }
    
    private function getAllUsers() {
        try {
            $sql = "SELECT u.*, r.role_name as role_name 
                    FROM users u 
                    LEFT JOIN user_roles r ON u.role_id = r.id 
                    WHERE u.is_active = 1 
                    ORDER BY u.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Error en getAllUsers: " . $e->getMessage());
            
            // FALLBACK: Si hay error, retornar usuarios sin JOIN
            $sql = "SELECT u.*, 
                        CASE 
                            WHEN u.role_id = 1 THEN 'Super Admin'
                            WHEN u.role_id = 2 THEN 'Administrador' 
                            ELSE 'Residente'
                        END as role_name
                    FROM users u 
                    WHERE u.is_active = 1 
                    ORDER BY u.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        }
    }
    
    private function getAllRoles() {
        try {
            $sql = "SELECT id, role_name as name, role_description as description 
                    FROM user_roles 
                    WHERE is_active = 1 
                    ORDER BY id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Error en getAllRoles: " . $e->getMessage());
            
            // FALLBACK: Roles por defecto
            return [
                ['id' => 1, 'name' => 'Super Admin', 'description' => 'Administrador total del sistema'],
                ['id' => 2, 'name' => 'Administrador', 'description' => 'Administrador de edificios'],
                ['id' => 3, 'name' => 'Residente', 'description' => 'Usuario residente'],
                ['id' => 4, 'name' => 'Conserje', 'description' => 'Personal de conserjería'],
                ['id' => 5, 'name' => 'Comité', 'description' => 'Miembro del comité de administración']
            ];
        }
    }
    
    private function getUserById($userId) {
        try {
            $sql = "SELECT u.*, r.role_name as role_name 
                    FROM users u 
                    LEFT JOIN user_roles r ON u.role_id = r.id 
                    WHERE u.id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            return $stmt->fetch();
            
        } catch (Exception $e) {
            error_log("Error en getUserById: " . $e->getMessage());
            
            // FALLBACK sin JOIN
            $sql = "SELECT u.*, 
                        CASE 
                            WHEN u.role_id = 1 THEN 'Super Admin'
                            WHEN u.role_id = 2 THEN 'Administrador' 
                            ELSE 'Residente'
                        END as role_name
                    FROM users u 
                    WHERE u.id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            return $stmt->fetch();
        }
    }
    
    private function asignarEdificioUsuario($userId, $edificioId, $isPrimaryAdmin = false) {
        // Verificar si ya existe la relación
        $sqlCheck = "SELECT id FROM user_edificio_relations WHERE user_id = ? AND edificio_id = ?";
        $stmtCheck = $this->db->prepare($sqlCheck);
        $stmtCheck->execute([$userId, $edificioId]);
        
        if (!$stmtCheck->fetch()) {
            $sql = "INSERT INTO user_edificio_relations (user_id, edificio_id, is_primary_admin, permissions, created_at) 
                    VALUES (?, ?, ?, ?, NOW())";
            
            $permissions = json_encode(['all' => true]);
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId, $edificioId, $isPrimaryAdmin ? 1 : 0, $permissions]);
        }
    }
    
    private function actualizarEdificiosUsuario($userId, $edificioIds) {
        // Eliminar asignaciones actuales
        $sqlDelete = "DELETE FROM user_edificio_relations WHERE user_id = ?";
        $stmtDelete = $this->db->prepare($sqlDelete);
        $stmtDelete->execute([$userId]);
        
        // Agregar nuevas asignaciones
        foreach ($edificioIds as $edificioId) {
            $this->asignarEdificioUsuario($userId, $edificioId, false);
        }
    }
    
    /**
     * Obtiene edificios asignados a un usuario específico (para edición)
     * ✅ CORREGIDO: Cambiado de private a protected
     * ✅ ACTUALIZADO: Usa el nuevo sistema de permisos
     */
    protected function getUserEdificiosRelations($userId) {
        try {
            $sql = 'SELECT e.*, uer.is_primary_admin, uer.permissions 
                    FROM user_edificio_relations uer 
                    JOIN edificios e ON uer.edificio_id = e.id 
                    WHERE uer.user_id = ? AND e.is_active = 1 
                    ORDER BY e.nombre';

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);

            return array_map([$this->security, 'processDataFromDB'], $stmt->fetchAll());
            
        } catch (Exception $e) {
            error_log("Error en getUserEdificiosRelations: " . $e->getMessage());
            return [];
        }
    }
}
?>