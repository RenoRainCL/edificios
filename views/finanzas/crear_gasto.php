<?php
// 📁 views/finanzas/crear_gasto.php

// Recuperar datos del formulario si hay errores
$formData = $_SESSION['form_data'] ?? [];
$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_data'], $_SESSION['form_errors']);
?>
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-plus-circle me-2"></i>Crear Gasto Común
            </h1>
            <p class="text-muted">Registrar nuevo gasto común para un edificio</p>
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
                <div class="card-header bg-transparent">
                    <h6 class="m-0 fw-bold">Información del Gasto Común</h6>
                </div>
                <div class="card-body">
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edificio_id" class="form-label small text-uppercase text-muted fw-bold">
                                    <i class="bi bi-building me-1"></i>Edificio *
                                </label>
                                <select class="form-select" id="edificio_id" name="edificio_id" required>
                                    <option value="">Seleccionar edificio</option>
                                    <?php foreach ($edificios as $edificio): ?>
                                        <option value="<?= $edificio['id'] ?>" 
                                            <?= ($formData['edificio_id'] ?? '') == $edificio['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($edificio['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">
                                    Por favor selecciona un edificio
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="periodo" class="form-label small text-uppercase text-muted fw-bold">
                                    <i class="bi bi-calendar me-1"></i>Período *
                                </label>
                                <input type="month" class="form-control" id="periodo" name="periodo" 
                                       value="<?= htmlspecialchars($formData['periodo'] ?? $periodo_actual) ?>" 
                                       required>
                                <div class="invalid-feedback">
                                    Por favor ingresa el período
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="nombre" class="form-label small text-uppercase text-muted fw-bold">
                                <i class="bi bi-tag me-1"></i>Nombre del Gasto *
                            </label>
                            <input type="text" class="form-control" id="nombre" name="nombre" 
                                   value="<?= htmlspecialchars($formData['nombre'] ?? '') ?>" 
                                   placeholder="Ej: Gastos Comunes Enero 2024" required>
                            <div class="invalid-feedback">
                                Por favor ingresa el nombre del gasto
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label small text-uppercase text-muted fw-bold">
                                <i class="bi bi-text-paragraph me-1"></i>Descripción
                            </label>
                            <textarea class="form-control" id="descripcion" name="descripcion" 
                                      rows="3" placeholder="Descripción detallada del gasto común"><?= htmlspecialchars($formData['descripcion'] ?? '') ?></textarea>
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
                                           min="0" step="0.01" placeholder="0.00" required>
                                </div>
                                <div class="invalid-feedback">
                                    Por favor ingresa el monto total
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="fecha_vencimiento" class="form-label small text-uppercase text-muted fw-bold">
                                    <i class="bi bi-calendar-x me-1"></i>Fecha Vencimiento *
                                </label>
                                <input type="date" class="form-control" id="fecha_vencimiento" name="fecha_vencimiento" 
                                       value="<?= htmlspecialchars($formData['fecha_vencimiento'] ?? $fecha_vencimiento) ?>" 
                                       required>
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
                                    El gasto se distribuirá automáticamente entre todos los departamentos del edificio 
                                    según su porcentaje de copropiedad.
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="<?= $url->to('finanzas/gastos-comunes') ?>" class="btn btn-outline-secondary me-md-2">
                                <i class="bi bi-x-circle me-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i>Crear Gasto Común
                            </button>
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
    const hoy = new Date().toISOString().split('T')[0];
    if (this.value < hoy) {
        this.setCustomValidity('La fecha de vencimiento no puede ser anterior a hoy');
    } else {
        this.setCustomValidity('');
    }
});

// Mostrar preview de distribución cuando se selecciona edificio
document.getElementById('edificio_id').addEventListener('change', function() {
    const edificioId = this.value;
    if (edificioId) {
        // Aquí podrías hacer una llamada AJAX para obtener información del edificio
        console.log('Edificio seleccionado:', edificioId);
    }
});
</script>