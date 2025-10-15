<?php
// 📁 views/auth/login.php
?>
<div class="container-fluid vh-100 bg-light">
    <div class="row h-100 justify-content-center align-items-center">
        <div class="col-md-4 col-lg-3">
            <div class="card card-hover shadow border-0">
                <div class="card-body p-4">
                    <!-- Logo y Título -->
                    <div class="text-center mb-4">
                        <div class="bg-primary rounded-circle d-inline-flex p-3 mb-3">
                            <i class="bi bi-building text-white fs-2"></i>
                        </div>
                        <h3 class="text-dark fw-bold">Edificios Chile</h3>
                        <p class="text-muted small">Sistema de Administración</p>
                    </div>

                    <!-- Mensajes de éxito/error -->
                    <?php if (isset($_GET['logout'])): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="bi bi-check-circle me-2"></i>
                            Sesión cerrada exitosamente
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <?php foreach ($errors as $error): ?>
                                <div><?= htmlspecialchars($error) ?></div>
                            <?php endforeach; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Formulario de Login -->
                    <form method="POST" class="needs-validation">
                        <div class="mb-3">
                            <label for="email" class="form-label small text-uppercase text-muted fw-bold">
                                <i class="bi bi-person me-1"></i>Email o RUT
                            </label>
                            <input type="text" 
                                   class="form-control form-control-lg" 
                                   id="email" 
                                   name="email" 
                                   value="<?= htmlspecialchars($email ?? '') ?>" 
                                   placeholder="usuario@ejemplo.cl o 12.345.678-9" 
                                   required>
                            <div class="invalid-feedback">
                                Por favor ingresa tu email o RUT
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label small text-uppercase text-muted fw-bold">
                                <i class="bi bi-lock me-1"></i>Contraseña
                            </label>
                            <input type="password" 
                                   class="form-control form-control-lg" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Tu contraseña" 
                                   required>
                            <div class="invalid-feedback">
                                Por favor ingresa tu contraseña
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg w-100 py-3 fw-bold">
                            <i class="bi bi-box-arrow-in-right me-2"></i>
                            Iniciar Sesión
                        </button>
                    </form>

                    <!-- Enlaces adicionales -->
                    <div class="text-center mt-4">
                        <a href="#" class="text-muted small text-decoration-none">
                            <i class="bi bi-question-circle me-1"></i>¿Problemas para acceder?
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Footer de login -->
            <div class="text-center mt-4">
                <p class="text-muted small">
                    &copy; <?= date('Y') ?> Sistema Edificios Chile. 
                    <span class="d-block d-sm-inline">Todos los derechos reservados.</span>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
// Validación de formulario frontend
(function() {
    'use strict';
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();

// Mostrar/ocultar contraseña
document.getElementById('password').addEventListener('input', function() {
    const feedback = this.nextElementSibling;
    if (this.value.length > 0 && this.value.length < 6) {
        feedback.textContent = 'La contraseña debe tener al menos 6 caracteres';
    }
});
</script>