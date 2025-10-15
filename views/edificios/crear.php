<?php
// 📁 views/edificios/crear.php
?>
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Crear Nuevo Edificio</h1>
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
            <h6 class="m-0 font-weight-bold text-primary">Información del Edificio</h6>
        </div>
        <div class="card-body">
            <form method="POST" id="edificioForm">
                <div class="row">
                    <div class="col-md-6">
                        <!-- Información Básica -->
                        <div class="mb-3">
                            <label class="form-label">Nombre del Edificio *</label>
                            <input type="text" name="nombre" class="form-control" 
                                   value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>" 
                                   required minlength="3" placeholder="Ej: Edificio Los Alerces">
                            <div class="form-text">Nombre oficial del edificio</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Dirección *</label>
                            <textarea name="direccion" class="form-control" rows="2" 
                                      required placeholder="Calle y número"><?= htmlspecialchars($_POST['direccion'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Región *</label>
                                    <select name="region" class="form-select" required id="regionSelect">
                                        <option value="">Seleccionar Región</option>
                                        <?php foreach ($regiones_chile as $region): ?>
                                        <option value="<?= $region ?>" 
                                                <?= (($_POST['region'] ?? '') == $region) ? 'selected' : '' ?>>
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
                                        <option value="">Primero selecciona región</option>
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
                                           value="<?= $_POST['total_departamentos'] ?? 0 ?>" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Número de Pisos</label>
                                    <input type="number" name="total_pisos" class="form-control" 
                                           value="<?= $_POST['total_pisos'] ?? 1 ?>" min="1">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Fecha de Construcción</label>
                            <input type="date" name="fecha_construccion" class="form-control" 
                                   value="<?= $_POST['fecha_construccion'] ?? '' ?>">
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
                                           value="<?= htmlspecialchars($_POST['rut_administrador'] ?? '') ?>" 
                                           placeholder="12.345.678-9">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Email Administrador</label>
                                    <input type="email" name="email_administrador" class="form-control" 
                                           value="<?= htmlspecialchars($_POST['email_administrador'] ?? '') ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Teléfono Administrador</label>
                                    <input type="tel" name="telefono_administrador" class="form-control" 
                                           value="<?= htmlspecialchars($_POST['telefono_administrador'] ?? '') ?>" 
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
                            <textarea name="reglamento_copropiedad" class="form-control" rows="4" 
                                      placeholder="Descripción del reglamento de copropiedad..."><?= htmlspecialchars($_POST['reglamento_copropiedad'] ?? '') ?></textarea>
                            <div class="form-text">Opcional: Puedes agregar el reglamento más tarde</div>
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
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Crear Edificio
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
    
    // Si hay una región seleccionada (en caso de error del formulario), cargar sus comunas
    const selectedRegion = regionSelect.value;
    if (selectedRegion && comunasPorRegion[selectedRegion]) {
        comunasPorRegion[selectedRegion].forEach(comuna => {
            const option = document.createElement('option');
            option.value = comuna;
            option.textContent = comuna;
            if (comuna === '<?= $_POST['comuna'] ?? '' ?>') {
                option.selected = true;
            }
            comunaSelect.appendChild(option);
        });
    }
    
    // Validación del formulario
    document.getElementById('edificioForm').addEventListener('submit', function(e) {
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
    const rutInput = document.querySelector('input[name="rut_administrador"]');
    rutInput.addEventListener('blur', function() {
        const rut = this.value.trim();
        if (rut && !isValidRUT(rut)) {
            alert('El RUT ingresado no tiene un formato válido. Formato: 12.345.678-9');
            this.focus();
        }
    });
    
    function isValidRUT(rut) {
        // Validación simple de formato RUT chileno
        return /^[0-9]{1,2}\.[0-9]{3}\.[0-9]{3}-[0-9kK]{1}$/.test(rut) || 
               /^[0-9]{7,8}-[0-9kK]{1}$/.test(rut);
    }
});
</script>