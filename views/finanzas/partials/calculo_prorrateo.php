<?php
// 📁 views/finanzas/partials/calculo_prorrateo.php
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <!-- Distribución Calculada -->
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-bar-chart me-2"></i>Distribución Calculada
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Departamento</th>
                                    <th>Propietario</th>
                                    <th class="text-end">Porcentaje</th>
                                    <th class="text-end">Monto</th>
                                    <th>Estado</th>
                                    <th width="100">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="distribucionBody">
                                <?php foreach ($distribucion as $item): ?>
                                <tr class="<?= $item['es_exento'] ? 'table-warning' : '' ?>">
                                    <td>
                                        <strong>Depto <?= htmlspecialchars($item['numero']) ?></strong>
                                        <?php if ($item['es_exento']): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($item['motivo_exencion']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($item['propietario']) ?></td>
                                    <td class="text-end">
                                        <span class="badge bg-<?= $item['es_exento'] ? 'warning' : 'primary' ?>">
                                            <?= number_format($item['porcentaje_aplicado'], 2) ?>%
                                        </span>
                                        <?php if ($item['porcentaje_original'] != $item['porcentaje_aplicado']): ?>
                                        <br><small class="text-muted">
                                            Original: <?= number_format($item['porcentaje_original'], 2) ?>%
                                        </small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <strong>$<?= number_format($item['monto'], 0, ',', '.') ?></strong>
                                    </td>
                                    <td>
                                        <?php if ($item['es_exento']): ?>
                                        <span class="badge bg-warning">Exento</span>
                                        <?php else: ?>
                                        <span class="badge bg-success">Activo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-secondary" 
                                                onclick="editarManual(<?= $item['departamento_id'] ?>)"
                                                title="Editar manualmente">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-dark">
                                <tr>
                                    <td colspan="2"><strong>TOTAL</strong></td>
                                    <td class="text-end"><strong>100.00%</strong></td>
                                    <td class="text-end"><strong>$<?= number_format($montoTotal, 0, ',', '.') ?></strong></td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Resumen y Validación -->
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-graph-up me-2"></i>Resumen de Distribución
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Estrategia Aplicada</small>
                        <div class="fw-bold"><?= htmlspecialchars($estrategia['nombre']) ?></div>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted">Método de Cálculo</small>
                        <div class="fw-bold"><?= ucfirst(str_replace('_', ' ', $estrategia['metodo_calculo'])) ?></div>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted">Departamentos</small>
                        <div class="fw-bold"><?= count($distribucion) ?> departamentos</div>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted">Exenciones Aplicadas</small>
                        <div class="fw-bold text-warning">
                            <?= count(array_filter($distribucion, fn($item) => $item['es_exento'])) ?> exentos
                        </div>
                    </div>
                </div>
            </div>

            <!-- Validación Legal -->
            <div class="card mb-3">
                <div class="card-header <?= $validacionLegal['es_valida'] ? 'bg-success' : 'bg-danger' ?> text-white">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-shield-check me-2"></i>Validación Legal
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-<?= $validacionLegal['es_valida'] ? 'success' : 'danger' ?>">
                        <i class="bi bi-<?= $validacionLegal['es_valida'] ? 'check-circle' : 'exclamation-triangle' ?> me-2"></i>
                        <?= htmlspecialchars($validacionLegal['mensaje']) ?>
                    </div>
                    
                    <div class="small">
                        <strong>Variación Detectada:</strong> <?= number_format($validacionLegal['variacion_detectada'], 2) ?>%<br>
                        <strong>Límite Legal:</strong> <?= number_format($validacionLegal['variacion_maxima_permitida'], 2) ?>%<br>
                        <strong>Mínimo:</strong> <?= number_format($validacionLegal['departamento_minimo'], 2) ?>%<br>
                        <strong>Máximo:</strong> <?= number_format($validacionLegal['departamento_maximo'], 2) ?>%
                    </div>
                </div>
            </div>

            <!-- Acciones -->
            <div class="card">
                <div class="card-body">
                    <form id="formAprobarProrrateo" method="POST" 
                          action="<?= $url->to("finanzas/prorrateo/guardar/{$gastoId}") ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Justificación (Opcional)</label>
                            <textarea name="justificacion" class="form-control" rows="3" 
                                      placeholder="Justificación para la distribución aplicada..."></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <?php if ($validacionLegal['es_valida']): ?>
                            <button type="submit" name="accion" value="aprobar" 
                                    class="btn btn-success">
                                <i class="bi bi-check-lg me-2"></i>Aprobar y Aplicar
                            </button>
                            <?php else: ?>
                            <button type="button" class="btn btn-warning" disabled
                                    title="La distribución no cumple con los límites legales">
                                <i class="bi bi-exclamation-triangle me-2"></i>Corregir para Aprobar
                            </button>
                            <?php endif; ?>
                            
                            <button type="submit" name="accion" value="guardar_borrador" 
                                    class="btn btn-outline-primary">
                                <i class="bi bi-save me-2"></i>Guardar como Borrador
                            </button>

                            <button type="button" class="btn btn-outline-secondary" 
                                    onclick="recalcularConOtraEstrategia()">
                                <i class="bi bi-arrow-repeat me-2"></i>Recalcular
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function editarManual(departamentoId) {
    // Implementar edición manual de porcentajes
    const nuevoPorcentaje = prompt('Ingrese nuevo porcentaje:');
    if (nuevoPorcentaje && !isNaN(nuevoPorcentaje)) {
        // Actualizar distribución via AJAX
        actualizarPorcentajeManual(departamentoId, parseFloat(nuevoPorcentaje));
    }
}

function recalcularConOtraEstrategia() {
    // Volver al selector de estrategias
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalCalcularProrrateo'));
    modal.hide();
    
    // Mostrar modal de selección de estrategia
    setTimeout(() => {
        prorrateoUI.calcularProrrateo(<?= $gastoId ?>);
    }, 500);
}

document.getElementById('formAprobarProrrateo').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch(this.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Cerrar modal y recargar lista
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalCalcularProrrateo'));
            modal.hide();
            
            // Mostrar mensaje de éxito
            mostrarNotificacion('success', data.message);
            
            // Recargar lista principal
            document.dispatchEvent(new Event('reloadProrrateoList'));
        } else {
            mostrarNotificacion('error', data.error);
        }
    })
    .catch(error => {
        mostrarNotificacion('error', 'Error de conexión');
    });
});

function mostrarNotificacion(tipo, mensaje) {
    // Implementar sistema de notificaciones
    alert(mensaje); // Temporal - reemplazar con sistema real
}
</script>