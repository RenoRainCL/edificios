<?php
// 📁 views/reservas/crear.php
?>
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Nueva Reserva</h1>
                    <p class="text-muted mb-0">Solicita el uso de un amenity común</p>
                </div>
                <div>
                    <a href="<?= $url->to('amenities/reservas/calendario?amenity_id=' . $amenity['id'] . '&fecha=' . $fecha_seleccionada) ?>" 
                       class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Volver al Calendario
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <form method="POST" id="formReserva" class="needs-validation" novalidate>
                <!-- Información del Amenity -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-building"></i> 
                            <?= htmlspecialchars($amenity['nombre']) ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Tipo:</strong> <?= ucfirst($amenity['tipo']) ?><br>
                                <strong>Capacidad:</strong> <?= $amenity['capacidad'] ?: 'N/A' ?> personas<br>
                                <strong>Costo:</strong> $<?= number_format($amenity['costo_uso'], 0) ?> por hora
                            </div>
                            <div class="col-md-6">
                                <strong>Horario:</strong> <?= substr($amenity['horario_apertura'], 0, 5) ?> - <?= substr($amenity['horario_cierre'], 0, 5) ?><br>
                                <strong>Aprobación:</strong> <?= $amenity['requiere_aprobacion'] ? 'Requiere aprobación' : 'Automática' ?><br>
                                <strong>Edificio:</strong> <?= htmlspecialchars($amenity['edificio_nombre']) ?>
                            </div>
                        </div>
                        <?php if ($amenity['descripcion']): ?>
                            <hr>
                            <p class="mb-0"><?= htmlspecialchars($amenity['descripcion']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Paso 1: Selección de Departamento -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">1. Selecciona tu Departamento</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($departamentos_usuario)): ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i>
                                No tienes departamentos asignados. Contacta al administrador.
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($departamentos_usuario as $depto): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="form-check card card-hover">
                                        <input class="form-check-input" type="radio" 
                                               name="departamento_id" 
                                               id="depto_<?= $depto['id'] ?>" 
                                               value="<?= $depto['id'] ?>" 
                                               required
                                               onchange="actualizarResumen()">
                                        <label class="form-check-label card-body" for="depto_<?= $depto['id'] ?>">
                                            <strong>Departamento <?= htmlspecialchars($depto['numero']) ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                <?= htmlspecialchars($depto['edificio_nombre']) ?>
                                                <?php if ($depto['propietario_rut']): ?>
                                                    · Propietario
                                                <?php elseif ($depto['arrendatario_rut']): ?>
                                                    · Arrendatario
                                                <?php endif; ?>
                                            </small>
                                        </label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Paso 2: Fecha y Horario -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">2. Fecha y Horario</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Fecha -->
                            <div class="col-md-6">
                                <label class="form-label">Fecha de Reserva <span class="text-danger">*</span></label>
                                <input type="date" name="fecha_reserva" class="form-control" 
                                       value="<?= $fecha_seleccionada ?>" 
                                       min="<?= date('Y-m-d') ?>" 
                                       max="<?= date('Y-m-d', strtotime('+'.$amenity['antelacion_maxima_dias'].' days')) ?>" 
                                       required
                                       onchange="cambiarFecha(this.value)">
                            </div>

                            <!-- Horario -->
                            <div class="col-md-6">
                                <label class="form-label">Selecciona un Horario <span class="text-danger">*</span></label>
                                <div id="contenedorHorarios">
                                    <?php if (empty($horarios_disponibles)): ?>
                                        <div class="alert alert-warning">
                                            No hay horarios disponibles para esta fecha.
                                        </div>
                                    <?php else: ?>
                                        <select name="horario_seleccionado" class="form-select" 
                                                id="selectHorario" required
                                                onchange="seleccionarHorario(this.value)">
                                            <option value="">Selecciona un horario...</option>
                                            <?php foreach ($horarios_disponibles as $horario): ?>
                                                <option value="<?= $horario['inicio'] ?>|<?= $horario['fin'] ?>" 
                                                        data-duracion="<?= $horario['duracion'] ?>" 
                                                        data-costo="<?= $horario['costo'] ?>">
                                                    <?= $horario['inicio'] ?> - <?= $horario['fin'] ?> 
                                                    (<?= $horario['duracion'] ?> min)
                                                    <?= $horario['costo'] > 0 ? ' - $' . number_format($horario['costo'], 0) : '' ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Horario Personalizado -->
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="horarioPersonalizado">
                                    <label class="form-check-label" for="horarioPersonalizado">
                                        Especificar horario personalizado
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-6" id="grupoHoraInicio" style="display: none;">
                                <label class="form-label">Hora de Inicio <span class="text-danger">*</span></label>
                                <input type="time" name="hora_inicio" class="form-control" 
                                       id="inputHoraInicio"
                                       onchange="validarHorarioPersonalizado()">
                            </div>

                            <div class="col-md-6" id="grupoHoraFin" style="display: none;">
                                <label class="form-label">Hora de Fin <span class="text-danger">*</span></label>
                                <input type="time" name="hora_fin" class="form-control" 
                                       id="inputHoraFin"
                                       onchange="validarHorarioPersonalizado()">
                            </div>

                            <!-- Validación de horario personalizado -->
                            <div class="col-12">
                                <div id="validacionHorario" class="alert" style="display: none;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Paso 3: Detalles de la Reserva -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">3. Detalles de la Reserva</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Motivo -->
                            <div class="col-12">
                                <label class="form-label">Motivo de la Reserva <span class="text-danger">*</span></label>
                                <textarea name="motivo" class="form-control" rows="3" 
                                          placeholder="Describe el propósito de tu reserva (reunión familiar, evento, etc.)..."
                                          required></textarea>
                                <small class="form-text text-muted">
                                    Mínimo 10 caracteres. Sé específico sobre el uso del espacio.
                                </small>
                            </div>

                            <!-- Número de Asistentes -->
                            <div class="col-md-6">
                                <label class="form-label">Número de Asistentes</label>
                                <input type="number" name="numero_asistentes" class="form-control" 
                                       value="1" min="1" 
                                       max="<?= $amenity['capacidad'] ?: 50 ?>"
                                       onchange="actualizarResumen()">
                                <small class="form-text text-muted">
                                    <?php if ($amenity['capacidad']): ?>
                                        Capacidad máxima: <?= $amenity['capacidad'] ?> personas
                                    <?php else: ?>
                                        Sin límite de capacidad
                                    <?php endif; ?>
                                </small>
                            </div>

                            <!-- Información Adicional -->
                            <div class="col-12">
                                <label class="form-label">Información Adicional (Opcional)</label>
                                <textarea name="observaciones" class="form-control" rows="2" 
                                          placeholder="Equipamiento especial requerido, necesidades específicas..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resumen y Confirmación -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">Resumen de la Reserva</h5>
                    </div>
                    <div class="card-body">
                        <div id="resumenReserva" class="text-center text-muted">
                            Completa los pasos anteriores para ver el resumen
                        </div>
                    </div>
                </div>

                <!-- Términos y Condiciones -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="aceptoTerminos" required>
                            <label class="form-check-label" for="aceptoTerminos">
                                Acepto los <a href="#" data-bs-toggle="modal" data-bs-target="#modalTerminos">términos y condiciones</a> 
                                y las <a href="#" data-bs-toggle="modal" data-bs-target="#modalReglas">reglas de uso</a> del amenity
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="<?= $url->to('amenities/reservas/calendario?amenity_id=' . $amenity['id'] . '&fecha=' . $fecha_seleccionada) ?>" 
                       class="btn btn-outline-secondary me-md-2">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary" id="btnEnviar">
                        <i class="bi bi-send"></i> 
                        <?= $amenity['requiere_aprobacion'] ? 'Enviar Solicitud' : 'Confirmar Reserva' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Términos -->
<div class="modal fade" id="modalTerminos" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Términos y Condiciones</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?= $amenity['reglas_uso'] ? nl2br(htmlspecialchars($amenity['reglas_uso'])) : 'No hay términos específicos definidos para este amenity.' ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Reglas -->
<div class="modal fade" id="modalReglas" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reglas de Uso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>Normas Generales:</h6>
                <ul>
                    <li>Respetar el horario establecido en la reserva</li>
                    <li>Dejar el espacio en las mismas condiciones en que se encontró</li>
                    <li>Reportar cualquier daño o incidente inmediatamente</li>
                    <li>Respetar la capacidad máxima del espacio</li>
                    <li>Cancelar con al menos 24 horas de anticipación si no se utilizará</li>
                </ul>
                
                <?php if ($amenity['costo_uso'] > 0): ?>
                <h6 class="mt-3">Política de Pagos:</h6>
                <ul>
                    <li>El costo se calculará por horas completas</li>
                    <li>El pago debe realizarse antes del uso del espacio</li>
                    <li>No hay reembolsos por cancelaciones con menos de 24 horas</li>
                </ul>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
// Variables globales
let horarioSeleccionado = null;
let costoTotal = 0;

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar fecha mínima y máxima
    const fechaInput = document.querySelector('input[name="fecha_reserva"]');
    const hoy = new Date().toISOString().split('T')[0];
    const maxFecha = new Date();
    maxFecha.setDate(maxFecha.getDate() + <?= $amenity['antelacion_maxima_dias'] ?>);
    
    fechaInput.min = hoy;
    fechaInput.max = maxFecha.toISOString().split('T')[0];

    // Configurar horario personalizado
    document.getElementById('horarioPersonalizado').addEventListener('change', function() {
        const mostrar = this.checked;
        document.getElementById('grupoHoraInicio').style.display = mostrar ? 'block' : 'none';
        document.getElementById('grupoHoraFin').style.display = mostrar ? 'block' : 'none';
        document.getElementById('selectHorario').disabled = mostrar;
        
        if (mostrar) {
            document.getElementById('selectHorario').value = '';
            actualizarResumen();
        }
    });

    // Validar formulario antes de enviar
    document.getElementById('formReserva').addEventListener('submit', function(e) {
        if (!this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        this.classList.add('was-validated');
        
        // Validación adicional de horario personalizado
        if (document.getElementById('horarioPersonalizado').checked) {
            const validacion = validarHorarioPersonalizado();
            if (!validacion.valido) {
                e.preventDefault();
                alert(validacion.mensaje);
                return;
            }
        }
        
        // Mostrar loading
        const btnEnviar = document.getElementById('btnEnviar');
        btnEnviar.disabled = true;
        btnEnviar.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando...';
    });
});

function cambiarFecha(nuevaFecha) {
    // Cargar horarios disponibles para la nueva fecha
    const amenityId = <?= $amenity['id'] ?>;

    fetch(`<?= $url->to('amenities/reservas/get-horarios-disponibles') ?>?amenity_id=${amenityId}&fecha=${nuevaFecha}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                actualizarSelectHorarios(data.data);
            } else {
                alert('Error al cargar horarios: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar horarios disponibles');
        });
}

function actualizarSelectHorarios(horarios) {
    const select = document.getElementById('selectHorario');
    select.innerHTML = '<option value="">Selecciona un horario...</option>';
    
    if (horarios.length === 0) {
        select.innerHTML = '<option value="">No hay horarios disponibles</option>';
        select.disabled = true;
    } else {
        select.disabled = false;
        horarios.forEach(horario => {
            const option = document.createElement('option');
            option.value = `${horario.inicio}|${horario.fin}`;
            option.textContent = `${horario.inicio} - ${horario.fin} (${horario.duracion} min)${horario.costo > 0 ? ' - $' + horario.costo.toLocaleString() : ''}`;
            option.setAttribute('data-duracion', horario.duracion);
            option.setAttribute('data-costo', horario.costo);
            select.appendChild(option);
        });
    }
    
    actualizarResumen();
}

function seleccionarHorario(valor) {
    if (valor) {
        const [inicio, fin] = valor.split('|');
        const option = document.getElementById('selectHorario').selectedOptions[0];
        
        horarioSeleccionado = { inicio, fin };
        costoTotal = parseFloat(option.getAttribute('data-costo'));
        
        // Si está en modo personalizado, actualizar los inputs
        if (document.getElementById('horarioPersonalizado').checked) {
            document.getElementById('inputHoraInicio').value = inicio;
            document.getElementById('inputHoraFin').value = fin;
        }
    } else {
        horarioSeleccionado = null;
        costoTotal = 0;
    }
    
    actualizarResumen();
}

function validarHorarioPersonalizado() {
    const inicio = document.getElementById('inputHoraInicio').value;
    const fin = document.getElementById('inputHoraFin').value;
    const validacionDiv = document.getElementById('validacionHorario');
    
    if (!inicio || !fin) {
        validacionDiv.style.display = 'none';
        return { valido: false, mensaje: 'Completa ambos horarios' };
    }
    
    // Verificar que fin sea mayor que inicio
    if (inicio >= fin) {
        validacionDiv.style.display = 'block';
        validacionDiv.className = 'alert alert-danger';
        validacionDiv.innerHTML = '<i class="bi bi-x-circle"></i> La hora de fin debe ser posterior a la hora de inicio';
        return { valido: false, mensaje: 'La hora de fin debe ser posterior a la hora de inicio' };
    }
    
    // Verificar disponibilidad via API
    const amenityId = <?= $amenity['id'] ?>;
    const fecha = document.querySelector('input[name="fecha_reserva"]').value;

    fetch(`<?= $url->to('amenities/reservas/verificar-disponibilidad') ?>?amenity_id=${amenityId}&fecha=${fecha}&hora_inicio=${inicio}&hora_fin=${fin}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.data.disponible) {
                    validacionDiv.style.display = 'block';
                    validacionDiv.className = 'alert alert-success';
                    validacionDiv.innerHTML = `<i class="bi bi-check-circle"></i> ${data.data.mensaje}`;
                    
                    horarioSeleccionado = { inicio, fin };
                    costoTotal = data.data.costo;
                    actualizarResumen();
                } else {
                    validacionDiv.style.display = 'block';
                    validacionDiv.className = 'alert alert-danger';
                    validacionDiv.innerHTML = `<i class="bi bi-x-circle"></i> ${data.data.mensaje}`;
                }
            }
        });
    
    return { valido: true, mensaje: 'Horario válido' };
}

function actualizarResumen() {
    const resumenDiv = document.getElementById('resumenReserva');
    const deptoSeleccionado = document.querySelector('input[name="departamento_id"]:checked');
    
    if (!deptoSeleccionado || !horarioSeleccionado) {
        resumenDiv.innerHTML = '<div class="text-muted">Completa los pasos anteriores para ver el resumen</div>';
        return;
    }
    
    const deptoLabel = deptoSeleccionado.nextElementSibling.querySelector('strong').textContent;
    const asistentes = document.querySelector('input[name="numero_asistentes"]').value;
    const fecha = document.querySelector('input[name="fecha_reserva"]').value;
    
    const html = `
        <div class="row text-start">
            <div class="col-md-6">
                <strong>Departamento:</strong><br>
                <strong>Fecha:</strong><br>
                <strong>Horario:</strong><br>
                <strong>Asistentes:</strong><br>
                ${costoTotal > 0 ? '<strong>Costo Total:</strong><br>' : ''}
                <strong>Estado:</strong>
            </div>
            <div class="col-md-6">
                ${deptoLabel}<br>
                ${new Date(fecha).toLocaleDateString('es-CL')}<br>
                ${horarioSeleccionado.inicio} - ${horarioSeleccionado.fin}<br>
                ${asistentes} personas<br>
                ${costoTotal > 0 ? '$' + costoTotal.toLocaleString() + '<br>' : ''}
                <span class="badge bg-<?= $amenity['requiere_aprobacion'] ? 'warning' : 'success' ?>">
                    <?= $amenity['requiere_aprobacion'] ? 'Pendiente de Aprobación' : 'Confirmada' ?>
                </span>
            </div>
        </div>
    `;
    
    resumenDiv.innerHTML = html;
}
</script>

<style>
.card-hover:hover {
    background-color: #f8f9fa;
    cursor: pointer;
}

.form-check-input:checked + .card-body {
    background-color: #e7f3ff;
    border-radius: 0.375rem;
}

.needs-validation .form-control:invalid,
.needs-validation .form-select:invalid {
    border-color: #dc3545;
}

.needs-validation .form-control:valid,
.needs-validation .form-select:valid {
    border-color: #198754;
}
</style>