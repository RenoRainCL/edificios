<?php
// 📁 utils/UrlHelper.php

class UrlHelper {
    
    private static $basePath = null;
    
    /**
     * Inicializa el helper con la ruta base
     */
    public static function init($basePath) {
        self::$basePath = rtrim($basePath, '/');
    }
    
    /**
     * Genera una URL completa con la ruta base
     */
    public static function to($path = '') {
        if (self::$basePath === null) {
            // Intentar detectar la ruta base automáticamente
            self::detectBasePath();
        }
        
        $path = ltrim($path, '/');
        return self::$basePath . '/' . $path;
    }
    
    /**
     * Genera URL para assets (CSS, JS, imágenes)
     */
    public static function asset($assetPath) {
        return self::to($assetPath);
    }
    
    /**
     * Redirecciona a una URL
     */
    public static function redirect($path) {
        header('Location: ' . self::to($path));
        exit;
    }
    
    /**
     * Detecta automáticamente la ruta base
     */
    private static function detectBasePath() {
        $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
        self::$basePath = $scriptDir === '/' || $scriptDir === '\\' ? '' : $scriptDir;
        
        error_log("📍 UrlHelper - BasePath detectado: '" . self::$basePath . "'");
    }
    
    /**
     * Obtiene la URL actual
     */
    public static function current() {
        return $_SERVER['REQUEST_URI'];
    }
    
    /**
     * Obtiene la ruta base
     */
    public static function base() {
        if (self::$basePath === null) {
            self::detectBasePath();
        }
        return self::$basePath;
    }
    
    /**
     * Genera URL con parámetros query
     */
    public static function toWithParams($path, $params = []) {
        $url = self::to($path);
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        return $url;
    }
}
?>