<?php
// 📁 views/finanzas/registrar_pago.php

// Recuperar datos del formulario si hay errores
$formData = $_SESSION['form_data'] ?? [];
$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_data'], $_SESSION['form_errors']);

// Si viene un pago específico por GET
$pagoSeleccionado = null;
if (isset($_GET['pago_id']) && !empty($pagos_pendientes)) {
    $pagoSeleccionado = array_filter($pagos_pendientes, fn($p) => $p['pago_id'] == $_GET['pago_id']);
    $pagoSeleccionado = $pagoSeleccionado ? reset($pagoSeleccionado) : null;
}
?>
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-cash-coin me-2"></i>Registrar Pago
            </h1>
            <p class="text-muted">Registrar pago manual de gastos comunes</p>
        </div>
        <a href="<?= $url->to("finanzas/pagos") ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Volver a Pagos
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

    <div class="row">
        <div class="col-lg-8">
            <div class="card card-hover">
                <div class="card-header bg-transparent">
                    <h6 class="m-0 fw-bold">Información del Pago</h6>
                </div>
                <div class="card-body">
                    <form method="POST" class="needs-validation" novalidate>
                        <!-- Selección de pago pendiente -->
                        <div class="mb-4">
                            <label for="pago_id" class="form-label small text-uppercase text-muted fw-bold">
                                <i class="bi bi-search me-1"></i>Seleccionar Pago Pendiente *
                            </label>
                            <select class="form-select" id="pago_id" name="pago_id" required 
                                    onchange="actualizarInfoPago(this.value)">
                                <option value="">Seleccionar pago pendiente...</option>
                                <?php foreach ($pagos_pendientes as $pago): ?>
                                    <option value="<?= $pago['pago_id'] ?>" 
                                        <?= ($formData['pago_id'] ?? ($pagoSeleccionado ? $pagoSeleccionado['pago_id'] : '')) == $pago['pago_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($pago['edificio_nombre']) ?> - 
                                        Depto. <?= htmlspecialchars($pago['numero']) ?> - 
                                        <?= htmlspecialchars($pago['gasto_nombre']) ?> - 
                                        $<?= number_format($pago['monto'], 0, ',', '.') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">
                                Por favor selecciona un pago pendiente
                            </div>
                        </div>

                        <!-- Información del pago seleccionado -->
                        <div id="info-pago" class="alert alert-info" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Departamento:</strong> 
                                    <span id="info-depto">-</span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Propietario:</strong> 
                                    <span id="info-propietario">-</span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Gasto:</strong> 
                                    <span id="info-gasto">-</span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Período:</strong> 
                                    <span id="info-periodo">-</span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Monto:</strong> 
                                    <span id="info-monto">-</span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Estado:</strong> 
                                    <span id="info-estado">-</span>
                                </div>
                            </div>
                        </div>

                        <!-- Información del pago -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="fecha_pago" class="form-label small text-uppercase text-muted fw-bold">
                                    <i class="bi bi-calendar me-1"></i>Fecha de Pago *
                                </label>
                                <input type="date" class="form-control" id="fecha_pago" name="fecha_pago" 
                                       value="<?= htmlspecialchars($formData['fecha_pago'] ?? date('Y-m-d')) ?>" 
                                       required>
                                <div class="invalid-feedback">
                                    Por favor ingresa la fecha de pago
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="metodo_pago" class="form-label small text-uppercase text-muted fw-bold">
                                    <i class="bi bi-credit-card me-1"></i>Método de Pago *
                                </label>
                                <select class="form-select" id="metodo_pago" name="metodo_pago" required>
                                    <option value="">Seleccionar método...</option>
                                    <option value="transferencia" <?= ($formData['metodo_pago'] ?? '') == 'transferencia' ? 'selected' : '' ?>>Transferencia</option>
                                    <option value="efectivo" <?= ($formData['metodo_pago'] ?? '') == 'efectivo' ? 'selected' : '' ?>>Efectivo</option>
                                    <option value="cheque" <?= ($formData['metodo_pago'] ?? '') == 'cheque' ? 'selected' : '' ?>>Cheque</option>
                                    <option value="webpay" <?= ($formData['metodo_pago'] ?? '') == 'webpay' ? 'selected' : '' ?>>Webpay</option>
                                    <option value="debito_automatico" <?= ($formData['metodo_pago'] ?? '') == 'debito_automatico' ? 'selected' : '' ?>>Débito Automático</option>
                                </select>
                                <div class="invalid-feedback">
                                    Por favor selecciona el método de pago
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="numero_comprobante" class="form-label small text-uppercase text-muted fw-bold">
                                    <i class="bi bi-receipt me-1"></i>N° Comprobante
                                </label>
                                <input type="text" class="form-control" id="numero_comprobante" name="numero_comprobante" 
                                       value="<?= htmlspecialchars($formData['numero_comprobante'] ?? '') ?>" 
                                       placeholder="Número de comprobante o transacción">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="referencia_bancaria" class="form-label small text-uppercase text-muted fw-bold">
                                    <i class="bi bi-bank me-1"></i>Referencia Bancaria
                                </label>
                                <input type="text" class="form-control" id="referencia_bancaria" name="referencia_bancaria" 
                                       value="<?= htmlspecialchars($formData['referencia_bancaria'] ?? '') ?>" 
                                       placeholder="Referencia o código bancario">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="observaciones" class="form-label small text-uppercase text-muted fw-bold">
                                <i class="bi bi-chat-text me-1"></i>Observaciones
                            </label>
                            <textarea class="form-control" id="observaciones" name="observaciones" 
                                      rows="3" placeholder="Observaciones adicionales del pago"><?= htmlspecialchars($formData['observaciones'] ?? '') ?></textarea>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="<?= $url->to("finanzas/pagos") ?>" class="btn btn-outline-secondary me-md-2">
                                <i class="bi bi-x-circle me-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i>Registrar Pago
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Información adicional -->
        <div class="col-lg-4">
            <div class="card card-hover">
                <div class="card-header bg-transparent">
                    <h6 class="m-0 fw-bold">Resumen de Pagos Pendientes</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($pagos_pendientes)): ?>
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-check-circle display-4"></i>
                            <p class="mt-2">No hay pagos pendientes</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php 
                            $agrupados = [];
                            foreach ($pagos_pendientes as $pago) {
                                $key = $pago['edificio_nombre'];
                                if (!isset($agrupados[$key])) {
                                    $agrupados[$key] = 0;
                                }
                                $agrupados[$key]++;
                            }
                            ?>
                            <?php foreach ($agrupados as $edificio => $cantidad): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= htmlspecialchars($edificio) ?>
                                    <span class="badge bg-primary rounded-pill"><?= $cantidad ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-3 text-center">
                            <small class="text-muted">
                                Total: <?= count($pagos_pendientes) ?> pagos pendientes
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Datos de los pagos pendientes para JavaScript
const pagosData = <?= json_encode($pagos_pendientes) ?>;

function actualizarInfoPago(pagoId) {
    const infoDiv = document.getElementById('info-pago');
    const pago = pagosData.find(p => p.pago_id == pagoId);
    
    if (pago) {
        document.getElementById('info-depto').textContent = pago.numero;
        document.getElementById('info-propietario').textContent = pago.propietario_nombre || 'No asignado';
        document.getElementById('info-gasto').textContent = pago.gasto_nombre;
        document.getElementById('info-periodo').textContent = pago.periodo;
        document.getElementById('info-monto').textContent = '$' + pago.monto.toLocaleString('es-CL');
        document.getElementById('info-estado').textContent = pago.estado;
        
        infoDiv.style.display = 'block';
    } else {
        infoDiv.style.display = 'none';
    }
}

// Inicializar información si hay un pago seleccionado
document.addEventListener('DOMContentLoaded', function() {
    const pagoSelect = document.getElementById('pago_id');
    <?php if ($pagoSeleccionado): ?>
        actualizarInfoPago('<?= $pagoSeleccionado['pago_id'] ?>');
    <?php endif; ?>
    
    // Validación de formulario
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
});
</script>