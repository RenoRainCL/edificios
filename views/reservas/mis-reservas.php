<?php
// 📁 views/reservas/mis-reservas.php
?>
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Mis Reservas</h1>
                    <p class="text-muted mb-0">Historial y gestión de tus reservas</p>
                </div>
                <div class="btn-group">
                    <a href="<?= $url->to('amenities/reservas/calendario') ?>" class="btn btn-outline-primary">
                        <i class="bi bi-calendar-week"></i> Calendario
                    </a>
                    <a href="<?= $url->to('amenities/gestionar') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-building"></i> Amenities
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas Personales -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-hover border-start border-primary border-4">
                <div class="card-body text-center">
                    <h3 class="text-primary"><?= $estadisticas['total_reservas'] ?></h3>
                    <small class="text-muted">Total Reservas</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-hover border-start border-success border-4">
                <div class="card-body text-center">
                    <h3 class="text-success"><?= $estadisticas['confirmadas'] ?></h3>
                    <small class="text-muted">Confirmadas</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-hover border-start border-warning border-4">
                <div class="card-body text-center">
                    <h3 class="text-warning"><?= $estadisticas['pendientes'] ?></h3>
                    <small class="text-muted">Pendientes</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-hover border-start border-info border-4">
                <div class="card-body text-center">
                    <h3 class="text-info"><?= $estadisticas['canceladas'] ?></h3>
                    <small class="text-muted">Canceladas</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-hover border-start border-danger border-4">
                <div class="card-body text-center">
                    <h3 class="text-danger">$<?= number_format($estadisticas['total_gastado'], 0) ?></h3>
                    <small class="text-muted">Total Gastado</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-hover border-start border-secondary border-4">
                <div class="card-body text-center">
                    <h3 class="text-secondary"><?= count($departamentos_usuario) ?></h3>
                    <small class="text-muted">Mis Deptos.</small>
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
                        <div class="col-md-3">
                            <label class="form-label">Estado</label>
                            <select class="form-select" id="filtroEstado">
                                <option value="">Todos los estados</option>
                                <option value="confirmada">Confirmadas</option>
                                <option value="pendiente">Pendientes</option>
                                <option value="cancelada">Canceladas</option>
                                <option value="rechazada">Rechazadas</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha Desde</label>
                            <input type="date" class="form-control" id="filtroFechaDesde">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha Hasta</label>
                            <input type="date" class="form-control" id="filtroFechaHasta">
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary w-100" onclick="aplicarFiltros()">
                                <i class="bi bi-funnel"></i> Filtrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Reservas -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Historial de Reservas</h5>
                    <small class="text-muted">Mostrando <?= count($reservas) ?> reservas</small>
                </div>
                <div class="card-body">
                    <?php if (empty($reservas)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-calendar-x display-1 text-muted"></i>
                            <h4 class="text-muted mt-3">No hay reservas</h4>
                            <p class="text-muted">Comienza reservando un amenity disponible</p>
                            <a href="<?= $url->to('reservas/calendario') ?>" class="btn btn-primary">
                                <i class="bi bi-calendar-week"></i> Ver Calendario
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover" id="tablaReservas">
                                <thead>
                                    <tr>
                                        <th>Amenity</th>
                                        <th>Fecha</th>
                                        <th>Horario</th>
                                        <th>Departamento</th>
                                        <th>Estado</th>
                                        <th>Costo</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reservas as $reserva): ?>
                                    <tr class="<?= $reserva['estado'] == 'pendiente' ? 'table-warning' : ($reserva['estado'] == 'confirmada' ? 'table-success' : 'table-secondary') ?>">
                                        <td>
                                            <strong><?= htmlspecialchars($reserva['amenity_nombre']) ?></strong>
                                            <br>
                                            <small class="text-muted"><?= htmlspecialchars($reserva['edificio_nombre']) ?></small>
                                        </td>
                                        <td>
                                            <?= date('d/m/Y', strtotime($reserva['fecha_reserva'])) ?>
                                            <br>
                                            <small class="text-muted">
                                                <?= $this->diasDesdeHoy($reserva['fecha_reserva']) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <?= substr($reserva['hora_inicio'], 0, 5) ?> - <?= substr($reserva['hora_fin'], 0, 5) ?>
                                            <br>
                                            <small class="text-muted">
                                                <?= $this->calcularDuracion($reserva['hora_inicio'], $reserva['hora_fin']) ?> min
                                            </small>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($reserva['departamento_numero']) ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $this->getEstadoBadgeColor($reserva['estado']) ?>">
                                                <?= ucfirst($reserva['estado']) ?>
                                            </span>
                                            <?php if ($reserva['estado'] == 'pendiente'): ?>
                                                <br>
                                                <small class="text-muted">Esperando aprobación</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            $<?= number_format($reserva['costo_total'], 0) ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" 
                                                        onclick="verDetalles(<?= $reserva['id'] ?>)"
                                                        title="Ver detalles">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                
                                                <?php if ($reserva['estado'] == 'pendiente' && $this->puedeCancelarReserva($reserva)): ?>
                                                    <button class="btn btn-outline-warning" 
                                                            onclick="cancelarReserva(<?= $reserva['id'] ?>)"
                                                            title="Cancelar reserva">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <?php if ($reserva['estado'] == 'confirmada' && $this->puedeCancelarReserva($reserva)): ?>
                                                    <button class="btn btn-outline-danger" 
                                                            onclick="cancelarReserva(<?= $reserva['id'] ?>)"
                                                            title="Cancelar reserva">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <nav aria-label="Paginación de reservas">
                            <ul class="pagination justify-content-center">
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" tabindex="-1">Anterior</a>
                                </li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                <li class="page-item">
                                    <a class="page-link" href="#">Siguiente</a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Detalles -->
<div class="modal fade" id="modalDetalles" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles de Reserva</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detallesReserva">
                <!-- Los detalles se cargan via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
function aplicarFiltros() {
    const estado = document.getElementById('filtroEstado').value;
    const fechaDesde = document.getElementById('filtroFechaDesde').value;
    const fechaHasta = document.getElementById('filtroFechaHasta').value;
    
    // Aquí iría la lógica para filtrar la tabla
    // Por ahora solo mostramos un mensaje
    alert('Filtros aplicados: Estado=' + estado + ', Desde=' + fechaDesde + ', Hasta=' + fechaHasta);
}

function verDetalles(reservaId) {
    fetch(`<?= $url->to('reservas/detalles/') ?>${reservaId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('detallesReserva').innerHTML = data.html;
                new bootstrap.Modal(document.getElementById('modalDetalles')).show();
            } else {
                alert('Error al cargar los detalles: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los detalles');
        });
}

function cancelarReserva(reservaId) {
    if (confirm('¿Estás seguro de que deseas cancelar esta reserva?')) {
        fetch(`<?= $url->to('reservas/cancelar/') ?>${reservaId}`, {
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
            alert('Error al cancelar la reserva');
        });
    }
}

// Inicializar filtros con valores por defecto
document.addEventListener('DOMContentLoaded', function() {
    // Establecer fecha hasta como hoy
    document.getElementById('filtroFechaHasta').value = new Date().toISOString().split('T')[0];
    
    // Establecer fecha desde como hace 30 días
    const fechaDesde = new Date();
    fechaDesde.setDate(fechaDesde.getDate() - 30);
    document.getElementById('filtroFechaDesde').value = fechaDesde.toISOString().split('T')[0];
});
</script>

<style>
.table-hover tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.075);
}

.btn-group-sm > .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
</style>