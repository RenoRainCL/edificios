<?php
// 📁 controllers/ReservasController.php

class ReservasController extends ControllerCore {
    
    public function __construct() {
        parent::__construct();
    }

    // ==================== VISTAS PRINCIPALES ====================

    /**
     * Calendario interactivo de reservas
     */
    public function calendario() {
        $userEdificios = $this->getUserEdificios($_SESSION['user_id']);
        $edificioId = $_GET['edificio_id'] ?? null;
        $amenityId = $_GET['amenity_id'] ?? null;
        $fecha = $_GET['fecha'] ?? date('Y-m-d');
        
        // Si no se especifica edificio, usar el primero disponible
        if (!$edificioId && !empty($userEdificios)) {
            $edificioId = $userEdificios[0]['id'];
        }
        
        $amenities = [];
        $reservas = [];
        $amenitySeleccionado = null;
        
        if ($edificioId) {
            $this->checkEdificioAccess($edificioId);
            $amenities = $this->getAmenitiesEdificio($edificioId);
            
            // Si se selecciona un amenity específico
            if ($amenityId) {
                $amenitySeleccionado = $this->getAmenityById($amenityId);
                $reservas = $this->getReservasAmenity($amenityId, $fecha);
            }
        }

        $data = [
            'amenities' => $amenities,
            'amenity_seleccionado' => $amenitySeleccionado,
            'reservas' => $reservas,
            'edificios' => $userEdificios,
            'edificio_actual' => $edificioId ? $this->getEdificioById($edificioId) : null,
            'fecha_actual' => $fecha,
            'user_name' => $_SESSION['user_name'],
            'flash_messages' => $this->getFlashMessages(),
            'departamentos_usuario' => $this->getDepartamentosUsuario($_SESSION['user_id'])
        ];
        
        $this->renderView('reservas/calendario', $data);
    }

    /**
     * Vista de mis reservas (usuario)
     */
    public function misReservas() {
        $reservas = $this->getReservasUsuario($_SESSION['user_id']);
        $estadisticas = $this->getEstadisticasUsuario($_SESSION['user_id']);
        
        // Asegurar que las estadísticas tengan valores por defecto
        $estadisticas = array_merge([
            'total_reservas' => 0,
            'confirmadas' => 0,
            'pendientes' => 0,
            'canceladas' => 0,
            'total_gastado' => 0
        ], $estadisticas);
        
        $data = [
            'reservas' => $reservas,
            'user_name' => $_SESSION['user_name'],
            'flash_messages' => $this->getFlashMessages(),
            'estadisticas' => $estadisticas,
            'departamentos_usuario' => $this->getDepartamentosUsuario($_SESSION['user_id'])
        ];
        
        $this->renderView('reservas/mis-reservas', $data);
    }
    /**
     * Panel de aprobaciones (admin)
     */
    public function aprobaciones() {
        $userEdificios = $this->getUserEdificios($_SESSION['user_id']);
        $edificioId = $_GET['edificio_id'] ?? null;
        
        if (!$edificioId && !empty($userEdificios)) {
            $edificioId = $userEdificios[0]['id'];
        }
        
        $reservasPendientes = [];
        $estadisticasAprobacion = [];
        
        if ($edificioId) {
            $this->checkEdificioAccess($edificioId);
            $reservasPendientes = $this->getReservasPendientesAprobacion($edificioId);
            
            // Calcular estadísticas para la vista
            $estadisticasAprobacion = [
                'aprobadas_hoy' => $this->getReservasAprobadasHoy($edificioId),
                'rechazadas_hoy' => $this->getReservasRechazadasHoy($edificioId),
                'tiempo_promedio' => $this->getTiempoPromedioAprobacion($edificioId),
                'amenities_pendientes' => $this->getAmenitiesConPendientes($edificioId),
                'usuarios_pendientes' => $this->getUsuariosConPendientes($edificioId)
            ];
        }

        $data = [
            'reservas_pendientes' => $reservasPendientes,
            'edificios' => $userEdificios,
            'edificio_actual' => $edificioId ? $this->getEdificioById($edificioId) : null,
            'user_name' => $_SESSION['user_name'],
            'flash_messages' => $this->getFlashMessages(),
            'estadisticas_aprobacion' => $estadisticasAprobacion // NUEVO: pasar estadísticas
        ];
        
        $this->renderView('reservas/aprobaciones', $data);
    }

    /**
     * Formulario de creación de reserva
     */
    public function crear() {
        $amenityId = $_GET['amenity_id'] ?? null;
        $fecha = $_GET['fecha'] ?? date('Y-m-d');
        
        if (!$amenityId) {
            $this->addFlashMessage('error', 'Selecciona un amenity para reservar');
            $this->redirect('reservas/calendario');
        }
        
        $amenity = $this->getAmenityById($amenityId);
        if (!$amenity) {
            $this->addFlashMessage('error', 'Amenity no encontrado');
            $this->redirect('reservas/calendario');
        }
        
        $this->checkEdificioAccess($amenity['edificio_id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->crearReserva($_POST);
        }

        $data = [
            'amenity' => $amenity,
            'fecha_seleccionada' => $fecha,
            'departamentos_usuario' => $this->getDepartamentosUsuario($_SESSION['user_id']),
            'horarios_disponibles' => $this->getHorariosDisponibles($amenityId, $fecha),
            'user_name' => $_SESSION['user_name'],
            'flash_messages' => $this->getFlashMessages(),
            'config_amenity' => $this->getConfiguracionAmenity($amenityId)
        ];

        $this->renderView('reservas/crear', $data);
    }

    // ==================== ACCIONES CRUD ====================

    /**
     * Crear nueva reserva
     */
    private function crearReserva($data) {
        $errors = $this->validateInput($data, [
            'amenity_id' => 'required|numeric',
            'departamento_id' => 'required|numeric',
            'fecha_reserva' => 'required|date',
            'hora_inicio' => 'required',
            'hora_fin' => 'required',
            'motivo' => 'required|min:10'
        ]);

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->addFlashMessage('error', $error);
            }
            $this->redirect('reservas/crear?amenity_id=' . $data['amenity_id'] . '&fecha=' . $data['fecha_reserva']);
        }

        try {
            $amenity = $this->getAmenityById($data['amenity_id']);
            $this->checkEdificioAccess($amenity['edificio_id']);
            
            // Verificar disponibilidad
            $disponibilidad = $this->verificarDisponibilidad(
                $data['amenity_id'], 
                $data['fecha_reserva'], 
                $data['hora_inicio'], 
                $data['hora_fin']
            );
            
            if (!$disponibilidad['disponible']) {
                $this->addFlashMessage('error', $disponibilidad['mensaje']);
                $this->redirect('reservas/crear?amenity_id=' . $data['amenity_id'] . '&fecha=' . $data['fecha_reserva']);
            }

            // Verificar límites de usuario
            if (!$this->verificarLimitesUsuario($data['departamento_id'], $data['amenity_id'], $data['fecha_reserva'])) {
                $this->addFlashMessage('error', 'Has alcanzado el límite de reservas para este amenity');
                $this->redirect('reservas/crear?amenity_id=' . $data['amenity_id'] . '&fecha=' . $data['fecha_reserva']);
            }

            // Determinar estado inicial
            $estado = $amenity['requiere_aprobacion'] ? 'pendiente' : 'confirmada';

            $sql = 'INSERT INTO reservas (
                    amenity_id, departamento_id, fecha_reserva, hora_inicio, hora_fin,
                    estado, motivo, numero_asistentes, costo_total, created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

            $costoTotal = $this->calcularCostoReserva($amenity, $data['hora_inicio'], $data['hora_fin']);
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['amenity_id'],
                $data['departamento_id'],
                $data['fecha_reserva'],
                $data['hora_inicio'],
                $data['hora_fin'],
                $estado,
                $data['motivo'],
                $data['numero_asistentes'] ?? 1,
                $costoTotal,
                $_SESSION['user_id']
            ]);

            $reservaId = $this->db->lastInsertId();

            // Notificaciones
            $this->notificarReservaCreada($reservaId, $amenity, $estado);

            $mensaje = $estado === 'confirmada' 
                ? 'Reserva confirmada exitosamente' 
                : 'Solicitud de reserva enviada, esperando aprobación';

            $this->addFlashMessage('success', $mensaje);
            $this->redirect('reservas/mis-reservas');

        } catch (Exception $e) {
            error_log('Error al crear reserva: ' . $e->getMessage());
            $this->addFlashMessage('error', 'Error al crear la reserva: ' . $e->getMessage());
            $this->redirect('reservas/crear?amenity_id=' . $data['amenity_id'] . '&fecha=' . $data['fecha_reserva']);
        }
    }

    /**
     * Cancelar reserva
     */
    public function cancelar($reservaId) {
        $reserva = $this->getReservaById($reservaId);
        
        if (!$reserva) {
            $this->jsonResponse(false, [], 'Reserva no encontrada');
        }

        // Verificar permisos: usuario puede cancelar sus propias reservas, admin cualquier reserva de sus edificios
        if ($reserva['created_by'] != $_SESSION['user_id']) {
            $amenity = $this->getAmenityById($reserva['amenity_id']);
            $this->checkEdificioAccess($amenity['edificio_id']);
        }

        try {
            $sql = "UPDATE reservas SET estado = 'cancelada', updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$reservaId]);

            $this->notificarReservaCancelada($reservaId);
            
            $this->jsonResponse(true, [], 'Reserva cancelada exitosamente');
        } catch (Exception $e) {
            error_log('Error al cancelar reserva: ' . $e->getMessage());
            $this->jsonResponse(false, [], 'Error al cancelar la reserva');
        }
    }

    /**
     * Aprobar reserva
     */
    public function aprobar($reservaId) {
        $reserva = $this->getReservaById($reservaId);
        
        if (!$reserva) {
            $this->jsonResponse(false, [], 'Reserva no encontrada');
        }

        $amenity = $this->getAmenityById($reserva['amenity_id']);
        $this->checkEdificioAccess($amenity['edificio_id']);

        try {
            // Verificar que no haya conflictos al aprobar
            $disponibilidad = $this->verificarDisponibilidad(
                $reserva['amenity_id'],
                $reserva['fecha_reserva'],
                $reserva['hora_inicio'],
                $reserva['hora_fin'],
                $reservaId // Excluir esta reserva del chequeo
            );
            
            if (!$disponibilidad['disponible']) {
                $this->jsonResponse(false, [], 'No se puede aprobar: ' . $disponibilidad['mensaje']);
                return;
            }

            $sql = "UPDATE reservas SET estado = 'confirmada', updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$reservaId]);

            $this->notificarReservaAprobada($reservaId);
            
            $this->jsonResponse(true, [], 'Reserva aprobada exitosamente');
        } catch (Exception $e) {
            error_log('Error al aprobar reserva: ' . $e->getMessage());
            $this->jsonResponse(false, [], 'Error al aprobar la reserva');
        }
    }

    /**
     * Rechazar reserva
     */
    public function rechazar($reservaId) {
        $reserva = $this->getReservaById($reservaId);
        
        if (!$reserva) {
            $this->jsonResponse(false, [], 'Reserva no encontrada');
        }

        $amenity = $this->getAmenityById($reserva['amenity_id']);
        $this->checkEdificioAccess($amenity['edificio_id']);

        try {
            // Obtener motivo del cuerpo de la petición
            $input = json_decode(file_get_contents('php://input'), true);
            $motivo = $input['motivo'] ?? 'Sin motivo especificado';
            
            $sql = "UPDATE reservas SET estado = 'rechazada', motivo_rechazo = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$motivo, $reservaId]);

            $this->notificarReservaRechazada($reservaId);
            
            $this->jsonResponse(true, [], 'Reserva rechazada exitosamente');
        } catch (Exception $e) {
            error_log('Error al rechazar reserva: ' . $e->getMessage());
            $this->jsonResponse(false, [], 'Error al rechazar la reserva');
        }
    }

    // ==================== API/VALIDACIONES ====================

    /**
     * Verificar disponibilidad en tiempo real (API)
     */
    public function verificarDisponibilidad() {
        $amenityId = $_GET['amenity_id'] ?? null;
        $fecha = $_GET['fecha'] ?? null;
        $horaInicio = $_GET['hora_inicio'] ?? null;
        $horaFin = $_GET['hora_fin'] ?? null;
        $excluirReserva = $_GET['excluir_reserva'] ?? null;

        if (!$amenityId || !$fecha || !$horaInicio || !$horaFin) {
            $this->jsonResponse(false, [], 'Parámetros incompletos');
        }

        $resultado = $this->verificarDisponibilidadCompleta($amenityId, $fecha, $horaInicio, $horaFin, $excluirReserva);
        $this->jsonResponse($resultado['disponible'], $resultado, $resultado['mensaje']);
    }

    /**
     * Obtener horarios disponibles (API)
     */
    public function getHorariosDisponibles() {
        $amenityId = $_GET['amenity_id'] ?? null;
        $fecha = $_GET['fecha'] ?? date('Y-m-d');

        if (!$amenityId) {
            $this->jsonResponse(false, [], 'Amenity no especificado');
        }

        $horarios = $this->getHorariosDisponiblesAmenity($amenityId, $fecha);
        $this->jsonResponse(true, $horarios, 'Horarios disponibles obtenidos');
    }

    // ==================== MÉTODOS PRIVADOS - CONSULTAS ====================

    /**
     * Obtener reserva por ID
     */
    private function getReservaById($reservaId) {
        $sql = 'SELECT r.*, a.nombre as amenity_nombre, a.edificio_id, 
                       d.numero as departamento_numero, u.nombre as usuario_nombre
                FROM reservas r
                JOIN amenities a ON r.amenity_id = a.id
                JOIN departamentos d ON r.departamento_id = d.id
                LEFT JOIN users u ON r.created_by = u.id
                WHERE r.id = ?';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$reservaId]);
        return $stmt->fetch();
    }

    /**
     * Obtener reservas de un usuario
     */
    private function getReservasUsuario($userId) {
        $sql = 'SELECT r.*, a.nombre as amenity_nombre, a.tipo as amenity_tipo,
                       e.nombre as edificio_nombre, d.numero as departamento_numero
                FROM reservas r
                JOIN amenities a ON r.amenity_id = a.id
                JOIN edificios e ON a.edificio_id = e.id
                JOIN departamentos d ON r.departamento_id = d.id
                WHERE r.created_by = ?
                ORDER BY r.fecha_reserva DESC, r.hora_inicio DESC
                LIMIT 50';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Obtener reservas de un amenity en una fecha
     */
    private function getReservasAmenity($amenityId, $fecha) {
        $sql = 'SELECT r.*, d.numero as departamento_numero, u.nombre as usuario_nombre
                FROM reservas r
                JOIN departamentos d ON r.departamento_id = d.id
                LEFT JOIN users u ON r.created_by = u.id
                WHERE r.amenity_id = ? AND r.fecha_reserva = ? AND r.estado IN ("confirmada", "pendiente")
                ORDER BY r.hora_inicio ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$amenityId, $fecha]);
        return $stmt->fetchAll();
    }

    /**
     * Obtener reservas pendientes de aprobación
     */
    private function getReservasPendientesAprobacion($edificioId) {
        $sql = 'SELECT r.*, a.nombre as amenity_nombre, a.tipo as amenity_tipo,
                       d.numero as departamento_numero, u.nombre as usuario_nombre,
                       u.telefono as usuario_telefono
                FROM reservas r
                JOIN amenities a ON r.amenity_id = a.id
                JOIN departamentos d ON r.departamento_id = d.id
                JOIN users u ON r.created_by = u.id
                WHERE a.edificio_id = ? AND r.estado = "pendiente"
                ORDER BY r.created_at ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$edificioId]);
        return $stmt->fetchAll();
    }

    /**
     * Obtener departamentos de un usuario
     */
    private function getDepartamentosUsuario($userId) {
        $sql = 'SELECT d.*, e.nombre as edificio_nombre
                FROM departamentos d
                JOIN edificios e ON d.edificio_id = e.id
                JOIN user_edificio_relations uer ON e.id = uer.edificio_id
                WHERE uer.user_id = ? AND (d.propietario_rut = (SELECT rut FROM users WHERE id = ?) 
                       OR d.arrendatario_rut = (SELECT rut FROM users WHERE id = ?))
                ORDER BY e.nombre, d.numero';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $userId, $userId]);
        return $stmt->fetchAll();
    }

    // ==================== MÉTODOS PRIVADOS - LÓGICA DE NEGOCIO ====================

    /**
     * Verificar disponibilidad completa
     */
    private function verificarDisponibilidadCompleta($amenityId, $fecha, $horaInicio, $horaFin, $excluirReserva = null) {
        $amenity = $this->getAmenityById($amenityId);
        
        // Verificar horarios del amenity
        if (!$this->validarHorarioAmenity($amenity, $fecha, $horaInicio, $horaFin)) {
            return [
                'disponible' => false,
                'mensaje' => 'El horario seleccionado está fuera del horario de funcionamiento'
            ];
        }

        // Verificar duración de reserva
        $duracion = $this->calcularDuracion($horaInicio, $horaFin);
        if ($amenity['duracion_minima_reserva'] && $duracion < $amenity['duracion_minima_reserva']) {
            return [
                'disponible' => false,
                'mensaje' => 'La duración mínima es ' . $amenity['duracion_minima_reserva'] . ' minutos'
            ];
        }
        
        if ($amenity['duracion_maxima_reserva'] && $duracion > $amenity['duracion_maxima_reserva']) {
            return [
                'disponible' => false,
                'mensaje' => 'La duración máxima es ' . $amenity['duracion_maxima_reserva'] . ' minutos'
            ];
        }

        // Verificar conflictos con otras reservas
        $sql = 'SELECT COUNT(*) as count FROM reservas 
                WHERE amenity_id = ? AND fecha_reserva = ? 
                AND estado IN ("confirmada", "pendiente")
                AND ((hora_inicio BETWEEN ? AND ?) OR (hora_fin BETWEEN ? AND ?))
                AND id != ?';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$amenityId, $fecha, $horaInicio, $horaFin, $horaInicio, $horaFin, $excluirReserva]);
        $result = $stmt->fetch();

        if ($result['count'] > 0) {
            return [
                'disponible' => false,
                'mensaje' => 'El amenity no está disponible en ese horario'
            ];
        }

        return [
            'disponible' => true,
            'mensaje' => 'Horario disponible',
            'duracion' => $duracion,
            'costo' => $this->calcularCostoReserva($amenity, $horaInicio, $horaFin)
        ];
    }

    /**
     * Verificar límites de usuario
     */
    private function verificarLimitesUsuario($departamentoId, $amenityId, $fecha) {
        $amenity = $this->getAmenityById($amenityId);
        
        // Verificar límite semanal
        if ($amenity['max_reservas_semana'] > 0) {
            $sql = 'SELECT COUNT(*) as count FROM reservas 
                    WHERE departamento_id = ? AND amenity_id = ? 
                    AND fecha_reserva BETWEEN DATE_SUB(?, INTERVAL 6 DAY) AND ?
                    AND estado IN ("confirmada", "pendiente")';
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$departamentoId, $amenityId, $fecha, $fecha]);
            $result = $stmt->fetch();
            
            if ($result['count'] >= $amenity['max_reservas_semana']) {
                return false;
            }
        }

        // Verificar límite mismo día
        if ($amenity['max_reservas_mismo_dia'] > 0) {
            $sql = 'SELECT COUNT(*) as count FROM reservas 
                    WHERE departamento_id = ? AND amenity_id = ? AND fecha_reserva = ?
                    AND estado IN ("confirmada", "pendiente")';
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$departamentoId, $amenityId, $fecha]);
            $result = $stmt->fetch();
            
            if ($result['count'] >= $amenity['max_reservas_mismo_dia']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Obtener horarios disponibles de un amenity
     */
    protected function getHorariosDisponiblesAmenity($amenityId, $fecha) {
        $amenity = $this->getAmenityById($amenityId);
        $horarios = [];

        // Obtener horario del día específico
        $horarioApertura = $this->getHorarioDelDia($amenity, $fecha);
        
        if (!$horarioApertura['apertura'] || !$horarioApertura['cierre']) {
            return $horarios;
        }

        // Generar bloques de tiempo disponibles
        $intervalo = 30; // minutos
        $apertura = strtotime($horarioApertura['apertura']);
        $cierre = strtotime($horarioApertura['cierre']);

        for ($time = $apertura; $time < $cierre; $time += $intervalo * 60) {
            $horaInicio = date('H:i', $time);
            $horaFin = date('H:i', $time + ($intervalo * 60));

            // Verificar disponibilidad de este bloque
            $disponible = $this->verificarDisponibilidadCompleta($amenityId, $fecha, $horaInicio, $horaFin);
            
            if ($disponible['disponible']) {
                $horarios[] = [
                    'inicio' => $horaInicio,
                    'fin' => $horaFin,
                    'duracion' => $disponible['duracion'],
                    'costo' => $disponible['costo']
                ];
            }
        }

        return $horarios;
    }

    // ==================== MÉTODOS PRIVADOS - UTILIDADES ====================

    /**
     * Validar horario contra configuración del amenity
     */
    private function validarHorarioAmenity($amenity, $fecha, $horaInicio, $horaFin) {
        $horarioDia = $this->getHorarioDelDia($amenity, $fecha);
        
        if (!$horarioDia['apertura'] || !$horarioDia['cierre']) {
            return false;
        }

        $apertura = strtotime($horarioDia['apertura']);
        $cierre = strtotime($horarioDia['cierre']);
        $inicio = strtotime($horaInicio);
        $fin = strtotime($horaFin);

        return ($inicio >= $apertura && $fin <= $cierre && $inicio < $fin);
    }

    /**
     * Obtener horario específico del día
     */
    private function getHorarioDelDia($amenity, $fecha) {
        $diaSemana = date('N', strtotime($fecha)); // 1=Lunes, 7=Domingo
        
        // Verificar primero horarios estacionales
        if ($this->estaEnPeriodoEstacional($amenity, $fecha)) {
            return [
                'apertura' => $amenity['horario_verano_apertura'] ?? $amenity['horario_verano_apertura'],
                'cierre' => $amenity['horario_verano_cierre'] ?? $amenity['horario_verano_cierre']
            ];
        }

        // Horarios por día de la semana
        switch ($diaSemana) {
            case 6: // Sábado
                return [
                    'apertura' => $amenity['horario_sabado_apertura'] ?? $amenity['horario_apertura'],
                    'cierre' => $amenity['horario_sabado_cierre'] ?? $amenity['horario_cierre']
                ];
            case 7: // Domingo
                return [
                    'apertura' => $amenity['horario_domingo_apertura'] ?? $amenity['horario_apertura'],
                    'cierre' => $amenity['horario_domingo_cierre'] ?? $amenity['horario_cierre']
                ];
            default: // Lunes a Viernes
                return [
                    'apertura' => $amenity['horario_lunes_apertura'] ?? $amenity['horario_apertura'],
                    'cierre' => $amenity['horario_lunes_cierre'] ?? $amenity['horario_cierre']
                ];
        }
    }

    /**
     * Verificar si está en periodo estacional
     */
    private function estaEnPeriodoEstacional($amenity, $fecha) {
        $fechaTs = strtotime($fecha);

        // Verano
        if ($amenity['horario_verano_inicio'] && $amenity['horario_verano_fin']) {
            $veranoInicio = strtotime($amenity['horario_verano_inicio']);
            $veranoFin = strtotime($amenity['horario_verano_fin']);
            
            if ($fechaTs >= $veranoInicio && $fechaTs <= $veranoFin) {
                return true;
            }
        }

        // Invierno
        if ($amenity['horario_invierno_inicio'] && $amenity['horario_invierno_fin']) {
            $inviernoInicio = strtotime($amenity['horario_invierno_inicio']);
            $inviernoFin = strtotime($amenity['horario_invierno_fin']);
            
            if ($fechaTs >= $inviernoInicio && $fechaTs <= $inviernoFin) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calcular duración en minutos
     */
    private function calcularDuracion($horaInicio, $horaFin) {
        $inicio = strtotime($horaInicio);
        $fin = strtotime($horaFin);
        return ($fin - $inicio) / 60;
    }

    /**
     * Calcular costo de reserva
     */
    private function calcularCostoReserva($amenity, $horaInicio, $horaFin) {
        if ($amenity['costo_uso'] <= 0) {
            return 0;
        }

        $duracion = $this->calcularDuracion($horaInicio, $horaFin);
        $horas = ceil($duracion / 60); // Redondear hacia arriba a horas completas
        
        return $horas * $amenity['costo_uso'];
    }

    /**
     * Obtener estadísticas del usuario
     */
    private function getEstadisticasUsuario($userId) {
        $sql = 'SELECT 
                COUNT(*) as total_reservas,
                COUNT(CASE WHEN estado = "confirmada" THEN 1 END) as confirmadas,
                COUNT(CASE WHEN estado = "pendiente" THEN 1 END) as pendientes,
                COUNT(CASE WHEN estado = "cancelada" THEN 1 END) as canceladas,
                COALESCE(SUM(costo_total), 0) as total_gastado
                FROM reservas 
                WHERE created_by = ?';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        
        // Asegurar que todos los valores estén definidos
        return [
            'total_reservas' => $result['total_reservas'] ?? 0,
            'confirmadas' => $result['confirmadas'] ?? 0,
            'pendientes' => $result['pendientes'] ?? 0,
            'canceladas' => $result['canceladas'] ?? 0,
            'total_gastado' => $result['total_gastado'] ?? 0
        ];
    }

    // ==================== MÉTODOS PRIVADOS - NOTIFICACIONES ====================

    /**
     * Notificar creación de reserva
     */
    private function notificarReservaCreada($reservaId, $amenity, $estado) {
        $reserva = $this->getReservaById($reservaId);
        
        // Notificar al usuario
        $this->notificationManager->createNotification(
            $_SESSION['user_id'],
            $estado === 'confirmada' ? 'success' : 'info',
            $estado === 'confirmada' ? 'Reserva Confirmada' : 'Solicitud de Reserva Enviada',
            $estado === 'confirmada' 
                ? "Tu reserva para {$amenity['nombre']} ha sido confirmada"
                : "Tu solicitud para {$amenity['nombre']} está en revisión",
            'reservas',
            $reservaId
        );

        // Notificar a administradores si requiere aprobación
        if ($estado === 'pendiente') {
            $admins = $this->getAdministradoresEdificio($amenity['edificio_id']);
            foreach ($admins as $admin) {
                $this->notificationManager->createNotification(
                    $admin['user_id'],
                    'warning',
                    'Nueva Solicitud de Reserva',
                    "Nueva reserva pendiente para {$amenity['nombre']}",
                    'reservas',
                    $reservaId
                );
            }
        }
    }

    /**
     * Notificar cancelación de reserva
     */
    private function notificarReservaCancelada($reservaId) {
        $reserva = $this->getReservaById($reservaId);
        
        $this->notificationManager->createNotification(
            $reserva['created_by'],
            'info',
            'Reserva Cancelada',
            "Tu reserva para {$reserva['amenity_nombre']} ha sido cancelada",
            'reservas',
            $reservaId
        );
    }

    /**
     * Notificar aprobación de reserva
     */
    private function notificarReservaAprobada($reservaId) {
        $reserva = $this->getReservaById($reservaId);
        
        $this->notificationManager->createNotification(
            $reserva['created_by'],
            'success',
            'Reserva Aprobada',
            "Tu reserva para {$reserva['amenity_nombre']} ha sido aprobada",
            'reservas',
            $reservaId
        );
    }

    /**
     * Notificar rechazo de reserva
     */
    private function notificarReservaRechazada($reservaId) {
        $reserva = $this->getReservaById($reservaId);
        
        $this->notificationManager->createNotification(
            $reserva['created_by'],
            'error',
            'Reserva Rechazada',
            "Tu reserva para {$reserva['amenity_nombre']} ha sido rechazada",
            'reservas',
            $reservaId
        );
    }

    /**
     * Obtener administradores del edificio
     */
    private function getAdministradoresEdificio($edificioId) {
        $sql = "SELECT user_id FROM user_edificio_relations 
                WHERE edificio_id = ? AND (is_primary_admin = 1 OR permissions LIKE '%\"reservas\":[\"write\"]%')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$edificioId]);
        return $stmt->fetchAll();
    }

    // ==================== MÉTODOS PARA ESTADÍSTICAS DE APROBACIONES ====================

    /**
     * Obtener reservas aprobadas para hoy
     */
    private function getReservasAprobadasHoy($edificioId) {
        $hoy = date('Y-m-d');
        
        $sql = 'SELECT COUNT(*) as total 
                FROM reservas r
                JOIN amenities a ON r.amenity_id = a.id
                WHERE a.edificio_id = ? 
                AND r.fecha_reserva = ? 
                AND r.estado = "confirmada"
                AND DATE(r.updated_at) = ?';
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$edificioId, $hoy, $hoy]);
        $result = $stmt->fetch();
        
        return $result['total'] ?? 0;
    }

    /**
     * Obtener reservas rechazadas para hoy
     */
    private function getReservasRechazadasHoy($edificioId) {
        $hoy = date('Y-m-d');
        
        $sql = 'SELECT COUNT(*) as total 
                FROM reservas r
                JOIN amenities a ON r.amenity_id = a.id
                WHERE a.edificio_id = ? 
                AND r.estado = "rechazada"
                AND DATE(r.updated_at) = ?';
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$edificioId, $hoy]);
        $result = $stmt->fetch();
        
        return $result['total'] ?? 0;
    }

    /**
     * Obtener tiempo promedio de aprobación en horas
     */
    private function getTiempoPromedioAprobacion($edificioId) {
        $sql = 'SELECT AVG(TIMESTAMPDIFF(HOUR, r.created_at, r.updated_at)) as promedio
                FROM reservas r
                JOIN amenities a ON r.amenity_id = a.id
                WHERE a.edificio_id = ? 
                AND r.estado IN ("confirmada", "rechazada")
                AND r.updated_at IS NOT NULL';
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$edificioId]);
        $result = $stmt->fetch();
        
        return $result['promedio'] ? round($result['promedio'], 1) : 0;
    }

    /**
     * Obtener número de amenities con reservas pendientes
     */
    private function getAmenitiesConPendientes($edificioId) {
        $sql = 'SELECT COUNT(DISTINCT a.id) as total
                FROM amenities a
                JOIN reservas r ON a.id = r.amenity_id
                WHERE a.edificio_id = ? 
                AND r.estado = "pendiente"';
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$edificioId]);
        $result = $stmt->fetch();
        
        return $result['total'] ?? 0;
    }

    /**
     * Obtener número de usuarios con reservas pendientes
     */
    private function getUsuariosConPendientes($edificioId) {
        $sql = 'SELECT COUNT(DISTINCT r.created_by) as total
                FROM reservas r
                JOIN amenities a ON r.amenity_id = a.id
                WHERE a.edificio_id = ? 
                AND r.estado = "pendiente"';
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$edificioId]);
        $result = $stmt->fetch();
        
        return $result['total'] ?? 0;
    }

    /**
     * Calcular tiempo de espera desde la creación
     */
    private function tiempoEspera($fechaCreacion) {
        $creacion = new DateTime($fechaCreacion);
        $ahora = new DateTime();
        $diferencia = $ahora->diff($creacion);
        
        if ($diferencia->d > 0) {
            return $diferencia->d . ' días';
        } elseif ($diferencia->h > 0) {
            return $diferencia->h . ' horas';
        } else {
            return $diferencia->i . ' minutos';
        }
    }

    /**
     * Obtener amenities de un edificio
     */
    protected function getAmenitiesEdificio($edificioId) {
        $sql = 'SELECT * FROM amenities WHERE edificio_id = ? AND is_active = 1 ORDER BY nombre';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$edificioId]);
        return $stmt->fetchAll();
    }

    /**
     * Obtener amenity por ID
     */
    protected function getAmenityById($amenityId) {
        $sql = 'SELECT a.*, e.nombre as edificio_nombre 
                FROM amenities a 
                JOIN edificios e ON a.edificio_id = e.id 
                WHERE a.id = ?';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$amenityId]);
        return $stmt->fetch();
    }

    /**
     * Obtener edificio por ID
     */
    protected function getEdificioById($edificioId) {
        $sql = 'SELECT * FROM edificios WHERE id = ?';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$edificioId]);
        return $stmt->fetch();
    }

    /**
     * Obtener configuración de amenity
     */
    protected function getConfiguracionAmenity($amenityId) {
        $sql = 'SELECT * FROM amenity_configuraciones WHERE amenity_id = ?';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$amenityId]);
        return $stmt->fetch();
    }

    /**
     * Verificar acceso a edificio
     */
    protected function checkEdificioAccess($edificioId) {
        $userEdificios = $this->getUserEdificios($_SESSION['user_id']);
        $edificioIds = array_column($userEdificios, 'id');
        
        if (!in_array($edificioId, $edificioIds)) {
            $this->addFlashMessage('error', 'No tienes acceso a este edificio');
            $this->redirect('reservas/calendario');
        }
    }

    /**
     * Obtener edificios del usuario
     */
    protected function getUserEdificios($userId = null) {
        $sql = "SELECT e.* 
                FROM edificios e
                JOIN user_edificio_relations uer ON e.id = uer.edificio_id
                WHERE uer.user_id = ?
                ORDER BY e.nombre";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Validar input
     */
    protected function validateInput($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $rulesArray = explode('|', $rule);
            
            foreach ($rulesArray as $singleRule) {
                if ($singleRule === 'required' && empty($data[$field])) {
                    $errors[] = "El campo {$field} es requerido";
                }
                
                if (strpos($singleRule, 'min:') === 0 && isset($data[$field])) {
                    $min = (int) str_replace('min:', '', $singleRule);
                    if (strlen($data[$field]) < $min) {
                        $errors[] = "El campo {$field} debe tener al menos {$min} caracteres";
                    }
                }
                
                if ($singleRule === 'numeric' && isset($data[$field]) && !is_numeric($data[$field])) {
                    $errors[] = "El campo {$field} debe ser numérico";
                }
                
                if ($singleRule === 'date' && isset($data[$field]) && !strtotime($data[$field])) {
                    $errors[] = "El campo {$field} debe ser una fecha válida";
                }
            }
        }
        
        return $errors;
    }    

    /**
     * Obtiene el color de badge Bootstrap según el tipo de amenity
     * 
     * @param string $tipo Tipo del amenity
     * @param string|null $estado Estado del amenity (opcional)
     * @return string Clase CSS de color Bootstrap
     */
    public function getAmenityBadgeColor($tipo, $estado = null) {
        // Si se proporciona estado, priorizar colores por estado
        if ($estado !== null) {
            switch ($estado) {
                case 'activo':
                case 'disponible':
                case 'confirmada':
                    return 'success';
                case 'mantenimiento':
                case 'bloqueado':
                case 'cancelada':
                    return 'danger';
                case 'pendiente':
                case 'en_revision':
                    return 'warning';
                case 'inactivo':
                case 'no_disponible':
                    return 'secondary';
                case 'reservada':
                case 'en_uso':
                    return 'info';
                default:
                    // Continuar con colores por tipo
                    break;
            }
        }
        
        // Colores por tipo de amenity
        switch ($tipo) {
            case 'gimnasio':
                return 'primary'; // Azul - Deporte/Energía
            case 'piscina':
                return 'info';    // Celeste - Agua/Refrescante
            case 'quincho':
                return 'warning'; // Naranja - Fuego/Comida
            case 'sala_eventos':
                return 'success'; // Verde - Social/Naturaleza
            case 'lavanderia':
                return 'secondary'; // Gris - Servicio/Utilidad
            case 'juegos_infantiles':
                return 'success';  // Verde - Diversión/Niños
            case 'terraza':
                return 'info';     // Celeste - Exterior/Cielo
            case 'estacionamiento':
                return 'dark';     // Negro/Gris - Vehículos
            case 'sala_reuniones':
                return 'primary';  // Azul - Profesional
            case 'biblioteca':
                return 'warning';  // Naranja - Conocimiento
            case 'spa':
                return 'danger';   // Rojo - Relax/Lujo
            case 'cancha_tenis':
                return 'success';  // Verde - Deporte
            case 'cancha_futbol':
                return 'success';  // Verde - Deporte
            case 'jacuzzi':
                return 'info';     // Celeste - Agua/Relax
            case 'sauna':
                return 'danger';   // Rojo - Calor/Salud
            default:
                return 'secondary'; // Gris por defecto
        }
    }


}
?>