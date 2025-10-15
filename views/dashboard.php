<?php
// 📁 views/dashboard.php - VERSIÓN CORREGIDA

error_log("🚨 ARCHIVO EJECUTADO: " . __FILE__);
error_log("🚨 URL generada para gastos: " . $url->to('finanzas/gastos-comunes/crear'));
// DEBUG TEMPORAL EN VISTA
error_log("=== 🚨 DASHBOARD VIEW DEBUG ===");
error_log("📍 Variable url existe: " . (isset($url) ? 'SÍ' : 'NO'));
if (isset($url)) {
    error_log("📍 Test url->to('edificios'): " . $url->to('edificios'));
    error_log("📍 Test url->to('finanzas/gastos-comunes'): " . $url->to('finanzas/gastos-comunes'));
}
error_log("=== 🚨 FIN DASHBOARD DEBUG ===");

?>
<!-- Contenido del Dashboard -->
<div class="row">
    <!-- Tarjetas de resumen -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-hover border-start border-primary border-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                            Edificios Activos
                        </div>
                        <div class="h5 mb-0 fw-bold text-gray-800">
                            <?php echo count($edificios); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-building fa-2x text-gray-300"></i>
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
                            Pagos del Mes
                        </div>
                        <div class="h5 mb-0 fw-bold text-gray-800"><?php echo $stats['pagos_mes'] ?? '0%'; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-currency-dollar fa-2x text-gray-300"></i>
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
                        <div class="h5 mb-0 fw-bold text-gray-800"><?php echo $stats['mantenimientos'] ?? 0; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-tools fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-hover border-start border-info border-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="text-xs fw-bold text-info text-uppercase mb-1">
                            Reservas Hoy
                        </div>
                        <div class="h5 mb-0 fw-bold text-gray-800"><?php echo $stats['reservas_hoy'] ?? 0; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-calendar-check fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Acciones rápidas -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 fw-bold">Acciones Rápidas</h6>
            </div>
            <div class="card-body">
                <div class="btn-group" role="group">
                    <!-- ✅ RUTAS CORREGIDAS - USANDO RUTAS QUE SÍ EXISTEN -->
                    <a href="<?php echo $url->to('edificios'); ?>" class="btn btn-outline-primary">
                        <i class="bi bi-building"></i> Gestionar Edificios
                    </a>
                    <a href="<?php echo $url->to('finanzas/gastos-comunes/crear'); ?>" class="btn btn-outline-success">
                        <i class="bi bi-receipt"></i> Gastos Comunes
                    </a>
                    <a href="<?php echo $url->to('mantenimiento'); ?>" class="btn btn-outline-warning">
                        <i class="bi bi-tools"></i> Mantenimiento
                    </a>
                    <a href="<?php echo $url->to('amenities/reservas'); ?>" class="btn btn-outline-info">
                        <i class="bi bi-calendar-check"></i> Reservas
                    </a>
                    <!-- ✅ NUEVA ACCIÓN: Reportes Financieros (ruta existente) -->
                    <a href="<?php echo $url->to('finanzas/reportes'); ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-graph-up"></i> Reportes
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Gráficos y tablas -->
<div class="row">
    <div class="col-lg-8">
        <div class="card card-hover">
            <div class="card-header bg-transparent">
                <h6 class="m-0 fw-bold">Estado de Pagos por Edificio</h6>
            </div>
            <div class="card-body">
                <canvas id="pagosChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card card-hover">
            <div class="card-header bg-transparent">
                <h6 class="m-0 fw-bold">Actividad Reciente</h6>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <?php if (!empty($notifications)): ?>
                        <?php foreach (array_slice($notifications, 0, 5) as $notif) { ?>
                        <div class="list-group-item d-flex justify-content-between align-items-start">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold"><?php echo $this->safeHtml($notif['titulo']); ?></div>
                                <?php echo $this->safeHtml($notif['mensaje']); ?>
                            </div>
                            <span class="badge bg-<?php echo $this->safeHtml($notif['tipo']); ?> rounded-pill">
                                <i class="bi bi-clock"></i>
                            </span>
                        </div>
                        <?php } ?>
                    <?php else: ?>
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-inbox display-4"></i>
                            <p class="mt-2">No hay notificaciones recientes</p>
                        </div>
                    <?php endif; ?>
                </div>
                <!-- ✅ ENLACE CORREGIDO: Eliminar o redirigir a comunicación -->
                <div class="text-center mt-3">
                    <a href="<?php echo $url->to('comunicaciones'); ?>" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-megaphone"></i> Ver Comunicaciones
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sección de Mantenimientos Pendientes (OPCIONAL) -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-transparent">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold">Mantenimientos Pendientes</h6>
                    <a href="<?php echo $url->to('mantenimiento'); ?>" class="btn btn-sm btn-outline-primary">
                        Ver Todos
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($mantenimientos_pendientes)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Edificio</th>
                                    <th>Título</th>
                                    <th>Prioridad</th>
                                    <th>Estado</th>
                                    <th>Fecha Programada</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($mantenimientos_pendientes, 0, 5) as $mant): ?>
                                <tr>
                                    <td><?php echo $this->safeHtml($mant['edificio_nombre']); ?></td>
                                    <td><?php echo $this->safeHtml($mant['titulo']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $this->getPriorityBadge($mant['prioridad']); ?>">
                                            <?php echo ucfirst($mant['prioridad']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning"><?php echo ucfirst($mant['estado']); ?></span>
                                    </td>
                                    <td><?php echo $mant['fecha_programada'] ? date('d/m/Y', strtotime($mant['fecha_programada'])) : 'No programada'; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-3">
                        <i class="bi bi-check-circle display-4"></i>
                        <p class="mt-2">No hay mantenimientos pendientes</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>