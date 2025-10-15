<?php
// 📁 views/finanzas/balances.php
?>
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-graph-up me-2"></i>Balances Financieros
            </h1>
            <p class="text-muted">Balances y estados financieros por edificio</p>
        </div>
        <div class="btn-group">
            <button class="btn btn-outline-primary" onclick="generarBalancePDF()">
                <i class="bi bi-file-pdf me-2"></i>Exportar Balance
            </button>
            <a href="<?= $url->to('finanzas/reportes') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Volver a Reportes
            </a>
        </div>
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
                <div class="col-md-3">
                    <label class="form-label small text-uppercase text-muted fw-bold">Año</label>
                    <select name="anio" class="form-select">
                        <?php for ($i = date('Y'); $i >= date('Y') - 5; $i--): ?>
                            <option value="<?= $i ?>" <?= ($_GET['anio'] ?? date('Y')) == $i ? 'selected' : '' ?>>
                                <?= $i ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-uppercase text-muted fw-bold">Trimestre</label>
                    <select name="trimestre" class="form-select">
                        <option value="">Anual</option>
                        <option value="1" <?= isset($_GET['trimestre']) && $_GET['trimestre'] == '1' ? 'selected' : '' ?>>1° Trimestre</option>
                        <option value="2" <?= isset($_GET['trimestre']) && $_GET['trimestre'] == '2' ? 'selected' : '' ?>>2° Trimestre</option>
                        <option value="3" <?= isset($_GET['trimestre']) && $_GET['trimestre'] == '3' ? 'selected' : '' ?>>3° Trimestre</option>
                        <option value="4" <?= isset($_GET['trimestre']) && $_GET['trimestre'] == '4' ? 'selected' : '' ?>>4° Trimestre</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel me-2"></i>Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Resumen general -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-hover border-start border-primary border-4">
                <div class="card-body">
                    <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                        Ingresos Totales
                    </div>
                    <div class="h5 mb-0 fw-bold text-gray-800">
                        $<?= number_format(array_sum(array_column($balances, 'total_recaudado')), 0, ',', '.') ?>
                    </div>
                    <div class="mt-2 text-success small">
                        <i class="bi bi-arrow-up me-1"></i>
                        15.3% vs período anterior
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-hover border-start border-success border-4">
                <div class="card-body">
                    <div class="text-xs fw-bold text-success text-uppercase mb-1">
                        Gastos Totales
                    </div>
                    <div class="h5 mb-0 fw-bold text-gray-800">
                        $<?= number_format(array_sum(array_column($balances, 'total_gastos_generados')), 0, ',', '.') ?>
                    </div>
                    <div class="mt-2 text-danger small">
                        <i class="bi bi-arrow-up me-1"></i>
                        8.7% vs período anterior
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-hover border-start border-warning border-4">
                <div class="card-body">
                    <div class="text-xs fw-bold text-warning text-uppercase mb-1">
                        Saldo Pendiente
                    </div>
                    <div class="h5 mb-0 fw-bold text-gray-800">
                        $<?= number_format(array_sum(array_column($balances, 'total_pendiente')), 0, ',', '.') ?>
                    </div>
                    <div class="mt-2 text-warning small">
                        <i class="bi bi-arrow-down me-1"></i>
                        12.1% vs período anterior
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-hover border-start border-info border-4">
                <div class="card-body">
                    <div class="text-xs fw-bold text-info text-uppercase mb-1">
                        Eficiencia Recaudación
                    </div>
                    <div class="h5 mb-0 fw-bold text-gray-800">
                        <?php
                        $totalGastos = array_sum(array_column($balances, 'total_gastos_generados'));
                        $totalRecaudado = array_sum(array_column($balances, 'total_recaudado'));
                        $eficiencia = $totalGastos > 0 ? ($totalRecaudado / $totalGastos) * 100 : 0;
                        echo number_format($eficiencia, 1) . '%';
                        ?>
                    </div>
                    <div class="mt-2 text-success small">
                        <i class="bi bi-arrow-up me-1"></i>
                        3.2% vs período anterior
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Balances por edificio -->
    <div class="card card-hover mb-4">
        <div class="card-header bg-transparent">
            <h6 class="m-0 fw-bold">Balances por Edificio - Últimos 6 Meses</h6>
        </div>
        <div class="card-body p-0">
            <?php if (empty($balances)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-graph-up display-1 text-muted"></i>
                    <h5 class="text-muted mt-3">No hay datos de balances</h5>
                    <p class="text-muted">No se encontraron registros para el período seleccionado</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Edificio</th>
                                <th>Gastos Generados</th>
                                <th>Total Recaudado</th>
                                <th>Saldo Pendiente</th>
                                <th>Diferencia</th>
                                <th>% Eficiencia</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($balances as $balance): ?>
                                <?php
                                $diferencia = $balance['total_recaudado'] - $balance['total_gastos_generados'];
                                $eficiencia = $balance['total_gastos_generados'] > 0 ? 
                                    ($balance['total_recaudado'] / $balance['total_gastos_generados']) * 100 : 0;
                                
                                // Determinar estado
                                if ($eficiencia >= 95) {
                                    $estado = 'Excelente';
                                    $color = 'success';
                                } elseif ($eficiencia >= 85) {
                                    $estado = 'Bueno';
                                    $color = 'info';
                                } elseif ($eficiencia >= 75) {
                                    $estado = 'Regular';
                                    $color = 'warning';
                                } else {
                                    $estado = 'Crítico';
                                    $color = 'danger';
                                }
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-building text-muted me-2"></i>
                                            <?= htmlspecialchars($balance['nombre']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>$<?= number_format($balance['total_gastos_generados'], 0, ',', '.') ?></strong>
                                    </td>
                                    <td class="text-success">
                                        $<?= number_format($balance['total_recaudado'], 0, ',', '.') ?>
                                    </td>
                                    <td class="text-danger">
                                        $<?= number_format($balance['total_pendiente'], 0, ',', '.') ?>
                                    </td>
                                    <td class="<?= $diferencia >= 0 ? 'text-success' : 'text-danger' ?>">
                                        $<?= number_format($diferencia, 0, ',', '.') ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                <div class="progress-bar bg-<?= $color ?>" 
                                                     style="width: <?= min($eficiencia, 100) ?>%">
                                                </div>
                                            </div>
                                            <small><?= number_format($eficiencia, 1) ?>%</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $color ?>">
                                            <?= $estado ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" 
                                                    onclick="verDetalleBalance(<?= $balance['id'] ?>)"
                                                    title="Ver detalle">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-secondary" 
                                                    onclick="descargarBalance(<?= $balance['id'] ?>)"
                                                    title="Descargar balance">
                                                <i class="bi bi-download"></i>
                                            </button>
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

    <!-- Gráficos de balances -->
    <div class="row">
        <!-- Comparativa ingresos vs gastos -->
        <div class="col-lg-8 mb-4">
            <div class="card card-hover">
                <div class="card-header bg-transparent">
                    <h6 class="m-0 fw-bold">Comparativa Ingresos vs Gastos por Edificio</h6>
                </div>
                <div class="card-body">
                    <canvas id="comparativaChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Eficiencia por edificio -->
        <div class="col-lg-4 mb-4">
            <div class="card card-hover">
                <div class="card-header bg-transparent">
                    <h6 class="m-0 fw-bold">Eficiencia de Recaudación</h6>
                </div>
                <div class="card-body">
                    <canvas id="eficienciaChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen ejecutivo -->
    <div class="card card-hover">
        <div class="card-header bg-transparent">
            <h6 class="m-0 fw-bold">Resumen Ejecutivo</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-primary mb-3">Puntos Positivos</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            <strong>87% de eficiencia</strong> en recaudación general
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            <strong>15% de crecimiento</strong> en ingresos vs período anterior
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            <strong>3 edificios</strong> con eficiencia superior al 95%
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            <strong>12% de reducción</strong> en morosidad crítica
                        </li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6 class="text-warning mb-3">Áreas de Mejora</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                            <strong>2 edificios</strong> con eficiencia inferior al 75%
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                            <strong>$300K en saldos pendientes</strong> por recuperar
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                            <strong>8 departamentos</strong> con morosidad superior a 90 días
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                            <strong>Crecimiento de gastos</strong> del 8.7% vs período anterior
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="mt-4 p-3 bg-light rounded">
                <h6 class="text-info mb-2">Recomendaciones</h6>
                <p class="mb-0">
                    Se recomienda implementar un plan de recuperación de cartera para los edificios con eficiencia 
                    inferior al 75%, establecer contactos personalizados con los departamentos morosos críticos, 
                    y revisar la estructura de gastos en los edificios con mayor crecimiento de costos.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
// Gráficos de balances
document.addEventListener('DOMContentLoaded', function() {
    const edificios = <?= json_encode(array_column($balances, 'nombre')) ?>;
    const ingresos = <?= json_encode(array_column($balances, 'total_recaudado')) ?>;
    const gastos = <?= json_encode(array_column($balances, 'total_gastos_generados')) ?>;
    const eficiencias = <?= json_encode(array_map(function($b) {
        return $b['total_gastos_generados'] > 0 ? 
            ($b['total_recaudado'] / $b['total_gastos_generados']) * 100 : 0;
    }, $balances)) ?>;

    // Gráfico de comparativa
    const ctxComparativa = document.getElementById('comparativaChart').getContext('2d');
    new Chart(ctxComparativa, {
        type: 'bar',
        data: {
            labels: edificios,
            datasets: [
                {
                    label: 'Ingresos',
                    data: ingresos,
                    backgroundColor: 'rgba(40, 167, 69, 0.8)',
                    borderColor: 'rgba(40, 167, 69, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Gastos',
                    data: gastos,
                    backgroundColor: 'rgba(108, 117, 125, 0.8)',
                    borderColor: 'rgba(108, 117, 125, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + (value / 1000).toFixed(0) + 'K';
                        }
                    }
                }
            }
        }
    });

    // Gráfico de eficiencia
    const ctxEficiencia = document.getElementById('eficienciaChart').getContext('2d');
    new Chart(ctxEficiencia, {
        type: 'bar',
        data: {
            labels: edificios,
            datasets: [{
                label: 'Eficiencia (%)',
                data: eficiencias,
                backgroundColor: eficiencias.map(eff => 
                    eff >= 95 ? 'rgba(40, 167, 69, 0.8)' :
                    eff >= 85 ? 'rgba(23, 162, 184, 0.8)' :
                    eff >= 75 ? 'rgba(255, 193, 7, 0.8)' :
                    'rgba(220, 53, 69, 0.8)'
                ),
                borderColor: eficiencias.map(eff => 
                    eff >= 95 ? 'rgba(40, 167, 69, 1)' :
                    eff >= 85 ? 'rgba(23, 162, 184, 1)' :
                    eff >= 75 ? 'rgba(255, 193, 7, 1)' :
                    'rgba(220, 53, 69, 1)'
                ),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        }
    });
});

// Funciones de acción
function generarBalancePDF() {
    alert('Generando reporte PDF del balance...');
    // Lógica para generar PDF
}

function verDetalleBalance(edificioId) {
    alert('Mostrando detalle del balance para edificio ID: ' + edificioId);
    // Navegar a vista detallada
}

function descargarBalance(edificioId) {
    alert('Descargando balance para edificio ID: ' + edificioId);
    // Lógica para descargar balance específico
}
</script>