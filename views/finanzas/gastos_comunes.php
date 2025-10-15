<?php
// 📁 views/finanzas/gastos_comunes.php
?>
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-receipt me-2"></i>Gastos Comunes
            </h1>
            <p class="text-muted">Gestión de gastos comunes por edificio</p>
        </div>
        <a href="<?= $url->to('finanzas/gastos-comunes/crear') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Nuevo Gasto
        </a>
    </div>

    <!-- Mensajes -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>
            <?= $_SESSION['success_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <?= $_SESSION['error_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="card card-hover mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small text-uppercase text-muted fw-bold">Edificio</label>
                    <select name="edificio_id" class="form-select">
                        <option value="">Todos los edificios</option>
                        <?php foreach ($edificios as $edificio): ?>
                            <option value="<?= $edificio['id'] ?>" 
                                <?= isset($_GET['edificio_id']) && $_GET['edificio_id'] == $edificio['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($edificio['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-uppercase text-muted fw-bold">Período</label>
                    <input type="month" name="periodo" class="form-control" 
                           value="<?= $_GET['periodo'] ?? date('Y-m') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-uppercase text-muted fw-bold">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">Todos los estados</option>
                        <option value="pendiente" <?= isset($_GET['estado']) && $_GET['estado'] == 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                        <option value="emitido" <?= isset($_GET['estado']) && $_GET['estado'] == 'emitido' ? 'selected' : '' ?>>Emitido</option>
                        <option value="vencido" <?= isset($_GET['estado']) && $_GET['estado'] == 'vencido' ? 'selected' : '' ?>>Vencido</option>
                        <option value="cerrado" <?= isset($_GET['estado']) && $_GET['estado'] == 'cerrado' ? 'selected' : '' ?>>Cerrado</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary w-100">
                        <i class="bi bi-funnel me-2"></i>Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tarjetas de resumen -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-hover border-start border-primary border-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                Total Gastos
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                <?= count($gastos) ?>
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
                                Monto Total
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                $<?= number_format(array_sum(array_column($gastos, 'monto_total')), 0, ',', '.') ?>
                            </div>
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
                                Pendientes
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                <?= count(array_filter($gastos, fn($g) => $g['estado'] === 'pendiente')) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-clock fa-2x text-gray-300"></i>
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
                                % Recaudación
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                <?php
                                $totalEsperado = array_sum(array_column($gastos, 'monto_total'));
                                $totalRecaudado = array_sum(array_column($gastos, 'monto_recaudado'));
                                $porcentaje = $totalEsperado > 0 ? ($totalRecaudado / $totalEsperado) * 100 : 0;
                                echo number_format($porcentaje, 1) . '%';
                                ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-graph-up fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de gastos -->
    <div class="card card-hover">
        <div class="card-header bg-transparent">
            <h6 class="m-0 fw-bold">Lista de Gastos Comunes</h6>
        </div>
        <div class="card-body p-0">
            <?php if (empty($gastos)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-receipt display-1 text-muted"></i>
                    <h5 class="text-muted mt-3">No hay gastos comunes registrados</h5>
                    <p class="text-muted">Comienza creando tu primer gasto común</p>
                    <a href="<?= $url->to('finanzas/gastos-comunes/crear') ?>" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Crear Primer Gasto
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Edificio</th>
                                <th>Nombre</th>
                                <th>Período</th>
                                <th>Monto Total</th>
                                <th>Vencimiento</th>
                                <th>Estado</th>
                                <th>Recaudación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($gastos as $gasto): ?>
                                <?php
                                $porcentajeRecaudacion = $gasto['monto_total'] > 0 ? 
                                    ($gasto['monto_recaudado'] / $gasto['monto_total']) * 100 : 0;
                                
                                // Colores según estado
                                $estadoColors = [
                                    'pendiente' => 'warning',
                                    'emitido' => 'info',
                                    'vencido' => 'danger',
                                    'cerrado' => 'success'
                                ];
                                
                                $estadoTexts = [
                                    'pendiente' => 'Pendiente',
                                    'emitido' => 'Emitido',
                                    'vencido' => 'Vencido',
                                    'cerrado' => 'Cerrado'
                                ];
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-building text-muted me-2"></i>
                                            <?= htmlspecialchars($gasto['edificio_nombre']) ?>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($gasto['nombre']) ?></td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            <?= date('m/Y', strtotime($gasto['periodo'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong>$<?= number_format($gasto['monto_total'], 0, ',', '.') ?></strong>
                                    </td>
                                    <td>
                                        <?= date('d/m/Y', strtotime($gasto['fecha_vencimiento'])) ?>
                                        <?php if (strtotime($gasto['fecha_vencimiento']) < time() && $gasto['estado'] !== 'cerrado'): ?>
                                            <span class="badge bg-danger ms-1">Vencido</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $estadoColors[$gasto['estado']] ?>">
                                            <?= $estadoTexts[$gasto['estado']] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                                <div class="progress-bar 
                                                    <?= $porcentajeRecaudacion >= 80 ? 'bg-success' : 
                                                       ($porcentajeRecaudacion >= 50 ? 'bg-warning' : 'bg-danger') ?>" 
                                                    style="width: <?= $porcentajeRecaudacion ?>%">
                                                </div>
                                            </div>
                                            <small class="text-muted"><?= number_format($porcentajeRecaudacion, 1) ?>%</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= $url->to('finanzas/gastos-comunes/ver/');?><?= $gasto['id'] ?>" 
                                               class="btn btn-outline-primary" 
                                               title="Ver detalle">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if ($gasto['estado'] === 'pendiente'): ?>
                                                <a href="<?= $url->to('finanzas/gastos-comunes/editar/');?><?= $gasto['id'] ?>" 
                                                   class="btn btn-outline-secondary" 
                                                   title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($gasto['estado'] !== 'cerrado'): ?>
                                                <form method="POST" 
                                                      action="/proyectos/edificios/finanzas/gastos-comunes/cerrar/<?= $gasto['id'] ?>" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('¿Está seguro de cerrar este gasto común?')">
                                                    <button type="submit" class="btn btn-outline-success" title="Cerrar">
                                                        <i class="bi bi-check-lg"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <a href="<?php echo $url->to('dashboard'); ?>" class="btn btn-primary btn-lg rounded-circle shadow">
            <i class="bi bi-house"></i>
        </a>
    </div>

</div>