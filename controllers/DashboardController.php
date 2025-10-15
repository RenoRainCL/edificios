<?php
// 📁 controllers/DashboardController.php - VERSIÓN ACTUALIZADA CON AMENITIES + RESERVAS + PRORRATEO

class DashboardController extends ControllerCore {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function index() {
        $userId = $_SESSION['user_id'];
        
        // ✅ VERIFICAR PERMISOS PARA MÓDULOS
        $puedeGestionarUsuarios = $this->checkPermission('usuarios', 'read');
        $puedeGestionarAmenities = $this->checkPermission('amenities', 'read');
        $puedeGestionarReservas = $this->checkPermission('reservas', 'read');
        $puedeGestionarProrrateo = $this->checkPermission('prorrateo', 'read'); // NUEVO: Permisos para prorrateo
        
        // OBTENER TODAS LAS ESTADÍSTICAS Y DATOS NECESARIOS
        $estadisticas = $this->getEstadisticasDashboard($userId, $puedeGestionarUsuarios, $puedeGestionarAmenities, $puedeGestionarProrrateo);
        $reservas_recientes = $this->getReservasRecientes($userId);
        $amenities_populares = $this->getAmenitiesPopulares($userId);
        $amenities_requieren_atencion = $this->getAmenitiesRequierenAtencion($userId);
        $estadisticas_uso_amenities = $this->getEstadisticasUsoAmenities($userId);
        $userEdificios = $this->getUserAccessibleEdificios();
        
        // NUEVO: Datos para el módulo de prorrateo
        $prorrateos_recientes = $puedeGestionarProrrateo ? $this->getProrrateosRecientes($userId) : [];
        $prorrateos_pendientes = $puedeGestionarProrrateo ? $this->getProrrateosPendientesCount($userId) : 0;

        $data = [
            // VARIABLES REQUERIDAS POR LA VISTA
            'estadisticas' => $estadisticas,
            'reservas_recientes' => $reservas_recientes,
            'amenities_populares' => $amenities_populares,
            'amenities_requieren_atencion' => $amenities_requieren_atencion,
            'estadisticas_uso_amenities' => $estadisticas_uso_amenities,
            'user_name' => $_SESSION['user_name'],
            'flash_messages' => $this->getFlashMessages(),
            
            // DATOS ADICIONALES PARA ACCIONES RÁPIDAS
            'edificios' => $userEdificios,
            'total_edificios' => count($userEdificios),
            'total_departamentos' => $this->getTotalDepartamentosUsuario($userId),
            
            // COMPATIBILIDAD CON VISTA EXISTENTE
            'stats' => $estadisticas,
            'notifications' => $this->getRecentNotifications($userId),
            'mantenimientos_pendientes' => $this->getMantenimientosPendientes($userId, 5),
            'notificaciones_importantes' => $this->getImportantNotifications($userId),
            'actividades_recientes' => $this->getActividadesRecientes($userId),
            
            // ✅ PERMISOS PARA VISTA
            'puede_gestionar_usuarios' => $puedeGestionarUsuarios,
            'puede_gestionar_amenities' => $puedeGestionarAmenities,
            'puede_gestionar_reservas' => $puedeGestionarReservas,
            'puede_gestionar_prorrateo' => $puedeGestionarProrrateo, // NUEVO
            'total_usuarios' => $puedeGestionarUsuarios ? $this->getTotalUsuarios() : 0,

            // NUEVO: Datos para widget de prorrateo
            'prorrateos_recientes' => $prorrateos_recientes,
            'prorrateos_pendientes' => $prorrateos_pendientes
        ];
        
        $this->renderView('dashboard/index', $data);
    }
    
    /**
     * Obtiene todas las estadísticas para el dashboard INCLUYENDO AMENITIES + RESERVAS + PRORRATEO
     */
    private function getEstadisticasDashboard($userId, $puedeGestionarUsuarios = false, $puedeGestionarAmenities = false, $puedeGestionarProrrateo = false) {
        $userEdificios = $this->getUserAccessibleEdificios();
        $edificioIds = array_column($userEdificios, 'id');
        
        if (empty($edificioIds)) {
            return [
                'reservas_hoy' => 0,
                'reservas_pendientes' => 0,
                'amenities_activos' => 0,
                'amenities_requieren_aprobacion' => 0,
                'amenities_con_conflictos' => 0,
                'mantenimientos_urgentes' => 0,
                'total_edificios' => 0,
                'total_departamentos' => 0,
                'pagos_mes' => '0%',
                'gastos_pendientes' => 0,
                'mantenimientos_pendientes' => 0,
                'total_usuarios' => 0,
                // NUEVAS ESTADÍSTICAS DE RESERVAS
                'mis_reservas_activas' => 0,
                'total_reservas_mes' => 0,
                'reservas_confirmadas_hoy' => 0,
                // NUEVO: Estadísticas de prorrateo
                'prorrateos_pendientes' => 0,
                'gastos_sin_prorratear' => 0,
                'prorrateos_mes_actual' => 0
            ];
        }
        
        return [
            'reservas_hoy' => $this->getReservasHoy($edificioIds),
            'reservas_pendientes' => $this->getReservasPendientes($edificioIds),
            'amenities_activos' => $this->getAmenitiesActivos($edificioIds),
            'amenities_requieren_aprobacion' => $this->getAmenitiesRequierenAprobacion($edificioIds),
            'amenities_con_conflictos' => $this->getAmenitiesConConflictos($edificioIds),
            'mantenimientos_urgentes' => $this->getMantenimientosUrgentes($edificioIds),
            'total_edificios' => count($userEdificios),
            'total_departamentos' => $this->getTotalDepartamentos($edificioIds),
            'pagos_mes' => $this->getPorcentajePagos($edificioIds),
            'gastos_pendientes' => $this->getGastosPendientes($edificioIds),
            'mantenimientos_pendientes' => $this->getMantenimientosUrgentes($edificioIds),
            'total_usuarios' => $puedeGestionarUsuarios ? $this->getTotalUsuarios() : 0,
            // NUEVAS ESTADÍSTICAS DE RESERVAS
            'mis_reservas_activas' => $this->getMisReservasActivas($userId),
            'total_reservas_mes' => $this->getTotalReservasMes($edificioIds),
            'reservas_confirmadas_hoy' => $this->getReservasConfirmadasHoy($edificioIds),
            // NUEVO: Estadísticas de prorrateo
            'prorrateos_pendientes' => $puedeGestionarProrrateo ? $this->getProrrateosPendientesCount($userId) : 0,
            'gastos_sin_prorratear' => $puedeGestionarProrrateo ? $this->getGastosSinProrratearCount($edificioIds) : 0,
            'prorrateos_mes_actual' => $puedeGestionarProrrateo ? $this->getProrrateosMesActualCount($edificioIds) : 0
        ];
    }

    // ==================== NUEVOS MÉTODOS PARA PRORRATEO ====================

    /**
     * Obtiene prorrateos recientes para el widget del dashboard
     */
    private function getProrrateosRecientes($userId, $limit = 5) {
        try {
            $userEdificios = $this->getUserAccessibleEdificios();
            $edificioIds = array_column($userEdificios, 'id');
            
            if (empty($edificioIds)) return [];
            
            $placeholders = str_repeat('?,', count($edificioIds) - 1) . '?';
            
            $sql = "SELECT 
                        gpl.id,
                        gpl.estado,
                        gpl.created_at,
                        gc.nombre as gasto_nombre,
                        gc.monto_total,
                        gc.periodo,
                        e.nombre as edificio_nombre,
                        es.nombre as estrategia_nombre
                    FROM gasto_prorrateo_log gpl
                    JOIN gastos_comunes gc ON gpl.gasto_comun_id = gc.id
                    JOIN edificios e ON gc.edificio_id = e.id
                    LEFT JOIN prorrateo_strategies es ON gpl.estrategia_id = es.id
                    WHERE gc.edificio_id IN ($placeholders)
                    ORDER BY gpl.created_at DESC
                    LIMIT ?";
            
            $stmt = $this->db->prepare($sql);
            $params = array_merge($edificioIds, [$limit]);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Error al obtener prorrateos recientes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene el conteo de prorrateos pendientes de aprobación
     */
    private function getProrrateosPendientesCount($userId) {
        try {
            $userEdificios = $this->getUserAccessibleEdificios();
            $edificioIds = array_column($userEdificios, 'id');
            
            if (empty($edificioIds)) return 0;
            
            $placeholders = str_repeat('?,', count($edificioIds) - 1) . '?';
            
            $sql = "SELECT COUNT(*) as total
                    FROM gasto_prorrateo_log gpl
                    JOIN gastos_comunes gc ON gpl.gasto_comun_id = gc.id
                    WHERE gc.edificio_id IN ($placeholders)
                    AND gpl.estado = 'pendiente_aprobacion'";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($edificioIds);
            $result = $stmt->fetch();
            
            return $result['total'] ?? 0;
            
        } catch (Exception $e) {
            error_log("Error al obtener conteo de prorrateos pendientes: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtiene el conteo de gastos sin prorratear
     */
    private function getGastosSinProrratearCount($edificioIds) {
        if (empty($edificioIds)) return 0;
        
        $placeholders = str_repeat('?,', count($edificioIds) - 1) . '?';
        
        try {
            $sql = "SELECT COUNT(*) as total
                    FROM gastos_comunes 
                    WHERE edificio_id IN ($placeholders) 
                    AND estado = 'pendiente'
                    AND distribucion_confirmada = 0";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($edificioIds);
            $result = $stmt->fetch();
            
            return $result['total'] ?? 0;
            
        } catch (Exception $e) {
            error_log("Error al obtener gastos sin prorratear: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtiene el conteo de prorrateos del mes actual
     */
    private function getProrrateosMesActualCount($edificioIds) {
        if (empty($edificioIds)) return 0;
        
        $placeholders = str_repeat('?,', count($edificioIds) - 1) . '?';
        $mes_actual = date('Y-m');
        
        try {
            $sql = "SELECT COUNT(*) as total
                    FROM gasto_prorrateo_log gpl
                    JOIN gastos_comunes gc ON gpl.gasto_comun_id = gc.id
                    WHERE gc.edificio_id IN ($placeholders)
                    AND DATE_FORMAT(gpl.created_at, '%Y-%m') = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(array_merge($edificioIds, [$mes_actual]));
            $result = $stmt->fetch();
            
            return $result['total'] ?? 0;
            
        } catch (Exception $e) {
            error_log("Error al obtener prorrateos del mes actual: " . $e->getMessage());
            return 0;
        }
    }

    // ==================== MÉTODOS EXISTENTES (MANTENIDOS) ====================

    /**
     * Obtiene actividades recientes para compatibilidad con vista - ACTUALIZADO CON PRORRATEO
     */
    private function getActividadesRecientes($userId, $limit = 5) {
        $userEdificios = $this->getUserAccessibleEdificios();
        $edificioIds = array_column($userEdificios, 'id');
        
        if (empty($edificioIds)) return [];
        
        $placeholders = str_repeat('?,', count($edificioIds) - 1) . '?';
        
        $sql = "(SELECT 
                    'reserva' as tipo,
                    r.id,
                    CONCAT('Reserva: ', a.nombre) as titulo,
                    CONCAT('Departamento ', d.numero) as descripcion,
                    e.nombre as edificio_nombre,
                    r.estado,
                    r.created_at as fecha
                FROM reservas r
                JOIN amenities a ON r.amenity_id = a.id
                JOIN departamentos d ON r.departamento_id = d.id
                JOIN edificios e ON a.edificio_id = e.id
                WHERE a.edificio_id IN ($placeholders)
                ORDER BY r.created_at DESC 
                LIMIT 2)
                
                UNION ALL
                
                (SELECT 
                    'mantenimiento' as tipo,
                    m.id,
                    CONCAT('Mantenimiento: ', m.titulo) as titulo,
                    m.descripcion,
                    e.nombre as edificio_nombre,
                    m.estado,
                    m.fecha_solicitud as fecha
                FROM mantenimientos m
                JOIN edificios e ON m.edificio_id = e.id
                WHERE m.edificio_id IN ($placeholders)
                ORDER BY m.fecha_solicitud DESC 
                LIMIT 1)
                
                UNION ALL
                
                (SELECT 
                    'prorrateo' as tipo,
                    gpl.id,
                    CONCAT('Prorrateo: ', gc.nombre) as titulo,
                    CONCAT('Estrategia: ', COALESCE(ps.nombre, 'Personalizado')) as descripcion,
                    e.nombre as edificio_nombre,
                    gpl.estado,
                    gpl.created_at as fecha
                FROM gasto_prorrateo_log gpl
                JOIN gastos_comunes gc ON gpl.gasto_comun_id = gc.id
                JOIN edificios e ON gc.edificio_id = e.id
                LEFT JOIN prorrateo_strategies ps ON gpl.estrategia_id = ps.id
                WHERE gc.edificio_id IN ($placeholders)
                ORDER BY gpl.created_at DESC 
                LIMIT 2)
                
                ORDER BY fecha DESC 
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $params = array_merge($edificioIds, $edificioIds, $edificioIds, [$limit]);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }

    // ==================== MÉTODOS EXISTENTES (SIN MODIFICACIONES) ====================

    /**
     * Obtiene total de usuarios activos en el sistema
     */
    private function getTotalUsuarios() {
        try {
            $sql = "SELECT COUNT(*) as total FROM users WHERE is_active = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            
            return $result['total'] ?? 0;
            
        } catch (Exception $e) {
            error_log("Error al obtener total de usuarios: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtiene total de departamentos para usuario
     */
    private function getTotalDepartamentosUsuario($userId) {
        $userEdificios = $this->getUserAccessibleEdificios();
        $edificioIds = array_column($userEdificios, 'id');
        
        if (empty($edificioIds)) return 0;
        
        return $this->getTotalDepartamentos($edificioIds);
    }
    
    /**
     * Obtiene gastos comunes pendientes
     */
    private function getGastosPendientes($edificioIds) {
        if (empty($edificioIds)) return 0;
        
        $placeholders = str_repeat('?,', count($edificioIds) - 1) . '?';
        
        $sql = "SELECT COUNT(*) as total 
                FROM gastos_comunes 
                WHERE edificio_id IN ($placeholders) AND estado = 'pendiente'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($edificioIds);
        $result = $stmt->fetch();
        
        return $result['total'] ?? 0;
    }
    
    /**
     * Obtiene reservas pendientes de aprobación
     */
    private function getReservasPendientes($edificioIds) {
        if (empty($edificioIds)) return 0;
        
        $placeholders = str_repeat('?,', count($edificioIds) - 1) . '?';
        
        $sql = "SELECT COUNT(*) as total 
                FROM reservas r
                JOIN amenities a ON r.amenity_id = a.id
                WHERE a.edificio_id IN ($placeholders) AND r.estado = 'pendiente'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($edificioIds);
        $result = $stmt->fetch();
        
        return $result['total'] ?? 0;
    }
    
    /**
     * Obtiene amenities activos
     */
    private function getAmenitiesActivos($edificioIds) {
        if (empty($edificioIds)) return 0;
        
        $placeholders = str_repeat('?,', count($edificioIds) - 1) . '?';
        
        $sql = "SELECT COUNT(*) as total 
                FROM amenities 
                WHERE edificio_id IN ($placeholders) AND is_active = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($edificioIds);
        $result = $stmt->fetch();
        
        return $result['total'] ?? 0;
    }
    
    /**
     * Obtiene mantenimientos urgentes
     */
    private function getMantenimientosUrgentes($edificioIds) {
        if (empty($edificioIds)) return 0;
        
        $placeholders = str_repeat('?,', count($edificioIds) - 1) . '?';
        
        $sql = "SELECT COUNT(*) as total 
                FROM mantenimientos 
                WHERE edificio_id IN ($placeholders) AND prioridad = 'urgente' AND estado IN ('pendiente', 'en_proceso')";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($edificioIds);
        $result = $stmt->fetch();
        
        return $result['total'] ?? 0;
    }
    
    /**
     * Obtiene total de departamentos
     */
    private function getTotalDepartamentos($edificioIds) {
        if (empty($edificioIds)) return 0;
        
        $placeholders = str_repeat('?,', count($edificioIds) - 1) . '?';
        
        $sql = "SELECT COUNT(*) as total 
                FROM departamentos 
                WHERE edificio_id IN ($placeholders)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($edificioIds);
        $result = $stmt->fetch();
        
        return $result['total'] ?? 0;
    }
    
    /**
     * Obtiene reservas recientes para el dashboard
     */
    private function getReservasRecientes($userId, $limit = 5) {
        $userEdificios = $this->getUserAccessibleEdificios();
        $edificioIds = array_column($userEdificios, 'id');
        
        if (empty($edificioIds)) return [];
        
        $placeholders = str_repeat('?,', count($edificioIds) - 1) . '?';
        
        $sql = "SELECT r.*, a.nombre as amenity_nombre, d.numero as departamento_numero
                FROM reservas r
                JOIN amenities a ON r.amenity_id = a.id
                JOIN departamentos d ON r.departamento_id = d.id
                WHERE a.edificio_id IN ($placeholders) 
                ORDER BY r.created_at DESC 
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $params = array_merge($edificioIds, [$limit]);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Obtiene amenities más populares
     */
    private function getAmenitiesPopulares($userId, $limit = 5) {
        $userEdificios = $this->getUserAccessibleEdificios();
        $edificioIds = array_column($userEdificios, 'id');
        
        if (empty($edificioIds)) return [];
        
        $placeholders = str_repeat('?,', count($edificioIds) - 1) . '?';
        
        $sql = "SELECT a.nombre, a.tipo, COUNT(r.id) as total_reservas
                FROM amenities a
                LEFT JOIN reservas r ON a.id = r.amenity_id AND r.estado = 'confirmada'
                WHERE a.edificio_id IN ($placeholders)
                GROUP BY a.id, a.nombre, a.tipo
                ORDER BY total_reservas DESC 
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $params = array_merge($edificioIds, [$limit]);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Obtiene número de reservas para hoy
     */
    private function getReservasHoy($edificioIds) {
        if (empty($edificioIds)) return 0;
        
        $placeholders = str_repeat('?,', count($edificioIds) - 1) . '?';
        $hoy = date('Y-m-d');
        
        $sql = "SELECT COUNT(*) as total 
                FROM reservas r
                JOIN amenities a ON r.amenity_id = a.id
                WHERE a.edificio_id IN ($placeholders) AND r.fecha_reserva = ? AND r.estado = 'confirmada'";
        
        $stmt = $this->db->prepare($sql);
        $params = array_merge($edificioIds, [$hoy]);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['total'] ?? 0;
    }
    
    /**
     * Obtiene porcentaje de pagos del mes
     */
    private function getPorcentajePagos($edificioIds) {
        if (empty($edificioIds)) return '0%';
        
        $placeholders = str_repeat('?,', count($edificioIds) - 1) . '?';
        $mesActual = date('Y-m');
        
        try {
            $sql = "SELECT 
                    COUNT(*) as total_gastos,
                    COUNT(CASE WHEN p.estado = 'pagado' THEN 1 END) as gastos_pagados
                    FROM gastos_comunes gc
                    LEFT JOIN pagos p ON gc.id = p.gasto_comun_id
                    WHERE gc.edificio_id IN ($placeholders) AND DATE_FORMAT(gc.periodo, '%Y-%m') = ?";
            
            $stmt = $this->db->prepare($sql);
            $params = array_merge($edificioIds, [$mesActual]);
            $stmt->execute($params);
            $result = $stmt->fetch();
            
            if ($result && $result['total_gastos'] > 0) {
                $porcentaje = ($result['gastos_pagados'] / $result['total_gastos']) * 100;
                return round($porcentaje) . '%';
            }
        } catch (Exception $e) {
            error_log("Error al calcular porcentaje de pagos: " . $e->getMessage());
        }
        
        return '0%';
    }
    
    /**
     * Obtiene notificaciones recientes
     */
    private function getRecentNotifications($userId, $limit = 5) {
        try {
            $sql = "SELECT titulo, mensaje, tipo, created_at 
                    FROM notificaciones 
                    WHERE user_id = ? 
                    ORDER BY created_at DESC 
                    LIMIT ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId, $limit]);
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Error al obtener notificaciones: " . $e->getMessage());
            
            return [
                [
                    'titulo' => 'Bienvenido al Sistema',
                    'mensaje' => 'Su cuenta ha sido activada correctamente',
                    'tipo' => 'success',
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ];
        }
    }
    
    /**
     * Obtiene notificaciones importantes
     */
    private function getImportantNotifications($userId) {
        try {
            $sql = "SELECT titulo, mensaje, tipo, created_at 
                    FROM notificaciones 
                    WHERE user_id = ? AND tipo IN ('warning', 'error', 'urgent') AND is_read = 0
                    ORDER BY created_at DESC 
                    LIMIT 10";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Error al obtener notificaciones importantes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene mantenimientos pendientes para el dashboard
     */
    private function getMantenimientosPendientes($userId, $limit = 5) {
        $userEdificios = $this->getUserAccessibleEdificios();
        $edificioIds = array_column($userEdificios, 'id');
        
        if (empty($edificioIds)) return [];
        
        $placeholders = str_repeat('?,', count($edificioIds) - 1) . '?';
        
        $sql = "SELECT m.*, e.nombre as edificio_nombre 
                FROM mantenimientos m
                JOIN edificios e ON m.edificio_id = e.id
                WHERE m.edificio_id IN ($placeholders) AND m.estado IN ('pendiente', 'en_proceso')
                ORDER BY 
                    CASE m.prioridad 
                        WHEN 'urgente' THEN 1
                        WHEN 'alta' THEN 2
                        WHEN 'media' THEN 3
                        WHEN 'baja' THEN 4
                    END,
                    m.fecha_solicitud DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $params = array_merge($edificioIds, [$limit]);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }

    // ==================== MÉTODOS DE RESERVAS (MANTENIDOS) ====================

    /**
     * Obtiene el número de reservas activas del usuario actual
     */
    private function getMisReservasActivas($userId) {
        try {
            $sql = "SELECT COUNT(*) as total 
                    FROM reservas 
                    WHERE created_by = ? 
                    AND estado IN ('confirmada', 'pendiente')
                    AND fecha_reserva >= CURDATE()";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $result = $stmt->fetch();
            
            return $result['total'] ?? 0;
            
        } catch (Exception $e) {
            error_log("Error al obtener mis reservas activas: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtiene el total de reservas del mes actual
     */
    private function getTotalReservasMes($edificioIds) {
        if (empty($edificioIds)) return 0;
        
        $placeholders = str_repeat('?,', count($edificioIds) - 1) . '?';
        $mes_actual = date('Y-m');
        
        try {
            $sql = "SELECT COUNT(*) as total 
                    FROM reservas r
                    JOIN amenities a ON r.amenity_id = a.id
                    WHERE a.edificio_id IN ($placeholders)
                    AND DATE_FORMAT(r.fecha_reserva, '%Y-%m') = ?";
            
            $stmt = $this->db->prepare($sql);
            $params = array_merge($edificioIds, [$mes_actual]);
            $stmt->execute($params);
            $result = $stmt->fetch();
            
            return $result['total'] ?? 0;
            
        } catch (Exception $e) {
            error_log("Error al obtener total reservas mes: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtiene reservas confirmadas para hoy
     */
    private function getReservasConfirmadasHoy($edificioIds) {
        if (empty($edificioIds)) return 0;
        
        $placeholders = str_repeat('?,', count($edificioIds) - 1) . '?';
        $hoy = date('Y-m-d');
        
        try {
            $sql = "SELECT COUNT(*) as total 
                    FROM reservas r
                    JOIN amenities a ON r.amenity_id = a.id
                    WHERE a.edificio_id IN ($placeholders) 
                    AND r.fecha_reserva = ? 
                    AND r.estado = 'confirmada'";
            
            $stmt = $this->db->prepare($sql);
            $params = array_merge($edificioIds, [$hoy]);
            $stmt->execute($params);
            $result = $stmt->fetch();
            
            return $result['total'] ?? 0;
            
        } catch (Exception $e) {
            error_log("Error al obtener reservas confirmadas hoy: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtiene las próximas reservas del usuario
     */
    private function getProximasReservasUsuario($userId, $limit = 5) {
        try {
            $sql = "SELECT r.*, a.nombre as amenity_nombre, a.tipo, e.nombre as edificio_nombre
                    FROM reservas r
                    JOIN amenities a ON r.amenity_id = a.id
                    JOIN edificios e ON a.edificio_id = e.id
                    WHERE r.created_by = ? 
                    AND r.estado = 'confirmada'
                    AND r.fecha_reserva >= CURDATE()
                    ORDER BY r.fecha_reserva ASC, r.hora_inicio ASC
                    LIMIT ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId, $limit]);
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Error al obtener próximas reservas: " . $e->getMessage());
            return [];
        }
    }

    // ==================== MÉTODOS DE AMENITIES (MANTENIDOS) ====================

    /**
     * Obtiene amenities que requieren aprobación administrativa
     */
    private function getAmenitiesRequierenAprobacion($edificioIds) {
        if (empty($edificioIds)) return 0;
        
        $placeholders = str_repeat('?,', count($edificioIds) - 1) . '?';
        
        $sql = "SELECT COUNT(*) as total 
                FROM amenities 
                WHERE edificio_id IN ($placeholders) 
                AND is_active = 1 
                AND requiere_aprobacion = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($edificioIds);
        $result = $stmt->fetch();
        
        return $result['total'] ?? 0;
    }

    /**
     * Obtiene amenities con conflictos de horario
     */
    private function getAmenitiesConConflictos($edificioIds) {
        if (empty($edificioIds)) return 0;
        
        $placeholders = str_repeat('?,', count($edificioIds) - 1) . '?';
        $hoy = date('Y-m-d');
        
        $sql = "SELECT COUNT(DISTINCT a.id) as total
                FROM amenities a
                JOIN reservas r1 ON a.id = r1.amenity_id
                JOIN reservas r2 ON a.id = r2.amenity_id 
                WHERE a.edificio_id IN ($placeholders)
                AND r1.fecha_reserva = ?
                AND r2.fecha_reserva = ?
                AND r1.id != r2.id
                AND r1.estado = 'confirmada'
                AND r2.estado = 'confirmada'
                AND (
                    (r1.hora_inicio BETWEEN r2.hora_inicio AND r2.hora_fin) OR
                    (r1.hora_fin BETWEEN r2.hora_inicio AND r2.hora_fin) OR
                    (r2.hora_inicio BETWEEN r1.hora_inicio AND r1.hora_fin)
                )";
        
        $stmt = $this->db->prepare($sql);
        $params = array_merge($edificioIds, [$hoy, $hoy]);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['total'] ?? 0;
    }

    /**
     * Obtiene amenities que requieren atención (aprobación + conflictos)
     */
    private function getAmenitiesRequierenAtencion($userId, $limit = 10) {
        $userEdificios = $this->getUserAccessibleEdificios();
        $edificioIds = array_column($userEdificios, 'id');
        
        if (empty($edificioIds)) return [];
        
        $placeholders = str_repeat('?,', count($edificioIds) - 1) . '?';
        $hoy = date('Y-m-d');
        
        $sql = "SELECT 
                    a.id,
                    a.nombre,
                    a.tipo,
                    e.nombre as edificio_nombre,
                    'requiere_aprobacion' as tipo_atencion,
                    'Requiere aprobación administrativa' as motivo,
                    NULL as fecha_conflicto
                FROM amenities a
                JOIN edificios e ON a.edificio_id = e.id
                WHERE a.edificio_id IN ($placeholders)
                AND a.is_active = 1
                AND a.requiere_aprobacion = 1
                
                UNION ALL
                
                SELECT 
                    a.id,
                    a.nombre,
                    a.tipo,
                    e.nombre as edificio_nombre,
                    'conflicto_horario' as tipo_atencion,
                    'Conflicto de horario detectado' as motivo,
                    r1.fecha_reserva as fecha_conflicto
                FROM amenities a
                JOIN edificios e ON a.edificio_id = e.id
                JOIN reservas r1 ON a.id = r1.amenity_id
                JOIN reservas r2 ON a.id = r2.amenity_id 
                WHERE a.edificio_id IN ($placeholders)
                AND r1.fecha_reserva = ?
                AND r2.fecha_reserva = ?
                AND r1.id != r2.id
                AND r1.estado = 'confirmada'
                AND r2.estado = 'confirmada'
                AND (
                    (r1.hora_inicio BETWEEN r2.hora_inicio AND r2.hora_fin) OR
                    (r1.hora_fin BETWEEN r2.hora_inicio AND r2.hora_fin) OR
                    (r2.hora_inicio BETWEEN r1.hora_inicio AND r1.hora_fin)
                )
                
                ORDER BY tipo_atencion, nombre
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $params = array_merge($edificioIds, $edificioIds, [$hoy, $hoy, $limit]);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }

    /**
     * Obtiene estadísticas de uso por tipo de amenity
     */
    private function getEstadisticasUsoAmenities($userId) {
        $userEdificios = $this->getUserAccessibleEdificios();
        $edificioIds = array_column($userEdificios, 'id');
        
        if (empty($edificioIds)) return [];
        
        $placeholders = str_repeat('?,', count($edificioIds) - 1) . '?';
        $mes_actual = date('Y-m');
        
        $sql = "SELECT 
                    a.tipo,
                    COUNT(DISTINCT a.id) as total_amenities,
                    COUNT(r.id) as total_reservas,
                    ROUND(COUNT(r.id) / COUNT(DISTINCT a.id), 1) as promedio_uso
                FROM amenities a
                LEFT JOIN reservas r ON a.id = r.amenity_id 
                    AND r.estado = 'confirmada'
                    AND DATE_FORMAT(r.created_at, '%Y-%m') = ?
                WHERE a.edificio_id IN ($placeholders)
                AND a.is_active = 1
                GROUP BY a.tipo
                ORDER BY total_reservas DESC";
        
        $stmt = $this->db->prepare($sql);
        $params = array_merge([$mes_actual], $edificioIds);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }

    /**
     * Obtiene badge class para prioridades
     */
    protected function getPriorityBadge($prioridad) {
        switch ($prioridad) {
            case 'urgente': return 'danger';
            case 'alta': return 'warning';
            case 'media': return 'info';
            case 'baja': return 'secondary';
            default: return 'secondary';
        }
    }
}