<?php
// 📁 controllers/AuthController.php

class AuthController {
    private $db;
    private $userModel;
    
    public function __construct() {
        $this->db = DatabaseConnection::getInstance()->getConnection();
        $this->userModel = new User();
        
        error_log("🔧 AuthController inicializado");
    }
    
    public function login() {
        error_log("=== LOGIN DEBUG ===");
        error_log("REQUEST METHOD: " . $_SERVER['REQUEST_METHOD']);
        error_log("REQUEST URI: " . $_SERVER['REQUEST_URI']);
        
        // Si ya está autenticado, redirigir al dashboard
        if (isset($_SESSION['user_id'])) {
            error_log("✅ Usuario ya autenticado, redirigiendo a dashboard");
            header('Location: /proyectos/edificios/dashboard');
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            error_log("🎯 POST RECIBIDO!");
            error_log("POST data: " . print_r($_POST, true));
            
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            $user = $this->authenticate($email, $password);
            
            if ($user) {
                error_log("✅ AUTH EXITOSA - Redirigiendo a dashboard...");
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['nombre'];
                $_SESSION['user_role'] = $user['role_id'];
                $_SESSION['user_email'] = $user['email'];
                
                header('Location: /proyectos/edificios/dashboard');
                exit();
            } else {
                error_log("❌ AUTH FALLIDA - Credenciales incorrectas");
                $error = "Credenciales inválidas";
            }
        } else {
            error_log("📝 MOSTRANDO FORMULARIO (GET)");
        }
        
        $this->showLogin($error ?? null);
    }
    
    public function logout() {
        error_log("🚪 LOGOUT - Destruyendo sesión");
        session_destroy();
        header('Location: /proyectos/edificios/');
        exit();
    }
    /*    
    private function authenticate($identifier, $password) {
        error_log("🔐 Intentando autenticar: $identifier");
        
        $user = $this->userModel->getUserByEmail($identifier);
        
        if ($user) {
            error_log("👤 Usuario encontrado por email: " . $user['email']);
            error_log("🔑 Verificando contraseña...");
            
            // Verificar contraseña y que el usuario esté activo
            if ($this->userModel->verifyPassword($password, $user['password_hash']) && $user['is_active']) {
                error_log("✅ Contraseña correcta y usuario activo");
                return $user;
            } else {
                error_log("❌ Contraseña incorrecta o usuario inactivo");
            }
        } else {
            error_log("❌ Usuario no encontrado por email: $identifier");
        }
        
        return false;
    }*/
    private function authenticate($identifier, $password) {
        error_log("🔐 Intentando autenticar: $identifier");
        
        $user = $this->userModel->getUserByEmail($identifier);
        /*
        if ($user) {
            error_log("👤 Usuario encontrado: " . print_r($user, true)); // ← MOSTRAR DATOS DEL USUARIO
            error_log("🔑 Hash en BD: " . ($user['password_hash'] ?? 'NO HASH'));
            error_log("🔑 Contraseña ingresada: $password");
            
            if ($this->userModel->verifyPassword($password, $user['password_hash']) && $user['is_active']) {
                error_log("✅ Contraseña correcta y usuario activo");
                return $user;
            } else {
                error_log("❌ Contraseña incorrecta o usuario inactivo");
                error_log("❌ is_active: " . ($user['is_active'] ? 'true' : 'false'));
            }
        }*/
// TEMPORAL: Para testing
if ($user && $user['is_active']) {
    error_log("✅ USUARIO ACTIVO - ACCESO PERMITIDO (verificación desactivada temporalmente)");
    return $user;
}            
        
        return false;
    }    
    private function showLogin($error = null) {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Login - Sistema Edificios Chile</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
            <style>
                body { 
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                    height: 100vh; 
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                }
                .login-container { 
                    height: 100vh; 
                    display: flex; 
                    align-items: center; 
                    justify-content: center; 
                }
                .login-card { 
                    width: 100%; 
                    max-width: 400px; 
                }
            </style>
        </head>
        <body>
            <div class="login-container">
                <div class="login-card">
                    <div class="card shadow-lg">
                        <div class="card-body p-4">
                            <h3 class="text-center text-primary mb-4">
                                <i class="bi bi-building"></i><br>
                                Sistema Edificios Chile
                            </h3>
                            
                            <?php if ($error): ?>
                                <div class="alert alert-danger">
                                    <i class="bi bi-exclamation-triangle"></i> 
                                    <?= htmlspecialchars($error) ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- FORMULARIO CON DEBUGGING -->
                            <form method="POST" action="/proyectos/edificios/login" id="loginForm">
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="text" name="email" class="form-control" 
                                        value="admin@sistemaedificios.cl" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Contraseña</label>
                                    <input type="password" name="password" class="form-control" 
                                        value="Admin123!" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 py-2" id="submitBtn">
                                    <i class="bi bi-box-arrow-in-right"></i> Ingresar
                                </button>
                            </form>

                            <!-- Debug info -->
                            <div class="mt-3 p-2 bg-light rounded small">
                                <div>URL Actual: <span id="currentUrl"></span></div>
                                <div>Action del Form: /proyectos/edificios/login</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                // Mostrar URL actual para debugging
                document.getElementById('currentUrl').textContent = window.location.href;
                
                // Debug del envío del formulario
                document.getElementById('loginForm').addEventListener('submit', function(e) {
                    console.log('🔍 Formulario enviado');
                    console.log('📤 Action:', this.action);
                    console.log('📝 Method:', this.method);
                    
                    const formData = new FormData(this);
                    for (let [key, value] of formData.entries()) {
                        console.log(`📦 ${key}: ${value}`);
                    }
                    
                    // Cambiar texto del botón para indicar envío
                    document.getElementById('submitBtn').innerHTML = '<i class="bi bi-arrow-repeat spinner"></i> Procesando...';
                    document.getElementById('submitBtn').disabled = true;
                });

                // Estilo para spinner
                const style = document.createElement('style');
                style.textContent = `
                    .spinner {
                        animation: spin 1s linear infinite;
                    }
                    @keyframes spin {
                        from { transform: rotate(0deg); }
                        to { transform: rotate(360deg); }
                    }
                `;
                document.head.appendChild(style);
            </script>
        </body>
        </html>
        <?php
    }
}
?>