<?php
// 📁 models/User.php

class User {
    private $db;
    private $security;
    
    public function __construct() {
        $this->db = DatabaseConnection::getInstance()->getConnection();
        $this->security = SecurityManager::getInstance();
    }
    
    /**
     * Obtiene usuario por email
     */
    public function getUserByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = ? AND is_active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            error_log("✅ Usuario encontrado por email: $email");
            return $this->security->processDataFromDB($user);
        }
        
        error_log("❌ Usuario NO encontrado por email: $email");
        return null;
    }
    
    /**
     * Obtiene usuario por RUT
     */
    public function getUserByRUT($rut) {
        $sql = "SELECT * FROM users WHERE rut = ? AND is_active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$rut]);
        $user = $stmt->fetch();
        
        if ($user) {
            error_log("✅ Usuario encontrado por RUT: $rut");
            return $this->security->processDataFromDB($user);
        }
        
        error_log("❌ Usuario NO encontrado por RUT: $rut");
        return null;
    }
    
    /**
     * Obtiene usuario por ID
     */
    public function getUserById($id) {
        $sql = "SELECT * FROM users WHERE id = ? AND is_active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        
        if ($user) {
            return $this->security->processDataFromDB($user);
        }
        return null;
    }
    
    /**
     * Verifica si la contraseña coincide con el hash
     */
    public function verifyPassword($inputPassword, $hashedPassword) {
        $result = password_verify($inputPassword, $hashedPassword);
        error_log("🔑 Verificación de contraseña: " . ($result ? "CORRECTA" : "INCORRECTA"));
        return $result;
    }
    
    /**
     * Crea hash de contraseña
     */
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    /**
     * Crea nuevo usuario (para uso interno)
     */
    public function createUser($userData) {
        $sql = "INSERT INTO users (rut, nombre, apellido, email, telefono, password_hash, role_id, is_active, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW())";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $userData['rut'] ?? null,
            $userData['nombre'],
            $userData['apellido'],
            $userData['email'],
            $userData['telefono'] ?? null,
            $this->hashPassword($userData['password']),
            $userData['role_id'] ?? 4 // Por defecto residente
        ]);
    }
}
?>