<?php
// 📁 views/finanzas/estado_pagos.php
?>
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-credit-card me-2"></i>Estado de Pagos
            </h1>
            <p class="text-muted">Resumen general del estado de pagos por edificio</p>
        </div>
        <a href="<?= $url->to('finanzas/pagos/registrar') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Registrar Pago
        </a>
    </div>

    <!-- Filtros -->
    <div class="card card-hover mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
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
                <div class="col-md-4">
                    <label class="form-label small text-uppercase text-muted fw-bold">Período</label>
                    <input type="month" name="periodo" class="form-control" 
                           value="<?= $_GET['periodo'] ?? date('Y-m') ?>">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary w-100">
                        <i class="bi bi-funnel me-2"></i>Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tarjetas de resumen general -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-hover border-start border-primary border-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                Total Esperado
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                $<?= number_format(array_sum(array_column($estado_pagos, 'total_esperado')), 0, ',', '.') ?>
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
            <div class="card card-hover border-start border-success border-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">
                                Total Recaudado
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                $<?= number_format(array_sum(array_column($estado_pagos, 'total_recaudado')), 0, ',', '.') ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-cash-coin fa-2x text-gray-300"></i>
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
                                % Recaudación Promedio
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                <?php
                                $totalEsperado = array_sum(array_column($estado_pagos, 'total_esperado'));
                                $totalRecaudado = array_sum(array_column($estado_pagos, 'total_recaudado'));
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

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-hover border-start border-info border-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-info text-uppercase mb-1">
                                Departamentos
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                <?= array_sum(array_column($estado_pagos, 'total_departamentos')) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-house-door fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de estado de pagos por edificio -->
    <div class="card card-hover">
        <div class="card-header bg-transparent">
            <h6 class="m-0 fw-bold">Estado de Pagos por Edificio</h6>
        </div>
        <div class="card-body p-0">
            <?php if (empty($estado_pagos)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-credit-card display-1 text-muted"></i>
                    <h5 class="text-muted mt-3">No hay información de pagos</h5>
                    <p class="text-muted">No se encontraron gastos comunes para el período seleccionado</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Edificio</th>
                                <th>Departamentos</th>
                                <th>Total Esperado</th>
                                <th>Total Recaudado</th>
                                <th>Pendiente</th>
                                <th>% Recaudación</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($estado_pagos as $estado): ?>
                                <?php
                                $porcentaje = $estado['porcentaje_recaudado'] ?? 0;
                                $pendiente = $estado['total_esperado'] - $estado['total_recaudado'];
                                
                                // Determinar color según porcentaje
                                if ($porcentaje >= 90) $color = 'success';
                                elseif ($porcentaje >= 70) $color = 'warning';
                                else $color = 'danger';
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-building text-muted me-2"></i>
                                            <?= htmlspecialchars($estado['edificio_nombre']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            <?= $estado['total_departamentos'] ?> deptos.
                                        </span>
                                    </td>
                                    <td>
                                        <strong>$<?= number_format($estado['total_esperado'], 0, ',', '.') ?></strong>
                                    </td>
                                    <td>
                                        <span class="text-success fw-bold">
                                            $<?= number_format($estado['total_recaudado'], 0, ',', '.') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-danger">
                                            $<?= number_format($pendiente, 0, ',', '.') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                <div class="progress-bar bg-<?= $color ?>" 
                                                     style="width: <?= $porcentaje ?>%">
                                                </div>
                                            </div>
                                            <span class="text-muted small"><?= number_format($porcentaje, 1) ?>%</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $color ?>">
                                            <?= $porcentaje >= 90 ? 'Excelente' : 
                                               ($porcentaje >= 70 ? 'Regular' : 'Crítico') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= $url->to("finanzas/pagos?edificio_id={$estado['edificio_id']}") ?>" 
                                               class="btn btn-outline-primary" 
                                               title="Ver pagos">
                                                <i class="bi bi-list"></i>
                                            </a>
                                            <a href="<?= $url->to("finanzas/gastos-comunes?edificio_id={$estado['edificio_id']}") ?>" 
                                               class="btn btn-outline-secondary" 
                                               title="Ver gastos">
                                                <i class="bi bi-receipt"></i>
                                            </a>
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

    <!-- Gráfico de recaudación -->
    <div class="row mt-4">
        <div class="col-lg-8">
            <div class="card card-hover">
                <div class="card-header bg-transparent">
                    <h6 class="m-0 fw-bold">Distribución de Recaudación por Edificio</h6>
                </div>
                <div class="card-body">
                    <canvas id="recaudacionChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card card-hover">
                <div class="card-header bg-transparent">
                    <h6 class="m-0 fw-bold">Resumen por Estado</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-success me-2"></span>
                                Excelente (90-100%)
                            </div>
                            <span class="badge bg-success rounded-pill">
                                <?= count(array_filter($estado_pagos, fn($e) => ($e['porcentaje_recaudado'] ?? 0) >= 90)) ?>
                            </span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-warning me-2"></span>
                                Regular (70-89%)
                            </div>
                            <span class="badge bg-warning rounded-pill">
                                <?= count(array_filter($estado_pagos, fn($e) => ($e['porcentaje_recaudado'] ?? 0) >= 70 && ($e['porcentaje_recaudado'] ?? 0) < 90)) ?>
                            </span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-danger me-2"></span>
                                Crítico (<70%)
                            </div>
                            <span class="badge bg-danger rounded-pill">
                                <?= count(array_filter($estado_pagos, fn($e) => ($e['porcentaje_recaudado'] ?? 0) < 70)) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Gráfico de recaudación
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('recaudacionChart').getContext('2d');
    const edificios = <?= json_encode(array_column($estado_pagos, 'edificio_nombre')) ?>;
    const recaudado = <?= json_encode(array_column($estado_pagos, 'total_recaudado')) ?>;
    const pendiente = <?= json_encode(array_map(function($e) {
        return $e['total_esperado'] - $e['total_recaudado'];
    }, $estado_pagos)) ?>;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: edificios,
            datasets: [
                {
                    label: 'Recaudado',
                    data: recaudado,
                    backgroundColor: 'rgba(40, 167, 69, 0.8)',
                    borderColor: 'rgba(40, 167, 69, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Pendiente',
                    data: pendiente,
                    backgroundColor: 'rgba(220, 53, 69, 0.8)',
                    borderColor: 'rgba(220, 53, 69, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    stacked: true
                },
                y: {
                    stacked: true,
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString('es-CL');
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += '$' + context.parsed.y.toLocaleString('es-CL');
                            return label;
                        }
                    }
                }
            }
        }
    });
});
</script>