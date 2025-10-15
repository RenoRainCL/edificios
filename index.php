<?php

// 📁 index.php
// ==================== CONFIGURACIÓN INICIAL ====================

// Mostrar errores en desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Definir constantes
define('APP_ROOT', __DIR__);
define('APP_DEBUG', true); // Cambiar a false en producción

// Iniciar sesión
session_start();

// Configurar zona horaria de Chile
date_default_timezone_set('America/Santiago');

// ==================== INICIALIZAR HELPER DE URL ====================
// DEBUG INICIAL
error_log("=== 🚨 INICIO DEBUG URLS ===");
error_log("📍 SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'NO SET'));
error_log("📍 REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'NO SET'));
// Obtener la ruta base del proyecto
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
$basePath = $scriptDir === '/' || $scriptDir === '\\' ? '' : $scriptDir;

// Inicializar UrlHelper
require_once __DIR__.'/utils/UrlHelper.php';
UrlHelper::init($basePath);

// TEST INMEDIATO
error_log("📍 TEST UrlHelper::to('dashboard'): " . UrlHelper::to('dashboard'));
error_log("=== 🚨 FIN DEBUG URLS ===");
error_log("📍 UrlHelper inicializado con basePath: '$basePath'");

// ==================== AUTOCARGADOR DE CLASES ====================

spl_autoload_register(function ($className) {
    $paths = [
        __DIR__.'/core/',
        __DIR__.'/controllers/',
        __DIR__.'/models/',
        __DIR__.'/utils/',
        __DIR__.'/modules/',
    ];

    foreach ($paths as $path) {
        $file = $path.$className.'.php';
        if (file_exists($file)) {
            require_once $file;

            return;
        }
    }

    // Log error si la clase no se encuentra
    error_log("Clase no encontrada: $className");
});

// ==================== DETECTAR RUTA BASE AUTOMÁTICAMENTE ====================

// Obtener la ruta base del proyecto de manera más robusta
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
$basePath = $scriptDir === '/' || $scriptDir === '\\' ? '' : $scriptDir;

// Debug del basePath
error_log("📍 Script Dir: '$scriptDir'");
error_log("📍 Base Path Calculado: '$basePath'");
error_log('📍 Document Root: '.($_SERVER['DOCUMENT_ROOT'] ?? 'NO SET'));

// Para tu caso: http://localhost:8080/proyectos/edificios/
// $basePath será '/proyectos/edificios'

// ==================== INICIALIZACIÓN ====================

try {
    // Cargar configuración - CORREGIR NOMBRE DEL ARCHIVO
    $configPath = __DIR__.'/config/.env_edificio';
    if (!file_exists($configPath)) {
        throw new Exception('Archivo de configuración no encontrado: .env_edificio');
    }

    $config = require_once $configPath;

    // Inicializar gestor de textos
    if (class_exists('TextManager')) {
        TextManager::loadTexts();
    }

    // Crear router CON RUTA BASE
    $router = new Router($basePath);

    // Cargar rutas
    $routesPath = __DIR__.'/config/routes.php';
    if (!file_exists($routesPath)) {
        throw new Exception('Archivo de rutas no encontrado: config/routes.php');
    }

    $routes = require_once $routesPath;
    $routes($router);

    // Ejecutar la ruta actual
    $router->dispatch();
} catch (Exception $e) {
    // Manejo global de excepciones
    http_response_code(500);

    echo '<h1>Error de Configuración</h1>';
    echo '<p><strong>Mensaje:</strong> '.htmlspecialchars($e->getMessage()).'</p>';
    echo '<p><strong>Ruta Base Detectada:</strong> '.htmlspecialchars($basePath ?? 'No detectada').'</p>';
    echo '<p><strong>Archivo:</strong> '.htmlspecialchars($e->getFile()).'</p>';
    echo '<p><strong>Línea:</strong> '.htmlspecialchars($e->getLine()).'</p>';

    if (defined('APP_DEBUG') && APP_DEBUG) {
        echo '<pre>'.htmlspecialchars($e->getTraceAsString()).'</pre>';
    }

    // Log del error
    error_log('Error de inicialización: '.$e->getMessage());
}
