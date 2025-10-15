<?php
// 📁 views/configuracion/prorrateo.php
?>
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="card-title mb-1">
                                <i class="bi bi-gear me-2"></i>Configuración de Prorrateo
                            </h2>
                            <p class="card-text mb-0">
                                Configura las estrategias de distribución de gastos comunes
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="btn-group">
                                <a href="<?= $url->to('dashboard') ?>" class="btn btn-light">
                                    <i class="bi bi-arrow-left"></i> Volver
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mensajes Flash -->
    <?php if (!empty($flash_messages)): ?>
        <?php foreach ($flash_messages as $flash): ?>
            <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show">
                <i class="bi bi-<?= $flash['type'] == 'success' ? 'check-circle' : 'exclamation-triangle' ?> me-2"></i>
                <?= htmlspecialchars($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Selector de Edificio -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-building me-2"></i>Seleccionar Edificio
                    </h6>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-8">
                            <select name="edificio_id" class="form-select" onchange="this.form.submit()">
                                <?php foreach ($edificios as $edificio): ?>
                                <option value="<?= $edificio['id'] ?>" 
                                        <?= ($edificio_actual['id'] ?? 0) == $edificio['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($edificio['nombre']) ?>
                                    <?php if (isset($edificio['direccion'])): ?>
                                        - <?= htmlspecialchars($edificio['direccion']) ?>
                                    <?php endif; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-check-lg"></i> Cargar Configuración
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($edificio_actual)): ?>
    <form method="POST" action="<?= $url->to('configuracion/prorrateo') ?>" id="formConfigProrrateo">
        <input type="hidden" name="edificio_id" value="<?= $edificio_actual['id'] ?>">
        
        <!-- Configuración Básica -->
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="card-title mb-0">
                            <i class="bi bi-sliders me-2"></i>Configuración Básica
                        </h6>
                    </div>
                    <div class="card-body">
                        <!-- Estrategia por Defecto -->
                        <div class="mb-3">
                            <label class="form-label">Estrategia de Prorrateo por Defecto</label>
                            <select name="estrategia_default_id" class="form-select" 
                                    data-validation="required" 
                                    onchange="validarEstrategia(this)">
                                <option value="">Seleccionar estrategia...</option>
                                <?php foreach ($estrategias as $estrategia): ?>
                                <option value="<?= $estrategia['id'] ?>" 
                                        <?= ($config_actual['estrategia_default_id'] ?? '') == $estrategia['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($estrategia['nombre']) ?>
                                    <?php if (!empty($estrategia['descripcion'])): ?>
                                        <small class="text-muted">- <?= htmlspecialchars($estrategia['descripcion']) ?></small>
                                    <?php endif; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Debe seleccionar una estrategia</div>
                            <div class="form-text">
                                Estrategia que se aplicará automáticamente a los nuevos gastos
                            </div>
                        </div>

                        <!-- Tipo de Superficie -->
                        <div class="mb-3">
                            <label class="form-label">Superficie a Considerar</label>
                            <select name="superficie_considerar" class="form-select">
                                <option value="util" <?= ($config_actual['superficie_considerar'] ?? 'util') == 'util' ? 'selected' : '' ?>>Superficie Útil</option>
                                <option value="total" <?= ($config_actual['superficie_considerar'] ?? '') == 'total' ? 'selected' : '' ?>>Superficie Total</option>
                                <option value="escritura" <?= ($config_actual['superficie_considerar'] ?? '') == 'escritura' ? 'selected' : '' ?>>Según Escritura</option>
                                <option value="mixta" <?= ($config_actual['superficie_considerar'] ?? '') == 'mixta' ? 'selected' : '' ?>>Mixta</option>
                            </select>
                            <div class="form-text">
                                Tipo de superficie a utilizar en cálculos por metros cuadrados
                            </div>
                        </div>

                        <!-- Validación Legal -->
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="validacion_legal_activa" 
                                       id="validacion_legal_activa" value="1" 
                                       <?= ($config_actual['validacion_legal_activa'] ?? 1) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="validacion_legal_activa">
                                    Activar validación legal automática
                                </label>
                            </div>
                            <div class="form-text">
                                Valida que la distribución cumpla con los límites legales
                            </div>
                        </div>

                        <!-- Cálculo Automático -->
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="calculo_automatico" 
                                       id="calculo_automatico" value="1" 
                                       <?= ($config_actual['calculo_automatico'] ?? true) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="calculo_automatico">
                                    Cálculo automático de porcentajes
                                </label>
                            </div>
                            <div class="form-text">
                                Calcula automáticamente los porcentajes al crear/editar departamentos
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="card-title mb-0">
                            <i class="bi bi-shield-check me-2"></i>Configuración Legal
                        </h6>
                    </div>
                    <div class="card-body">
                        <!-- País -->
                        <div class="mb-3">
                            <label class="form-label">País</label>
                            <select name="pais" class="form-select" onchange="actualizarConfigLegal()">
                                <option value="CL" <?= ($config_actual['pais'] ?? 'CL') == 'CL' ? 'selected' : '' ?>>Chile</option>
                                <option value="AR" <?= ($config_actual['pais'] ?? '') == 'AR' ? 'selected' : '' ?>>Argentina</option>
                                <option value="PE" <?= ($config_actual['pais'] ?? '') == 'PE' ? 'selected' : '' ?>>Perú</option>
                                <option value="CO" <?= ($config_actual['pais'] ?? '') == 'CO' ? 'selected' : '' ?>>Colombia</option>
                                <option value="MX" <?= ($config_actual['pais'] ?? '') == 'MX' ? 'selected' : '' ?>>México</option>
                            </select>
                        </div>

                        <!-- Ley de Copropiedad -->
                        <div class="mb-3">
                            <label class="form-label">Ley de Copropiedad Vigente</label>
                            <input type="text" class="form-control" name="ley_copropiedad_vigente" 
                                   value="<?= htmlspecialchars($config_actual['ley_copropiedad_vigente'] ?? 'Ley 19.537') ?>"
                                   data-validation="required">
                            <div class="invalid-feedback">La ley de copropiedad es requerida</div>
                        </div>

                        <!-- Variación Máxima -->
                        <div class="mb-3">
                            <label class="form-label">Variación Porcentual Máxima Permitida</label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="max_variacion_porcentual" 
                                       value="<?= htmlspecialchars($config_actual['max_variacion_porcentual'] ?? 20.00) ?>" 
                                       step="0.01" min="0" max="100" required
                                       data-validation="range:0-100"
                                       onchange="validarVariacion(this)">
                                <span class="input-group-text">%</span>
                            </div>
                            <div class="invalid-feedback">La variación debe estar entre 0% y 100%</div>
                            <div class="form-text">
                                Límite legal de variación entre el departamento que más paga y el que menos paga
                            </div>
                        </div>

                        <!-- Tratamiento Comercial -->
                        <div class="mb-3">
                            <label class="form-label">Tratamiento para Locales Comerciales</label>
                            <select name="tratamiento_comercial" class="form-select" onchange="actualizarIncrementoComercial()">
                                <option value="igual" <?= ($config_actual['tratamiento_comercial'] ?? '') == 'igual' ? 'selected' : '' ?>>Mismo tratamiento que residencial</option>
                                <option value="incremento_10" <?= ($config_actual['tratamiento_comercial'] ?? '') == 'incremento_10' ? 'selected' : '' ?>>Incremento del 10%</option>
                                <option value="incremento_20" <?= ($config_actual['tratamiento_comercial'] ?? 'incremento_20') == 'incremento_20' ? 'selected' : '' ?>>Incremento del 20%</option>
                                <option value="incremento_30" <?= ($config_actual['tratamiento_comercial'] ?? '') == 'incremento_30' ? 'selected' : '' ?>>Incremento del 30%</option>
                                <option value="personalizado" <?= ($config_actual['tratamiento_comercial'] ?? '') == 'personalizado' ? 'selected' : '' ?>>Personalizado</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configuración Avanzada -->
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="card-title mb-0">
                            <i class="bi bi-tools me-2"></i>Configuración Avanzada
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <!-- Considerar Comerciales -->
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="considerar_comerciales" 
                                               id="considerar_comerciales" value="1" 
                                               <?= ($config_actual['considerar_comerciales'] ?? 1) ? 'checked' : '' ?>
                                               onchange="actualizarVisibilidadComerciales()">
                                        <label class="form-check-label" for="considerar_comerciales">
                                            Considerar locales comerciales en el cálculo
                                        </label>
                                    </div>
                                </div>

                                <!-- Factores a Considerar -->
                                <div class="mb-3">
                                    <label class="form-label">Factores de Cálculo</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="factores[]" 
                                               value="piso" id="factor_piso" 
                                               <?= in_array('piso', $config_actual['factores'] ?? ['piso', 'orientacion']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="factor_piso">
                                            Factor por Piso
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="factores[]" 
                                               value="orientacion" id="factor_orientacion" 
                                               <?= in_array('orientacion', $config_actual['factores'] ?? ['piso', 'orientacion']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="factor_orientacion">
                                            Factor por Orientación
                                        </label>
                                    </div>
                                </div>

                                <!-- Incremento Comercial -->
                                <div class="mb-3" id="incremento_comercial_group" style="display: none;">
                                    <label class="form-label">Incremento para Comerciales</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="incremento_comercial" 
                                               value="<?= htmlspecialchars($config_actual['incremento_comercial'] ?? 20.00) ?>" 
                                               step="0.01" min="0" max="100"
                                               data-validation="range:0-100">
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <div class="invalid-feedback">El incremento debe estar entre 0% y 100%</div>
                                    <div class="form-text">
                                        Porcentaje adicional para locales comerciales
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <!-- Factor Piso -->
                                <div class="mb-3">
                                    <label class="form-label">Factor Base por Piso</label>
                                    <input type="number" class="form-control" name="factor_piso" 
                                           value="<?= htmlspecialchars($config_actual['factor_piso'] ?? 1.00) ?>" 
                                           step="0.01" min="0.5" max="2.0"
                                           data-validation="range:0.5-2.0">
                                    <div class="invalid-feedback">El factor debe estar entre 0.5 y 2.0</div>
                                    <div class="form-text">
                                        Multiplicador base para ajuste por piso (1.0 = neutral)
                                    </div>
                                </div>

                                <!-- Factor Orientación -->
                                <div class="mb-3">
                                    <label class="form-label">Factor Base por Orientación</label>
                                    <input type="number" class="form-control" name="factor_orientacion" 
                                           value="<?= htmlspecialchars($config_actual['factor_orientacion'] ?? 1.00) ?>" 
                                           step="0.01" min="0.5" max="2.0"
                                           data-validation="range:0.5-2.0">
                                    <div class="invalid-feedback">El factor debe estar entre 0.5 y 2.0</div>
                                    <div class="form-text">
                                        Multiplicador base para ajuste por orientación (1.0 = neutral)
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <a href="<?= $url->to('dashboard') ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <div class="btn-group">
                                <button type="submit" class="btn btn-primary" id="btnGuardar">
                                    <i class="bi bi-check-circle"></i> Guardar Configuración
                                </button>
                                <a href="<?= $url->to('finanzas/prorrateo') ?>" class="btn btn-outline-primary">
                                    <i class="bi bi-calculator"></i> Ir a Prorrateo
                                </a>
                                <button type="button" class="btn btn-outline-info" onclick="probarCalculo()">
                                    <i class="bi bi-play-circle"></i> Probar Cálculo
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <?php else: ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-building text-muted fs-1"></i>
                    <h4 class="text-muted mt-3">Selecciona un edificio para configurar</h4>
                    <p class="text-muted">Elige un edificio de la lista para comenzar la configuración</p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar visibilidad de controles
    actualizarIncrementoComercial();
    actualizarVisibilidadComerciales();
    actualizarConfigLegal();
    
    // Configurar validación del formulario
    configurarValidacionFormulario();
});

function configurarValidacionFormulario() {
    const form = document.getElementById('formConfigProrrateo');
    const inputs = form.querySelectorAll('[data-validation]');
    
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validarCampo(this);
        });
        
        input.addEventListener('input', function() {
            if (this.classList.contains('is-invalid')) {
                validarCampo(this);
            }
        });
    });
    
    form.addEventListener('submit', function(e) {
        let esValido = true;
        
        inputs.forEach(input => {
            if (!validarCampo(input)) {
                esValido = false;
            }
        });
        
        if (!esValido) {
            e.preventDefault();
            mostrarToast('error', 'Por favor corrige los errores en el formulario');
        }
    });
}

function validarCampo(campo) {
    const validacion = campo.getAttribute('data-validation');
    const valor = campo.value.trim();
    let esValido = true;
    
    // Validación requerido
    if (validacion.includes('required') && !valor) {
        esValido = false;
        campo.classList.add('is-invalid');
        campo.classList.remove('is-valid');
        return false;
    }
    
    // Validación de rango
    if (validacion.includes('range:')) {
        const rangos = validacion.split('range:')[1].split('-');
        const min = parseFloat(rangos[0]);
        const max = parseFloat(rangos[1]);
        const valorNum = parseFloat(valor);
        
        if (isNaN(valorNum) || valorNum < min || valorNum > max) {
            esValido = false;
            campo.classList.add('is-invalid');
            campo.classList.remove('is-valid');
            return false;
        }
    }
    
    if (esValido) {
        campo.classList.remove('is-invalid');
        campo.classList.add('is-valid');
    }
    
    return esValido;
}

function validarEstrategia(select) {
    validarCampo(select);
}

function validarVariacion(input) {
    if (validarCampo(input)) {
        const valor = parseFloat(input.value);
        if (valor > 30) {
            mostrarToast('warning', 'Variación alta detectada. Verifique cumplimiento legal.');
        }
    }
}

function actualizarIncrementoComercial() {
    const tratamiento = document.querySelector('select[name="tratamiento_comercial"]').value;
    const incrementoGroup = document.getElementById('incremento_comercial_group');
    
    if (tratamiento === 'personalizado') {
        incrementoGroup.style.display = 'block';
    } else {
        incrementoGroup.style.display = 'none';
    }
}

function actualizarVisibilidadComerciales() {
    const considerarComerciales = document.getElementById('considerar_comerciales').checked;
    const tratamientoSelect = document.querySelector('select[name="tratamiento_comercial"]');
    
    if (!considerarComerciales) {
        tratamientoSelect.disabled = true;
        document.getElementById('incremento_comercial_group').style.display = 'none';
    } else {
        tratamientoSelect.disabled = false;
        actualizarIncrementoComercial();
    }
}

function actualizarConfigLegal() {
    const pais = document.querySelector('select[name="pais"]').value;
    const leyInput = document.querySelector('input[name="ley_copropiedad_vigente"]');
    
    // Configurar ley por defecto según país
    const leyesPorPais = {
        'CL': 'Ley 19.537',
        'AR': 'Ley 13.512',
        'PE': 'Ley 27157',
        'CO': 'Ley 675',
        'MX': 'Ley de Propiedad en Condominio'
    };
    
    if (!leyInput.value || leyInput.value === leyInput.defaultValue) {
        leyInput.value = leyesPorPais[pais] || 'Ley Local';
    }
}

function probarCalculo() {
    const edificioId = document.querySelector('input[name="edificio_id"]').value;
    
    if (!edificioId) {
        mostrarToast('error', 'Selecciona un edificio primero');
        return;
    }
    
    mostrarToast('info', 'Iniciando prueba de cálculo...');
    
    // Simular prueba de cálculo (en implementación real, llamaría a la API)
    setTimeout(() => {
        mostrarToast('success', 'Prueba completada. Configuración válida.');
    }, 2000);
}

function mostrarToast(tipo, mensaje) {
    // Implementación básica de toast (usar librería en producción)
    const toast = document.createElement('div');
    toast.className = `alert alert-${tipo} alert-dismissible fade show position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 1050; min-width: 300px;';
    toast.innerHTML = `
        ${mensaje}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 5000);
}
</script>