<?php

// 📁 modules/mantenimiento/MantenimientoController.php
class MantenimientoController
{
    private $db;
    private $notificationManager;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance()->getConnection();
        $this->notificationManager = new NotificationManager();
    }

    public function crearSolicitud($data)
    {
        $sql = 'INSERT INTO mantenimientos (edificio_id, tipo, titulo, descripcion, area, prioridad, fecha_programada, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['edificio_id'],
            $data['tipo'],
            $data['titulo'],
            $data['descripcion'],
            $data['area'],
            $data['prioridad'],
            $data['fecha_programada'],
            $_SESSION['user_id'],
        ]);

        $mantenimientoId = $this->db->lastInsertId();

        // Notificar a administradores
        $this->notificarNuevoMantenimiento($data['edificio_id'], $mantenimientoId, $data['titulo']);

        return $mantenimientoId;
    }

    public function getMantenimientosEdificio($edificioId, $estado = null)
    {
        $sql = 'SELECT m.*, u.nombre as creador_nombre 
                FROM mantenimientos m 
                LEFT JOIN users u ON m.created_by = u.id 
                WHERE m.edificio_id = ?';

        if ($estado) {
            $sql .= ' AND m.estado = ?';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$edificioId, $estado]);
        } else {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$edificioId]);
        }

        return $stmt->fetchAll();
    }

    public function actualizarEstado($mantenimientoId, $estado, $costoReal = null)
    {
        $sql = 'UPDATE mantenimientos SET estado = ?, fecha_completada = NOW()';
        $params = [$estado];

        if ($costoReal !== null) {
            $sql .= ', costo_real = ?';
            $params[] = $costoReal;
        }

        $sql .= ' WHERE id = ?';
        $params[] = $mantenimientoId;

        $stmt = $this->db->prepare($sql);

        return $stmt->execute($params);
    }

    private function notificarNuevoMantenimiento($edificioId, $mantenimientoId, $titulo)
    {
        $admins = $this->getAdministradoresEdificio($edificioId);

        foreach ($admins as $admin) {
            $this->notificationManager->createNotification(
                $admin['user_id'],
                'warning',
                'Nueva Solicitud de Mantenimiento',
                'Se ha creado una nueva solicitud: '.$titulo,
                ['type' => 'mantenimiento', 'id' => $mantenimientoId]
            );
        }
    }

    private function getAdministradoresEdificio($edificioId)
    {
        $sql = "SELECT user_id FROM user_edificio_relations 
                WHERE edificio_id = ? AND permissions LIKE '%mantenimiento%'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$edificioId]);

        return $stmt->fetchAll();
    }
}
