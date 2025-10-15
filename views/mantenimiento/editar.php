<?php
// Vista para editar solicitud de mantenimiento
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Editar Solicitud de Mantenimiento</h1>
                <a href="<?= $this->url('mantenimiento'); ?>" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver al Listado
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="<?= $this->url('mantenimiento/editar/' . $mantenimiento['id']); ?>" method="POST" id="form-mantenimiento">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edificio_id" class="form-label">Edificio *</label>
                                    <select class="form-select" id="edificio_id" name="edificio_id" required>
                                        <option value="">Seleccionar Edificio</option>
                                        <?php foreach ($edificios as $edificio): ?>
                                        <option value="<?= $edificio['id'] ?>" <?= $mantenimiento['edificio_id'] == $edificio['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($edificio['nombre']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tipo" class="form-label">Tipo de Mantenimiento *</label>
                                    <select class="form-select" id="tipo" name="tipo" required>
                                        <option value="">Seleccionar Tipo</option>
                                        <?php foreach ($tipos_mantenimiento as $key => $value): ?>
                                        <option value="<?= $key ?>" <?= $mantenimiento['tipo'] == $key ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($value) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="titulo" class="form-label">Título *</label>
                                    <input type="text" class="form-control" id="titulo" name="titulo" 
                                           value="<?= htmlspecialchars($mantenimiento['titulo']) ?>" required>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="prioridad" class="form-label">Prioridad *</label>
                                    <select class="form-select" id="prioridad" name="prioridad" required>
                                        <option value="">Seleccionar Prioridad</option>
                                        <?php foreach ($prioridades as $key => $value): ?>
                                        <option value="<?= $key ?>" <?= $mantenimiento['prioridad'] == $key ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($value) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción Detallada</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="4"><?= htmlspecialchars($mantenimiento['descripcion']) ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="area" class="form-label">Área/Ubicación</label>
                                    <select class="form-select" id="area" name="area">
                                        <option value="">Seleccionar Área</option>
                                        <?php foreach ($areas_comunes as $area): ?>
                                        <option value="<?= $area ?>" <?= $mantenimiento['area'] == $area ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($area) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="fecha_programada" class="form-label">Fecha Programada</label>
                                    <input type="date" class="form-control" id="fecha_programada" name="fecha_programada" 
                                           value="<?= $mantenimiento['fecha_programada'] ? date('Y-m-d', strtotime($mantenimiento['fecha_programada'])) : '' ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="costo_estimado" class="form-label">Costo Estimado ($)</label>
                                    <input type="number" class="form-control" id="costo_estimado" name="costo_estimado" 
                                           value="<?= htmlspecialchars($mantenimiento['costo_estimado']) ?>" 
                                           step="0.01" min="0">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="costo_real" class="form-label">Costo Real ($)</label>
                                <input type="number" class="form-control" id="costo_real" name="costo_real" 
                                    value="<?= htmlspecialchars($mantenimiento['costo_real']) ?>" 
                                    step="0.01" min="0"
                                    placeholder="Ingrese el costo real ejecutado">
                                <div class="form-text">
                                    Solo ingrese cuando el trabajo esté completado
                                </div>
                            </div>
                        </div>                        

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="proveedor" class="form-label">Proveedor/Contratista</label>
                                    <input type="text" class="form-control" id="proveedor" name="proveedor" 
                                           value="<?= htmlspecialchars($mantenimiento['proveedor']) ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="contacto_proveedor" class="form-label">Contacto del Proveedor</label>
                                    <input type="text" class="form-control" id="contacto_proveedor" name="contacto_proveedor" 
                                           value="<?= htmlspecialchars($mantenimiento['contacto_proveedor']) ?>">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="observaciones" class="form-label">Observaciones Adicionales</label>
                            <textarea class="form-control" id="observaciones" name="observaciones" rows="3"><?= htmlspecialchars($mantenimiento['observaciones']) ?></textarea>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="<?= $this->url('mantenimiento/ver/' . $mantenimiento['id']); ?>" class="btn btn-outline-secondary me-md-2">
                                <i class="bi bi-eye"></i> Ver Detalles
                            </a>
                            <a href="<?= $this->url('mantenimiento'); ?>" class="btn btn-secondary me-md-2">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Actualizar Solicitud</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>