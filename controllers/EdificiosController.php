<?php
// 📁 controllers/EdificiosController.php

class EdificiosController extends ControllerCore {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function index() {
        $this->requirePermission('edificios', 'read');
        $userEdificios = $this->getUserEdificios($_SESSION['user_id']);
        
        $data = [
            'edificios' => $userEdificios,
            'user_name' => $_SESSION['user_name'],
            'flash_messages' => $this->getFlashMessages()
        ];
        
        $this->renderView('edificios/index', $data);
    }
    
    public function crear() {
        $this->requirePermission('edificios', 'create');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->crearEdificio($_POST);
        }
        
        $data = [
            'user_name' => $_SESSION['user_name'],
            'regiones_chile' => $this->getRegionesChile(),
            'comunas_chile' => $this->getComunasChile(),
            'flash_messages' => $this->getFlashMessages()
        ];
        
        $this->renderView('edificios/crear', $data);
    }
    
    public function editar($edificioId) {
        $this->requirePermission('edificios', 'update');
        $this->checkEdificioAccess($edificioId);
        $edificio = $this->getEdificioById($edificioId);
        
        if (!$edificio) {
            $this->addFlashMessage('error', 'Edificio no encontrado');
            header('Location: ' . $this->url('edificios'));
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->actualizarEdificio($edificioId, $_POST);
        }
        
        $data = [
            'edificio' => $edificio,
            'user_name' => $_SESSION['user_name'],
            'regiones_chile' => $this->getRegionesChile(),
            'comunas_chile' => $this->getComunasChile(),
            'flash_messages' => $this->getFlashMessages()
        ];
        
        $this->renderView('edificios/editar', $data);
    }
    
    public function gestionar($edificioId) {
        $this->requirePermission('edificios', 'read');
        $this->checkEdificioAccess($edificioId);
        
        // Obtener datos del edificio
        $edificio = $this->getEdificioById($edificioId);
        
        // Obtener departamentos del edificio con información extendida
        $departamentos = $this->getDepartamentosEdificio($edificioId);
        
        // Obtener estadísticas
        $estadisticas = $this->getEstadisticasEdificio($edificioId);
        
        $data = [
            'edificio' => $edificio,
            'departamentos' => $departamentos,
            'estadisticas' => $estadisticas,
            'user_name' => $_SESSION['user_name'],
            'flash_messages' => $this->getFlashMessages()
        ];
        
        $this->renderView('edificios/gestionar', $data);
    }
    
    public function desactivar($edificioId) {
        $this->requirePermission('edificios', 'delete');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->desactivarEdificio($edificioId);
        }
    }
    
    private function crearEdificio($data) {
        $errors = $this->validateInput($data, [
            'nombre' => 'required|min:3',
            'direccion' => 'required',
            'comuna' => 'required',
            'region' => 'required'
        ]);
        
        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->addFlashMessage('error', $error);
            }
            header('Location: ' . $this->url('edificios/crear'));
            exit();
        }
        
        try {
            $sql = "INSERT INTO edificios (nombre, direccion, comuna, region, rut_administrador, telefono_administrador, email_administrador, total_departamentos, total_pisos, fecha_construccion, reglamento_copropiedad, configuracion, is_active, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['nombre'],
                $data['direccion'],
                $data['comuna'],
                $data['region'],
                $data['rut_administrador'] ?? null,
                $data['telefono_administrador'] ?? null,
                $data['email_administrador'] ?? null,
                $data['total_departamentos'] ?? 0,
                $data['total_pisos'] ?? 1,
                !empty($data['fecha_construccion']) ? $data['fecha_construccion'] : null,
                $data['reglamento_copropiedad'] ?? null,
                json_encode(['theme_id' => 1])
            ]);
            
            $edificioId = $this->db->lastInsertId();
            
            // Asignar el edificio al usuario actual como administrador principal
            $this->asignarEdificioUsuario($_SESSION['user_id'], $edificioId, true);
            
            $this->addFlashMessage('success', 'Edificio creado exitosamente');
            header('Location: ' . $this->url('edificios'));
            exit();
            
        } catch (Exception $e) {
            error_log("Error al crear edificio: " . $e->getMessage());
            $this->addFlashMessage('error', 'Error al crear el edificio: ' . $e->getMessage());
            header('Location: ' . $this->url('edificios/crear'));
            exit();
        }
    }
    
    private function actualizarEdificio($edificioId, $data) {
        $errors = $this->validateInput($data, [
            'nombre' => 'required|min:3',
            'direccion' => 'required',
            'comuna' => 'required',
            'region' => 'required'
        ]);
        
        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->addFlashMessage('error', $error);
            }
            header('Location: ' . $this->url('edificios/editar/' . $edificioId));
            exit();
        }
        
        try {
            $sql = "UPDATE edificios SET 
                    nombre = ?, 
                    direccion = ?, 
                    comuna = ?, 
                    region = ?, 
                    rut_administrador = ?, 
                    telefono_administrador = ?, 
                    email_administrador = ?, 
                    total_departamentos = ?, 
                    total_pisos = ?, 
                    fecha_construccion = ?, 
                    reglamento_copropiedad = ?,
                    updated_at = NOW()
                    WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['nombre'],
                $data['direccion'],
                $data['comuna'],
                $data['region'],
                $data['rut_administrador'] ?? null,
                $data['telefono_administrador'] ?? null,
                $data['email_administrador'] ?? null,
                $data['total_departamentos'] ?? 0,
                $data['total_pisos'] ?? 1,
                !empty($data['fecha_construccion']) ? $data['fecha_construccion'] : null,
                $data['reglamento_copropiedad'] ?? null,
                $edificioId
            ]);
            
            $this->addFlashMessage('success', 'Edificio actualizado exitosamente');
            header('Location: ' . $this->url('edificios'));
            exit();
            
        } catch (Exception $e) {
            error_log("Error al actualizar edificio: " . $e->getMessage());
            $this->addFlashMessage('error', 'Error al actualizar el edificio: ' . $e->getMessage());
            header('Location: ' . $this->url('edificios/editar/' . $edificioId));
            exit();
        }
    }
    
    private function desactivarEdificio($edificioId) {
        try {
            $sql = "UPDATE edificios SET is_active = 0 WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$edificioId]);
            
            echo json_encode(['success' => true, 'message' => 'Edificio desactivado exitosamente']);
            
        } catch (Exception $e) {
            error_log("Error al desactivar edificio: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error al desactivar el edificio']);
        }
    }
    
    private function getRegionesChile() {
        return [
            'Arica y Parinacota',
            'Tarapacá',
            'Antofagasta',
            'Atacama',
            'Coquimbo',
            'Valparaíso',
            'Metropolitana',
            'O\'Higgins',
            'Maule',
            'Ñuble',
            'Biobío',
            'Araucanía',
            'Los Ríos',
            'Los Lagos',
            'Aysén',
            'Magallanes'
        ];
    }
    
    private function getComunasChile() {
        return [
            'Metropolitana' => [
                'Santiago', 'Providencia', 'Las Condes', 'Vitacura', 'Ñuñoa', 'La Reina',
                'Macul', 'Peñalolén', 'La Florida', 'Puente Alto', 'Maipú', 'Quilicura',
                'Recoleta', 'Independencia', 'Conchalí', 'Huechuraba', 'Renca', 'Cerro Navia',
                'Lo Prado', 'Quinta Normal', 'Pudahuel', 'Estación Central', 'Cerrillos'
            ],
            'Valparaíso' => [
                'Valparaíso', 'Viña del Mar', 'Concón', 'Quilpué', 'Villa Alemana', 'Limache'
            ],
            'Biobío' => [
                'Concepción', 'Talcahuano', 'Chiguayante', 'San Pedro de la Paz', 'Coronel'
            ]
        ];
    }
    
    private function getEstadisticasEdificio($edificioId) {
        $sql = "SELECT 
                COUNT(DISTINCT d.id) as total_departamentos,
                COUNT(DISTINCT CASE WHEN d.is_habitado = 1 THEN d.id END) as deptos_habitados,
                COUNT(DISTINCT m.id) as mantenimientos_pendientes,
                COUNT(DISTINCT gc.id) as gastos_pendientes
                FROM edificios e
                LEFT JOIN departamentos d ON e.id = d.edificio_id
                LEFT JOIN mantenimientos m ON e.id = m.edificio_id AND m.estado IN ('pendiente', 'en_proceso')
                LEFT JOIN gastos_comunes gc ON e.id = gc.edificio_id AND gc.estado = 'pendiente'
                WHERE e.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$edificioId]);
        return $stmt->fetch();
    }
    
    private function getDepartamentosEdificio($edificioId) {
        $sql = "SELECT d.*, 
                       COUNT(p.id) as total_pagos,
                       COUNT(CASE WHEN p.estado = 'pagado' THEN 1 END) as pagos_pagados
                FROM departamentos d
                LEFT JOIN pagos p ON d.id = p.departamento_id
                WHERE d.edificio_id = ?
                GROUP BY d.id
                ORDER BY d.piso DESC, d.numero ASC
                LIMIT 10";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$edificioId]);
        return $stmt->fetchAll();
    }
    
    private function asignarEdificioUsuario($userId, $edificioId, $isPrimaryAdmin = false) {
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
}
?>