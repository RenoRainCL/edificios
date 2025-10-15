<?php
// 📁 views/users/editar.php
?>
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Editar Usuario</h1>
        <a href="<?= $url->to('usuarios') ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver a Usuarios
        </a>
    </div>

    <!-- Mensajes Flash -->
    <?php if (!empty($flash_messages)): ?>
        <?php foreach ($flash_messages as $flash): ?>
            <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show">
                <?= htmlspecialchars($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Formulario -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Editando: <?= htmlspecialchars($user['nombre']) ?>
                <?php if ($user['id'] == $_SESSION['user_id']): ?>
                    <span class="badge bg-info ms-2">Tu cuenta</span>
                <?php endif; ?>
            </h6>
        </div>
        <div class="card-body">
            <form method="POST" id="userForm">
                <div class="row">
                    <div class="col-md-6">
                        <!-- Información Básica -->
                        <div class="mb-3">
                            <label class="form-label">Nombre Completo *</label>
                            <input type="text" name="nombre" class="form-control" 
                                   value="<?= htmlspecialchars($user['nombre']) ?>" 
                                   required minlength="3">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Apellido *</label>
                            <input type="text" name="apellido" class="form-control" 
                                value="<?= htmlspecialchars($user['apellido']) ?>" 
                                required minlength="3">
                        </div>                        

                        <div class="mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control" 
                                   value="<?= htmlspecialchars($user['email']) ?>" 
                                   required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">RUT</label>
                            <input type="text" name="rut" class="form-control" 
                                   value="<?= htmlspecialchars($user['rut'] ?? '') ?>" 
                                   placeholder="12.345.678-9">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <!-- Seguridad y Roles -->
                        <div class="mb-3">
                            <label class="form-label">Nueva Contraseña</label>
                            <input type="password" name="password" id="password" class="form-control" 
                                   minlength="6">
                            <div class="form-text">Dejar en blanco para mantener la contraseña actual</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Confirmar Contraseña</label>
                            <input type="password" name="password_confirm" id="password_confirm" 
                                   class="form-control">
                            <div class="form-text" id="passwordMatch"></div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Rol del Usuario *</label>
                            <select name="role_id" class="form-select" required>
                                <option value="">Seleccionar Rol</option>
                                <?php foreach ($roles as $role): ?>
                                <option value="<?= $role['id'] ?>" 
                                        <?= ($user['role_id'] == $role['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($role['name']) ?> - <?= htmlspecialchars($role['description']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Asignación de Edificios -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="bi bi-building me-2"></i>Edificios Asignados
                                </h6>
                                <p class="text-muted small mb-3">Selecciona los edificios a los que tendrá acceso este usuario</p>
                                
                                <div class="row">
                                    <?php if (empty($edificios)): ?>
                                        <div class="col-12">
                                            <div class="alert alert-warning">
                                                <i class="bi bi-exclamation-triangle"></i>
                                                No hay edificios disponibles.
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <?php 
                                        $userEdificioIds = array_column($user_edificios, 'id');
                                        ?>
                                        <?php foreach ($edificios as $edificio): ?>
                                        <div class="col-md-4 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="edificios[]" value="<?= $edificio['id'] ?>"
                                                       id="edificio_<?= $edificio['id'] ?>"
                                                       <?= (in_array($edificio['id'], $userEdificioIds)) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="edificio_<?= $edificio['id'] ?>">
                                                    <strong><?= htmlspecialchars($edificio['nombre']) ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?= htmlspecialchars($edificio['direccion']) ?></small>
                                                    <?php if (isset($edificio['is_primary_admin']) && $edificio['is_primary_admin']): ?>
                                                        <span class="badge bg-primary ms-1">Admin</span>
                                                    <?php endif; ?>
                                                </label>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Información del Sistema -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card border-0">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="bi bi-info-circle me-2"></i>Información del Sistema
                                </h6>
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong>ID Usuario:</strong> <?= $user['id'] ?>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Fecha Creación:</strong> <?= date('d/m/Y H:i', strtotime($user['created_at'])) ?>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Estado:</strong> 
                                        <span class="badge bg-<?= $user['is_active'] ? 'success' : 'danger' ?>">
                                            <?= $user['is_active'] ? 'Activo' : 'Inactivo' ?>
                                        </span>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Rol Actual:</strong> 
                                        <span class="badge bg-secondary"><?= htmlspecialchars($user['role_name'] ?? 'Sin rol') ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Botones -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between">
                            <a href="<?= $url->to('usuarios') ?>" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> Actualizar Usuario
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const password = document.getElementById('password');
    const passwordConfirm = document.getElementById('password_confirm');
    const passwordMatch = document.getElementById('passwordMatch');
    
    function validatePassword() {
        if (password.value === '' && passwordConfirm.value === '') {
            passwordMatch.innerHTML = '<span class="text-muted">Contraseña actual se mantendrá</span>';
            return true;
        }
        
        if (password.value !== passwordConfirm.value) {
            passwordMatch.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle"></i> Las contraseñas no coinciden</span>';
            return false;
        } else if (password.value.length >= 6) {
            passwordMatch.innerHTML = '<span class="text-success"><i class="bi bi-check-circle"></i> Las contraseñas coinciden</span>';
            return true;
        } else {
            passwordMatch.innerHTML = '<span class="text-warning">Mínimo 6 caracteres</span>';
            return false;
        }
    }
    
    password.addEventListener('input', validatePassword);
    passwordConfirm.addEventListener('input', validatePassword);
    
    // Validación del formulario
    document.getElementById('userForm').addEventListener('submit', function(e) {
        if (!validatePassword()) {
            e.preventDefault();
            alert('Por favor, corrige los errores en las contraseñas.');
            return;
        }
        
        // Mostrar loading
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Actualizando...';
        submitBtn.disabled = true;
    });
});
</script>