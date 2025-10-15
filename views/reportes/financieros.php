<?php
// 📁 views/reportes/financieros.php
?>
<div class="container-fluid">
    <!-- Header con Filtros -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Edificio</label>
                            <select class="form-select" id="edificioSelect">
                                <option value="<?= $edificio['id'] ?>"><?= $edificio['nombre'] ?></option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha Inicio</label>
                            <input type="date" class="form-control" id="fechaInicio" 
                                   value="<?= $filtros['fecha_inicio'] ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha Fin</label>
                            <input type="date" class="form-control" id="fechaFin" 
                                   value="<?= $filtros['fecha_fin'] ?>">
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary w-100" onclick="aplicarFiltros()">
                                <i class="bi bi-funnel"></i> Generar Reporte
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjetas de Resumen -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-hover border-start border-primary border-4">
                <div class="card-body text-center">
                    <h3 class="text-primary">$<?= number_format($reporte['resumen_general']['total_ingresos'] ?? 0, 0, ',', '.') ?></h3>
                    <small class="text-muted">Ingresos Totales</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-hover border-start border-success border-4">
                <div class="card-body text-center">
                    <h3 class="text-success">$<?= number_format($reporte['resumen_general']['total_egresos'] ?? 0, 0, ',', '.') ?></h3>
                    <small class="text-muted">Egresos Totales</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-hover border-start border-<?= ($reporte['resumen_general']['diferencia'] ?? 0) >= 0 ? 'info' : 'danger' ?> border-4">
                <div class="card-body text-center">
                    <h3 class="text-<?= ($reporte['resumen_general']['diferencia'] ?? 0) >= 0 ? 'info' : 'danger' ?>">
                        $<?= number_format($reporte['resumen_general']['diferencia'] ?? 0, 0, ',', '.') ?>
                    </h3>
                    <small class="text-muted">Balance</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-hover border-start border-warning border-4">
                <div class="card-body text-center">
                    <h3 class="text-warning"><?= number_format($reporte['resumen_general']['tasa_recaudacion'] ?? 0, 1) ?>%</h3>
                    <small class="text-muted">Tasa Recaudación</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-hover border-start border-info border-4">
                <div class="card-body text-center">
                    <h3 class="text-info"><?= $reporte['resumen_general']['total_gastos'] ?? 0 ?></h3>
                    <small class="text-muted">Total Gastos</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-hover border-start border-danger border-4">
                <div class="card-body text-center">
                    <h3 class="text-danger"><?= $reporte['resumen_general']['deptos_morosos'] ?? 0 ?></h3>
                    <small class="text-muted">Deptos Morosos</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos Principales -->
    <div class="row">
        <!-- Evolución Mensual -->
        <div class="col-lg-8 mb-4">
            <div class="card card-hover">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold">Evolución Mensual de Ingresos vs Egresos</h6>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-primary active" data-chart-type="line">Línea</button>
                        <button type="button" class="btn btn-outline-primary" data-chart-type="bar">Barras</button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="evolucionChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Distribución de Gastos -->
        <div class="col-lg-4 mb-4">
            <div class="card card-hover">
                <div class="card-header bg-transparent">
                    <h6 class="m-0 fw-bold">Distribución de Gastos</h6>
                </div>
                <div class="card-body">
                    <canvas id="distribucionGastosChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos Secundarios -->
    <div class="row">
        <!-- Tasa de Recaudación -->
        <div class="col-lg-6 mb-4">
            <div class="card card-hover">
                <div class="card-header bg-transparent">
                    <h6 class="m-0 fw-bold">Tasa de Recaudación por Mes</h6>
                </div>
                <div class="card-body">
                    <canvas id="tasaRecaudacionChart" height="250"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Deudores -->
        <div class="col-lg-6 mb-4">
            <div class="card card-hover">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold">Top 10 Deudores Morosos</h6>
                    <span class="badge bg-danger">Total: $<?= number_format(array_sum(array_column($reporte['deudores_morosos'], 'deuda_total')), 0, ',', '.') ?></span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Departamento</th>
                                    <th>Propietario</th>
                                    <th class="text-end">Deuda Total</th>
                                    <th class="text-end">Meses Atraso</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($reporte['deudores_morosos'], 0, 10) as $deudor): ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($deudor['departamento']) ?></td>
                                    <td><?= htmlspecialchars($deudor['propietario_nombre']) ?></td>
                                    <td class="text-end text-danger fw-bold">
                                        $<?= number_format($deudor['deuda_total'], 0, ',', '.') ?>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-danger"><?= $deudor['meses_atraso'] ?> meses</span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Botones de Exportación -->
    <div class="row mt-4">
        <div class="col-12 text-center">
            <div class="btn-group">
                <a href="/reportes/generar-pdf/financiero?edificio_id=<?= $edificio['id'] ?>&fecha_inicio=<?= $filtros['fecha_inicio'] ?>&fecha_fin=<?= $filtros['fecha_fin'] ?>" 
                   class="btn btn-danger">
                    <i class="bi bi-file-pdf"></i> Exportar PDF
                </a>
                <button class="btn btn-success" onclick="exportarExcel()">
                    <i class="bi bi-file-excel"></i> Exportar Excel
                </button>
                <button class="btn btn-info" onclick="imprimirReporte()">
                    <i class="bi bi-printer"></i> Imprimir
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Datos para gráficos (estos vendrían del controlador)
const datosEvolucion = <?= json_encode($reporte['evolucion_ingresos']) ?>;
const datosDistribucion = <?= json_encode($reporte['distribucion_gastos']) ?>;

// Gráfico de Evolución
const evolucionCtx = document.getElementById('evolucionChart').getContext('2d');
const evolucionChart = new Chart(evolucionCtx, {
    type: 'line',
    data: {
        labels: datosEvolucion.map(d => new Date(d.mes + '-01').toLocaleDateString('es-CL', { month: 'short', year: 'numeric' })),
        datasets: [
            {
                label: 'Ingresos Reales',
                data: datosEvolucion.map(d => d.ingresos_reales),
                borderColor: 'var(--success-color)',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4,
                fill: true
            },
            {
                label: 'Gastos Generados',
                data: datosEvolucion.map(d => d.gastos_generados),
                borderColor: 'var(--danger-color)',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                tension: 0.4,
                fill: true
            }
        ]
    },
    options: {
        responsive: true,
        interaction: {
            intersect: false,
            mode: 'index'
        },
        scales: {
            y: {
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
                        return context.dataset.label + ': $' + context.parsed.y.toLocaleString('es-CL');
                    }
                }
            }
        }
    }
});

// Gráfico de Distribución de Gastos
const distribucionCtx = document.getElementById('distribucionGastosChart').getContext('2d');
const distribucionChart = new Chart(distribucionCtx, {
    type: 'doughnut',
    data: {
        labels: datosDistribucion.map(d => d.categoria),
        datasets: [{
            data: datosDistribucion.map(d => d.monto_total),
            backgroundColor: [
                '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', 
                '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF'
            ],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const value = context.parsed;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = Math.round((value / total) * 100);
                        return `${context.label}: $${value.toLocaleString('es-CL')} (${percentage}%)`;
                    }
                }
            }
        }
    }
});

// Gráfico de Tasa de Recaudación
const tasaCtx = document.getElementById('tasaRecaudacionChart').getContext('2d');
const tasaChart = new Chart(tasaCtx, {
    type: 'bar',
    data: {
        labels: datosEvolucion.map(d => new Date(d.mes + '-01').toLocaleDateString('es-CL', { month: 'short' })),
        datasets: [{
            label: 'Tasa de Recaudación (%)',
            data: datosEvolucion.map(d => {
                const tasa = (d.ingresos_reales / d.gastos_generados) * 100;
                return isNaN(tasa) ? 0 : Math.min(tasa, 100);
            }),
            backgroundColor: datosEvolucion.map(d => {
                const tasa = (d.ingresos_reales / d.gastos_generados) * 100;
                return tasa >= 80 ? 'var(--success-color)' : 
                       tasa >= 60 ? 'var(--warning-color)' : 'var(--danger-color)';
            }),
            borderWidth: 0
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
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return `Tasa: ${context.parsed.y.toFixed(1)}%`;
                    }
                }
            }
        }
    }
});

// Funciones de control
function aplicarFiltros() {
    const edificioId = document.getElementById('edificioSelect').value;
    const fechaInicio = document.getElementById('fechaInicio').value;
    const fechaFin = document.getElementById('fechaFin').value;
    
    window.location.href = `/reportes/financieros?edificio_id=${edificioId}&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`;
}

function exportarExcel() {
    // Implementar exportación a Excel usando SheetJS o similar
    const table = document.getElementById('tablaReporte');
    // Lógica de exportación...
    alert('Exportando a Excel...');
}

function imprimirReporte() {
    window.print();
}

// Cambiar tipo de gráfico
document.querySelectorAll('[data-chart-type]').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('[data-chart-type]').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        evolucionChart.config.type = this.dataset.chartType;
        evolucionChart.update();
    });
});
</script>

<style>
@media print {
    .btn, .card-header .btn-group, .text-center .btn-group {
        display: none !important;
    }
    
    .card {
        border: 1px solid #000 !important;
        break-inside: avoid;
    }
    
    .container-fluid {
        max-width: 100% !important;
    }
}
</style>