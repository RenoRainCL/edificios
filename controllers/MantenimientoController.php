<?php

// 📁 controllers/MantenimientoController.php

class MantenimientoController extends ControllerCore
{
    public function __construct()
    {
        parent::__construct();
    }


    public function index() {
        $userEdificios = $this->getUserEdificios($_SESSION['user_id']);
        $edificioId = $_GET['edificio_id'] ?? null;
        
        // DEBUG
        error_log("🎯 Módulo Mantenimiento - INDEX");
        error_log("🎯 edificioId desde GET: '" . $edificioId . "'");
        
        // Obtener filtros adicionales - CORREGIDO: usar nombres consistentes
        $filtros = [
            'estado' => $_GET['estado'] ?? null,
            'prioridad' => $_GET['prioridad'] ?? null
        ];
        
        error_log("🎯 Filtros adicionales: " . print_r($filtros, true));
        
        // CORRECCIÓN: POR DEFECTO MOSTRAR "TODOS" EN LUGAR DEL PRIMER EDIFICIO
        if (empty($edificioId) && !empty($userEdificios)) {
            $edificioId = 'todos';
            error_log("🎯 Usando valor por defecto: 'todos'");
        }
        
        // Lógica para determinar qué mostrar CON FILTROS APLICADOS
        if ($edificioId === 'todos') {
            error_log("🎯 Modo: MOSTRAR TODOS LOS EDIFICIOS");
            $mantenimientos = $this->getMantenimientosTodosEdificios($_SESSION['user_id']);
            $edificioActual = null;
        } elseif (!empty($edificioId)) {
            error_log("🎯 Modo: EDIFICIO ESPECÍFICO - " . $edificioId);
            $this->checkEdificioAccess($edificioId);
            $mantenimientos = $this->getMantenimientosEdificio($edificioId, $filtros); // Pasar filtros aquí
            $edificioActual = $this->getEdificioById($edificioId);
        } else {
            error_log("🎯 Modo: SIN EDIFICIOS DISPONIBLES");
            $mantenimientos = [];
            $edificioActual = null;
        }
        
        // Si estamos en modo "todos", aplicar filtros adicionales manualmente
        if ($edificioId === 'todos' && (!empty($filtros['estado']) || !empty($filtros['prioridad']))) {
            error_log("🎯 Aplicando filtros adicionales a todos los edificios");
            $mantenimientos = $this->aplicarFiltrosAdicionales($mantenimientos, $filtros);
        }
        
        error_log("🎯 Total mantenimientos encontrados después de filtros: " . count($mantenimientos));
        
        $data = [
            'mantenimientos' => $mantenimientos,
            'edificios' => $userEdificios,
            'edificio_actual' => $edificioActual,
            'user_name' => $_SESSION['user_name'],
            'estadisticas' => $this->getEstadisticasMantenimiento($edificioId === 'todos' ? null : $edificioId),
            'flash_messages' => $this->getFlashMessages(),
            'filtro_edificio_actual' => $edificioId,
            'filtro_estado_actual' => $filtros['estado'], // Pasar a la vista
            'filtro_prioridad_actual' => $filtros['prioridad'] // Pasar a la vista
        ];
        
        $this->renderView('mantenimiento/index', $data);
    }

    public function crear()
    {
        $userEdificios = $this->getUserEdificios($_SESSION['user_id']);

        if (empty($userEdificios)) {
            $this->addFlashMessage('error', 'Primero debes crear un edificio');
            $this->redirect('edificios/crear');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->crearMantenimiento($_POST);
        }

        $data = [
            'user_name' => $_SESSION['user_name'],
            'edificios' => $userEdificios,
            'tipos_mantenimiento' => $this->getTiposMantenimiento(),
            'prioridades' => $this->getPrioridades(),
            'areas_comunes' => $this->getAreasComunes(),
            'flash_messages' => $this->getFlashMessages(),
        ];

        $this->renderView('mantenimiento/crear', $data);
    }

    public function editar($mantenimientoId)
    {
        $mantenimiento = $this->getMantenimientoById($mantenimientoId);

        if (!$mantenimiento) {
            $this->addFlashMessage('error', 'Solicitud de mantenimiento no encontrada');
            $this->redirect('mantenimiento');
        }

        $this->checkEdificioAccess($mantenimiento['edificio_id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->actualizarMantenimiento($mantenimientoId, $_POST);
        }

        $userEdificios = $this->getUserEdificios($_SESSION['user_id']);

        $data = [
            'mantenimiento' => $mantenimiento,
            'edificios' => $userEdificios,
            'tipos_mantenimiento' => $this->getTiposMantenimiento(),
            'prioridades' => $this->getPrioridades(),
            'areas_comunes' => $this->getAreasComunes(),
            'user_name' => $_SESSION['user_name'],
            'flash_messages' => $this->getFlashMessages(),
        ];

        $this->renderView('mantenimiento/editar', $data);
    }

    public function ver($mantenimientoId) {
        $mantenimiento = $this->getMantenimientoById($mantenimientoId);
        
        if (!$mantenimiento) {
            $this->addFlashMessage('error', 'Solicitud de mantenimiento no encontrada');
            $this->redirect('mantenimiento');
        }
        
        $this->checkEdificioAccess($mantenimiento['edificio_id']);
        
        // Cargar datos adicionales de forma segura
        $data = [
            'mantenimiento' => $mantenimiento,
            'user_name' => $_SESSION['user_name'],
            'historial' => $this->getHistorialMantenimiento($mantenimientoId),
            'documentos' => $this->getDocumentosMantenimiento($mantenimientoId)
        ];
        
        // Si hay error al cargar datos adicionales, continuar sin ellos
        if (empty($data['historial'])) {
            $data['historial'] = [];
        }
        
        if (empty($data['documentos'])) {
            $data['documentos'] = [];
        }
        
        $this->renderView('mantenimiento/ver', $data);
    }

    public function cambiarEstado($mantenimientoId)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->actualizarEstadoMantenimiento($mantenimientoId, $_POST);
        }
    }

    // ========== MÉTODOS PRIVADOS ==========

    private function crearMantenimiento($data)
    {
        $errors = $this->validateInput($data, [
            'edificio_id' => 'required|numeric',
            'tipo' => 'required',
            'titulo' => 'required|min:5',
            'prioridad' => 'required',
        ]);

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->addFlashMessage('error', $error);
            }
            $this->redirect('mantenimiento/crear');
        }

        try {
            $this->checkEdificioAccess($data['edificio_id']);

            $sql = 'INSERT INTO mantenimientos (
                    edificio_id, tipo, titulo, descripcion, area, prioridad, 
                    fecha_programada, costo_estimado, proveedor, contacto_proveedor,
                    observaciones, created_by, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())';

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['edificio_id'],
                $data['tipo'],
                $data['titulo'],
                $data['descripcion'] ?? null,
                $data['area'] ?? null,
                $data['prioridad'],
                !empty($data['fecha_programada']) ? $data['fecha_programada'] : null,
                !empty($data['costo_estimado']) ? $data['costo_estimado'] : null,
                $data['proveedor'] ?? null,
                $data['contacto_proveedor'] ?? null,
                $data['observaciones'] ?? null,
                $_SESSION['user_id'],
            ]);

            $mantenimientoId = $this->db->lastInsertId();

            // Crear notificación
            $this->notificarNuevoMantenimiento($data['edificio_id'], $mantenimientoId, $data['titulo']);

            $this->addFlashMessage('success', 'Solicitud de mantenimiento creada exitosamente');
            $this->redirect('mantenimiento?edificio_id='.$data['edificio_id']);
        } catch (Exception $e) {
            error_log('Error al crear mantenimiento: '.$e->getMessage());
            $this->addFlashMessage('error', 'Error al crear la solicitud: '.$e->getMessage());
            $this->redirect('mantenimiento/crear');
        }
    }

    private function actualizarMantenimiento($mantenimientoId, $data) {
        $errors = $this->validateInput($data, [
            'edificio_id' => 'required|numeric',
            'tipo' => 'required',
            'titulo' => 'required|min:5',
            'prioridad' => 'required'
        ]);
        
        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->addFlashMessage('error', $error);
            }
            $this->redirect('mantenimiento/editar/' . $mantenimientoId);
        }
        
        try {
            $this->checkEdificioAccess($data['edificio_id']);
            
            $sql = "UPDATE mantenimientos SET 
                    edificio_id = ?, 
                    tipo = ?, 
                    titulo = ?, 
                    descripcion = ?, 
                    area = ?, 
                    prioridad = ?, 
                    fecha_programada = ?, 
                    costo_estimado = ?, 
                    costo_real = ?,  <!-- NUEVO: Actualizar costo real -->
                    proveedor = ?, 
                    contacto_proveedor = ?,
                    observaciones = ?,
                    updated_at = NOW()
                    WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['edificio_id'],
                $data['tipo'],
                $data['titulo'],
                $data['descripcion'] ?? null,
                $data['area'] ?? null,
                $data['prioridad'],
                !empty($data['fecha_programada']) ? $data['fecha_programada'] : null,
                !empty($data['costo_estimado']) ? $data['costo_estimado'] : null,
                !empty($data['costo_real']) ? $data['costo_real'] : null,  // NUEVO
                $data['proveedor'] ?? null,
                $data['contacto_proveedor'] ?? null,
                $data['observaciones'] ?? null,
                $mantenimientoId
            ]);
            
            $this->addFlashMessage('success', 'Solicitud de mantenimiento actualizada exitosamente');
            $this->redirect('mantenimiento?edificio_id=' . $data['edificio_id']);
            
        } catch (Exception $e) {
            error_log("Error al actualizar mantenimiento: " . $e->getMessage());
            $this->addFlashMessage('error', 'Error al actualizar la solicitud: ' . $e->getMessage());
            $this->redirect('mantenimiento/editar/' . $mantenimientoId);
        }
    }

    private function actualizarEstadoMantenimiento($mantenimientoId, $data) {
        try {
            // VALIDACIÓN: Si se completa, requerir costo real
            if ($data['estado'] === 'completado' && (empty($data['costo_real']) || $data['costo_real'] <= 0)) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'El costo real es obligatorio cuando se completa el mantenimiento'
                ]);
                return;
            }
            
            $sql = "UPDATE mantenimientos SET 
                    estado = ?, 
                    fecha_completada = ?,
                    costo_real = ?,
                    observaciones = CONCAT(IFNULL(observaciones, ''), ?),
                    updated_at = NOW()
                    WHERE id = ?";
            
            $fechaCompletada = ($data['estado'] === 'completado') ? date('Y-m-d H:i:s') : null;
            
            // Si se cancela o no se completa, mantener el costo real existente o null
            $costoReal = ($data['estado'] === 'completado') ? $data['costo_real'] : null;
            
            $observacionAdicional = "\n\n--- Cambio de estado: " . $data['estado'] . " - " . date('d/m/Y H:i') . " ---\n" . ($data['observacion_adicional'] ?? '');
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['estado'],
                $fechaCompletada,
                $costoReal,
                $observacionAdicional,
                $mantenimientoId
            ]);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Estado actualizado exitosamente'
            ]);
            
        } catch (Exception $e) {
            error_log("Error al actualizar estado: " . $e->getMessage());
            echo json_encode([
                'success' => false, 
                'message' => 'Error al actualizar el estado: ' . $e->getMessage()
            ]);
        }
    }

    private function getMantenimientoById($mantenimientoId)
    {
        $sql = 'SELECT m.*, e.nombre as edificio_nombre, u.nombre as creador_nombre
                FROM mantenimientos m
                JOIN edificios e ON m.edificio_id = e.id
                LEFT JOIN users u ON m.created_by = u.id
                WHERE m.id = ?';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$mantenimientoId]);

        return $stmt->fetch();
    }

    private function getMantenimientosEdificio($edificioId, $filtros = [])
    {
        $sql = 'SELECT m.*, e.nombre as edificio_nombre, u.nombre as creador_nombre
                FROM mantenimientos m
                JOIN edificios e ON m.edificio_id = e.id
                LEFT JOIN users u ON m.created_by = u.id
                WHERE m.edificio_id = ?';

        $params = [$edificioId];

        // Aplicar filtros - CORREGIDO: verificar que no estén vacíos
        if (!empty($filtros['estado']) && in_array($filtros['estado'], ['pendiente', 'en_proceso', 'completado', 'cancelado'])) {
            $sql .= ' AND m.estado = ?';
            $params[] = $filtros['estado'];
            error_log("🎯 Aplicando filtro estado: " . $filtros['estado']);
        }

        if (!empty($filtros['prioridad']) && in_array($filtros['prioridad'], ['baja', 'media', 'alta', 'urgente'])) {
            $sql .= ' AND m.prioridad = ?';
            $params[] = $filtros['prioridad'];
            error_log("🎯 Aplicando filtro prioridad: " . $filtros['prioridad']);
        }

        $sql .= " ORDER BY 
                CASE m.prioridad 
                    WHEN 'urgente' THEN 1
                    WHEN 'alta' THEN 2
                    WHEN 'media' THEN 3
                    WHEN 'baja' THEN 4
                END,
                m.fecha_solicitud DESC";

        error_log("🎯 SQL con filtros: " . $sql);
        error_log("🎯 Parámetros: " . print_r($params, true));

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Obtiene mantenimientos de todos los edificios del usuario
     */
    private function getMantenimientosTodosEdificios($userId) {
        $userEdificios = $this->getUserEdificios($userId);
        $edificioIds = array_column($userEdificios, 'id');
        
        if (empty($edificioIds)) {
            return [];
        }
        
        $placeholders = str_repeat('?,', count($edificioIds) - 1) . '?';
        
        $sql = "SELECT m.*, e.nombre as edificio_nombre, u.nombre as creador_nombre
                FROM mantenimientos m
                JOIN edificios e ON m.edificio_id = e.id
                LEFT JOIN users u ON m.created_by = u.id
                WHERE m.edificio_id IN ($placeholders)
                ORDER BY 
                    e.nombre ASC,
                    CASE m.prioridad 
                        WHEN 'urgente' THEN 1
                        WHEN 'alta' THEN 2
                        WHEN 'media' THEN 3
                        WHEN 'baja' THEN 4
                    END,
                    m.fecha_solicitud DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($edificioIds);
        
        return $stmt->fetchAll();
    }

    private function getEstadisticasMantenimiento($edificioId = null) {
        if (!$edificioId) {
            // Estadísticas para todos los edificios
            return $this->getEstadisticasTodosEdificios($_SESSION['user_id']);
        }
        
        // Estadísticas para un edificio específico (código existente)
        $sql = "SELECT 
                COUNT(*) as total,
                COUNT(CASE WHEN estado = 'pendiente' THEN 1 END) as pendientes,
                COUNT(CASE WHEN estado = 'en_proceso' THEN 1 END) as en_proceso,
                COUNT(CASE WHEN estado = 'completado' THEN 1 END) as completados,
                COUNT(CASE WHEN prioridad = 'urgente' THEN 1 END) as urgentes,
                SUM(CASE WHEN estado = 'completado' THEN costo_real ELSE 0 END) as total_gastado
                FROM mantenimientos 
                WHERE edificio_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$edificioId]);
        return $stmt->fetch();
    }

    /**
     * Obtiene estadísticas de todos los edificios del usuario
     */
    private function getEstadisticasTodosEdificios($userId) {
        $userEdificios = $this->getUserEdificios($userId);
        $edificioIds = array_column($userEdificios, 'id');
        
        if (empty($edificioIds)) {
            return [
                'total' => 0,
                'pendientes' => 0,
                'en_proceso' => 0,
                'completados' => 0,
                'urgentes' => 0,
                'total_gastado' => 0
            ];
        }
        
        $placeholders = str_repeat('?,', count($edificioIds) - 1) . '?';
        
        $sql = "SELECT 
                COUNT(*) as total,
                COUNT(CASE WHEN estado = 'pendiente' THEN 1 END) as pendientes,
                COUNT(CASE WHEN estado = 'en_proceso' THEN 1 END) as en_proceso,
                COUNT(CASE WHEN estado = 'completado' THEN 1 END) as completados,
                COUNT(CASE WHEN prioridad = 'urgente' THEN 1 END) as urgentes,
                SUM(CASE WHEN estado = 'completado' THEN costo_real ELSE 0 END) as total_gastado
                FROM mantenimientos 
                WHERE edificio_id IN ($placeholders)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($edificioIds);
        return $stmt->fetch();
    }

    private function getHistorialMantenimiento($mantenimientoId)
    {
        $sql = "SELECT * FROM audit_log 
                WHERE entity_type = 'mantenimiento' AND entity_id = ? 
                ORDER BY created_at DESC 
                LIMIT 10";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$mantenimientoId]);

        return $stmt->fetchAll();
    }

    private function getDocumentosMantenimiento($mantenimientoId) {
        // Verificar si la columna related_entity_type existe
        // Si no existe, usar una consulta alternativa o retornar array vacío
        try {
            // Primero verificamos la estructura de la tabla
            $sqlCheck = "SHOW COLUMNS FROM documentos_legales LIKE 'related_entity_type'";
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->execute();
            $columnExists = $stmtCheck->fetch();
            
            if ($columnExists) {
                $sql = "SELECT * FROM documentos_legales 
                        WHERE related_entity_type = 'mantenimiento' AND related_entity_id = ? 
                        ORDER BY created_at DESC";
            } else {
                // Si la columna no existe, retornar array vacío
                return [];
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$mantenimientoId]);
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Error al obtener documentos de mantenimiento: " . $e->getMessage());
            return [];
        }
    }

    private function notificarNuevoMantenimiento($edificioId, $mantenimientoId, $titulo)
    {
        // Obtener administradores del edificio
        $admins = $this->getAdministradoresEdificio($edificioId);

        foreach ($admins as $admin) {
            $this->notificationManager->createNotification(
                $admin['user_id'],
                'warning',
                'Nueva Solicitud de Mantenimiento',
                'Se ha creado una nueva solicitud: '.$titulo,
                'mantenimiento',
                $mantenimientoId
            );
        }
    }

    private function getAdministradoresEdificio($edificioId)
    {
        $sql = "SELECT user_id FROM user_edificio_relations 
                WHERE edificio_id = ? AND (is_primary_admin = 1 OR permissions LIKE '%\"mantenimiento\":[\"write\"]%')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$edificioId]);

        return $stmt->fetchAll();
    }

    private function getTiposMantenimiento()
    {
        return [
            'preventivo' => 'Mantenimiento Preventivo',
            'correctivo' => 'Mantenimiento Correctivo',
            'urgente' => 'Reparación Urgente',
            'mejora' => 'Mejora o Renovación',
        ];
    }

    private function getPrioridades()
    {
        return [
            'baja' => 'Baja',
            'media' => 'Media',
            'alta' => 'Alta',
            'urgente' => 'Urgente',
        ];
    }

    private function getAreasComunes()
    {
        return [
            'Ascensores',
            'Estacionamiento',
            'Piscina',
            'Gimnasio',
            'Jardines',
            'Terraza',
            'Salón de Eventos',
            'Pasillos',
            'Fachada',
            'Sistema Eléctrico',
            'Sistema de Agua',
            'Sistema de Gas',
            'Otro',
        ];
    }

    private function getTotalGastadoMantenimiento($edificioId, $periodo = null) {
        $sql = "SELECT SUM(costo_real) as total_gastado 
                FROM mantenimientos 
                WHERE edificio_id = ? AND estado = 'completado'";
        
        $params = [$edificioId];
        
        if ($periodo) {
            switch ($periodo) {
                case 'mes_actual':
                    $sql .= " AND YEAR(fecha_completada) = YEAR(CURDATE()) AND MONTH(fecha_completada) = MONTH(CURDATE())";
                    break;
                case 'trimestre_actual':
                    $sql .= " AND YEAR(fecha_completada) = YEAR(CURDATE()) AND QUARTER(fecha_completada) = QUARTER(CURDATE())";
                    break;
                case 'anio_actual':
                    $sql .= " AND YEAR(fecha_completada) = YEAR(CURDATE())";
                    break;
                case 'ultimos_30_dias':
                    $sql .= " AND fecha_completada >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
                    break;
            }
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['total_gastado'] ?? 0;
    }    

    /**
     * Aplica filtros adicionales al array de mantenimientos
     */
    private function aplicarFiltrosAdicionales($mantenimientos, $filtros) {
        error_log("🎯 Aplicando filtros PHP a " . count($mantenimientos) . " mantenimientos");
        
        return array_filter($mantenimientos, function($mantenimiento) use ($filtros) {
            $cumpleFiltro = true;
            
            if (!empty($filtros['estado']) && $mantenimiento['estado'] !== $filtros['estado']) {
                $cumpleFiltro = false;
                error_log("🎯 Filtrado por estado: " . $mantenimiento['estado'] . " != " . $filtros['estado']);
            }
            
            if (!empty($filtros['prioridad']) && $mantenimiento['prioridad'] !== $filtros['prioridad']) {
                $cumpleFiltro = false;
                error_log("🎯 Filtrado por prioridad: " . $mantenimiento['prioridad'] . " != " . $filtros['prioridad']);
            }
            
            if ($cumpleFiltro) {
                error_log("🎯 Mantenimiento " . $mantenimiento['id'] . " pasa los filtros");
            }
            
            return $cumpleFiltro;
        });
    }  

}
