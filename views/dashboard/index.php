<?php
// 📁 views/dashboard/index.php - VERSIÓN CON MÓDULO AMENITIES
?>
<div class="container-fluid">
    <!-- Bienvenida -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="card-title mb-1">Bienvenido, <?= htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) ?>!</h2>
                            <p class="card-text mb-0">
                                <i class="bi bi-calendar-check"></i> 
                                Hoy es <?= date('d/m/Y') ?> - 
                                <?= $stats['total_edificios'] ?> edificio(s) asignado(s)
                                <?php if ($puede_gestionar_usuarios): ?>
                                 - <?= $total_usuarios ?> usuario(s) activo(s)
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="btn-group">
                                <a href="<?= $url->to('edificios') ?>" class="btn btn-light">
                                    <i class="bi bi-building"></i> Ver Edificios
                                </a>
                                <?php if ($puede_gestionar_usuarios): ?>
                                <a href="<?= $url->to('usuarios') ?>" class="btn btn-outline-light">
                                    <i class="bi bi-people"></i> Usuarios
                                </a>
                                <?php endif; ?>
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
                <i class="bi bi-<?= $flash['type'] == 'success' ? 'check-circle' : 'info-circle' ?> me-2"></i>
                <?= htmlspecialchars($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Tarjetas de Estadísticas PRINCIPALES -->
    <div class="row">
        <!-- Tarjeta de Usuarios (Solo para administradores) -->
        <?php if ($puede_gestionar_usuarios): ?>
        <div class="col-xl-2 col-md-4 col-6 mb-4">
            <div class="card card-hover border-start border-info border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?= $stats['total_usuarios'] ?></h4>
                            <small class="text-muted">Usuarios</small>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-people text-info fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="col-xl-2 col-md-4 col-6 mb-4">
            <div class="card card-hover border-start border-primary border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?= $stats['total_edificios'] ?></h4>
                            <small class="text-muted">Edificios</small>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-building text-primary fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-6 mb-4">
            <div class="card card-hover border-start border-success border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?= $stats['total_departamentos'] ?></h4>
                            <small class="text-muted">Departamentos</small>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-house-door text-success fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-6 mb-4">
            <div class="card card-hover border-start border-warning border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?= $stats['mantenimientos_pendientes'] ?></h4>
                            <small class="text-muted">Mantenimientos</small>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-tools text-warning fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-6 mb-4">
            <div class="card card-hover border-start border-danger border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?= $stats['reservas_hoy'] ?></h4>
                            <small class="text-muted">Reservas Hoy</small>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-calendar-check text-danger fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-6 mb-4">
            <div class="card card-hover border-start border-secondary border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?= count($notificaciones_importantes) ?></h4>
                            <small class="text-muted">Notificaciones</small>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-bell text-secondary fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- NUEVA SECCIÓN: ESPACIOS COMUNES (AMENITIES) -->
    <?php if ($puede_gestionar_amenities): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-building-gear me-2"></i>Espacios Comunes
                    </h5>
                    <div class="btn-group">
                        <a href="<?= $url->to('amenities/gestionar') ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-list"></i> Ver Todos
                        </a>
                        <a href="<?= $url->to('amenities/crear') ?>" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-circle"></i> Nuevo Amenity
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Tarjetas de Estadísticas de Amenities -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card card-hover border-start border-primary border-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0"><?= $stats['amenities_activos'] ?></h4>
                                            <small class="text-muted">Amenities Activos</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="bi bi-building text-primary fs-3"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card card-hover border-start border-warning border-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0"><?= $stats['amenities_requieren_aprobacion'] ?></h4>
                                            <small class="text-muted">Requieren Aprobación</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="bi bi-shield-check text-warning fs-3"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card card-hover border-start border-danger border-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0"><?= $stats['amenities_con_conflictos'] ?></h4>
                                            <small class="text-muted">Con Conflictos</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="bi bi-exclamation-triangle text-danger fs-3"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card card-hover border-start border-success border-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0"><?= $stats['reservas_pendientes'] ?></h4>
                                            <small class="text-muted">Reservas Pendientes</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="bi bi-clock-history text-success fs-3"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Reservas Stats -->
                    <div class="row mb-4">
                        <!-- Fila 1: Estadísticas Principales -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Reservas Hoy
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= $estadisticas['reservas_hoy'] ?? 0 ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Pendientes Aprobación
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= $estadisticas['reservas_pendientes'] ?? 0 ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Amenities Activos
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= $estadisticas['amenities_activos'] ?? 0 ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-swimming-pool fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Mis Reservas Activas
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= $estadisticas['mis_reservas_activas'] ?? 0 ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-list fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Fila 2: Estadísticas Secundarias -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-secondary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                                Total Reservas Mes
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= $estadisticas['total_reservas_mes'] ?? 0 ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-chart-bar fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-danger shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                                Amenities con Conflictos
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= $estadisticas['amenities_con_conflictos'] ?? 0 ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-dark shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                                                Mantenimientos Urgentes
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= $estadisticas['mantenimientos_urgentes'] ?? 0 ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-tools fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Pagos del Mes
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= $estadisticas['pagos_mes'] ?? '0%' ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Contenido Expandido de Amenities -->
                    <div class="row">
                        <!-- Amenities que Requieren Atención -->
                        <div class="col-lg-6 mb-4">
                            <div class="card card-hover h-100">
                                <div class="card-header bg-transparent">
                                    <h6 class="card-title mb-0">Amenities que Requieren Atención</h6>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($amenities_requieren_atencion)): ?>
                                        <div class="list-group list-group-flush">
                                            <?php foreach ($amenities_requieren_atencion as $amenity): ?>
                                            <div class="list-group-item d-flex justify-content-between align-items-start">
                                                <div class="ms-2 me-auto">
                                                    <div class="fw-bold"><?= htmlspecialchars($amenity['nombre']) ?></div>
                                                    <small class="text-muted">
                                                        <?= htmlspecialchars($amenity['edificio_nombre']) ?> • 
                                                        <?= $amenity['tipo'] ?>
                                                    </small>
                                                    <br>
                                                    <small class="text-<?= $amenity['tipo_atencion'] == 'conflicto_horario' ? 'danger' : 'warning' ?>">
                                                        <i class="bi bi-<?= $amenity['tipo_atencion'] == 'conflicto_horario' ? 'exclamation-triangle' : 'shield-check' ?>"></i>
                                                        <?= htmlspecialchars($amenity['motivo']) ?>
                                                    </small>
                                                </div>
                                                <span class="badge bg-<?= $amenity['tipo_atencion'] == 'conflicto_horario' ? 'danger' : 'warning' ?>">
                                                    <?= $amenity['tipo_atencion'] == 'conflicto_horario' ? 'Conflicto' : 'Aprobación' ?>
                                                </span>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center p-3">
                                            <i class="bi bi-check-circle text-success fs-4"></i>
                                            <p class="text-muted mb-0">No hay amenities que requieran atención inmediata</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Estadísticas de Uso por Tipo -->
                        <div class="col-lg-6 mb-4">
                            <div class="card card-hover h-100">
                                <div class="card-header bg-transparent">
                                    <h6 class="card-title mb-0">Uso por Tipo de Amenity</h6>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($estadisticas_uso_amenities)): ?>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Tipo</th>
                                                        <th class="text-center">Cantidad</th>
                                                        <th class="text-center">Reservas</th>
                                                        <th class="text-center">Promedio</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($estadisticas_uso_amenities as $estadistica): ?>
                                                    <tr>
                                                        <td>
                                                            <span class="badge bg-light text-dark">
                                                                <?= htmlspecialchars($estadistica['tipo']) ?>
                                                            </span>
                                                        </td>
                                                        <td class="text-center"><?= $estadistica['total_amenities'] ?></td>
                                                        <td class="text-center"><?= $estadistica['total_reservas'] ?></td>
                                                        <td class="text-center">
                                                            <span class="badge bg-<?= $estadistica['promedio_uso'] > 5 ? 'success' : ($estadistica['promedio_uso'] > 2 ? 'warning' : 'secondary') ?>">
                                                                <?= $estadistica['promedio_uso'] ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center p-3">
                                            <i class="bi bi-bar-chart text-muted fs-4"></i>
                                            <p class="text-muted mb-0">No hay datos de uso disponibles</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Amenities Más Populares -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card card-hover">
                                <div class="card-header bg-transparent">
                                    <h6 class="card-title mb-0">Amenities Más Populares</h6>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($amenities_populares)): ?>
                                        <div class="row g-3">
                                            <?php foreach ($amenities_populares as $amenity): ?>
                                            <div class="col-md-4">
                                                <div class="d-flex align-items-center p-2 border rounded">
                                                    <div class="flex-shrink-0">
                                                        <i class="bi bi-<?= 
                                                            $amenity['tipo'] == 'piscina' ? 'droplet' : 
                                                            ($amenity['tipo'] == 'gimnasio' ? 'activity' : 
                                                            ($amenity['tipo'] == 'quincho' ? 'fire' : 'building'))
                                                        ?> fs-4 text-primary"></i>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <h6 class="mb-0"><?= htmlspecialchars($amenity['nombre']) ?></h6>
                                                        <small class="text-muted">
                                                            <?= $amenity['total_reservas'] ?> reservas
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center p-3">
                                            <i class="bi bi-building text-muted fs-4"></i>
                                            <p class="text-muted mb-0">No hay amenities populares</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <!-- NUEVA SECCIÓN: PRORRATEO DE GASTOS -->
    <?php if ($puede_gestionar_prorrateo): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-calculator me-2"></i>Prorrateo de Gastos Comunes
                    </h5>
                    <div class="btn-group">
                        <a href="<?= $url->to('finanzas/prorrateo') ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-list"></i> Ver Todos
                        </a>
                        <a href="<?= $url->to('configuracion/prorrateo') ?>" class="btn btn-sm btn-primary">
                            <i class="bi bi-gear"></i> Configurar
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Estadísticas de Prorrateo -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card card-hover border-start border-warning border-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0"><?= $stats['prorrateos_pendientes'] ?? 0 ?></h4>
                                            <small class="text-muted">Pendientes Aprobación</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="bi bi-clock text-warning fs-3"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card card-hover border-start border-info border-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0"><?= $stats['gastos_sin_prorratear'] ?? 0 ?></h4>
                                            <small class="text-muted">Gastos Sin Prorratear</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="bi bi-receipt text-info fs-3"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card card-hover border-start border-success border-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0"><?= $stats['prorrateos_mes_actual'] ?? 0 ?></h4>
                                            <small class="text-muted">Este Mes</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="bi bi-check-circle text-success fs-3"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card card-hover border-start border-primary border-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0"><?= count($edificios) ?></h4>
                                            <small class="text-muted">Edificios Configurados</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="bi bi-building text-primary fs-3"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Prorrateos Recientes -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card card-hover">
                                <div class="card-header bg-transparent">
                                    <h6 class="card-title mb-0">Prorrateos Recientes</h6>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($prorrateos_recientes)): ?>
                                        <div class="list-group list-group-flush">
                                            <?php foreach ($prorrateos_recientes as $prorrateo): ?>
                                            <div class="list-group-item d-flex justify-content-between align-items-start">
                                                <div class="ms-2 me-auto">
                                                    <div class="fw-bold"><?= htmlspecialchars($prorrateo['edificio_nombre']) ?></div>
                                                    <small class="text-muted">
                                                        <?= htmlspecialchars($prorrateo['gasto_nombre']) ?> • 
                                                        <?= date('d/m/Y', strtotime($prorrateo['periodo'])) ?>
                                                    </small>
                                                    <br>
                                                    <small class="text-<?= 
                                                        $prorrateo['estado'] == 'pendiente_aprobacion' ? 'warning' : 
                                                        ($prorrateo['estado'] == 'aprobado' ? 'success' : 'secondary')
                                                    ?>">
                                                        <i class="bi bi-<?= 
                                                            $prorrateo['estado'] == 'pendiente_aprobacion' ? 'clock' : 
                                                            ($prorrateo['estado'] == 'aprobado' ? 'check-circle' : 'file-text')
                                                        ?>"></i>
                                                        <?= ucfirst(str_replace('_', ' ', $prorrateo['estado'])) ?>
                                                    </small>
                                                </div>
                                                <div class="text-end">
                                                    <small class="d-block text-muted">$<?= number_format($prorrateo['monto_total'], 0, ',', '.') ?></small>
                                                    <?php if ($prorrateo['estado'] == 'pendiente_aprobacion'): ?>
                                                    <a href="<?= $url->to("finanzas/prorrateo/aprobar/{$prorrateo['id']}") ?>" 
                                                    class="btn btn-sm btn-outline-success mt-1">
                                                        <i class="bi bi-check-lg"></i> Revisar
                                                    </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center p-3">
                                            <i class="bi bi-calculator text-muted fs-4"></i>
                                            <p class="text-muted mb-0">No hay prorrateos recientes</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <!-- Contenido Principal (Existente) -->
    <div class="row">
        <!-- Actividades Recientes -->
        <div class="col-lg-8 mb-4">
            <div class="card card-hover">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold">Actividades Recientes</h6>
                    <a href="#" class="btn btn-sm btn-outline-primary">Ver Todas</a>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($actividades_recientes)): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($actividades_recientes as $actividad): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-start">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold">
                                    <i class="bi bi-<?= 
                                        $actividad['tipo'] == 'mantenimiento' ? 'tools' : 
                                        ($actividad['tipo'] == 'gasto' ? 'receipt' : 'calendar-check')
                                    ?> me-2 text-<?= 
                                        $actividad['tipo'] == 'mantenimiento' ? 'warning' : 
                                        ($actividad['tipo'] == 'gasto' ? 'info' : 'success')
                                    ?>"></i>
                                    <?= htmlspecialchars($actividad['titulo']) ?>
                                </div>
                                <small class="text-muted"><?= htmlspecialchars($actividad['descripcion']) ?></small>
                                <br>
                                <small class="text-muted">
                                    <i class="bi bi-building me-1"></i><?= htmlspecialchars($actividad['edificio_nombre']) ?>
                                    • 
                                    <i class="bi bi-clock me-1"></i><?= date('d/m H:i', strtotime($actividad['fecha'])) ?>
                                </small>
                            </div>
                            <span class="badge bg-<?= 
                                $actividad['estado'] == 'completado' ? 'success' : 
                                ($actividad['estado'] == 'pendiente' ? 'warning' : 'info')
                            ?> rounded-pill">
                                <?= ucfirst($actividad['estado']) ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center p-4">
                        <i class="bi bi-inbox text-muted fs-1"></i>
                        <p class="text-muted mt-2">No hay actividades recientes</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Notificaciones y Acciones Rápidas -->
        <div class="col-lg-4">
            <!-- Notificaciones -->
            <div class="card card-hover mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold">Notificaciones</h6>
                    <span class="badge bg-danger"><?= count($notificaciones_importantes) ?></span>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($notificaciones_importantes)): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($notificaciones_importantes as $notif): ?>
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <small class="text-<?= $notif['tipo'] ?> fw-bold">
                                    <?= htmlspecialchars($notif['titulo']) ?>
                                </small>
                            </div>
                            <small><?= htmlspecialchars($notif['mensaje']) ?></small>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center p-3">
                        <i class="bi bi-check-circle text-success fs-4"></i>
                        <p class="text-muted mb-0 small">No hay notificaciones pendientes</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="card card-hover">
                <div class="card-header bg-transparent">
                    <h6 class="m-0 fw-bold">Acciones Rápidas</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?php echo $url->to('edificios'); ?>" class="btn btn-outline-primary">
                            <i class="bi bi-building"></i> Gestionar Edificios
                        </a>
                        
                        <?php if ($puede_gestionar_usuarios): ?>
                        <a href="<?php echo $url->to('usuarios'); ?>" class="btn btn-outline-info">
                            <i class="bi bi-people"></i> Gestionar Usuarios
                        </a>
                        <?php endif; ?>

                        <?php if ($puede_gestionar_amenities): ?>
                        <a href="<?php echo $url->to('amenities/gestionar'); ?>" class="btn btn-outline-success">
                            <i class="bi bi-building-gear"></i> Espacios Comunes
                        </a>
                        <?php endif; ?>
                        
                        <a href="<?php echo $url->to('finanzas/gastos-comunes'); ?>" class="btn btn-outline-success">
                            <i class="bi bi-receipt"></i> Gastos Comunes
                        </a>
                        <a href="<?php echo $url->to('mantenimiento'); ?>" class="btn btn-outline-warning">
                            <i class="bi bi-tools"></i> Mantenimiento
                        </a>
                        <a href="<?php echo $url->to('amenities/reservas/calendario'); ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-calendar-check"></i> Reservas
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card-hover:hover {
    transform: translateY(-2px);
    transition: transform 0.2s ease-in-out;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.border-4 {
    border-width: 4px !important;
}
</style>