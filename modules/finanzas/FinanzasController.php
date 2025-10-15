<?php

// 📁 modules/finanzas/FinanzasController.php
class FinanzasController
{
    private $db;
    private $security;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance()->getConnection();
        $this->security = SecurityManager::getInstance();
    }

    public function getGastosComunes($edificioId, $periodo = null)
    {
        if (!$periodo) {
            $periodo = date('Y-m');
        }

        $sql = 'SELECT * FROM gastos_comunes 
                WHERE edificio_id = ? AND periodo = ? 
                ORDER BY fecha_vencimiento DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$edificioId, $periodo]);

        return $stmt->fetchAll();
    }

    public function crearGastoComun($data)
    {
        $secureData = $this->security->processDataForDB($data);

        $sql = 'INSERT INTO gastos_comunes (edificio_id, nombre, descripcion, monto_total, periodo, fecha_vencimiento, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?)';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $secureData['edificio_id'],
            $secureData['nombre'],
            $secureData['descripcion'],
            $secureData['monto_total'],
            $secureData['periodo'],
            $secureData['fecha_vencimiento'],
            $_SESSION['user_id'],
        ]);

        return $this->db->lastInsertId();
    }
}
