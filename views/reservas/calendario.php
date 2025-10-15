<?php
// 📁 views/reservas/calendario.php
?>
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Calendario de Reservas</h1>
                    <p class="text-muted mb-0">Visualiza y gestiona las reservas de amenities</p>
                </div>
                <div class="btn-group">
                    <a href="<?= $url->to('/amenities/reservas/mis-reservas') ?>" class="btn btn-outline-primary">
                        <i class="bi bi-list-ul"></i> Mis Reservas
                    </a>
                    <a href="<?= $url->to('/amenities/reservas/aprobaciones') ?>" class="btn btn-outline-warning">
                        <i class="bi bi-shield-check"></i> Aprobaciones
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Edificio</label>
                            <select name="edificio_id" class="form-select" onchange="cambiarEdificio(this.value)">
                                <option value="">Seleccionar edificio</option>
                                <?php foreach ($edificios as $edificio): ?>
                                    <option value="<?= $edificio['id'] ?>" 
                                        <?= ($edificio_actual && $edificio['id'] == $edificio_actual['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($edificio['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Amenity</label>
                            <select name="amenity_id" class="form-select" id="selectAmenity">
                                <option value="">Todos los amenities</option>
                                <?php foreach ($amenities as $amenity): ?>
                                    <option value="<?= $amenity['id'] ?>" 
                                        <?= (isset($_GET['amenity_id']) && $_GET['amenity_id'] == $amenity['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($amenity['nombre']) ?> (<?= $amenity['tipo'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha</label>
                            <input type="date" name="fecha" class="form-control" 
                                   value="<?= $fecha_actual ?>" 
                                   onchange="this.form.submit()">
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-funnel"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas Rápidas -->
    <?php if ($edificio_actual): ?>
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-hover border-start border-primary border-4">
                <div class="card-body text-center">
                    <h3 class="text-primary"><?= count($amenities) ?></h3>
                    <small class="text-muted">Amenities Activos</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-hover border-start border-success border-4">
                <div class="card-body text-center">
                    <h3 class="text-success">
                        <?= array_sum(array_column($amenities, 'reservas_confirmadas')) ?>
                    </h3>
                    <small class="text-muted">Reservas Hoy</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-hover border-start border-warning border-4">
                <div class="card-body text-center">
                    <h3 class="text-warning">
                        <?= array_sum(array_column($amenities, 'reservas_pendientes')) ?>
                    </h3>
                    <small class="text-muted">Pendientes Aprobación</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-hover border-start border-info border-4">
                <div class="card-body text-center">
                    <h3 class="text-info">
                        <?= count($departamentos_usuario) ?>
                    </h3>
                    <small class="text-muted">Mis Departamentos</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-hover border-start border-danger border-4">
                <div class="card-body text-center">
                    <h3 class="text-danger">
                        <?= array_sum(array_column($amenities, 'capacidad_total')) ?>
                    </h3>
                    <small class="text-muted">Capacidad Total</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-hover border-start border-secondary border-4">
                <div class="card-body text-center">
                    <h3 class="text-secondary">
                        <?= array_sum(array_column($amenities, 'utilizacion_hoy')) ?>%
                    </h3>
                    <small class="text-muted">Utilización Hoy</small>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Vista Calendario -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <?php if ($amenity_seleccionado): ?>
                            Reservas - <?= htmlspecialchars($amenity_seleccionado['nombre']) ?> 
                            <small class="text-muted">(<?= date('d/m/Y', strtotime($fecha_actual)) ?>)</small>
                        <?php else: ?>
                            Vista General - <?= $edificio_actual ? htmlspecialchars($edificio_actual['nombre']) : 'Selecciona un edificio' ?>
                        <?php endif; ?>
                    </h5>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-secondary" onclick="cambiarFecha(-1)">
                            <i class="bi bi-chevron-left"></i> Ayer
                        </button>
                        <button class="btn btn-sm btn-outline-primary" onclick="irHoy()">
                            Hoy
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" onclick="cambiarFecha(1)">
                            Mañana <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!$edificio_actual): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-building display-1 text-muted"></i>
                            <h4 class="text-muted mt-3">Selecciona un edificio</h4>
                            <p class="text-muted">Elige un edificio para ver sus reservas</p>
                        </div>
                    <?php elseif ($amenity_seleccionado): ?>
                        <!-- Vista detallada de un amenity -->
                        <div class="row">
                            <div class="col-md-8">
                                <div class="timeline-reservas">
                                    <h6 class="text-muted mb-3">Horario del Día</h6>
                                    <?php 
                                    $horarios = $this->getHorariosDisponiblesAmenity($amenity_seleccionado['id'], $fecha_actual);
                                    $reservasPorHora = [];
                                    
                                    foreach ($reservas as $reserva) {
                                        $hora = substr($reserva['hora_inicio'], 0, 5);
                                        $reservasPorHora[$hora] = $reserva;
                                    }
                                    
                                    for ($hora = 6; $hora <= 23; $hora++): 
                                        for ($minuto = 0; $minuto < 60; $minuto += 30): 
                                            $horaActual = sprintf('%02d:%02d', $hora, $minuto);
                                            $reserva = $reservasPorHora[$horaActual] ?? null;
                                    ?>
                                        <div class="timeline-item d-flex align-items-center mb-2">
                                            <div class="timeline-time col-2">
                                                <small class="text-muted"><?= $horaActual ?></small>
                                            </div>
                                            <div class="timeline-content col-10">
                                                <?php if ($reserva): ?>
                                                    <div class="reserva-card card <?= $reserva['estado'] == 'confirmada' ? 'border-success' : 'border-warning' ?>">
                                                        <div class="card-body py-2">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <div>
                                                                    <strong><?= htmlspecialchars($reserva['departamento_numero']) ?></strong>
                                                                    <small class="text-muted"> - <?= htmlspecialchars($reserva['usuario_nombre']) ?></small>
                                                                    <br>
                                                                    <small><?= htmlspecialchars($reserva['motivo']) ?></small>
                                                                </div>
                                                                <div class="text-end">
                                                                    <span class="badge bg-<?= $reserva['estado'] == 'confirmada' ? 'success' : 'warning' ?>">
                                                                        <?= $reserva['estado'] ?>
                                                                    </span>
                                                                    <br>
                                                                    <small class="text-muted">
                                                                        <?= substr($reserva['hora_inicio'], 0, 5) ?> - <?= substr($reserva['hora_fin'], 0, 5) ?>
                                                                    </small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="disponible-card card border-dashed">
                                                        <div class="card-body py-2 text-center">
                                                            <small class="text-success">
                                                                <i class="bi bi-check-circle"></i> Disponible
                                                            </small>
                                                            <?php if ($this->usuarioPuedeReservar()): ?>
                                                                <br>
                                                                <button class="btn btn-sm btn-outline-primary mt-1" 
                                                                        onclick="reservarHorario('<?= $horaActual ?>')">
                                                                    <i class="bi bi-plus"></i> Reservar
                                                                </button>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endfor; endfor; ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <!-- Información del Amenity -->
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">Información del Amenity</h6>
                                    </div>
                                    <div class="card-body">
                                        <h5><?= htmlspecialchars($amenity_seleccionado['nombre']) ?></h5>
                                        <p class="text-muted"><?= htmlspecialchars($amenity_seleccionado['descripcion']) ?></p>
                                        
                                        <div class="amenity-info">
                                            <div class="row small">
                                                <div class="col-6">
                                                    <i class="bi bi-people"></i> 
                                                    Capacidad: <?= $amenity_seleccionado['capacidad'] ?: 'N/A' ?>
                                                </div>
                                                <div class="col-6">
                                                    <i class="bi bi-cash-coin"></i> 
                                                    Costo: $<?= number_format($amenity_seleccionado['costo_uso'], 0) ?>
                                                </div>
                                                <div class="col-6">
                                                    <i class="bi bi-clock"></i> 
                                                    Horario: <?= substr($amenity_seleccionado['horario_apertura'], 0, 5) ?> - <?= substr($amenity_seleccionado['horario_cierre'], 0, 5) ?>
                                                </div>
                                                <div class="col-6">
                                                    <i class="bi bi-shield-check"></i> 
                                                    <?= $amenity_seleccionado['requiere_aprobacion'] ? 'Requiere aprobación' : 'Auto-aprobación' ?>
                                                </div>
                                            </div>
                                        </div>

                                        <hr>
                                        
                                        <div class="d-grid gap-2">
                                            <?php if ($this->usuarioPuedeReservar()): ?>
                                                <a href="<?= $url->to('amenities/reservas/crear?amenity_id=' . $amenity_seleccionado['id'] . '&fecha=' . $fecha_actual) ?>" 
                                                   class="btn btn-primary">
                                                    <i class="bi bi-plus-circle"></i> Nueva Reserva
                                                </a>
                                            <?php endif; ?>
                                            <a href="<?= $url->to('amenities/editar/' . $amenity_seleccionado['id']) ?>" 
                                               class="btn btn-outline-secondary">
                                                <i class="bi bi-pencil"></i> Editar Amenity
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Estadísticas del Día -->
                                <div class="card mt-3">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">Estadísticas del Día</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row text-center">
                                            <div class="col-6">
                                                <h4 class="text-success"><?= count(array_filter($reservas, function($r) { return $r['estado'] == 'confirmada'; })) ?></h4>
                                                <small class="text-muted">Confirmadas</small>
                                            </div>
                                            <div class="col-6">
                                                <h4 class="text-warning"><?= count(array_filter($reservas, function($r) { return $r['estado'] == 'pendiente'; })) ?></h4>
                                                <small class="text-muted">Pendientes</small>
                                            </div>
                                        </div>
                                        <div class="progress mt-2" style="height: 8px;">
                                            <?php 
                                            $totalHoras = 18; // 6:00 - 24:00
                                            $horasOcupadas = count($reservas) * 2; // Asumiendo 2 horas por reserva
                                            $porcentaje = min(100, ($horasOcupadas / $totalHoras) * 100);
                                            ?>
                                            <div class="progress-bar bg-success" style="width: <?= $porcentaje ?>%"></div>
                                        </div>
                                        <small class="text-muted"><?= round($porcentaje) ?>% de ocupación</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Vista general de todos los amenities -->
                        <div class="row g-3">
                            <?php foreach ($amenities as $amenity): ?>
                            <div class="col-xl-4 col-md-6">
                                <div class="card card-hover h-100">
                                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                                        <span class="badge bg-<?= $this->getAmenityBadgeColor($amenity['tipo']) ?>">
                                            <?= $amenity['tipo'] ?>
                                        </span>
                                        <small class="text-muted">
                                            <?= $amenity['reservas_hoy'] ?> reservas hoy
                                        </small>
                                    </div>
                                    <div class="card-body">
                                        <h6 class="card-title"><?= htmlspecialchars($amenity['nombre']) ?></h6>
                                        
                                        <!-- Barra de ocupación -->
                                        <div class="ocupacion-progress mb-2">
                                            <div class="d-flex justify-content-between small text-muted mb-1">
                                                <span>Ocupación del día</span>
                                                <span><?= $amenity['utilizacion_hoy'] ?>%</span>
                                            </div>
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar bg-<?= $amenity['utilizacion_hoy'] > 80 ? 'danger' : ($amenity['utilizacion_hoy'] > 50 ? 'warning' : 'success') ?>" 
                                                     style="width: <?= $amenity['utilizacion_hoy'] ?>%"></div>
                                            </div>
                                        </div>

                                        <!-- Reservas próximas -->
                                        <div class="reservas-proximas">
                                            <small class="text-muted d-block mb-2">Próximas reservas:</small>
                                            <?php 
                                            $reservasAmenity = $this->getReservasAmenity($amenity['id'], $fecha_actual);
                                            $proximas = array_slice($reservasAmenity, 0, 3);
                                            ?>
                                            
                                            <?php if (empty($proximas)): ?>
                                                <div class="text-center text-muted py-2">
                                                    <small>No hay reservas para hoy</small>
                                                </div>
                                            <?php else: ?>
                                                <?php foreach ($proximas as $reserva): ?>
                                                    <div class="reserva-item d-flex justify-content-between align-items-center mb-1 p-1 border rounded">
                                                        <div>
                                                            <small class="fw-bold"><?= $reserva['departamento_numero'] ?></small>
                                                            <br>
                                                            <small class="text-muted"><?= substr($reserva['hora_inicio'], 0, 5) ?></small>
                                                        </div>
                                                        <span class="badge bg-<?= $reserva['estado'] == 'confirmada' ? 'success' : 'warning' ?>">
                                                            <?= $reserva['estado'] ?>
                                                        </span>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-transparent">
                                        <div class="d-grid">
                                            <a href="<?= $url->to('amenities/reservas/calendario?edificio_id=' . $edificio_actual['id'] . '&amenity_id=' . $amenity['id'] . '&fecha=' . $fecha_actual) ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                Ver Detalles
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function cambiarEdificio(edificioId) {
    if (edificioId) {
        window.location.href = '<?= $url->to('amenities/reservas/calendario') ?>?edificio_id=' + edificioId;
    } else {
        window.location.href = '<?= $url->to('amenities/reservas/calendario') ?>';
    }
}

function cambiarFecha(dias) {
    const fechaActual = new Date('<?= $fecha_actual ?>');
    fechaActual.setDate(fechaActual.getDate() + dias);
    
    const nuevaFecha = fechaActual.toISOString().split('T')[0];
    const url = new URL(window.location.href);
    url.searchParams.set('fecha', nuevaFecha);
    window.location.href = url.toString();
}

function irHoy() {
    const url = new URL(window.location.href);
    url.searchParams.set('fecha', '<?= date('Y-m-d') ?>');
    window.location.href = url.toString();
}

function reservarHorario(horaInicio) {
    const amenityId = '<?= $amenity_seleccionado['id'] ?? '' ?>';
    const fecha = '<?= $fecha_actual ?>';
    
    if (amenityId) {
        window.location.href = `<?= $url->to('amenities/reservas/crear') ?>?amenity_id=${amenityId}&fecha=${fecha}&hora_inicio=${horaInicio}`;
    }
}

// Actualizar automáticamente cada 2 minutos si estamos en vista detallada
<?php if ($amenity_seleccionado): ?>
setTimeout(() => {
    window.location.reload();
}, 120000);
<?php endif; ?>
</script>

<style>
.timeline-item {
    border-left: 2px solid #e9ecef;
    padding-left: 15px;
}

.timeline-time {
    min-width: 60px;
}

.reserva-card {
    background-color: #f8f9fa;
    border-left: 4px solid #28a745 !important;
}

.reserva-card.border-warning {
    border-left-color: #ffc107 !important;
}

.disponible-card {
    background-color: #f0fff0;
    border: 1px dashed #28a745;
    border-left: 4px solid #28a745 !important;
}

.border-dashed {
    border-style: dashed !important;
}

.card-hover:hover {
    transform: translateY(-2px);
    transition: transform 0.2s ease-in-out;
}

.ocupacion-progress .progress {
    background-color: #e9ecef;
}

.reserva-item {
    background-color: #f8f9fa;
    transition: background-color 0.2s;
}

.reserva-item:hover {
    background-color: #e9ecef;
}
</style>