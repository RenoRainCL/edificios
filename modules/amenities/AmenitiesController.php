<?php
// 📁 modules/amenities/AmenitiesController.php
class AmenitiesController {
    private $db;
    
    public function __construct() {
        $this->db = DatabaseConnection::getInstance()->getConnection();
    }
    
    public function getAmenitiesEdificio($edificioId) {
        $sql = "SELECT * FROM amenities WHERE edificio_id = ? AND is_active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$edificioId]);
        return $stmt->fetchAll();
    }
    
    public function crearReserva($data) {
        // Validar disponibilidad
        if (!$this->validarDisponibilidad($data['amenity_id'], $data['fecha_reserva'], $data['hora_inicio'], $data['hora_fin'])) {
            return [
                'success' => false,
                'message' => 'El espacio no está disponible en ese horario'
            ];
        }
        
        // Validar límite de reservas semanales
        if (!$this->validarLimiteReservas($data['departamento_id'], $data['amenity_id'])) {
            return [
                'success' => false,
                'message' => 'Has alcanzado el límite de reservas semanales para este espacio'
            ];
        }
        
        $sql = "INSERT INTO reservas (amenity_id, departamento_id, fecha_reserva, hora_inicio, hora_fin, motivo) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['amenity_id'],
            $data['departamento_id'],
            $data['fecha_reserva'],
            $data['hora_inicio'],
            $data['hora_fin'],
            $data['motivo']
        ]);
        
        return [
            'success' => true,
            'reserva_id' => $this->db->lastInsertId()
        ];
    }
    
    private function validarDisponibilidad($amenityId, $fecha, $horaInicio, $horaFin) {
        $sql = "SELECT COUNT(*) as count FROM reservas 
                WHERE amenity_id = ? AND fecha_reserva = ? 
                AND estado = 'confirmada'
                AND ((hora_inicio BETWEEN ? AND ?) OR (hora_fin BETWEEN ? AND ?))";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$amenityId, $fecha, $horaInicio, $horaFin, $horaInicio, $horaFin]);
        $result = $stmt->fetch();
        
        return $result['count'] == 0;
    }
    
    private function validarLimiteReservas($departamentoId, $amenityId) {
        $sql = "SELECT COUNT(*) as count FROM reservas 
                WHERE departamento_id = ? AND amenity_id = ? 
                AND fecha_reserva >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                AND estado = 'confirmada'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$departamentoId, $amenityId]);
        $result = $stmt->fetch();
        
        $config = include __DIR__ . '/../../../config/.env_proyecto';
        $maxReservas = $config['MAX_RESERVAS_SEMANA'] ?? 2;
        
        return $result['count'] < $maxReservas;
    }
    
    public function getReservasAmenity($amenityId, $fecha = null) {
        if (!$fecha) {
            $fecha = date('Y-m-d');
        }
        
        $sql = "SELECT r.*, d.numero as departamento_numero 
                FROM reservas r 
                JOIN departamentos d ON r.departamento_id = d.id 
                WHERE r.amenity_id = ? AND r.fecha_reserva = ? 
                ORDER BY r.hora_inicio";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$amenityId, $fecha]);
        return $stmt->fetchAll();
    }
}
?>