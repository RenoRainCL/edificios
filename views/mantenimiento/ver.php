<?php
// Función helper para manejar valores NULL
function safe_html($value, $default = '') {
    if ($value === null) {
        return $default;
    }
    return htmlspecialchars((string)$value);
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Detalles de Mantenimiento</h1>
                <div class="btn-group">
                    <a href="<?= $this->url('mantenimiento/editar/' . $mantenimiento['id']); ?>" class="btn btn-outline-primary">
                        <i class="bi bi-pencil"></i> Editar
                    </a>
                    <a href="<?= $this->url('mantenimiento'); ?>" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Información Principal -->
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Información de la Solicitud</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Detalles Básicos</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Edificio:</strong></td>
                                            <td><?= safe_html($mantenimiento['edificio_nombre']) ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tipo:</strong></td>
                                            <td>
                                                <span class="badge bg-secondary"><?= safe_html($mantenimiento['tipo']) ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Prioridad:</strong></td>
                                            <td>
                                                <?php
                                                $prioridadClass = [
                                                    'baja' => 'bg-success',
                                                    'media' => 'bg-warning',
                                                    'alta' => 'bg-danger',
                                                    'urgente' => 'bg-dark'
                                                ][$mantenimiento['prioridad']] ?? 'bg-secondary';
                                                ?>
                                                <span class="badge <?= $prioridadClass ?>">
                                                    <?= safe_html($mantenimiento['prioridad']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Estado:</strong></td>
                                            <td>
                                                <?php
                                                $estadoClass = [
                                                    'pendiente' => 'bg-warning',
                                                    'en_proceso' => 'bg-info',
                                                    'completado' => 'bg-success',
                                                    'cancelado' => 'bg-secondary'
                                                ][$mantenimiento['estado']] ?? 'bg-secondary';
                                                ?>
                                                <span class="badge <?= $estadoClass ?>">
                                                    <?= safe_html($mantenimiento['estado']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6>Fechas y Costos</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Solicitado:</strong></td>
                                            <td><?= date('d/m/Y H:i', strtotime($mantenimiento['fecha_solicitud'])) ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Programado:</strong></td>
                                            <td>
                                                <?= $mantenimiento['fecha_programada'] ? date('d/m/Y', strtotime($mantenimiento['fecha_programada'])) : 'No programado' ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Completado:</strong></td>
                                            <td>
                                                <?= $mantenimiento['fecha_completada'] ? date('d/m/Y H:i', strtotime($mantenimiento['fecha_completada'])) : 'Pendiente' ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Costo Estimado:</strong></td>
                                            <td>
                                                <?= $mantenimiento['costo_estimado'] ? '$' . number_format($mantenimiento['costo_estimado'], 0, ',', '.') : 'No estimado' ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Costo Real:</strong></td>
                                            <td>
                                                <?= $mantenimiento['costo_real'] ? '$' . number_format($mantenimiento['costo_real'], 0, ',', '.') : 'No registrado' ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-12">
                                    <h6>Descripción</h6>
                                    <div class="border rounded p-3 bg-light">
                                        <?= $mantenimiento['descripcion'] ? nl2br(safe_html($mantenimiento['descripcion'])) : '<em class="text-muted">Sin descripción</em>' ?>
                                    </div>
                                </div>
                            </div>

                            <?php if ($mantenimiento['area']): ?>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h6>Área/Ubicación</h6>
                                    <p class="mb-0"><?= safe_html($mantenimiento['area']) ?></p>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if ($mantenimiento['proveedor']): ?>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h6>Información del Proveedor</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Proveedor:</strong></td>
                                            <td><?= safe_html($mantenimiento['proveedor']) ?></td>
                                        </tr>
                                        <?php if ($mantenimiento['contacto_proveedor']): ?>
                                        <tr>
                                            <td><strong>Contacto:</strong></td>
                                            <td><?= safe_html($mantenimiento['contacto_proveedor']) ?></td>
                                        </tr>
                                        <?php endif; ?>
                                    </table>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if ($mantenimiento['observaciones']): ?>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h6>Observaciones</h6>
                                    <div class="border rounded p-3 bg-light">
                                        <?= nl2br(safe_html($mantenimiento['observaciones'])) ?>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Panel de Acciones -->
                <div class="col-md-4">
                    <!-- Cambiar Estado -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Cambiar Estado</h6>
                        </div>
                        <div class="card-body">
                            <form id="form-cambiar-estado">
                                <div class="mb-3">
                                    <label class="form-label">Nuevo Estado</label>
                                    <select class="form-select" name="estado" id="select-estado" required>
                                        <option value="pendiente" <?= $mantenimiento['estado'] == 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                        <option value="en_proceso" <?= $mantenimiento['estado'] == 'en_proceso' ? 'selected' : '' ?>>En Proceso</option>
                                        <option value="completado" <?= $mantenimiento['estado'] == 'completado' ? 'selected' : '' ?>>Completado</option>
                                        <option value="cancelado" <?= $mantenimiento['estado'] == 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                                    </select>
                                </div>
                                
                                <!-- Campo de costo real REQUERIDO cuando se completa -->
                                <div class="mb-3" id="costo-real-container">
                                    <label class="form-label">Costo Real ($) *</label>
                                    <input type="number" class="form-control" name="costo_real" 
                                           id="input-costo-real"
                                           step="0.01" min="0" 
                                           placeholder="Ingrese el costo real ejecutado"
                                           value="<?= safe_html($mantenimiento['costo_real']) ?>"> <!-- CORREGIDO -->
                                    <div class="form-text text-danger" id="costo-real-error" style="display: none;">
                                        El costo real es obligatorio cuando se completa el mantenimiento
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Observación Adicional</label>
                                    <textarea class="form-control" name="observacion_adicional" rows="2" 
                                              placeholder="Observación sobre el cambio de estado..."></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100">Actualizar Estado</button>
                            </form>
                        </div>
                    </div>

                    <!-- Información del Creador -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Información del Solicitante</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-1"><strong>Creado por:</strong></p>
                            <p class="mb-2"><?= safe_html($mantenimiento['creador_nombre'] ?? 'Sistema') ?></p>
                            
                            <p class="mb-1"><strong>Fecha de creación:</strong></p>
                            <p class="mb-2"><?= date('d/m/Y H:i', strtotime($mantenimiento['created_at'])) ?></p>
                            
                            <?php if ($mantenimiento['updated_at'] != $mantenimiento['created_at']): ?>
                            <p class="mb-1"><strong>Última actualización:</strong></p>
                            <p class="mb-0"><?= date('d/m/Y H:i', strtotime($mantenimiento['updated_at'])) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Historial de Cambios -->
            <?php if (!empty($historial)): ?>
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Historial de Cambios</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Usuario</th>
                                            <th>Acción</th>
                                            <th>Detalles</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($historial as $registro): ?>
                                        <tr>
                                            <td><?= date('d/m/Y H:i', strtotime($registro['created_at'])) ?></td>
                                            <td><?= safe_html($registro['user_id'] ? 'Usuario ' . $registro['user_id'] : 'Sistema') ?></td>
                                            <td><?= safe_html($registro['action']) ?></td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= safe_html(substr($registro['new_values'] ?? '', 0, 100)) ?>
                                                    <?= strlen($registro['new_values'] ?? '') > 100 ? '...' : '' ?>
                                                </small>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Mostrar/ocultar y validar campo de costo real
document.addEventListener('DOMContentLoaded', function() {
    const estadoSelect = document.getElementById('select-estado');
    const costoRealContainer = document.getElementById('costo-real-container');
    const costoRealInput = document.getElementById('input-costo-real');
    const costoRealError = document.getElementById('costo-real-error');
    
    function toggleCostoReal() {
        if (estadoSelect.value === 'completado') {
            costoRealContainer.style.display = 'block';
            costoRealInput.required = true;
        } else {
            costoRealContainer.style.display = 'none';
            costoRealInput.required = false;
            costoRealError.style.display = 'none';
        }
    }
    
    // Estado inicial
    toggleCostoReal();
    
    // Cambiar cuando se selecciona otro estado
    estadoSelect.addEventListener('change', toggleCostoReal);
    
    // Validación del formulario
    document.getElementById('form-cambiar-estado').addEventListener('submit', function(e) {
        if (estadoSelect.value === 'completado' && (!costoRealInput.value || costoRealInput.value <= 0)) {
            e.preventDefault();
            costoRealError.style.display = 'block';
            costoRealInput.focus();
            return false;
        }
        
        // Enviar formulario
        const formData = new FormData(this);
        
        fetch('<?= $this->url('mantenimiento/cambiar-estado/' . $mantenimiento['id']); ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Estado actualizado exitosamente');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al actualizar el estado');
        });
        
        e.preventDefault();
    });
});
</script>