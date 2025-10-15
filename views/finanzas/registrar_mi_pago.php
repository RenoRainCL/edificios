<?php
// 📁 views/finanzas/registrar_mi_pago.php

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

// Verificar si hay pagos pendientes
$hayPagosPendientes = !empty($pagos_pendientes);
?>
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-cash-coin me-2"></i>Registrar Mi Pago
            </h1>
            <p class="text-muted">Registrar pago de gastos comunes (requiere aprobación)</p>
        </div>
        <a href="<?= $url->to("finanzas/mis-pagos") ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Volver a Mis Pagos
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

    <?php if (!$hayPagosPendientes): ?>
        <!-- NO HAY PAGOS PENDIENTES -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card card-hover">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-check-circle display-1 text-success mb-4"></i>
                        <h3 class="text-success">¡No tienes pagos pendientes!</h3>
                        <p class="text-muted mb-4">
                            Actualmente no hay gastos comunes pendientes de pago para tus departamentos.
                        </p>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <i class="bi bi-receipt text-primary fs-1 mb-3"></i>
                                        <h5>Ver Gastos Comunes</h5>
                                        <p class="text-muted small">Revisa los gastos comunes generados para tus departamentos</p>
                                        <a href="<?= $url->to('finanzas/gastos-comunes') ?>" class="btn btn-outline-primary btn-sm">
                                            Ver Gastos
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <i class="bi bi-clock-history text-warning fs-1 mb-3"></i>
                                        <h5>Mis Pagos</h5>
                                        <p class="text-muted small">Revisa el historial de tus pagos anteriores</p>
                                        <a href="<?= $url->to('finanzas/mis-pagos') ?>" class="btn btn-outline-warning btn-sm">
                                            Ver Historial
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Información:</strong> Los pagos pendientes aparecerán aquí cuando se generen nuevos gastos comunes 
                            para tus departamentos o cuando tengas pagos atrasados.
                        </div>

                        <a href="<?= $url->to('finanzas/mis-pagos') ?>" class="btn btn-primary">
                            <i class="bi bi-arrow-left me-2"></i>Volver a Mis Pagos
                        </a>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- HAY PAGOS PENDIENTES - MOSTRAR FORMULARIO -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card card-hover">
                    <div class="card-header bg-transparent">
                        <h6 class="m-0 fw-bold">Registro de Pago</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="needs-validation" novalidate>
                            <!-- Información importante -->
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Importante:</strong> Todos los pagos registrados requieren aprobación del administrador antes de ser marcados como pagados.
                            </div>

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
                                            <?= ($formData['pago_id'] ?? ($pagoSeleccionado ? $pagoSeleccionado['pago_id'] : '')) == $pago['pago_id'] ? 'selected' : '' ?>
                                            data-pago='<?= json_encode($pago) ?>'>
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
                            <div id="info-pago" class="alert alert-secondary" style="display: none;">
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
                                        <strong>Gasto Común:</strong> 
                                        <span id="info-gasto">-</span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Período:</strong> 
                                        <span id="info-periodo">-</span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Monto a Pagar:</strong> 
                                        <span id="info-monto" class="fw-bold">-</span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Vencimiento:</strong> 
                                        <span id="info-vencimiento">-</span>
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
                                           required max="<?= date('Y-m-d') ?>">
                                    <div class="invalid-feedback">
                                        Por favor ingresa la fecha de pago (no puede ser futura)
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="metodo_pago" class="form-label small text-uppercase text-muted fw-bold">
                                        <i class="bi bi-credit-card me-1"></i>Método de Pago *
                                    </label>
                                    <select class="form-select" id="metodo_pago" name="metodo_pago" required>
                                        <option value="">Seleccionar método...</option>
                                        <option value="transferencia" <?= ($formData['metodo_pago'] ?? '') == 'transferencia' ? 'selected' : '' ?>>Transferencia Bancaria</option>
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
                                        <i class="bi bi-receipt me-1"></i>N° Comprobante *
                                    </label>
                                    <input type="text" class="form-control" id="numero_comprobante" name="numero_comprobante" 
                                           value="<?= htmlspecialchars($formData['numero_comprobante'] ?? '') ?>" 
                                           placeholder="Número de comprobante o transacción"
                                           required>
                                    <div class="invalid-feedback">
                                        Por favor ingresa el número de comprobante
                                    </div>
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
                                          rows="3" placeholder="Observaciones adicionales sobre el pago (opcional)"><?= htmlspecialchars($formData['observaciones'] ?? '') ?></textarea>
                            </div>

                            <!-- Acuerdo de términos -->
                            <div class="alert alert-warning">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="acepto_terminos" required>
                                    <label class="form-check-label" for="acepto_terminos">
                                        Confirmo que la información proporcionada es verídica y acepto que este pago 
                                        será revisado por el administrador antes de ser aprobado.
                                    </label>
                                    <div class="invalid-feedback">
                                        Debes aceptar los términos para continuar
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="<?= $url->to("finanzas/mis-pagos") ?>" class="btn btn-outline-secondary me-md-2">
                                    <i class="bi bi-x-circle me-2"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send-check me-2"></i>Enviar para Aprobación
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Panel informativo -->
            <div class="col-lg-4">
                <!-- Información sobre el proceso -->
                <div class="card card-hover mb-4">
                    <div class="card-header bg-transparent">
                        <h6 class="m-0 fw-bold">Proceso de Aprobación</h6>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <div class="list-group-item d-flex align-items-start">
                                <span class="badge bg-primary rounded-pill me-3">1</span>
                                <div>
                                    <strong>Registro</strong>
                                    <p class="mb-0 small text-muted">Completas el formulario con los datos del pago</p>
                                </div>
                            </div>
                            <div class="list-group-item d-flex align-items-start">
                                <span class="badge bg-warning rounded-pill me-3">2</span>
                                <div>
                                    <strong>Revisión</strong>
                                    <p class="mb-0 small text-muted">El administrador revisa la información proporcionada</p>
                                </div>
                            </div>
                            <div class="list-group-item d-flex align-items-start">
                                <span class="badge bg-success rounded-pill me-3">3</span>
                                <div>
                                    <strong>Aprobación</strong>
                                    <p class="mb-0 small text-muted">Una vez aprobado, el pago se marca como completado</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagos pendientes rápidos -->
                <div class="card card-hover">
                    <div class="card-header bg-transparent">
                        <h6 class="m-0 fw-bold">Pagos Pendientes Rápidos</h6>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <?php 
                            $contador = 0;
                            foreach ($pagos_pendientes as $pago): 
                                if ($contador >= 5) break;
                                $contador++;
                            ?>
                                <a href="<?= $url->to("finanzas/mis-pagos/registrar?pago_id={$pago['pago_id']}") ?>" 
                                   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="text-muted"><?= htmlspecialchars($pago['edificio_nombre']) ?></small><br>
                                        <strong>$<?= number_format($pago['monto'], 0, ',', '.') ?></strong>
                                        <small class="d-block text-muted"><?= htmlspecialchars($pago['gasto_nombre']) ?></small>
                                    </div>
                                    <i class="bi bi-arrow-right text-primary"></i>
                                </a>
                            <?php endforeach; ?>
                        </div>
                        <?php if (count($pagos_pendientes) > 5): ?>
                            <div class="mt-3 text-center">
                                <small class="text-muted">
                                    +<?= count($pagos_pendientes) - 5 ?> pagos más pendientes
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <script>
        function actualizarInfoPago(pagoId) {
            const infoDiv = document.getElementById('info-pago');
            const select = document.getElementById('pago_id');
            const option = select.querySelector(`option[value="${pagoId}"]`);
            
            if (option && option.dataset.pago) {
                const pago = JSON.parse(option.dataset.pago);
                
                document.getElementById('info-depto').textContent = pago.numero;
                document.getElementById('info-propietario').textContent = pago.propietario_nombre || 'No asignado';
                document.getElementById('info-gasto').textContent = pago.gasto_nombre;
                document.getElementById('info-periodo').textContent = pago.periodo;
                document.getElementById('info-monto').textContent = '$' + pago.monto.toLocaleString('es-CL');
                
                // Formatear fecha de vencimiento
                const fechaVencimiento = new Date(pago.fecha_vencimiento);
                document.getElementById('info-vencimiento').textContent = fechaVencimiento.toLocaleDateString('es-CL');
                
                // Resaltar si está vencido
                const hoy = new Date();
                if (fechaVencimiento < hoy) {
                    document.getElementById('info-vencimiento').className = 'text-danger fw-bold';
                }
                
                infoDiv.style.display = 'block';
            } else {
                infoDiv.style.display = 'none';
            }
        }

        // Inicializar información si hay un pago seleccionado
        document.addEventListener('DOMContentLoaded', function() {
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
            
            // Validar fecha no futura
            const fechaPagoInput = document.getElementById('fecha_pago');
            if (fechaPagoInput) {
                fechaPagoInput.addEventListener('change', function() {
                    const selectedDate = new Date(this.value);
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    
                    if (selectedDate > today) {
                        this.setCustomValidity('La fecha de pago no puede ser futura');
                    } else {
                        this.setCustomValidity('');
                    }
                });
            }
        });
        </script>
    <?php endif; ?>
</div>