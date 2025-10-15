<?php
// 📁 views/amenities/gestionar.php
?>
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Gestión de Amenities</h1>
                    <p class="text-muted mb-0">Administra los espacios comunes del edificio</p>
                </div>
                <div class="btn-group">
                    <a href="<?= $url->to('amenities/crear') ?>" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Nuevo Amenity
                    </a>
                    <a href="<?= $url->to('amenities/configuracion') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-gear"></i> Configuración
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
                            <select name="edificio_id" class="form-select" onchange="this.form.submit()">
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
                            <label class="form-label">Tipo</label>
                            <select name="tipo" class="form-select" onchange="this.form.submit()">
                                <option value="">Todos los tipos</option>
                                <?php foreach ($tipos_amenities as $key => $nombre): ?>
                                    <option value="<?= $key ?>" <?= (isset($_GET['tipo']) && $_GET['tipo'] == $key) ? 'selected' : '' ?>>
                                        <?= $nombre ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-outline-primary w-100">
                                <i class="bi bi-funnel"></i> Filtrar
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
                    <small class="text-muted">Total Amenities</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-hover border-start border-success border-4">
                <div class="card-body text-center">
                    <h3 class="text-success"><?= array_sum(array_column($amenities, 'reservas_activas')) ?></h3>
                    <small class="text-muted">Reservas Activas</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-hover border-start border-info border-4">
                <div class="card-body text-center">
                    <h3 class="text-info"><?= array_sum(array_column($amenities, 'total_reservas')) ?></h3>
                    <small class="text-muted">Total Reservas</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-hover border-start border-warning border-4">
                <div class="card-body text-center">
                    <h3 class="text-warning">
                        <?= count(array_filter($amenities, function($a) { return $a['requiere_aprobacion'] == 1; })) ?>
                    </h3>
                    <small class="text-muted">Requieren Aprobación</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-hover border-start border-danger border-4">
                <div class="card-body text-center">
                    <h3 class="text-danger">
                        <?= count(array_filter($amenities, function($a) { return $a['costo_uso'] > 0; })) ?>
                    </h3>
                    <small class="text-muted">Con Costo</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-hover border-start border-secondary border-4">
                <div class="card-body text-center">
                    <h3 class="text-secondary">
                        <?= count(array_filter($amenities, function($a) { return $a['capacidad'] > 0; })) ?>
                    </h3>
                    <small class="text-muted">Con Capacidad</small>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Lista de Amenities -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">
                        <?= $edificio_actual ? 'Amenities - ' . htmlspecialchars($edificio_actual['nombre']) : 'Selecciona un edificio' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($amenities) && $edificio_actual): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-building display-1 text-muted"></i>
                            <h4 class="text-muted mt-3">No hay amenities registrados</h4>
                            <p class="text-muted">Comienza agregando el primer amenity a este edificio</p>
                            <a href="<?= $url->to('amenities/crear') ?>" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Crear Primer Amenity
                            </a>
                        </div>
                    <?php elseif (!$edificio_actual): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-building display-1 text-muted"></i>
                            <h4 class="text-muted mt-3">Selecciona un edificio</h4>
                            <p class="text-muted">Elige un edificio para gestionar sus amenities</p>
                        </div>
                    <?php else: ?>
                        <div class="row g-4">
                            <?php foreach ($amenities as $amenity): ?>
                            <div class="col-xl-4 col-md-6">
                                <div class="card card-hover h-100">
                                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                                        <span class="badge bg-<?= $this->getAmenityBadgeColor($amenity['tipo']) ?>">
                                            <?= $tipos_amenities[$amenity['tipo']] ?>
                                        </span>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                    type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item" 
                                                       href="<?= $url->to('amenities/editar/' . $amenity['id']) ?>">
                                                        <i class="bi bi-pencil"></i> Editar
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" 
                                                       href="<?= $url->to('amenities/imagenes/' . $amenity['id']) ?>">
                                                        <i class="bi bi-images"></i> Imágenes
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <button class="dropdown-item text-danger" 
                                                            onclick="confirmarDesactivar(<?= $amenity['id'] ?>)">
                                                        <i class="bi bi-trash"></i> Desactivar
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <h6 class="card-title"><?= htmlspecialchars($amenity['nombre']) ?></h6>
                                        <p class="card-text text-muted small">
                                            <?= $amenity['descripcion'] ? htmlspecialchars(substr($amenity['descripcion'], 0, 100)) . '...' : 'Sin descripción' ?>
                                        </p>
                                        
                                        <div class="amenity-info mt-3">
                                            <div class="row small text-muted">
                                                <div class="col-6">
                                                    <i class="bi bi-people"></i> 
                                                    Cap: <?= $amenity['capacidad'] ?: 'N/A' ?>
                                                </div>
                                                <div class="col-6">
                                                    <i class="bi bi-cash-coin"></i> 
                                                    $<?= number_format($amenity['costo_uso'], 0) ?>
                                                </div>
                                                <div class="col-6">
                                                    <i class="bi bi-clock"></i> 
                                                    <?= $amenity['horario_apertura'] ? substr($amenity['horario_apertura'], 0, 5) : 'N/A' ?>
                                                </div>
                                                <div class="col-6">
                                                    <i class="bi bi-calendar-check"></i> 
                                                    <?= $amenity['reservas_activas'] ?> activas
                                                </div>
                                            </div>
                                        </div>

                                        <div class="amenity-config mt-3">
                                            <div class="d-flex flex-wrap gap-1">
                                                <?php if ($amenity['requiere_aprobacion']): ?>
                                                    <span class="badge bg-warning">
                                                        <i class="bi bi-shield-check"></i> Requiere aprobación
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-lightning"></i> Auto-aprobación
                                                    </span>
                                                <?php endif; ?>
                                                
                                                <?php if ($amenity['max_reservas_semana'] > 0): ?>
                                                    <span class="badge bg-info">
                                                        <i class="bi bi-calendar-week"></i> 
                                                        Máx <?= $amenity['max_reservas_semana'] ?>/semana
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-transparent">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <?= $amenity['total_reservas'] ?> reservas totales
                                            </small>
                                            <a href="<?= $url->to('reservas?amenity_id=' . $amenity['id']) ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                Ver Reservas
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
function confirmarDesactivar(amenityId) {
    if (confirm('¿Estás seguro de que deseas desactivar este amenity? No se podrá realizar nuevas reservas.')) {
        fetch('<?= $url->to('amenities/desactivar/') ?>' + amenityId, {
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
            alert('Error al desactivar el amenity');
        });
    }
}

// Color badges por tipo de amenity
function getAmenityBadgeColor(tipo) {
    const colores = {
        'gimnasio': 'primary',
        'piscina': 'info', 
        'quincho': 'warning',
        'sala_eventos': 'success',
        'lavanderia': 'secondary',
        'juegos_infantiles': 'danger',
        'terraza': 'dark',
        'otro': 'light'
    };
    return colores[tipo] || 'light';
}
</script>

<style>
.card-hover:hover {
    transform: translateY(-2px);
    transition: transform 0.2s ease-in-out;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.amenity-info .row {
    margin-bottom: -0.5rem;
}

.amenity-info .col-6 {
    margin-bottom: 0.5rem;
}
</style>