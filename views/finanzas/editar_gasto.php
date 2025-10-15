<?php
// 📁 views/finanzas/editar_gasto.php

if (!$gasto) {
    echo '<div class="alert alert-danger">Gasto común no encontrado</div>';
    return;
}

// Recuperar datos del formulario si hay errores
$formData = $_SESSION['form_data'] ?? $gasto;
$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_data'], $_SESSION['form_errors']);
?>
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-pencil me-2"></i>Editar Gasto Común
            </h1>
            <p class="text-muted">Modificar gasto común existente</p>
        </div>
        <a href="<?= $url->to('finanzas/gastos-comunes') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Volver
        </a>
    </div>

    <!-- Mensajes de error -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Errores encontrados:</strong>
            <ul class="mb-0 mt-2">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-hover">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold">Información del Gasto Común</h6>
                    <span class="badge bg-<?= $gasto['estado'] === 'pendiente' ? 'warning' : 'secondary' ?>">
                        <?= ucfirst($gasto['estado']) ?>
                    </span>
                </div>
                <div class="card-body">
                    <?php if ($gasto['estado'] !== 'pendiente'): ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Solo se pueden editar gastos en estado "Pendiente"
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="needs-validation" novalidate 
                          <?= $gasto['estado'] !== 'pendiente' ? 'onsubmit="return false;"' : '' ?>>
                        
                        <div class="mb-3">
                            <label class="form-label small text-uppercase text-muted fw-bold">
                                <i class="bi bi-building me-1"></i>Edificio
                            </label>
                            <p class="form-control-plaintext"><?= htmlspecialchars($gasto['edificio_nombre']) ?></p>
                            <input type="hidden" name="edificio_id" value="<?= $gasto['edificio_id'] ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-uppercase text-muted fw-bold">
                                <i class="bi bi-calendar me-1"></i>Período
                            </label>
                            <p class="form-control-plaintext"><?= date('F Y', strtotime($gasto['periodo'])) ?></p>
                        </div>

                        <div class="mb-3">
                            <label for="nombre" class="form-label small text-uppercase text-muted fw-bold">
                                <i class="bi bi-tag me-1"></i>Nombre del Gasto *
                            </label>
                            <input type="text" class="form-control" id="nombre" name="nombre" 
                                   value="<?= htmlspecialchars($formData['nombre'] ?? '') ?>" 
                                   placeholder="Ej: Gastos Comunes Enero 2024" 
                                   <?= $gasto['estado'] !== 'pendiente' ? 'readonly' : '' ?> required>
                            <div class="invalid-feedback">
                                Por favor ingresa el nombre del gasto
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label small text-uppercase text-muted fw-bold">
                                <i class="bi bi-text-paragraph me-1"></i>Descripción
                            </label>
                            <textarea class="form-control" id="descripcion" name="descripcion" 
                                      rows="3" placeholder="Descripción detallada del gasto común"
                                      <?= $gasto['estado'] !== 'pendiente' ? 'readonly' : '' ?>><?= htmlspecialchars($formData['descripcion'] ?? '') ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="monto_total" class="form-label small text-uppercase text-muted fw-bold">
                                    <i class="bi bi-currency-dollar me-1"></i>Monto Total *
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="monto_total" name="monto_total" 
                                           value="<?= htmlspecialchars($formData['monto_total'] ?? '') ?>" 
                                           min="0" step="0.01" placeholder="0.00" 
                                           <?= $gasto['estado'] !== 'pendiente' ? 'readonly' : '' ?> required>
                                </div>
                                <div class="invalid-feedback">
                                    Por favor ingresa el monto total
                                </div>
                                <?php if ($gasto['estado'] !== 'pendiente'): ?>
                                    <small class="text-muted">El monto no se puede modificar porque el gasto ya fue emitido</small>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="fecha_vencimiento" class="form-label small text-uppercase text-muted fw-bold">
                                    <i class="bi bi-calendar-x me-1"></i>Fecha Vencimiento *
                                </label>
                                <input type="date" class="form-control" id="fecha_vencimiento" name="fecha_vencimiento" 
                                       value="<?= htmlspecialchars($formData['fecha_vencimiento'] ?? '') ?>" 
                                       <?= $gasto['estado'] !== 'pendiente' ? 'readonly' : '' ?> required>
                                <div class="invalid-feedback">
                                    Por favor ingresa la fecha de vencimiento
                                </div>
                            </div>
                        </div>

                        <!-- Información de distribución -->
                        <div class="alert alert-info">
                            <div class="d-flex">
                                <i class="bi bi-info-circle me-2 mt-1"></i>
                                <div>
                                    <strong>Distribución automática</strong><br>
                                    <?php if ($gasto['estado'] === 'pendiente'): ?>
                                        Al modificar el monto total, la distribución se recalculará automáticamente.
                                    <?php else: ?>
                                        La distribución ya fue calculada y no se puede modificar.
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="<?= $url->to('finanzas/gastos-comunes') ?>" class="btn btn-outline-secondary me-md-2">
                                <i class="bi bi-x-circle me-2"></i>Cancelar
                            </a>
                            <?php if ($gasto['estado'] === 'pendiente'): ?>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-2"></i>Actualizar Gasto
                                </button>
                            <?php else: ?>
                                <button type="button" class="btn btn-secondary" disabled>
                                    <i class="bi bi-lock me-2"></i>Edición Bloqueada
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validación de formulario
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

// Validación de fechas
document.getElementById('fecha_vencimiento').addEventListener('change', function() {
    if (this.readOnly) return;
    
    const hoy = new Date().toISOString().split('T')[0];
    if (this.value < hoy) {
        this.setCustomValidity('La fecha de vencimiento no puede ser anterior a hoy');
    } else {
        this.setCustomValidity('');
    }
});
</script>