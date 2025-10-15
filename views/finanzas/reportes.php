<?php
// 📁 views/finanzas/reportes.php
?>
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-graph-up me-2"></i>Reportes Financieros
            </h1>
            <p class="text-muted">Reportes y análisis financieros del sistema</p>
        </div>
        <div class="btn-group">
            <button class="btn btn-outline-primary" onclick="generarPDF()">
                <i class="bi bi-file-pdf me-2"></i>Exportar PDF
            </button>
            <button class="btn btn-outline-success" onclick="exportarExcel()">
                <i class="bi bi-file-earmark-excel me-2"></i>Exportar Excel
            </button>
        </div>
    </div>

    <!-- Filtros para reportes -->
    <div class="card card-hover mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small text-uppercase text-muted fw-bold">Edificio</label>
                    <select name="edificio_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Todos los edificios</option>
                        <?php foreach ($edificios as $edificio): ?>
                            <option value="<?= $edificio['id'] ?>" 
                                <?= isset($_GET['edificio_id']) && $_GET['edificio_id'] == $edificio['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($edificio['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-uppercase text-muted fw-bold">Año</label>
                    <select name="anio" class="form-select" onchange="this.form.submit()">
                        <?php for ($i = date('Y'); $i >= date('Y') - 5; $i--): ?>
                            <option value="<?= $i ?>" <?= isset($_GET['anio']) && $_GET['anio'] == $i ? 'selected' : '' ?>>
                                <?= $i ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-uppercase text-muted fw-bold">Mes</label>
                    <select name="mes" class="form-select" onchange="this.form.submit()">
                        <option value="">Todo el año</option>
                        <?php 
                        $meses = [
                            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                        ];
                        foreach ($meses as $num => $nombre): ?>
                            <option value="<?= $num ?>" <?= isset($_GET['mes']) && $_GET['mes'] == $num ? 'selected' : '' ?>>
                                <?= $nombre ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-uppercase text-muted fw-bold">Tipo de Reporte</label>
                    <select name="tipo_reporte" class="form-select" onchange="this.form.submit()">
                        <option value="general" <?= ($filtros['tipo_reporte'] ?? 'general') == 'general' ? 'selected' : '' ?>>Reporte General</option>
                        <option value="recaudacion" <?= ($filtros['tipo_reporte'] ?? '') == 'recaudacion' ? 'selected' : '' ?>>Recaudación</option>
                        <option value="morosidad" <?= ($filtros['tipo_reporte'] ?? '') == 'morosidad' ? 'selected' : '' ?>>Morosidad</option>
                        <option value="gastos" <?= ($filtros['tipo_reporte'] ?? '') == 'gastos' ? 'selected' : '' ?>>Análisis de Gastos</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel me-2"></i>Generar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php if (!empty($reporte)): ?>
    <!-- Tarjetas de métricas -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card card-hover border-start border-primary border-4">
                <div class="card-body">
                    <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                        Gastos Totales
                    </div>
                    <div class="h5 mb-0 fw-bold text-gray-800">
                        $<?= number_format($reporte['metricas']['gastos_totales'] ?? 0, 0, ',', '.') ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card card-hover border-start border-success border-4">
                <div class="card-body">
                    <div class="text-xs fw-bold text-success text-uppercase mb-1">
                        Recaudación
                    </div>
                    <div class="h5 mb-0 fw-bold text-gray-800">
                        $<?= number_format($reporte['metricas']['recaudacion'] ?? 0, 0, ',', '.') ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card card-hover border-start border-warning border-4">
                <div class="card-body">
                    <div class="text-xs fw-bold text-warning text-uppercase mb-1">
                        Morosidad
                    </div>
                    <div class="h5 mb-0 fw-bold text-gray-800">
                        $<?= number_format($reporte['metricas']['morosidad'] ?? 0, 0, ',', '.') ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card card-hover border-start border-info border-4">
                <div class="card-body">
                    <div class="text-xs fw-bold text-info text-uppercase mb-1">
                        % Recaudación
                    </div>
                    <div class="h5 mb-0 fw-bold text-gray-800">
                        <?= number_format($reporte['metricas']['porcentaje_recaudacion'] ?? 0, 1) ?>%
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card card-hover border-start border-danger border-4">
                <div class="card-body">
                    <div class="text-xs fw-bold text-danger text-uppercase mb-1">
                        Deptos. Morosos
                    </div>
                    <div class="h5 mb-0 fw-bold text-gray-800">
                        <?= $reporte['metricas']['deptos_morosos'] ?? 0 ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card card-hover border-start border-secondary border-4">
                <div class="card-body">
                    <div class="text-xs fw-bold text-secondary text-uppercase mb-1">
                        Promedio Pago
                    </div>
                    <div class="h5 mb-0 fw-bold text-gray-800">
                        $<?= number_format($reporte['metricas']['promedio_pago'] ?? 0, 0, ',', '.') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos y reportes -->
    <div class="row">
        <!-- Gráfico de tendencia -->
        <div class="col-lg-8 mb-4">
            <div class="card card-hover">
                <div class="card-header bg-transparent">
                    <h6 class="m-0 fw-bold">Tendencia de Recaudación - Año <?= $filtros['anio'] ?></h6>
                </div>
                <div class="card-body">
                    <canvas id="tendenciaChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Distribución de morosidad -->
        <div class="col-lg-4 mb-4">
            <div class="card card-hover">
                <div class="card-header bg-transparent">
                    <h6 class="m-0 fw-bold">Distribución de Morosidad</h6>
                </div>
                <div class="card-body">
                    <canvas id="morosidadChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tablas de reportes -->
    <div class="row">
        <!-- Top departamentos morosos -->
        <div class="col-lg-6 mb-4">
            <div class="card card-hover">
                <div class="card-header bg-transparent">
                    <h6 class="m-0 fw-bold">Top 10 - Departamentos Morosos</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Departamento</th>
                                    <th>Propietario</th>
                                    <th>Monto Deuda</th>
                                    <th>Meses Atraso</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($reporte['top_morosos'])): ?>
                                    <?php foreach ($reporte['top_morosos'] as $moroso): ?>
                                        <tr>
                                            <td>
                                                <i class="bi bi-house-door text-muted me-2"></i>
                                                Depto. <?= htmlspecialchars($moroso['numero']) ?>
                                            </td>
                                            <td><?= htmlspecialchars($moroso['propietario_nombre'] ?? 'No registrado') ?></td>
                                            <td class="text-danger fw-bold">
                                                $<?= number_format($moroso['deuda_total'], 0, ',', '.') ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-danger"><?= $moroso['meses_atraso'] ?> meses</span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">
                                            No hay departamentos morosos
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mejores pagadores -->
        <div class="col-lg-6 mb-4">
            <div class="card card-hover">
                <div class="card-header bg-transparent">
                    <h6 class="m-0 fw-bold">Mejores Pagadores - Año <?= $filtros['anio'] ?></h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Departamento</th>
                                    <th>Propietario</th>
                                    <th>Puntualidad</th>
                                    <th>Total Pagado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($reporte['mejores_pagadores'])): ?>
                                    <?php foreach ($reporte['mejores_pagadores'] as $pagador): ?>
                                        <tr>
                                            <td>
                                                <i class="bi bi-house-door text-muted me-2"></i>
                                                Depto. <?= htmlspecialchars($pagador['numero']) ?>
                                            </td>
                                            <td><?= htmlspecialchars($pagador['propietario_nombre'] ?? 'No registrado') ?></td>
                                            <td>
                                                <span class="badge bg-success"><?= number_format($pagador['puntualidad'], 1) ?>%</span>
                                            </td>
                                            <td class="text-success fw-bold">
                                                $<?= number_format($pagador['total_pagado'], 0, ',', '.') ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">
                                            No hay datos de pagos
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reporte detallado por edificio -->
    <?php if (!empty($reporte['comparativa_edificios'])): ?>
    <div class="card card-hover">
        <div class="card-header bg-transparent">
            <h6 class="m-0 fw-bold">Reporte Detallado por Edificio - Año <?= $filtros['anio'] ?></h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Edificio</th>
                            <th>Gastos Generados</th>
                            <th>Total Recaudado</th>
                            <th>Morosidad</th>
                            <th>% Recaudación</th>
                            <th>Deptos. al Día</th>
                            <th>Deptos. Morosos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reporte['comparativa_edificios'] as $edificio): ?>
                            <tr>
                                <td>
                                    <i class="bi bi-building text-muted me-2"></i>
                                    <?= htmlspecialchars($edificio['nombre']) ?>
                                </td>
                                <td>$<?= number_format($edificio['gastos_generados'], 0, ',', '.') ?></td>
                                <td class="text-success">$<?= number_format($edificio['recaudacion'], 0, ',', '.') ?></td>
                                <td class="text-danger">$<?= number_format($edificio['morosidad'], 0, ',', '.') ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                            <div class="progress-bar 
                                                <?= $edificio['porcentaje_recaudacion'] >= 90 ? 'bg-success' : 
                                                   ($edificio['porcentaje_recaudacion'] >= 80 ? 'bg-warning' : 'bg-danger') ?>" 
                                                style="width: <?= $edificio['porcentaje_recaudacion'] ?>%">
                                            </div>
                                        </div>
                                        <small><?= number_format($edificio['porcentaje_recaudacion'], 1) ?>%</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-success"><?= $edificio['deptos_al_dia'] ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-danger"><?= $edificio['deptos_morosos'] ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <div class="text-center py-5">
        <i class="bi bi-graph-up display-1 text-muted"></i>
        <h4 class="text-muted mt-3">Selecciona un edificio para ver los reportes</h4>
        <p class="text-muted">Utiliza los filtros superiores para generar reportes financieros.</p>
    </div>
    <?php endif; ?>

    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <a href="<?php echo $url->to('dashboard'); ?>" class="btn btn-primary btn-lg rounded-circle shadow">
            <i class="bi bi-house"></i>
        </a>
    </div>

</div>

<script>
// Gráficos de reportes
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($reporte['tendencia_mensual'])): ?>
    // Gráfico de tendencia
    const ctxTendencia = document.getElementById('tendenciaChart').getContext('2d');
    new Chart(ctxTendencia, {
        type: 'line',
        data: {
            labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
            datasets: [
                {
                    label: 'Gastos Generados',
                    data: [<?= implode(',', array_column($reporte['tendencia_mensual'], 'gastos_generados')) ?>],
                    borderColor: 'rgba(108, 117, 125, 1)',
                    backgroundColor: 'rgba(108, 117, 125, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Recaudación',
                    data: [<?= implode(',', array_column($reporte['tendencia_mensual'], 'recaudacion')) ?>],
                    borderColor: 'rgba(40, 167, 69, 1)',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4
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
    <?php endif; ?>

    <?php if (!empty($reporte['distribucion_morosidad'])): ?>
    // Gráfico de morosidad
    const ctxMorosidad = document.getElementById('morosidadChart').getContext('2d');
    new Chart(ctxMorosidad, {
        type: 'doughnut',
        data: {
            labels: [<?= implode(',', array_map(function($item) { return "'" . $item['categoria'] . "'"; }, $reporte['distribucion_morosidad'])) ?>],
            datasets: [{
                data: [<?= implode(',', array_column($reporte['distribucion_morosidad'], 'cantidad_deptos')) ?>],
                backgroundColor: [
                    'rgba(40, 167, 69, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(253, 126, 20, 0.8)',
                    'rgba(220, 53, 69, 0.8)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    <?php endif; ?>
});

// Funciones de exportación
function generarPDF() {
    const params = new URLSearchParams(window.location.search);
    fetch(`<?= $this->url('finanzas/reportes/exportar-pdf'); ?>?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('PDF generado exitosamente');
                // Aquí podrías descargar el PDF
                console.log('Datos para PDF:', data.data);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al generar PDF');
        });
}

function exportarExcel() {
    const params = new URLSearchParams(window.location.search);
    fetch(`<?= $this->url('finanzas/reportes/exportar-excel'); ?>?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Excel exportado exitosamente');
                // Aquí podrías descargar el Excel
                console.log('Datos para Excel:', data.data);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al exportar Excel');
        });
}
</script>