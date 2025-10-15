<?php
// 📁 views/reservas/aprobaciones.php
?>
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Aprobación de Reservas</h1>
                    <p class="text-muted mb-0">Gestiona las solicitudes de reserva pendientes</p>
                </div>
                <div class="btn-group">
                    <a href="<?= $url->to('reservas/calendario') ?>" class="btn btn-outline-primary">
                        <i class="bi bi-calendar-week"></i> Calendario
                    </a>
                    <a href="<?= $url->to('reservas/mis-reservas') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-list-ul"></i> Mis Reservas
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
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6">
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
                        <div class="col-md-6">
                            <div class="alert alert-info mb-0">
                                <small>
                                    <i class="bi bi-info-circle"></i>
                                    <strong>Reservas pendientes:</strong> 
                                    <?= count($reservas_pendientes) ?> solicitudes esperando aprobación
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas de Aprobación -->
    <?php if ($edificio_actual): ?>
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-hover border-start border-warning border-4">
                <div class="card-body text-center">
                    <h3 class="text-warning"><?= count($reservas_pendientes) ?></h3>
                    <small class="text-muted">Pendientes</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-hover border-start border-success border-4">
                <div class="card-body text-center">
                    <h3 class="text-success">
                        <?= $estadisticas_aprobacion['aprobadas_hoy'] ?? 0 ?>
                    </h3>
                    <small class="text-muted">Aprobadas Hoy</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-hover border-start border-danger border-4">
                <div class="card-body text-center">
                    <h3 class="text-danger">
                        <?= $estadisticas_aprobacion['rechazadas_hoy'] ?? 0 ?>
                    </h3>
                    <small class="text-muted">Rechazadas Hoy</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-hover border-start border-info border-4">
                <div class="card-body text-center">
                    <h3 class="text-info">
                        <?= $estadisticas_aprobacion['tiempo_promedio'] ?? 0 ?>h
                    </h3>
                    <small class="text-muted">Tiempo Respuesta</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-hover border-start border-primary border-4">
                <div class="card-body text-center">
                    <h3 class="text-primary">
                        <?= $estadisticas_aprobacion['amenities_pendientes'] ?? 0 ?>
                    </h3>
                    <small class="text-muted">Amenities con Pendientes</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-hover border-start border-secondary border-4">
                <div class="card-body text-center">
                    <h3 class="text-secondary">
                        <?= $estadisticas_aprobacion['usuarios_pendientes'] ?? 0 ?>
                    </h3>
                    <small class="text-muted">Usuarios Esperando</small>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Lista de Reservas Pendientes -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <?= $edificio_actual ? 'Solicitudes Pendientes - ' . htmlspecialchars($edificio_actual['nombre']) : 'Selecciona un edificio' ?>
                    </h5>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-success" onclick="aprobarTodas()">
                            <i class="bi bi-check-all"></i> Aprobar Todas
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="rechazarTodas()">
                            <i class="bi bi-x-circle"></i> Rechazar Todas
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!$edificio_actual): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-building display-1 text-muted"></i>
                            <h4 class="text-muted mt-3">Selecciona un edificio</h4>
                            <p class="text-muted">Elige un edificio para gestionar sus aprobaciones</p>
                        </div>
                    <?php elseif (empty($reservas_pendientes)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-check-circle display-1 text-success"></i>
                            <h4 class="text-success mt-3">¡Todo al día!</h4>
                            <p class="text-muted">No hay reservas pendientes de aprobación</p>
                        </div>
                    <?php else: ?>
                        <div class="row g-4">
                            <?php foreach ($reservas_pendientes as $reserva): ?>
                            <div class="col-12">
                                <div class="card card-hover border-warning">
                                    <div class="card-header bg-warning bg-opacity-10 d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge bg-warning">
                                                <i class="bi bi-clock"></i> Pendiente
                                            </span>
                                            <small class="text-muted ms-2">
                                                Solicitado: <?= date('d/m/Y H:i', strtotime($reserva['created_at'])) ?>
                                            </small>
                                        </div>
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-success" 
                                                    onclick="aprobarReserva(<?= $reserva['id'] ?>)">
                                                <i class="bi bi-check-lg"></i> Aprobar
                                            </button>
                                            <button class="btn btn-sm btn-danger" 
                                                    onclick="rechazarReserva(<?= $reserva['id'] ?>)">
                                                <i class="bi bi-x-lg"></i> Rechazar
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary" 
                                                    onclick="verDetallesReserva(<?= $reserva['id'] ?>)">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <h6><?= htmlspecialchars($reserva['amenity_nombre']) ?></h6>
                                                <small class="text-muted"><?= ucfirst($reserva['amenity_tipo']) ?></small>
                                            </div>
                                            <div class="col-md-2">
                                                <strong><?= date('d/m/Y', strtotime($reserva['fecha_reserva'])) ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    <?= substr($reserva['hora_inicio'], 0, 5) ?> - <?= substr($reserva['hora_fin'], 0, 5) ?>
                                                </small>
                                            </div>
                                            <div class="col-md-2">
                                                <i class="bi bi-building"></i> 
                                                <strong><?= htmlspecialchars($reserva['departamento_numero']) ?></strong>
                                                <br>
                                                <small class="text-muted">Depto.</small>
                                            </div>
                                            <div class="col-md-3">
                                                <i class="bi bi-person"></i> 
                                                <?= htmlspecialchars($reserva['usuario_nombre']) ?>
                                                <?php if ($reserva['usuario_telefono']): ?>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="bi bi-telephone"></i> <?= htmlspecialchars($reserva['usuario_telefono']) ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-2">
                                                <small class="text-muted">Motivo:</small>
                                                <br>
                                                <span title="<?= htmlspecialchars($reserva['motivo']) ?>">
                                                    <?= htmlspecialchars(substr($reserva['motivo'], 0, 30)) ?><?= strlen($reserva['motivo']) > 30 ? '...' : '' ?>
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <!-- Información adicional -->
                                        <div class="row mt-3">
                                            <div class="col-12">
                                                <div class="d-flex flex-wrap gap-3">
                                                    <small>
                                                        <i class="bi bi-people"></i> 
                                                        <?= $reserva['numero_asistentes'] ?> asistentes
                                                    </small>
                                                    <small>
                                                        <i class="bi bi-cash-coin"></i> 
                                                        $<?= number_format($reserva['costo_total'], 0) ?>
                                                    </small>
                                                    <small>
                                                        <i class="bi bi-clock"></i> 
                                                        <?= $this->calcularDuracion($reserva['hora_inicio'], $reserva['hora_fin']) ?> minutos
                                                    </small>
                                                    <small class="text-warning">
                                                        <i class="bi bi-exclamation-triangle"></i> 
                                                        <?= $this->tiempoEspera($reserva['created_at']) ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Verificación de disponibilidad -->
                                        <div class="row mt-2">
                                            <div class="col-12">
                                                <?php 
                                                $disponibilidad = $this->verificarDisponibilidadCompleta(
                                                    $reserva['amenity_id'],
                                                    $reserva['fecha_reserva'],
                                                    $reserva['hora_inicio'],
                                                    $reserva['hora_fin'],
                                                    $reserva['id']
                                                );
                                                ?>
                                                <div class="alert alert-<?= $disponibilidad['disponible'] ? 'success' : 'danger' ?> py-2 mb-0">
                                                    <small>
                                                        <i class="bi bi-<?= $disponibilidad['disponible'] ? 'check-circle' : 'x-circle' ?>"></i>
                                                        <?= $disponibilidad['mensaje'] ?>
                                                        <?php if (!$disponibilidad['disponible']): ?>
                                                            <button class="btn btn-sm btn-outline-danger ms-2" 
                                                                    onclick="rechazarReserva(<?= $reserva['id'] ?>, 'conflicto')">
                                                                Rechazar por conflicto
                                                            </button>
                                                        <?php endif; ?>
                                                    </small>
                                                </div>
                                            </div>
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

<!-- Modal para rechazar con motivo -->
<div class="modal fade" id="modalRechazar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rechazar Reserva</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="reservaRechazarId">
                <div class="mb-3">
                    <label class="form-label">Motivo del rechazo</label>
                    <textarea class="form-control" id="motivoRechazo" rows="3" 
                              placeholder="Explica brevemente el motivo del rechazo..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="confirmarRechazo()">Rechazar Reserva</button>
            </div>
        </div>
    </div>
</div>

<script>
function cambiarEdificio(edificioId) {
    if (edificioId) {
        window.location.href = '<?= $url->to('reservas/aprobaciones') ?>?edificio_id=' + edificioId;
    } else {
        window.location.href = '<?= $url->to('reservas/aprobaciones') ?>';
    }
}

function aprobarReserva(reservaId) {
    if (confirm('¿Estás seguro de que deseas aprobar esta reserva?')) {
        fetch(`<?= $url->to('reservas/aprobar/') ?>${reservaId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al aprobar la reserva');
        });
    }
}

function rechazarReserva(reservaId, tipo = 'normal') {
    if (tipo === 'conflicto') {
        // Rechazo automático por conflicto
        if (confirm('¿Rechazar esta reserva debido al conflicto de horario?')) {
            realizarRechazo(reservaId, 'Conflicto de horario con otra reserva');
        }
    } else {
        // Rechazo con motivo personalizado
        document.getElementById('reservaRechazarId').value = reservaId;
        new bootstrap.Modal(document.getElementById('modalRechazar')).show();
    }
}

function confirmarRechazo() {
    const reservaId = document.getElementById('reservaRechazarId').value;
    const motivo = document.getElementById('motivoRechazo').value;
    
    if (!motivo.trim()) {
        alert('Por favor ingresa un motivo para el rechazo');
        return;
    }

    realizarRechazo(reservaId, motivo);
    bootstrap.Modal.getInstance(document.getElementById('modalRechazar')).hide();
}

function realizarRechazo(reservaId, motivo) {
    fetch(`<?= $url->to('reservas/rechazar/') ?>${reservaId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ motivo: motivo })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al rechazar la reserva');
    });
}

function aprobarTodas() {
    if (confirm('¿Estás seguro de que deseas aprobar TODAS las reservas pendientes?')) {
        const reservasIds = <?= json_encode(array_column($reservas_pendientes, 'id')) ?>;
        
        Promise.all(
            reservasIds.map(id => 
                fetch(`<?= $url->to('reservas/aprobar/') ?>${id}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' }
                }).then(r => r.json())
            )
        ).then(results => {
            const exitosas = results.filter(r => r.success).length;
            alert(`Se aprobaron ${exitosas} de ${reservasIds.length} reservas`);
            location.reload();
        });
    }
}

function rechazarTodas() {
    if (confirm('¿Estás seguro de que deseas rechazar TODAS las reservas pendientes?')) {
        const motivo = prompt('Ingresa el motivo general para el rechazo:');
        if (motivo !== null) {
            const reservasIds = <?= json_encode(array_column($reservas_pendientes, 'id')) ?>;
            
            Promise.all(
                reservasIds.map(id => 
                    fetch(`<?= $url->to('reservas/rechazar/') ?>${id}`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ motivo: motivo })
                    }).then(r => r.json())
                )
            ).then(results => {
                const exitosas = results.filter(r => r.success).length;
                alert(`Se rechazaron ${exitosas} de ${reservasIds.length} reservas`);
                location.reload();
            });
        }
    }
}

function verDetallesReserva(reservaId) {
    // Similar a la función en mis-reservas, cargar detalles via AJAX
    alert('Detalles de reserva ' + reservaId + ' (implementar modal de detalles)');
}

// Auto-refresh cada 30 segundos si hay reservas pendientes
<?php if (!empty($reservas_pendientes)): ?>
setTimeout(() => {
    window.location.reload();
}, 30000);
<?php endif; ?>
</script>

<style>
.card-hover:hover {
    transform: translateY(-2px);
    transition: transform 0.2s ease-in-out;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.bg-opacity-10 {
    background-color: rgba(var(--bs-warning-rgb), 0.1) !important;
}

.btn-group .btn {
    margin: 0 2px;
}
</style>