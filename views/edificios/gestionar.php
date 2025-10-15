<?php
// 📁 views/edificios/gestionar.php
?>
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 text-gray-800">Gestionar Edificio</h1>
            <p class="text-muted mb-0"><?= htmlspecialchars($edificio['nombre']) ?> - <?= htmlspecialchars($edificio['direccion']) ?></p>
        </div>
        <div>
            <a href="<?= $url->to('edificios/editar/') ?><?= $edificio['id'] ?>" class="btn btn-outline-primary me-2">
                <i class="bi bi-pencil"></i> Editar
            </a>
            <a href="<?= $url->to('edificios') ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
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

    <!-- Tarjetas de Estadísticas -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-hover border-start border-primary border-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                Departamentos
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                <?= $estadisticas['total_departamentos'] ?? 0 ?>
                            </div>
                            <div class="text-xs text-muted">
                                <?= $estadisticas['deptos_habitados'] ?? 0 ?> habitados
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-house-door fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-hover border-start border-warning border-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">
                                Mantenimientos
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                <?= $estadisticas['mantenimientos_pendientes'] ?? 0 ?>
                            </div>
                            <div class="text-xs text-muted">
                                Pendientes
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-tools fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-hover border-start border-danger border-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-danger text-uppercase mb-1">
                                Gastos Pendientes
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                <?= $estadisticas['gastos_pendientes'] ?? 0 ?>
                            </div>
                            <div class="text-xs text-muted">
                                Por procesar
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-receipt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-hover border-start border-success border-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">
                                Pisos
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                <?= $edificio['total_pisos'] ?>
                            </div>
                            <div class="text-xs text-muted">
                                Total
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-building fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Información del Edificio -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-info-circle me-2"></i>Información del Edificio
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <strong>Nombre:</strong><br>
                            <?= htmlspecialchars($edificio['nombre']) ?>
                        </div>
                        <div class="col-sm-6">
                            <strong>Dirección:</strong><br>
                            <?= htmlspecialchars($edificio['direccion']) ?>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-6">
                            <strong>Comuna:</strong><br>
                            <span class="badge bg-secondary"><?= htmlspecialchars($edificio['comuna']) ?></span>
                        </div>
                        <div class="col-sm-6">
                            <strong>Región:</strong><br>
                            <?= htmlspecialchars($edificio['region']) ?>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-6">
                            <strong>Departamentos:</strong><br>
                            <span class="badge bg-info"><?= $edificio['total_departamentos'] ?> total</span>
                        </div>
                        <div class="col-sm-6">
                            <strong>Pisos:</strong><br>
                            <span class="badge bg-warning"><?= $edificio['total_pisos'] ?> pisos</span>
                        </div>
                    </div>
                    <?php if ($edificio['fecha_construccion']): ?>
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <strong>Fecha Construcción:</strong><br>
                            <?= date('d/m/Y', strtotime($edificio['fecha_construccion'])) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Administrador -->
            <?php if ($edificio['rut_administrador'] || $edificio['email_administrador']): ?>
            <div class="card shadow mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-person-badge me-2"></i>Administrador
                    </h6>
                </div>
                <div class="card-body">
                    <?php if ($edificio['rut_administrador']): ?>
                    <div class="mb-2">
                        <strong>RUT:</strong> <?= htmlspecialchars($edificio['rut_administrador']) ?>
                    </div>
                    <?php endif; ?>
                    <?php if ($edificio['email_administrador']): ?>
                    <div class="mb-2">
                        <strong>Email:</strong> <?= htmlspecialchars($edificio['email_administrador']) ?>
                    </div>
                    <?php endif; ?>
                    <?php if ($edificio['telefono_administrador']): ?>
                    <div class="mb-2">
                        <strong>Teléfono:</strong> <?= htmlspecialchars($edificio['telefono_administrador']) ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Lista de Departamentos -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-house-door me-2"></i>Departamentos
                    </h6>
                    <div>
                        <span class="badge bg-primary me-2"><?= count($departamentos) ?></span>
                        <a href="<?= $url->to('departamentos?edificio_id=' . $edificio['id']) ?>" 
                           class="btn btn-primary btn-sm">
                            <i class="bi bi-gear"></i> Gestionar
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($departamentos)): ?>
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-house display-4 d-block mb-2"></i>
                            No hay departamentos registrados
                            <br>
                            <a href="<?= $url->to('departamentos/crear?edificio_id=' . $edificio['id']) ?>" 
                               class="btn btn-primary btn-sm mt-2">
                                <i class="bi bi-plus-circle"></i> Crear Primer Departamento
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Número</th>
                                        <th>Piso</th>
                                        <th>Propietario</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($departamentos as $depto): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($depto['numero']) ?></strong>
                                            <?php if ($depto['metros_cuadrados']): ?>
                                                <br>
                                                <small class="text-muted"><?= $depto['metros_cuadrados'] ?> m²</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">Piso <?= $depto['piso'] ?></span>
                                        </td>
                                        <td>
                                            <?php if ($depto['propietario_nombre']): ?>
                                                <?= htmlspecialchars($depto['propietario_nombre']) ?>
                                            <?php else: ?>
                                                <span class="text-muted">No asignado</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $depto['is_habitado'] ? 'success' : 'secondary' ?>">
                                                <?= $depto['is_habitado'] ? 'Habitado' : 'Vacío' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= $url->to('departamentos/ver/' . $depto['id']) ?>" 
                                                   class="btn btn-outline-info" 
                                                   title="Ver detalles">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="<?= $url->to('departamentos/editar/' . $depto['id']) ?>" 
                                                   class="btn btn-outline-primary" 
                                                   title="Editar departamento">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Enlace para ver todos los departamentos -->
                        <div class="text-center mt-3">
                            <a href="<?= $url->to('departamentos?edificio_id=' . $edificio['id']) ?>" 
                               class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-list-ul"></i> Ver Todos los Departamentos
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="card shadow mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-lightning me-2"></i>Acciones Rápidas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <a href="<?= $url->to('finanzas/gastos-comunes') ?>?edificio_id=<?= $edificio['id'] ?>" 
                               class="btn btn-outline-primary w-100 text-start">
                                <i class="bi bi-receipt me-2"></i>Gastos Comunes
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?= $url->to('mantenimiento') ?>?edificio_id=<?= $edificio['id'] ?>" 
                               class="btn btn-outline-warning w-100 text-start">
                                <i class="bi bi-tools me-2"></i>Mantenimiento
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?= $url->to('amenities/reservas/calendario') ?>?edificio_id=<?= $edificio['id'] ?>" 
                               class="btn btn-outline-success w-100 text-start">
                                <i class="bi bi-calendar-check me-2"></i>Reservas
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?= $url->to('comunicaciones') ?>?edificio_id=<?= $edificio['id'] ?>" 
                               class="btn btn-outline-info w-100 text-start">
                                <i class="bi bi-megaphone me-2"></i>Comunicaciones
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>