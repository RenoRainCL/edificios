<?php
class SecurityManager {
    private static $instance = null;
    private $encryptionKey;
    private $encryptedFields;
    private $cipher = "AES-256-CBC";
    
    private function __construct() {
        $config = include __DIR__ . '/../config/.env_edificio';
        $this->encryptionKey = $config['ENCRYPTION_KEY'];
        $this->encryptedFields = $config['ENCRYPTED_FIELDS'];
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new SecurityManager();
        }
        return self::$instance;
    }
    
    /**
     * MÉTODO FALTANTE - Procesa datos desde la base de datos
     */
    public function processDataFromDB($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeOutput'], $data);
        }
        return $this->sanitizeOutput($data);
    }
    
    /**
     * Sanitiza output para prevenir XSS
     */
    public function sanitizeOutput($value) {
        if (is_array($value)) {
            return array_map([$this, 'sanitizeOutput'], $value);
        }
        
        if (is_string($value)) {
            return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
        
        return $value;
    }
    
    /**
     * Sanitiza input del usuario
     */
    public function sanitizeInput($value) {
        if (is_array($value)) {
            return array_map([$this, 'sanitizeInput'], $value);
        }
        
        if (is_string($value)) {
            $value = trim($value);
            // Limpiar posibles inyecciones XSS básicas
            $value = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $value);
        }
        
        return $value;
    }
    
    // ... TUS MÉTODOS EXISTENTES SE MANTIENEN ...
    public function encryptField($data) {
        if (empty($data)) return $data;
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->cipher));
        $encrypted = openssl_encrypt($data, $this->cipher, $this->encryptionKey, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }
    
    public function decryptField($encryptedData) {
        if (empty($encryptedData)) return $encryptedData;
        try {
            list($encrypted_data, $iv) = explode('::', base64_decode($encryptedData), 2);
            return openssl_decrypt($encrypted_data, $this->cipher, $this->encryptionKey, 0, $iv);
        } catch (Exception $e) {
            return $encryptedData;
        }
    }
    
    public function validateRUT($rut) {
        if (!preg_match('/^[0-9]+-[0-9kK]{1}$/', $rut)) return false;
        list($numero, $digitoVerificador) = explode('-', $rut);
        $digitoVerificador = strtoupper($digitoVerificador);
        
        $i = 2;
        $suma = 0;
        foreach (array_reverse(str_split($numero)) as $v) {
            if ($i > 7) $i = 2;
            $suma += $v * $i;
            $i++;
        }
        
        $dvr = 11 - ($suma % 11);
        if ($dvr == 11) $dvr = 0;
        if ($dvr == 10) $dvr = 'K';
        
        return $digitoVerificador == $dvr;
    }
}
?>