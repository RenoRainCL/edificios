<?php
// 📁 core/Router.php

class Router {
    private $routes = [];
    private $path404 = '/404';
    private $basePath = '';
    
    public function __construct($basePath = '') {
        $this->basePath = rtrim($basePath, '/');
    }
    
    /**
     * Agrega una ruta GET
     */
    public function get($path, $handler) {
        $this->addRoute('GET', $path, $handler);
    }
    
    /**
     * Agrega una ruta POST
     */
    public function post($path, $handler) {
        $this->addRoute('POST', $path, $handler);
    }
    
    /**
     * Agrega una ruta PUT
     */
    public function put($path, $handler) {
        $this->addRoute('PUT', $path, $handler);
    }
    
    /**
     * Agrega una ruta DELETE
     */
    public function delete($path, $handler) {
        $this->addRoute('DELETE', $path, $handler);
    }
    
    /**
     * Agrega una ruta para cualquier método
     */
    public function any($path, $handler) {
        $this->addRoute(['GET', 'POST', 'PUT', 'DELETE'], $path, $handler);
    }
    
    /**
     * Agrega ruta con múltiples métodos
     */
    private function addRoute($methods, $path, $handler) {
        if (!is_array($methods)) {
            $methods = [$methods];
        }
        
        // Asegurar que el path empiece con /
        $path = '/' . ltrim($path, '/');
        
        foreach ($methods as $method) {
            $this->routes[] = [
                'method' => strtoupper($method),
                'path' => $path,
                'pattern' => $this->buildPattern($path),
                'handler' => $handler
            ];
            
            error_log("📝 Ruta registrada: $method $path -> " . (is_string($handler) ? $handler : 'callback'));
        }
    }
    
    /**
     * Construye patrón regex para la ruta
     */
    private function buildPattern($path) {
        // Convertir parámetros {id} a grupos de captura
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $path);
        // Escapar slashes para regex
        $pattern = str_replace('/', '\/', $pattern);
        
        $result = '#^' . $pattern . '$#';
        return $result;
    }
    
    /**
     * Ejecuta el router con la ruta actual
     */
    public function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        error_log("🔍 === INICIO DISPATCH ===");
        error_log("🔍 Router dispatch: $method $path");
        error_log("🔍 Base path configurado: '{$this->basePath}'");

        // Remover base path si existe
        $processedPath = $path;
        if ($this->basePath && strpos($path, $this->basePath) === 0) {
            $processedPath = substr($path, strlen($this->basePath));
            error_log("🔍 Path después de basePath: '$processedPath'");
        }
        
        // Asegurar que empiece con / y no termine con /
        $processedPath = '/' . trim($processedPath, '/');
        if ($processedPath === '') $processedPath = '/';
        
        error_log("🔍 Path final procesado: '$processedPath'");

        // PRIMERO: Buscar coincidencia exacta (más eficiente)
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $route['path'] === $processedPath) {
                error_log("✅ RUTA EXACTA ENCONTRADA: {$route['method']} {$route['path']} -> {$route['handler']}");
                return $this->executeHandler($route['handler'], []);
            }
        }
        
        // SEGUNDO: Buscar con patrones regex (para rutas con parámetros)
        foreach ($this->routes as $route) {
            if ($route['method'] === $method) {
                if (preg_match($route['pattern'], $processedPath, $matches)) {
                    array_shift($matches); // Remover el match completo
                    error_log("✅ RUTA PATTERN ENCONTRADA: {$route['method']} {$route['path']} -> {$route['handler']}");
                    error_log("🔍 Parámetros: " . json_encode($matches));
                    return $this->executeHandler($route['handler'], $matches);
                }
            }
        }
        
        error_log("❌ RUTA NO ENCONTRADA para: $method $processedPath");
        error_log("🔍 Rutas registradas:");
        foreach ($this->routes as $route) {
            error_log("   - {$route['method']} {$route['path']}");
        }
        
        $this->handle404();
    }
    
    /**
     * Ejecuta el handler de la ruta
     */
    private function executeHandler($handler, $params = []) {
        try {
            if (is_string($handler) && strpos($handler, '@') !== false) {
                // Formato: "Controller@method"
                list($controllerName, $methodName) = explode('@', $handler);
                
                error_log("🎯 Ejecutando controlador: $controllerName@$methodName");
                
                // Cargar controlador
                $controllerFile = __DIR__ . '/../controllers/' . $controllerName . '.php';
                if (!file_exists($controllerFile)) {
                    throw new Exception("Controlador no encontrado: $controllerName");
                }
                
                require_once $controllerFile;
                
                if (!class_exists($controllerName)) {
                    throw new Exception("Clase no encontrada: $controllerName");
                }
                
                $controller = new $controllerName();
                
                if (!method_exists($controller, $methodName)) {
                    throw new Exception("Método no encontrado: $methodName en $controllerName");
                }
                
                // Ejecutar método con parámetros
                return call_user_func_array([$controller, $methodName], $params);
            } elseif (is_callable($handler)) {
                // Función anónima
                return call_user_func_array($handler, $params);
            } else {
                throw new Exception("Handler inválido");
            }
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }
    
    /**
     * Maneja error 404
     */
    private function handle404() {
        http_response_code(404);
        
        if ($this->path404) {
            $this->executeHandler($this->path404);
        } else {
            echo "Página no encontrada - 404";
        }
    }
    
    /**
     * Maneja errores de la aplicación
     */
    private function handleError(Exception $e) {
        error_log("❌ Error en router: " . $e->getMessage());
        http_response_code(500);
        
        if (defined('APP_DEBUG') && APP_DEBUG) {
            echo "Error: " . $e->getMessage() . "<br>";
            echo "Archivo: " . $e->getFile() . "<br>";
            echo "Línea: " . $e->getLine() . "<br>";
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
        } else {
            echo "Error interno del servidor";
        }
    }
    
    /**
     * Define la ruta para errores 404
     */
    public function set404($handler) {
        $this->path404 = $handler;
    }
    
    /**
     * Obtiene la URL base de la aplicación
     */
    public function getBaseUrl() {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $base = $this->basePath ?: '';
        
        return $protocol . '://' . $host . $base;
    }
    
    /**
     * Genera URL para una ruta con parámetros
     */
    public function url($path, $params = []) {
        $url = $this->getBaseUrl() . $path;
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        return $url;
    }
}
?>