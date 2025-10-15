<?php
// Vista para crear nueva solicitud de mantenimiento
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Nueva Solicitud de Mantenimiento</h1>
                <a href="<?php echo $this->url('mantenimiento'); ?>" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver al Listado
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="<?php echo $this->url('mantenimiento/crear'); ?>" method="POST" id="form-mantenimiento">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edificio_id" class="form-label">Edificio *</label>
                                    <select class="form-select" id="edificio_id" name="edificio_id" required>
                                        <option value="">Seleccionar Edificio</option>
                                        <?php foreach ($edificios as $edificio) { ?>
                                        <option value="<?php echo $edificio['id']; ?>" <?php echo isset($_POST['edificio_id']) && $_POST['edificio_id'] == $edificio['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($edificio['nombre']); ?>
                                        </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tipo" class="form-label">Tipo de Mantenimiento *</label>
                                    <select class="form-select" id="tipo" name="tipo" required>
                                        <option value="">Seleccionar Tipo</option>
                                        <?php foreach ($tipos_mantenimiento as $key => $value) { ?>
                                        <option value="<?php echo $key; ?>" <?php echo isset($_POST['tipo']) && $_POST['tipo'] == $key ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($value); ?>
                                        </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="titulo" class="form-label">Título *</label>
                                    <input type="text" class="form-control" id="titulo" name="titulo" 
                                           value="<?php echo htmlspecialchars($_POST['titulo'] ?? ''); ?>" 
                                           placeholder="Ej: Reparación de ascensor principal" required>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="prioridad" class="form-label">Prioridad *</label>
                                    <select class="form-select" id="prioridad" name="prioridad" required>
                                        <option value="">Seleccionar Prioridad</option>
                                        <?php foreach ($prioridades as $key => $value) { ?>
                                        <option value="<?php echo $key; ?>" <?php echo isset($_POST['prioridad']) && $_POST['prioridad'] == $key ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($value); ?>
                                        </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción Detallada</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="4" 
                                      placeholder="Describa en detalle el problema o trabajo a realizar..."><?php echo htmlspecialchars($_POST['descripcion'] ?? ''); ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="area" class="form-label">Área/Ubicación</label>
                                    <select class="form-select" id="area" name="area">
                                        <option value="">Seleccionar Área</option>
                                        <?php foreach ($areas_comunes as $area) { ?>
                                        <option value="<?php echo $area; ?>" <?php echo isset($_POST['area']) && $_POST['area'] == $area ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($area); ?>
                                        </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="fecha_programada" class="form-label">Fecha Programada</label>
                                    <input type="date" class="form-control" id="fecha_programada" name="fecha_programada" 
                                           value="<?php echo htmlspecialchars($_POST['fecha_programada'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="costo_estimado" class="form-label">Costo Estimado ($)</label>
                                    <input type="number" class="form-control" id="costo_estimado" name="costo_estimado" 
                                           value="<?php echo htmlspecialchars($_POST['costo_estimado'] ?? ''); ?>" 
                                           placeholder="0" step="0.01" min="0">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="proveedor" class="form-label">Proveedor/Contratista</label>
                                    <input type="text" class="form-control" id="proveedor" name="proveedor" 
                                           value="<?php echo htmlspecialchars($_POST['proveedor'] ?? ''); ?>" 
                                           placeholder="Nombre del proveedor">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="contacto_proveedor" class="form-label">Contacto del Proveedor</label>
                                    <input type="text" class="form-control" id="contacto_proveedor" name="contacto_proveedor" 
                                           value="<?php echo htmlspecialchars($_POST['contacto_proveedor'] ?? ''); ?>" 
                                           placeholder="Teléfono o email de contacto">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="observaciones" class="form-label">Observaciones Adicionales</label>
                            <textarea class="form-control" id="observaciones" name="observaciones" rows="3" 
                                      placeholder="Observaciones, notas internas, consideraciones especiales..."><?php echo htmlspecialchars($_POST['observaciones'] ?? ''); ?></textarea>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="<?php echo $this->url('mantenimiento'); ?>" class="btn btn-secondary me-md-2">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Crear Solicitud</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validación del formulario
document.getElementById('form-mantenimiento').addEventListener('submit', function(e) {
    const titulo = document.getElementById('titulo').value.trim();
    const edificio = document.getElementById('edificio_id').value;
    const tipo = document.getElementById('tipo').value;
    const prioridad = document.getElementById('prioridad').value;
    
    if (!edificio || !tipo || !titulo || !prioridad) {
        e.preventDefault();
        alert('Por favor complete todos los campos obligatorios (*)');
        return false;
    }
    
    if (titulo.length < 5) {
        e.preventDefault();
        alert('El título debe tener al menos 5 caracteres');
        return false;
    }
});
</script>