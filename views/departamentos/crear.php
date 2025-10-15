<?php
// 📁 views/departamentos/crear.php
?>
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Crear Nuevo Departamento</h1>
        <a href="<?= $url->to('departamentos' . ($edificio_actual ? '?edificio_id=' . $edificio_actual['id'] : '')) ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver a Departamentos
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
            <h6 class="m-0 font-weight-bold text-primary">Información del Departamento</h6>
        </div>
        <div class="card-body">
            <form method="POST" id="departamentoForm">
                <div class="row">
                    <div class="col-md-6">
                        <!-- Información Básica -->
                        <div class="mb-3">
                            <label class="form-label">Edificio *</label>
                            <select name="edificio_id" class="form-select" required id="edificioSelect">
                                <option value="">Seleccionar Edificio</option>
                                <?php foreach ($edificios as $edificio): ?>
                                <option value="<?= $edificio['id'] ?>" 
                                        <?= (($edificio_actual && $edificio_actual['id'] == $edificio['id']) || ($_POST['edificio_id'] ?? '') == $edificio['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($edificio['nombre']) ?> - <?= htmlspecialchars($edificio['direccion']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Número Departamento *</label>
                                    <input type="text" name="numero" class="form-control" 
                                           value="<?= htmlspecialchars($_POST['numero'] ?? '') ?>" 
                                           required placeholder="Ej: 101, A-1">
                                    <div class="form-text">Número único en el edificio</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Piso *</label>
                                    <input type="number" name="piso" class="form-control" 
                                           value="<?= $_POST['piso'] ?? 1 ?>" min="1" max="100" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Metros Cuadrados</label>
                                    <input type="number" name="metros_cuadrados" class="form-control" 
                                           value="<?= $_POST['metros_cuadrados'] ?? '' ?>" min="0" step="0.01" placeholder="m²">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Orientación</label>
                                    <select name="orientacion" class="form-select">
                                        <option value="">Seleccionar</option>
                                        <option value="Norte" <?= ($_POST['orientacion'] ?? '') == 'Norte' ? 'selected' : '' ?>>Norte</option>
                                        <option value="Sur" <?= ($_POST['orientacion'] ?? '') == 'Sur' ? 'selected' : '' ?>>Sur</option>
                                        <option value="Este" <?= ($_POST['orientacion'] ?? '') == 'Este' ? 'selected' : '' ?>>Este</option>
                                        <option value="Oeste" <?= ($_POST['orientacion'] ?? '') == 'Oeste' ? 'selected' : '' ?>>Oeste</option>
                                        <option value="Noreste" <?= ($_POST['orientacion'] ?? '') == 'Noreste' ? 'selected' : '' ?>>Noreste</option>
                                        <option value="Noroeste" <?= ($_POST['orientacion'] ?? '') == 'Noroeste' ? 'selected' : '' ?>>Noroeste</option>
                                        <option value="Sureste" <?= ($_POST['orientacion'] ?? '') == 'Sureste' ? 'selected' : '' ?>>Sureste</option>
                                        <option value="Suroeste" <?= ($_POST['orientacion'] ?? '') == 'Suroeste' ? 'selected' : '' ?>>Suroeste</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Características -->
                        <div class="card border-0 bg-light mt-3">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="bi bi-house-gear me-2"></i>Características
                                </h6>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Dormitorios</label>
                                            <input type="number" name="dormitorios" class="form-control" 
                                                   value="<?= $_POST['dormitorios'] ?? 1 ?>" min="0" max="10">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Baños</label>
                                            <input type="number" name="banos" class="form-control" 
                                                   value="<?= $_POST['banos'] ?? 1 ?>" min="0" max="10">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Estacionamientos</label>
                                            <input type="number" name="estacionamientos" class="form-control" 
                                                   value="<?= $_POST['estacionamientos'] ?? 0 ?>" min="0" max="5">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Bodegas</label>
                                            <input type="number" name="bodegas" class="form-control" 
                                                   value="<?= $_POST['bodegas'] ?? 0 ?>" min="0" max="5">
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">% Copropiedad</label>
                                    <input type="number" name="porcentaje_copropiedad" class="form-control" 
                                           value="<?= $_POST['porcentaje_copropiedad'] ?? 0.00 ?>" min="0" max="100" step="0.01">
                                    <div class="form-text">Porcentaje de participación en gastos comunes</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <!-- Información del Propietario -->
                        <div class="card border-0 bg-light mb-4">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="bi bi-person-badge me-2"></i>Información del Propietario
                                </h6>
                                
                                <div class="mb-3">
                                    <label class="form-label">RUT Propietario</label>
                                    <input type="text" name="propietario_rut" class="form-control" 
                                           value="<?= htmlspecialchars($_POST['propietario_rut'] ?? '') ?>" 
                                           placeholder="12.345.678-9">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Nombre Propietario</label>
                                    <input type="text" name="propietario_nombre" class="form-control" 
                                           value="<?= htmlspecialchars($_POST['propietario_nombre'] ?? '') ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Email Propietario</label>
                                    <input type="email" name="propietario_email" class="form-control" 
                                           value="<?= htmlspecialchars($_POST['propietario_email'] ?? '') ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Teléfono Propietario</label>
                                    <input type="tel" name="propietario_telefono" class="form-control" 
                                           value="<?= htmlspecialchars($_POST['propietario_telefono'] ?? '') ?>" 
                                           placeholder="+56912345678">
                                </div>
                            </div>
                        </div>

                        <!-- Información del Arrendatario -->
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="bi bi-person me-2"></i>Información del Arrendatario (Opcional)
                                </h6>
                                
                                <div class="mb-3">
                                    <label class="form-label">RUT Arrendatario</label>
                                    <input type="text" name="arrendatario_rut" class="form-control" 
                                           value="<?= htmlspecialchars($_POST['arrendatario_rut'] ?? '') ?>" 
                                           placeholder="12.345.678-9">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Nombre Arrendatario</label>
                                    <input type="text" name="arrendatario_nombre" class="form-control" 
                                           value="<?= htmlspecialchars($_POST['arrendatario_nombre'] ?? '') ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Email Arrendatario</label>
                                    <input type="email" name="arrendatario_email" class="form-control" 
                                           value="<?= htmlspecialchars($_POST['arrendatario_email'] ?? '') ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Teléfono Arrendatario</label>
                                    <input type="tel" name="arrendatario_telefono" class="form-control" 
                                           value="<?= htmlspecialchars($_POST['arrendatario_telefono'] ?? '') ?>" 
                                           placeholder="+56912345678">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Estado y Observaciones -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card border-0">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="is_habitado" 
                                                       id="is_habitado" value="1" 
                                                       <?= ($_POST['is_habitado'] ?? 1) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="is_habitado">
                                                    <strong>Departamento Habitado</strong>
                                                </label>
                                            </div>
                                            <div class="form-text">Desmarcar si el departamento está vacío</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Observaciones</label>
                                    <textarea name="observaciones" class="form-control" rows="3" 
                                              placeholder="Observaciones adicionales..."><?= htmlspecialchars($_POST['observaciones'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Botones -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between">
                            <a href="<?= $url->to('departamentos<?= $edificio_actual ? '?edificio_id=' . $edificio_actual['id'] : '' ?>') ?>" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Crear Departamento
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validación del formulario
    document.getElementById('departamentoForm').addEventListener('submit', function(e) {
        let isValid = true;
        
        // Validar campos requeridos
        const requiredFields = this.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.classList.add('is-invalid');
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('Por favor, completa todos los campos requeridos.');
            return;
        }
        
        // Mostrar loading
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Creando...';
        submitBtn.disabled = true;
    });
    
    // Validar RUT en tiempo real
    const rutInputs = document.querySelectorAll('input[type="text"][name*="rut"]');
    rutInputs.forEach(input => {
        input.addEventListener('blur', function() {
            const rut = this.value.trim();
            if (rut && !isValidRUT(rut)) {
                alert('El RUT ingresado no tiene un formato válido. Formato: 12.345.678-9');
                this.focus();
            }
        });
    });
    
    function isValidRUT(rut) {
        // Validación simple de formato RUT chileno
        return /^[0-9]{1,2}\.[0-9]{3}\.[0-9]{3}-[0-9kK]{1}$/.test(rut) || 
               /^[0-9]{7,8}-[0-9kK]{1}$/.test(rut);
    }
});
</script>