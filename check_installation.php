<?php
// 📁 check_installation.php

echo "<h1>🔧 Verificación de Instalación - Sistema Edificios Chile</h1>";
echo "<p><strong>URL:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p><strong>Script:</strong> " . $_SERVER['SCRIPT_NAME'] . "</p>";

// Verificar estructura de directorios
$requiredDirs = ['config', 'core', 'controllers', 'models', 'views'];
$requiredFiles = [
    'config/.env_edificio',
    'core/Router.php', 
    'core/ControllerCore.php',
    'core/DatabaseConnection.php',
    'core/SecurityManager.php',
    'controllers/AuthController.php',
    'index.php'
];

echo "<h2>📁 Verificación de Directorios</h2>";
foreach ($requiredDirs as $dir) {
    if (is_dir($dir)) {
        echo "<p style='color: green;'>✅ $dir/</p>";
    } else {
        echo "<p style='color: red;'>❌ $dir/ - NO EXISTE</p>";
    }
}

echo "<h2>📄 Verificación de Archivos</h2>";
foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✅ $file</p>";
    } else {
        echo "<p style='color: red;'>❌ $file - NO EXISTE</p>";
    }
}

// Verificar configuración de base de datos
echo "<h2>🗄️ Verificación de Base de Datos</h2>";
try {
    $config = require_once 'config/.env_edificio';
    echo "<p style='color: green;'>✅ Archivo de configuración cargado</p>";
    echo "<pre>" . print_r($config, true) . "</pre>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error cargando configuración: " . $e->getMessage() . "</p>";
}

// Verificar rutas
echo "<h2>🛣️ Verificación de Rutas</h2>";
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
$basePath = $scriptDir === '/' ? '' : $scriptDir;
echo "<p><strong>Ruta Base Detectada:</strong> $basePath</p>";

// Probar conexión a base de datos
echo "<h2>🔌 Verificación de Conexión a BD</h2>";
try {
    if (file_exists('core/DatabaseConnection.php')) {
        require_once 'core/DatabaseConnection.php';
        $db = DatabaseConnection::getInstance();
        echo "<p style='color: green;'>✅ Conexión a base de datos exitosa</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ No se puede probar conexión - DatabaseConnection.php no encontrado</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error de conexión a BD: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>🚀 Próximos Pasos:</h3>";
echo "<ol>";
echo "<li>Verificar que todos los archivos existan</li>";
echo "<li>Configurar la base de datos con el script SQL</li>";
echo "<li>Acceder a: http://localhost:8080/proyectos/edificios/</li>";
echo "<li>Debería mostrar el formulario de login</li>";
echo "</ol>";
?>