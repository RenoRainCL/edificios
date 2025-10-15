<?php
// 📁 views/amenities/configuracion.php
?>
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Configuración de Amenities</h1>
                    <p class="text-muted mb-0">Configuración global y por edificio del sistema de amenities</p>
                </div>
                <div>
                    <a href="<?= $url->to('amenities/gestionar') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Selector de Nivel -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label">Nivel de Configuración</label>
                            <select name="edificio_id" class="form-select" onchange="cambiarNivel(this.value)">
                                <option value="">Configuración Global (Sistema)</option>
                                <?php foreach ($edificios as $edificio): ?>
                                    <option value="<?= $edificio['id'] ?>" 
                                        <?= ($edificio_actual && $edificio['id'] == $edificio_actual['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($edificio['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-info mb-0">
                                <small>
                                    <i class="bi bi-info-circle"></i>
                                    <strong>Jerarquía:</strong> Global → Edificio → Amenity específico
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Indicador de Nivel Actual -->
    <?php if ($edificio_actual): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning">
                <i class="bi bi-building"></i>
                <strong>Configurando:</strong> <?= htmlspecialchars($edificio_actual['nombre']) ?>
                <small class="float-end">
                    Esta configuración sobreescribe la configuración global
                </small>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-primary">
                <i class="bi bi-globe"></i>
                <strong>Configurando:</strong> Sistema Global
                <small class="float-end">
                    Esta configuración aplica a todos los edificios por defecto
                </small>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Formulario de Configuración -->
    <div class="row">
        <div class="col-12">
            <form method="POST" id="formConfiguracion">
                <input type="hidden" name="nivel" value="<?= $edificio_actual ? 'edificio' : 'global' ?>">
                <input type="hidden" name="entidad_id" value="<?= $edificio_actual ? $edificio_actual['id'] : '' ?>">

                <!-- Configuración General -->
                <div class="card">
                    <div class="card-header bg-transparent">
                        <h5 class="card-title mb-0">Configuración General</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Horarios Globales -->
                            <div class="col-md-6">
                                <label class="form-label">Horario Apertura Global</label>
                                <input type="time" name="horario_global_apertura" class="form-control" 
                                       value="<?= $config_edificio['horario_global_apertura'] ?? $config_global['horario_global_apertura'] ?? '08:00' ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Horario Cierre Global</label>
                                <input type="time" name="horario_global_cierre" class="form-control" 
                                       value="<?= $config_edificio['horario_global_cierre'] ?? $config_global['horario_global_cierre'] ?? '22:00' ?>">
                            </div>

                            <!-- Límites Generales -->
                            <div class="col-md-4">
                                <label class="form-label">Máx. Horas por Reserva</label>
                                <input type="number" name="max_horas_por_reserva" class="form-control" 
                                       value="<?= $config_edificio['max_horas_por_reserva'] ?? $config_global['max_horas_por_reserva'] ?? 4 ?>" 
                                       min="1" max="24">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Días Anticipación Reserva</label>
                                <input type="number" name="dias_anticipacion_reserva" class="form-control" 
                                       value="<?= $config_edificio['dias_anticipacion_reserva'] ?? $config_global['dias_anticipacion_reserva'] ?? 7 ?>" 
                                       min="1" max="365">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Permisos de Reserva</label>
                                <select name="permisos_reserva[]" class="form-select" multiple>
                                    <option value="propietario" 
                                        <?= (in_array('propietario', $config_edificio['permisos_reserva'] ?? $config_global['permisos_reserva'] ?? [])) ? 'selected' : '' ?>>
                                        Propietarios
                                    </option>
                                    <option value="arrendatario" 
                                        <?= (in_array('arrendatario', $config_edificio['permisos_reserva'] ?? $config_global['permisos_reserva'] ?? [])) ? 'selected' : '' ?>>
                                        Arrendatarios
                                    </option>
                                    <option value="residente" 
                                        <?= (in_array('residente', $config_edificio['permisos_reserva'] ?? $config_global['permisos_reserva'] ?? [])) ? 'selected' : '' ?>>
                                        Residentes
                                    </option>
                                </select>
                                <small class="form-text text-muted">Mantén Ctrl para seleccionar múltiples</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Configuración de Comportamiento -->
                <div class="card mt-4">
                    <div class="card-header bg-transparent">
                        <h5 class="card-title mb-0">Comportamiento del Sistema</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Switches -->
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="notificar_conflictos" 
                                           id="notificar_conflictos" value="1"
                                           <?= ($config_edificio['notificar_conflictos'] ?? $config_global['notificar_conflictos'] ?? true) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="notificar_conflictos">
                                        Notificar conflictos de reserva automáticamente
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="auto_aprobar_reservas" 
                                           id="auto_aprobar_reservas" value="1"
                                           <?= ($config_edificio['auto_aprobar_reservas'] ?? $config_global['auto_aprobar_reservas'] ?? false) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="auto_aprobar_reservas">
                                        Aprobación automática de reservas (sin revisión)
                                    </label>
                                </div>
                            </div>

                            <!-- Bloques Horarios Default -->
                            <div class="col-12">
                                <label class="form-label">Bloques Horarios por Defecto</label>
                                <div id="bloquesDefaultContainer">
                                    <?php 
                                    $bloquesDefault = $config_edificio['bloques_horarios_default'] ?? $config_global['bloques_horarios_default'] ?? [];
                                    if (!empty($bloquesDefault)): 
                                        foreach ($bloquesDefault as $index => $bloque): 
                                    ?>
                                        <div class="bloque-default input-group mb-2">
                                            <input type="time" class="form-control" 
                                                   name="bloques_horarios_default[<?= $index ?>][inicio]" 
                                                   value="<?= $bloque['inicio'] ?>">
                                            <span class="input-group-text">a</span>
                                            <input type="time" class="form-control" 
                                                   name="bloques_horarios_default[<?= $index ?>][fin]" 
                                                   value="<?= $bloque['fin'] ?>">
                                            <button type="button" class="btn btn-outline-danger quitar-bloque-default">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="bloque-default input-group mb-2">
                                            <input type="time" class="form-control" 
                                                   name="bloques_horarios_default[0][inicio]" 
                                                   value="09:00">
                                            <span class="input-group-text">a</span>
                                            <input type="time" class="form-control" 
                                                   name="bloques_horarios_default[0][fin]" 
                                                   value="11:00">
                                            <button type="button" class="btn btn-outline-danger quitar-bloque-default">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <button type="button" id="agregarBloqueDefault" class="btn btn-sm btn-outline-primary mt-2">
                                    <i class="bi bi-plus"></i> Agregar Bloque
                                </button>
                                <small class="form-text text-muted">
                                    Bloques horarios que se sugieren al crear nuevos amenities
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información de Herencia -->
                <?php if ($edificio_actual && $config_global): ?>
                <div class="card mt-4">
                    <div class="card-header bg-transparent">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-diagram-3"></i> Herencia de Configuración
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Configuración</th>
                                        <th class="text-center">Valor Global</th>
                                        <th class="text-center">Valor Edificio</th>
                                        <th class="text-center">Se Usa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Horario Apertura</td>
                                        <td class="text-center"><?= $config_global['horario_global_apertura'] ?? '08:00' ?></td>
                                        <td class="text-center"><?= $config_edificio['horario_global_apertura'] ?? '<em class="text-muted">(heredado)</em>' ?></td>
                                        <td class="text-center">
                                            <?= isset($config_edificio['horario_global_apertura']) ? '🏢 Edificio' : '🌎 Global' ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Máx. Horas/Reserva</td>
                                        <td class="text-center"><?= $config_global['max_horas_por_reserva'] ?? 4 ?></td>
                                        <td class="text-center"><?= $config_edificio['max_horas_por_reserva'] ?? '<em class="text-muted">(heredado)</em>' ?></td>
                                        <td class="text-center">
                                            <?= isset($config_edificio['max_horas_por_reserva']) ? '🏢 Edificio' : '🌎 Global' ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Aprobación Automática</td>
                                        <td class="text-center"><?= ($config_global['auto_aprobar_reservas'] ?? false) ? '✅ Sí' : '❌ No' ?></td>
                                        <td class="text-center"><?= isset($config_edificio['auto_aprobar_reservas']) ? ($config_edificio['auto_aprobar_reservas'] ? '✅ Sí' : '❌ No') : '<em class="text-muted">(heredado)</em>' ?></td>
                                        <td class="text-center">
                                            <?= isset($config_edificio['auto_aprobar_reservas']) ? '🏢 Edificio' : '🌎 Global' ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Botones -->
                <div class="card mt-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-secondary" onclick="resetearConfiguracion()">
                                <i class="bi bi-arrow-clockwise"></i> Restablecer Valores
                            </button>
                            <div class="btn-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> Guardar Configuración
                                </button>
                                <?php if ($edificio_actual && $config_edificio): ?>
                                <button type="button" class="btn btn-outline-danger" onclick="eliminarConfiguracionEdificio()">
                                    <i class="bi bi-trash"></i> Eliminar Config Edificio
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function cambiarNivel(edificioId) {
    if (edificioId) {
        window.location.href = '<?= $url->to('amenities/configuracion') ?>?edificio_id=' + edificioId;
    } else {
        window.location.href = '<?= $url->to('amenities/configuracion') ?>';
    }
}

// Gestión de bloques horarios default
let contadorBloquesDefault = <?= !empty($bloquesDefault) ? count($bloquesDefault) : 1 ?>;

document.getElementById('agregarBloqueDefault').addEventListener('click', function() {
    contadorBloquesDefault++;
    const nuevoBloque = document.createElement('div');
    nuevoBloque.className = 'bloque-default input-group mb-2';
    nuevoBloque.innerHTML = `
        <input type="time" class="form-control" 
               name="bloques_horarios_default[${contadorBloquesDefault}][inicio]" 
               value="09:00">
        <span class="input-group-text">a</span>
        <input type="time" class="form-control" 
               name="bloques_horarios_default[${contadorBloquesDefault}][fin]" 
               value="11:00">
        <button type="button" class="btn btn-outline-danger quitar-bloque-default">
            <i class="bi bi-trash"></i>
        </button>
    `;
    document.getElementById('bloquesDefaultContainer').appendChild(nuevoBloque);
});

// Delegación de eventos para botones de quitar bloque default
document.getElementById('bloquesDefaultContainer').addEventListener('click', function(e) {
    if (e.target.classList.contains('quitar-bloque-default') || e.target.closest('.quitar-bloque-default')) {
        const bloque = e.target.closest('.bloque-default');
        if (document.querySelectorAll('.bloque-default').length > 1) {
            bloque.remove();
        } else {
            alert('Debe haber al menos un bloque horario por defecto');
        }
    }
});

function resetearConfiguracion() {
    if (confirm('¿Estás seguro de que deseas restablecer todos los valores a los predeterminados?')) {
        document.querySelectorAll('input, select').forEach(element => {
            if (element.type !== 'hidden') {
                if (element.type === 'checkbox') {
                    element.checked = false;
                } else {
                    element.value = element.defaultValue;
                }
            }
        });
    }
}

function eliminarConfiguracionEdificio() {
    if (confirm('¿Estás seguro de que deseas eliminar la configuración específica de este edificio? Se heredará la configuración global.')) {
        fetch('<?= $url->to('amenities/eliminar-config-edificio/' . ($edificio_actual ? $edificio_actual['id'] : '')) ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al eliminar la configuración');
        });
    }
}
</script>

<style>
.bloque-horario,
.bloque-default {
    max-width: 300px;
}

.form-check.form-switch {
    padding-left: 3.5em;
}

.form-check-input {
    width: 3em;
    height: 1.5em;
}
</style>