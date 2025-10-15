<?php
// 📁 views/finanzas/ver_gasto.php

if (!$gasto) {
    echo '<div class="alert alert-danger">Gasto común no encontrado</div>';
    return;
}

$porcentajeRecaudacion = $gasto['monto_total'] > 0 ? 
    ($estado_pagos ? array_sum(array_column($estado_pagos, 'monto')) / $gasto['monto_total'] * 100 : 0) : 0;

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
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-receipt me-2"></i><?= htmlspecialchars($gasto['nombre']) ?>
            </h1>
            <p class="text-muted">
                <i class="bi bi-building me-1"></i><?= htmlspecialchars($gasto['edificio_nombre']) ?> 
                • Período: <?= date('m/Y', strtotime($gasto['periodo'])) ?>
            </p>
        </div>
        <div class="btn-group">
            <a href=<?= $url->to("/finanzas/gastos-comunes") ?> class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Volver
            </a>
            <?php if ($gasto['estado'] === 'pendiente'): ?>
                <a href=<?= $url->to("/finanzas/gastos-comunes/editar/{$gasto['id']}") ?> class="btn btn-outline-primary">
                    <i class="bi bi-pencil me-2"></i>Editar
                </a>
            <?php endif; ?>
            <?php if ($gasto['estado'] !== 'cerrado'): ?>
                <form method="POST" action=<?= $url->to("/finanzas/gastos-comunes/cerrar/{$gasto['id']}") ?> class="d-inline"
                      onsubmit="return confirm('¿Está seguro de cerrar este gasto común?')">
                    <button type="submit" class="btn btn-outline-success">
                        <i class="bi bi-check-lg me-2"></i>Cerrar
                    </button>
                </form>
            <?php endif; ?>
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
                                Monto Total
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                $<?= number_format($gasto['monto_total'], 0, ',', '.') ?>
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
            <div class="card card-hover border-start border-<?= $estadoColors[$gasto['estado']] ?> border-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-<?= $estadoColors[$gasto['estado']] ?> text-uppercase mb-1">
                                Estado
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                <?= $estadoTexts[$gasto['estado']] ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-<?= $gasto['estado'] === 'cerrado' ? 'check' : 'clock' ?> fa-2x text-gray-300"></i>
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
                                Departamentos
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                <?= count($distribucion) ?>
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
            <div class="card card-hover border-start border-success border-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">
                                Recaudación
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                <?= number_format($porcentajeRecaudacion, 1) ?>%
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

    <div class="row">
        <!-- Información general -->
        <div class="col-lg-4 mb-4">
            <div class="card card-hover">
                <div class="card-header bg-transparent">
                    <h6 class="m-0 fw-bold">Información General</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong class="text-muted small">Edificio:</strong><br>
                        <?= htmlspecialchars($gasto['edificio_nombre']) ?>
                    </div>
                    <div class="mb-3">
                        <strong class="text-muted small">Período:</strong><br>
                        <?= date('F Y', strtotime($gasto['periodo'])) ?>
                    </div>
                    <div class="mb-3">
                        <strong class="text-muted small">Fecha Vencimiento:</strong><br>
                        <?= date('d/m/Y', strtotime($gasto['fecha_vencimiento'])) ?>
                        <?php if (strtotime($gasto['fecha_vencimiento']) < time() && $gasto['estado'] !== 'cerrado'): ?>
                            <span class="badge bg-danger ms-1">Vencido</span>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <strong class="text-muted small">Estado:</strong><br>
                        <span class="badge bg-<?= $estadoColors[$gasto['estado']] ?>">
                            <?= $estadoTexts[$gasto['estado']] ?>
                        </span>
                    </div>
                    <?php if ($gasto['descripcion']): ?>
                        <div>
                            <strong class="text-muted small">Descripción:</strong><br>
                            <?= nl2br(htmlspecialchars($gasto['descripcion'])) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Distribución por departamento -->
        <div class="col-lg-8">
            <div class="card card-hover mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold">Distribución por Departamento</h6>
                    <span class="badge bg-primary">
                        Total: $<?= number_format($gasto['monto_total'], 0, ',', '.') ?>
                    </span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($distribucion)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-house-door display-4 text-muted"></i>
                            <p class="text-muted mt-2">No hay departamentos en este edificio</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Departamento</th>
                                        <th>Propietario</th>
                                        <th>% Copropiedad</th>
                                        <th>Monto</th>
                                        <th>Estado Pago</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($distribucion as $depto): ?>
                                        <?php
                                        $pagoDepto = array_filter($estado_pagos, fn($p) => $p['departamento_id'] == $depto['departamento_id']);
                                        $pago = $pagoDepto ? reset($pagoDepto) : null;
                                        ?>
                                        <tr>
                                            <td>
                                                <i class="bi bi-house-door text-muted me-2"></i>
                                                <?= htmlspecialchars($depto['numero']) ?>
                                            </td>
                                            <td><?= htmlspecialchars($depto['propietario_nombre'] ?? 'No asignado') ?></td>
                                            <td><?= number_format($depto['porcentaje'], 2) ?>%</td>
                                            <td>
                                                <strong>$<?= number_format($depto['monto'], 0, ',', '.') ?></strong>
                                            </td>
                                            <td>
                                                <?php if ($pago): ?>
                                                    <span class="badge bg-<?= $pago['estado_color'] ?>">
                                                        <?= ucfirst($pago['estado']) ?>
                                                    </span>
                                                    <?php if ($pago['estado'] === 'pagado'): ?>
                                                        <br>
                                                        <small class="text-muted">
                                                            <?= date('d/m/Y', strtotime($pago['fecha_pago'])) ?>
                                                        </small>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">Pendiente</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Barra de progreso de recaudación -->
            <div class="card card-hover">
                <div class="card-header bg-transparent">
                    <h6 class="m-0 fw-bold">Estado de Recaudación</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">Progreso de recaudación</span>
                            <span class="text-muted small"><?= number_format($porcentajeRecaudacion, 1) ?>%</span>
                        </div>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar 
                                <?= $porcentajeRecaudacion >= 80 ? 'bg-success' : 
                                   ($porcentajeRecaudacion >= 50 ? 'bg-warning' : 'bg-danger') ?>" 
                                style="width: <?= $porcentajeRecaudacion ?>%">
                                <?= number_format($porcentajeRecaudacion, 1) ?>%
                            </div>
                        </div>
                    </div>
                    
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="border-end">
                                <div class="h5 mb-0 fw-bold text-primary">
                                    $<?= number_format($gasto['monto_total'], 0, ',', '.') ?>
                                </div>
                                <small class="text-muted">Total Esperado</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-end">
                                <div class="h5 mb-0 fw-bold text-success">
                                    $<?= number_format($estado_pagos ? array_sum(array_column($estado_pagos, 'monto')) : 0, 0, ',', '.') ?>
                                </div>
                                <small class="text-muted">Total Recaudado</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div>
                                <div class="h5 mb-0 fw-bold text-danger">
                                    $<?= number_format($gasto['monto_total'] - ($estado_pagos ? array_sum(array_column($estado_pagos, 'monto')) : 0), 0, ',', '.') ?>
                                </div>
                                <small class="text-muted">Total Pendiente</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>