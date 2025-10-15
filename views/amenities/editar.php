<?php
// 📁 views/amenities/editar.php
?>
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Editar Amenity</h1>
                    <p class="text-muted mb-0">Modifica la información del espacio común</p>
                </div>
                <div class="btn-group">
                    <a href="<?= $url->to('amenities/gestionar?edificio_id=' . $amenity['edificio_id']) ?>" 
                       class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                    <a href="<?= $url->to('amenities/imagenes/' . $amenity['id']) ?>" 
                       class="btn btn-outline-primary">
                        <i class="bi bi-images"></i> Gestionar Imágenes
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario de Edición -->
    <div class="row">
        <div class="col-12">
            <form method="POST" id="formEditarAmenity">
                <div class="card">
                    <div class="card-header bg-transparent">
                        <h5 class="card-title mb-0">Información Básica</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Edificio (solo lectura) -->
                            <div class="col-md-6">
                                <label class="form-label">Edificio</label>
                                <input type="text" class="form-control" 
                                       value="<?= htmlspecialchars($amenity['edificio_nombre']) ?>" 
                                       readonly>
                                <input type="hidden" name="edificio_id" value="<?= $amenity['edificio_id'] ?>">
                            </div>

                            <!-- Tipo -->
                            <div class="col-md-6">
                                <label class="form-label">Tipo <span class="text-danger">*</span></label>
                                <select name="tipo" class="form-select" required>
                                    <?php foreach ($tipos_amenities as $key => $nombre): ?>
                                        <option value="<?= $key ?>" 
                                            <?= ($amenity['tipo'] == $key) ? 'selected' : '' ?>>
                                            <?= $nombre ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Nombre -->
                            <div class="col-md-8">
                                <label class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" name="nombre" class="form-control" 
                                       value="<?= htmlspecialchars($amenity['nombre']) ?>" required>
                            </div>

                            <!-- Capacidad -->
                            <div class="col-md-4">
                                <label class="form-label">Capacidad</label>
                                <input type="number" name="capacidad" class="form-control" 
                                       value="<?= $amenity['capacidad'] ?>" min="1">
                            </div>

                            <!-- Descripción -->
                            <div class="col-12">
                                <label class="form-label">Descripción</label>
                                <textarea name="descripcion" class="form-control" rows="3"><?= htmlspecialchars($amenity['descripcion']) ?></textarea>
                            </div>

                            <!-- Reglas de Uso -->
                            <div class="col-12">
                                <label class="form-label">Reglas de Uso</label>
                                <textarea name="reglas_uso" class="form-control" rows="3"><?= htmlspecialchars($amenity['reglas_uso']) ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vista Previa de Imágenes -->
                <?php if (!empty($imagenes)): ?>
                <div class="card mt-4">
                    <div class="card-header bg-transparent">
                        <h5 class="card-title mb-0">Imágenes Actuales</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <?php foreach ($imagenes as $imagen): ?>
                            <div class="col-xl-2 col-md-3 col-4">
                                <div class="position-relative">
                                    <img src="<?= $url->asset('uploads/amenities/' . $amenity['id'] . '/' . $imagen['ruta_archivo']) ?>" 
                                         class="img-thumbnail w-100" 
                                         style="height: 100px; object-fit: cover;"
                                         alt="<?= htmlspecialchars($imagen['nombre_archivo']) ?>">
                                    <?php if ($imagen['is_principal']): ?>
                                        <span class="position-absolute top-0 start-0 badge bg-primary">
                                            <i class="bi bi-star-fill"></i> Principal
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Horarios (similar al crear pero con valores actuales) -->
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
                                       value="<?= $amenity['horario_apertura'] ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Horario Cierre General</label>
                                <input type="time" name="horario_cierre" class="form-control" 
                                       value="<?= $amenity['horario_cierre'] ?>">
                            </div>

                            <!-- Horarios Específicos -->
                            <div class="col-12">
                                <hr>
                                <h6 class="text-muted">Horarios Específicos por Día</h6>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Lunes a Viernes - Apertura</label>
                                <input type="time" name="horario_lunes_apertura" class="form-control" 
                                       value="<?= $amenity['horario_lunes_apertura'] ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Lunes a Viernes - Cierre</label>
                                <input type="time" name="horario_lunes_cierre" class="form-control" 
                                       value="<?= $amenity['horario_lunes_cierre'] ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Sábado - Apertura</label>
                                <input type="time" name="horario_sabado_apertura" class="form-control" 
                                       value="<?= $amenity['horario_sabado_apertura'] ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Sábado - Cierre</label>
                                <input type="time" name="horario_sabado_cierre" class="form-control" 
                                       value="<?= $amenity['horario_sabado_cierre'] ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Domingo - Apertura</label>
                                <input type="time" name="horario_domingo_apertura" class="form-control" 
                                       value="<?= $amenity['horario_domingo_apertura'] ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Domingo - Cierre</label>
                                <input type="time" name="horario_domingo_cierre" class="form-control" 
                                       value="<?= $amenity['horario_domingo_cierre'] ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Horarios Estacionales -->
                <div class="card mt-4">
                    <div class="card-header bg-transparent">
                        <h5 class="card-title mb-0">Horarios Estacionales</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Verano -->
                            <div class="col-md-6">
                                <label class="form-label">Verano - Inicio</label>
                                <input type="date" name="horario_verano_inicio" class="form-control" 
                                       value="<?= $amenity['horario_verano_inicio'] ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Verano - Fin</label>
                                <input type="date" name="horario_verano_fin" class="form-control" 
                                       value="<?= $amenity['horario_verano_fin'] ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Verano - Apertura</label>
                                <input type="time" name="horario_verano_apertura" class="form-control" 
                                       value="<?= $amenity['horario_verano_apertura'] ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Verano - Cierre</label>
                                <input type="time" name="horario_verano_cierre" class="form-control" 
                                       value="<?= $amenity['horario_verano_cierre'] ?>">
                            </div>

                            <!-- Invierno -->
                            <div class="col-md-6">
                                <label class="form-label">Invierno - Inicio</label>
                                <input type="date" name="horario_invierno_inicio" class="form-control" 
                                       value="<?= $amenity['horario_invierno_inicio'] ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Invierno - Fin</label>
                                <input type="date" name="horario_invierno_fin" class="form-control" 
                                       value="<?= $amenity['horario_invierno_fin'] ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Invierno - Apertura</label>
                                <input type="time" name="horario_invierno_apertura" class="form-control" 
                                       value="<?= $amenity['horario_invierno_apertura'] ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Invierno - Cierre</label>
                                <input type="time" name="horario_invierno_cierre" class="form-control" 
                                       value="<?= $amenity['horario_invierno_cierre'] ?>">
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
                                       value="<?= $amenity['costo_uso'] ?>" min="0" step="0.01">
                            </div>

                            <!-- Aprobación -->
                            <div class="col-md-6">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" name="requiere_aprobacion" 
                                           id="requiere_aprobacion" value="1" 
                                           <?= $amenity['requiere_aprobacion'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="requiere_aprobacion">
                                        Requiere aprobación administrativa
                                    </label>
                                </div>
                            </div>

                            <!-- Límites -->
                            <div class="col-md-4">
                                <label class="form-label">Máx. Reservas por Semana</label>
                                <input type="number" name="max_reservas_semana" class="form-control" 
                                       value="<?= $amenity['max_reservas_semana'] ?>" min="0">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Máx. Reservas Mismo Día</label>
                                <input type="number" name="max_reservas_mismo_dia" class="form-control" 
                                       value="<?= $amenity['max_reservas_mismo_dia'] ?>" min="0">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Antelación Máxima (días)</label>
                                <input type="number" name="antelacion_maxima_dias" class="form-control" 
                                       value="<?= $amenity['antelacion_maxima_dias'] ?>" min="1">
                            </div>

                            <!-- Duración -->
                            <div class="col-md-6">
                                <label class="form-label">Duración Mínima (minutos)</label>
                                <input type="number" name="duracion_minima_reserva" class="form-control" 
                                       value="<?= $amenity['duracion_minima_reserva'] ?>" min="15" step="15">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Duración Máxima (minutos)</label>
                                <input type="number" name="duracion_maxima_reserva" class="form-control" 
                                       value="<?= $amenity['duracion_maxima_reserva'] ?>" min="30" step="30">
                            </div>

                            <!-- Bloques Horarios -->
                            <div class="col-12">
                                <label class="form-label">Bloques Horarios Predefinidos</label>
                                <div id="bloquesHorariosContainer">
                                    <?php 
                                    $bloques = $amenity['bloques_horarios'] ? json_decode($amenity['bloques_horarios'], true) : [];
                                    if (!empty($bloques)): 
                                        foreach ($bloques as $index => $bloque): 
                                    ?>
                                        <div class="bloque-horario input-group mb-2">
                                            <input type="time" class="form-control" 
                                                   name="bloques_horarios[<?= $index ?>][inicio]" 
                                                   value="<?= $bloque['inicio'] ?>">
                                            <span class="input-group-text">a</span>
                                            <input type="time" class="form-control" 
                                                   name="bloques_horarios[<?= $index ?>][fin]" 
                                                   value="<?= $bloque['fin'] ?>">
                                            <button type="button" class="btn btn-outline-danger quitar-bloque">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="bloque-horario input-group mb-2">
                                            <input type="time" class="form-control" 
                                                   name="bloques_horarios[0][inicio]" 
                                                   placeholder="Inicio">
                                            <span class="input-group-text">a</span>
                                            <input type="time" class="form-control" 
                                                   name="bloques_horarios[0][fin]" 
                                                   placeholder="Fin">
                                            <button type="button" class="btn btn-outline-danger quitar-bloque">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <button type="button" id="agregarBloque" class="btn btn-sm btn-outline-primary mt-2">
                                    <i class="bi bi-plus"></i> Agregar Bloque Horario
                                </button>
                                <small class="form-text text-muted">
                                    Define bloques horarios preestablecidos para facilitar las reservas
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="card mt-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <a href="<?= $url->to('amenities/gestionar?edificio_id=' . $amenity['edificio_id']) ?>" 
                               class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <div class="btn-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> Guardar Cambios
                                </button>
                                <button type="button" class="btn btn-outline-danger" 
                                        onclick="confirmarDesactivar(<?= $amenity['id'] ?>)">
                                    <i class="bi bi-trash"></i> Desactivar
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
// Gestión de bloques horarios
let contadorBloques = <?= !empty($bloques) ? count($bloques) : 1 ?>;

document.getElementById('agregarBloque').addEventListener('click', function() {
    contadorBloques++;
    const nuevoBloque = document.createElement('div');
    nuevoBloque.className = 'bloque-horario input-group mb-2';
    nuevoBloque.innerHTML = `
        <input type="time" class="form-control" 
               name="bloques_horarios[${contadorBloques}][inicio]" 
               placeholder="Inicio">
        <span class="input-group-text">a</span>
        <input type="time" class="form-control" 
               name="bloques_horarios[${contadorBloques}][fin]" 
               placeholder="Fin">
        <button type="button" class="btn btn-outline-danger quitar-bloque">
            <i class="bi bi-trash"></i>
        </button>
    `;
    document.getElementById('bloquesHorariosContainer').appendChild(nuevoBloque);
});

// Delegación de eventos para botones de quitar bloque
document.getElementById('bloquesHorariosContainer').addEventListener('click', function(e) {
    if (e.target.classList.contains('quitar-bloque') || e.target.closest('.quitar-bloque')) {
        const bloque = e.target.closest('.bloque-horario');
        if (document.querySelectorAll('.bloque-horario').length > 1) {
            bloque.remove();
        } else {
            alert('Debe haber al menos un bloque horario');
        }
    }
});

function confirmarDesactivar(amenityId) {
    if (confirm('¿Estás seguro de que deseas desactivar este amenity? Las reservas futuras serán canceladas.')) {
        fetch('<?= $url->to('amenities/desactivar/') ?>' + amenityId, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '<?= $url->to('amenities/gestionar?edificio_id=' . $amenity['edificio_id']) ?>';
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al desactivar el amenity');
        });
    }
}
</script>