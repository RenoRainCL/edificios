<?php
// 📁 views/edificios/editar.php
?>
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Editar Edificio</h1>
        <a href="<?= $url->to('edificios') ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver a Edificios
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
                Editando: <?= htmlspecialchars($edificio['nombre']) ?>
            </h6>
        </div>
        <div class="card-body">
            <form method="POST" id="edificioForm">
                <div class="row">
                    <div class="col-md-6">
                        <!-- Información Básica -->
                        <div class="mb-3">
                            <label class="form-label">Nombre del Edificio *</label>
                            <input type="text" name="nombre" class="form-control" 
                                   value="<?= htmlspecialchars($edificio['nombre']) ?>" 
                                   required minlength="3">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Dirección *</label>
                            <textarea name="direccion" class="form-control" rows="2" 
                                      required><?= htmlspecialchars($edificio['direccion']) ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Región *</label>
                                    <select name="region" class="form-select" required id="regionSelect">
                                        <option value="">Seleccionar Región</option>
                                        <?php foreach ($regiones_chile as $region): ?>
                                        <option value="<?= $region ?>" 
                                                <?= ($edificio['region'] == $region) ? 'selected' : '' ?>>
                                            <?= $region ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Comuna *</label>
                                    <select name="comuna" class="form-select" required id="comunaSelect">
                                        <option value="">Cargando comunas...</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <!-- Características del Edificio -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Total de Departamentos</label>
                                    <input type="number" name="total_departamentos" class="form-control" 
                                           value="<?= $edificio['total_departamentos'] ?>" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Número de Pisos</label>
                                    <input type="number" name="total_pisos" class="form-control" 
                                           value="<?= $edificio['total_pisos'] ?>" min="1">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Fecha de Construcción</label>
                            <input type="date" name="fecha_construccion" class="form-control" 
                                   value="<?= $edificio['fecha_construccion'] ?>">
                        </div>
                        
                        <!-- Información del Administrador -->
                        <div class="card border-0 bg-light mt-3">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="bi bi-person-badge me-2"></i>Información del Administrador
                                </h6>
                                
                                <div class="mb-3">
                                    <label class="form-label">RUT Administrador</label>
                                    <input type="text" name="rut_administrador" class="form-control" 
                                           value="<?= htmlspecialchars($edificio['rut_administrador'] ?? '') ?>" 
                                           placeholder="12.345.678-9">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Email Administrador</label>
                                    <input type="email" name="email_administrador" class="form-control" 
                                           value="<?= htmlspecialchars($edificio['email_administrador'] ?? '') ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Teléfono Administrador</label>
                                    <input type="tel" name="telefono_administrador" class="form-control" 
                                           value="<?= htmlspecialchars($edificio['telefono_administrador'] ?? '') ?>" 
                                           placeholder="+56912345678">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Reglamento de Copropiedad -->
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label">Reglamento de Copropiedad</label>
                            <textarea name="reglamento_copropiedad" class="form-control" rows="4"><?= htmlspecialchars($edificio['reglamento_copropiedad'] ?? '') ?></textarea>
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
                                        <strong>ID Edificio:</strong> <?= $edificio['id'] ?>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Fecha Creación:</strong> <?= date('d/m/Y H:i', strtotime($edificio['created_at'])) ?>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Última Actualización:</strong> <?= date('d/m/Y H:i', strtotime($edificio['updated_at'])) ?>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Estado:</strong> 
                                        <span class="badge bg-<?= $edificio['is_active'] ? 'success' : 'danger' ?>">
                                            <?= $edificio['is_active'] ? 'Activo' : 'Inactivo' ?>
                                        </span>
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
                            <a href="<?= $url->to('edificios') ?>" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <div>
                                <a href="<?= $url->to('edificios/gestionar/') ?><?= $edificio['id'] ?>" class="btn btn-info me-2">
                                    <i class="bi bi-gear"></i> Gestionar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> Actualizar Edificio
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
    const regionSelect = document.getElementById('regionSelect');
    const comunaSelect = document.getElementById('comunaSelect');
    
    // Datos de comunas por región
    const comunasPorRegion = <?= json_encode($comunas_chile) ?>;
    
    // Cargar comunas cuando cambia la región
    regionSelect.addEventListener('change', function() {
        const region = this.value;
        comunaSelect.innerHTML = '<option value="">Seleccionar Comuna</option>';
        
        if (region && comunasPorRegion[region]) {
            comunasPorRegion[region].forEach(comuna => {
                const option = document.createElement('option');
                option.value = comuna;
                option.textContent = comuna;
                comunaSelect.appendChild(option);
            });
        }
    });
    
    // Cargar comunas de la región actual
    const currentRegion = '<?= $edificio['region'] ?>';
    const currentComuna = '<?= $edificio['comuna'] ?>';
    
    if (currentRegion && comunasPorRegion[currentRegion]) {
        comunaSelect.innerHTML = '<option value="">Seleccionar Comuna</option>';
        comunasPorRegion[currentRegion].forEach(comuna => {
            const option = document.createElement('option');
            option.value = comuna;
            option.textContent = comuna;
            if (comuna === currentComuna) {
                option.selected = true;
            }
            comunaSelect.appendChild(option);
        });
    }
    
    // Validación del formulario
    document.getElementById('edificioForm').addEventListener('submit', function(e) {
        // Mostrar loading
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Actualizando...';
        submitBtn.disabled = true;
    });
});
</script>