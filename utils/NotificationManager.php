<?php

// 📁 utils/NotificationManager.php
class NotificationManager
{
    private $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance()->getConnection();
    }

    public function createNotification($userId, $type, $title, $message, $relatedEntity = null)
    {
        $sql = 'INSERT INTO notificaciones (user_id, tipo, titulo, mensaje, related_entity_type, related_entity_id) 
                VALUES (?, ?, ?, ?, ?, ?)';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $userId,
            $type,
            $title,
            $message,
            $relatedEntity['type'] ?? null,
            $relatedEntity['id'] ?? null,
        ]);

        return $this->db->lastInsertId();
    }

    public function getUserNotifications($userId, $unreadOnly = false)
    {
        $sql = 'SELECT * FROM notificaciones WHERE user_id = ?';
        if ($unreadOnly) {
            $sql .= ' AND is_read = 0';
        }
        $sql .= ' ORDER BY created_at DESC LIMIT 50';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);

        return $stmt->fetchAll();
    }

    public function markAsRead($notificationId, $userId)
    {
        $sql = 'UPDATE notificaciones SET is_read = 1, fecha_lectura = NOW() 
                WHERE id = ? AND user_id = ?';
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([$notificationId, $userId]);
    }

    public function getUnreadCount($userId)
    {
        $sql = 'SELECT COUNT(*) as count FROM notificaciones 
                WHERE user_id = ? AND is_read = 0';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $result = $stmt->fetch();

        return $result['count'];
    }
}
