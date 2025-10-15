<?php
// 📁 views/amenities/crear.php
?>
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Crear Nuevo Amenity</h1>
                    <p class="text-muted mb-0">Agrega un nuevo espacio común al edificio</p>
                </div>
                <div>
                    <a href="<?= $url->to('amenities/gestionar') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario -->
    <div class="row">
        <div class="col-12">
            <form method="POST" id="formAmenity">
                <div class="card">
                    <div class="card-header bg-transparent">
                        <h5 class="card-title mb-0">Información Básica</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Edificio -->
                            <div class="col-md-6">
                                <label class="form-label">Edificio <span class="text-danger">*</span></label>
                                <select name="edificio_id" class="form-select" required>
                                    <option value="">Seleccionar edificio</option>
                                    <?php foreach ($edificios as $edificio): ?>
                                        <option value="<?= $edificio['id'] ?>" 
                                            <?= (isset($_POST['edificio_id']) && $_POST['edificio_id'] == $edificio['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($edificio['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Tipo -->
                            <div class="col-md-6">
                                <label class="form-label">Tipo <span class="text-danger">*</span></label>
                                <select name="tipo" class="form-select" required>
                                    <option value="">Seleccionar tipo</option>
                                    <?php foreach ($tipos_amenities as $key => $nombre): ?>
                                        <option value="<?= $key ?>" 
                                            <?= (isset($_POST['tipo']) && $_POST['tipo'] == $key) ? 'selected' : '' ?>>
                                            <?= $nombre ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Nombre -->
                            <div class="col-md-8">
                                <label class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" name="nombre" class="form-control" 
                                       value="<?= isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : '' ?>" 
                                       placeholder="Ej: Piscina Principal, Gimnasio Completo..." required>
                            </div>

                            <!-- Capacidad -->
                            <div class="col-md-4">
                                <label class="form-label">Capacidad</label>
                                <input type="number" name="capacidad" class="form-control" 
                                       value="<?= isset($_POST['capacidad']) ? htmlspecialchars($_POST['capacidad']) : '' ?>" 
                                       placeholder="Número de personas" min="1">
                            </div>

                            <!-- Descripción -->
                            <div class="col-12">
                                <label class="form-label">Descripción</label>
                                <textarea name="descripcion" class="form-control" rows="3" 
                                          placeholder="Describe las características del amenity..."><?= isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : '' ?></textarea>
                            </div>

                            <!-- Reglas de Uso -->
                            <div class="col-12">
                                <label class="form-label">Reglas de Uso</label>
                                <textarea name="reglas_uso" class="form-control" rows="3" 
                                          placeholder="Establece las reglas para el uso de este amenity..."><?= isset($_POST['reglas_uso']) ? htmlspecialchars($_POST['reglas_uso']) : '' ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Horarios -->
                <div class="card mt-4">
                    <div class="card-header bg-transparent">
                        <h5 class="card-title mb-0">Horarios de Funcionamiento</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Horario General -->
                            <div class="col-md-6">
                                <label class="form-label">Horario Apertura General</label>
                                <input type="time" name="horario_apertura" class="form-control" 
                                       value="<?= isset($_POST['horario_apertura']) ? htmlspecialchars($_POST['horario_apertura']) : '08:00' ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Horario Cierre General</label>
                                <input type="time" name="horario_cierre" class="form-control" 
                                       value="<?= isset($_POST['horario_cierre']) ? htmlspecialchars($_POST['horario_cierre']) : '22:00' ?>">
                            </div>

                            <!-- Separador -->
                            <div class="col-12">
                                <hr>
                                <h6 class="text-muted">Horarios Específicos por Día</h6>
                            </div>

                            <!-- Lunes a Viernes -->
                            <div class="col-md-6">
                                <label class="form-label">Lunes a Viernes - Apertura</label>
                                <input type="time" name="horario_lunes_apertura" class="form-control" 
                                       value="<?= isset($_POST['horario_lunes_apertura']) ? htmlspecialchars($_POST['horario_lunes_apertura']) : '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Lunes a Viernes - Cierre</label>
                                <input type="time" name="horario_lunes_cierre" class="form-control" 
                                       value="<?= isset($_POST['horario_lunes_cierre']) ? htmlspecialchars($_POST['horario_lunes_cierre']) : '' ?>">
                            </div>

                            <!-- Sábado -->
                            <div class="col-md-6">
                                <label class="form-label">Sábado - Apertura</label>
                                <input type="time" name="horario_sabado_apertura" class="form-control" 
                                       value="<?= isset($_POST['horario_sabado_apertura']) ? htmlspecialchars($_POST['horario_sabado_apertura']) : '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Sábado - Cierre</label>
                                <input type="time" name="horario_sabado_cierre" class="form-control" 
                                       value="<?= isset($_POST['horario_sabado_cierre']) ? htmlspecialchars($_POST['horario_sabado_cierre']) : '' ?>">
                            </div>

                            <!-- Domingo -->
                            <div class="col-md-6">
                                <label class="form-label">Domingo - Apertura</label>
                                <input type="time" name="horario_domingo_apertura" class="form-control" 
                                       value="<?= isset($_POST['horario_domingo_apertura']) ? htmlspecialchars($_POST['horario_domingo_apertura']) : '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Domingo - Cierre</label>
                                <input type="time" name="horario_domingo_cierre" class="form-control" 
                                       value="<?= isset($_POST['horario_domingo_cierre']) ? htmlspecialchars($_POST['horario_domingo_cierre']) : '' ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Horarios Estacionales -->
                <div class="card mt-4">
                    <div class="card-header bg-transparent">
                        <h5 class="card-title mb-0">Horarios Estacionales</h5>
                        <p class="text-muted mb-0 small">Configura horarios especiales para verano/invierno</p>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Verano -->
                            <div class="col-md-6">
                                <label class="form-label">Verano - Inicio</label>
                                <input type="date" name="horario_verano_inicio" class="form-control" 
                                       value="<?= isset($_POST['horario_verano_inicio']) ? htmlspecialchars($_POST['horario_verano_inicio']) : '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Verano - Fin</label>
                                <input type="date" name="horario_verano_fin" class="form-control" 
                                       value="<?= isset($_POST['horario_verano_fin']) ? htmlspecialchars($_POST['horario_verano_fin']) : '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Verano - Apertura</label>
                                <input type="time" name="horario_verano_apertura" class="form-control" 
                                       value="<?= isset($_POST['horario_verano_apertura']) ? htmlspecialchars($_POST['horario_verano_apertura']) : '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Verano - Cierre</label>
                                <input type="time" name="horario_verano_cierre" class="form-control" 
                                       value="<?= isset($_POST['horario_verano_cierre']) ? htmlspecialchars($_POST['horario_verano_cierre']) : '' ?>">
                            </div>

                            <!-- Invierno -->
                            <div class="col-md-6">
                                <label class="form-label">Invierno - Inicio</label>
                                <input type="date" name="horario_invierno_inicio" class="form-control" 
                                       value="<?= isset($_POST['horario_invierno_inicio']) ? htmlspecialchars($_POST['horario_invierno_inicio']) : '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Invierno - Fin</label>
                                <input type="date" name="horario_invierno_fin" class="form-control" 
                                       value="<?= isset($_POST['horario_invierno_fin']) ? htmlspecialchars($_POST['horario_invierno_fin']) : '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Invierno - Apertura</label>
                                <input type="time" name="horario_invierno_apertura" class="form-control" 
                                       value="<?= isset($_POST['horario_invierno_apertura']) ? htmlspecialchars($_POST['horario_invierno_apertura']) : '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Invierno - Cierre</label>
                                <input type="time" name="horario_invierno_cierre" class="form-control" 
                                       value="<?= isset($_POST['horario_invierno_cierre']) ? htmlspecialchars($_POST['horario_invierno_cierre']) : '' ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Configuración de Reservas -->
                <div class="card mt-4">
                    <div class="card-header bg-transparent">
                        <h5 class="card-title mb-0">Configuración de Reservas</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Costo -->
                            <div class="col-md-6">
                                <label class="form-label">Costo de Uso ($)</label>
                                <input type="number" name="costo_uso" class="form-control" 
                                       value="<?= isset($_POST['costo_uso']) ? htmlspecialchars($_POST['costo_uso']) : '0' ?>" 
                                       min="0" step="0.01" placeholder="0.00">
                            </div>

                            <!-- Aprobación -->
                            <div class="col-md-6">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" name="requiere_aprobacion" 
                                           id="requiere_aprobacion" value="1" 
                                           <?= (isset($_POST['requiere_aprobacion']) && $_POST['requiere_aprobacion']) ? 'checked' : 'checked' ?>>
                                    <label class="form-check-label" for="requiere_aprobacion">
                                        Requiere aprobación administrativa
                                    </label>
                                </div>
                            </div>

                            <!-- Límites -->
                            <div class="col-md-4">
                                <label class="form-label">Máx. Reservas por Semana</label>
                                <input type="number" name="max_reservas_semana" class="form-control" 
                                       value="<?= isset($_POST['max_reservas_semana']) ? htmlspecialchars($_POST['max_reservas_semana']) : '2' ?>" 
                                       min="0" placeholder="2">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Máx. Reservas Mismo Día</label>
                                <input type="number" name="max_reservas_mismo_dia" class="form-control" 
                                       value="<?= isset($_POST['max_reservas_mismo_dia']) ? htmlspecialchars($_POST['max_reservas_mismo_dia']) : '1' ?>" 
                                       min="0" placeholder="1">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Antelación Máxima (días)</label>
                                <input type="number" name="antelacion_maxima_dias" class="form-control" 
                                       value="<?= isset($_POST['antelacion_maxima_dias']) ? htmlspecialchars($_POST['antelacion_maxima_dias']) : '30' ?>" 
                                       min="1" placeholder="30">
                            </div>

                            <!-- Duración -->
                            <div class="col-md-6">
                                <label class="form-label">Duración Mínima (minutos)</label>
                                <input type="number" name="duracion_minima_reserva" class="form-control" 
                                       value="<?= isset($_POST['duracion_minima_reserva']) ? htmlspecialchars($_POST['duracion_minima_reserva']) : '60' ?>" 
                                       min="15" step="15" placeholder="60">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Duración Máxima (minutos)</label>
                                <input type="number" name="duracion_maxima_reserva" class="form-control" 
                                       value="<?= isset($_POST['duracion_maxima_reserva']) ? htmlspecialchars($_POST['duracion_maxima_reserva']) : '240' ?>" 
                                       min="30" step="30" placeholder="240">
                            </div>

                            <!-- Configuración Específica -->
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="config_especifica" 
                                           id="config_especifica" value="1"
                                           <?= isset($_POST['config_especifica']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="config_especifica">
                                        Usar configuración específica para este amenity (sobrescribe configuración general)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="card mt-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <a href="<?= $url->to('amenities/gestionar') ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Crear Amenity
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('formAmenity').addEventListener('submit', function(e) {
    // Validaciones adicionales pueden ir aquí
    const edificio = this.elements['edificio_id'].value;
    const tipo = this.elements['tipo'].value;
    const nombre = this.elements['nombre'].value;

    if (!edificio || !tipo || !nombre) {
        e.preventDefault();
        alert('Por favor completa los campos obligatorios');
        return false;
    }
});

// Auto-completar horarios estacionales con fechas típicas
document.addEventListener('DOMContentLoaded', function() {
    const hoy = new Date();
    const año = hoy.getFullYear();
    
    // Verano: Diciembre a Febrero
    const veranoInicio = año + '-12-01';
    const veranoFin = (año + 1) + '-02-28';
    
    // Invierno: Junio a Agosto  
    const inviernoInicio = año + '-06-01';
    const inviernoFin = año + '-08-31';

    // Solo si los campos están vacíos
    const veranoInicioField = document.querySelector('input[name="horario_verano_inicio"]');
    const veranoFinField = document.querySelector('input[name="horario_verano_fin"]');
    const inviernoInicioField = document.querySelector('input[name="horario_invierno_inicio"]');
    const inviernoFinField = document.querySelector('input[name="horario_invierno_fin"]');

    if (veranoInicioField && !veranoInicioField.value) {
        veranoInicioField.value = veranoInicio;
        veranoFinField.value = veranoFin;
    }
    
    if (inviernoInicioField && !inviernoInicioField.value) {
        inviernoInicioField.value = inviernoInicio;
        inviernoFinField.value = inviernoFin;
    }
});
</script>