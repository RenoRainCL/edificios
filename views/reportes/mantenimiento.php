<?php
// 📁 views/reportes/mantenimiento.php
?>
<div class="container-fluid">
    <!-- KPIs de Mantenimiento -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-hover border-start border-primary border-4">
                <div class="card-body text-center">
                    <h3 class="text-primary"><?= $reporte['estadisticas']['total_solicitudes'] ?></h3>
                    <small class="text-muted">Total Solicitudes</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-hover border-start border-success border-4">
                <div class="card-body text-center">
                    <h3 class="text-success"><?= $reporte['estadisticas']['solicitudes_completadas'] ?></h3>
                    <small class="text-muted">Completadas</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-hover border-start border-warning border-4">
                <div class="card-body text-center">
                    <h3 class="text-warning"><?= $reporte['estadisticas']['solicitudes_pendientes'] ?></h3>
                    <small class="text-muted">Pendientes</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-hover border-start border-info border-4">
                <div class="card-body text-center">
                    <h3 class="text-info"><?= number_format($reporte['estadisticas']['costo_total'], 0, ',', '.') ?></h3>
                    <small class="text-muted">Costo Total</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-hover border-start border-danger border-4">
                <div class="card-body text-center">
                    <h3 class="text-danger"><?= $reporte['estadisticas']['tiempo_promedio'] ?>d</h3>
                    <small class="text-muted">Tiempo Promedio</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card card-hover border-start border-secondary border-4">
                <div class="card-body text-center">
                    <h3 class="text-secondary"><?= $reporte['estadisticas']['satisfaccion_promedio'] ?>%</h3>
                    <small class="text-muted">Satisfacción</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos de Mantenimiento -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card card-hover">
                <div class="card-header bg-transparent">
                    <h6 class="m-0 fw-bold">Solicitudes por Tipo</h6>
                </div>
                <div class="card-body">
                    <canvas id="solicitudesTipoChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card card-hover">
                <div class="card-header bg-transparent">
                    <h6 class="m-0 fw-bold">Costos por Tipo de Mantenimiento</h6>
                </div>
                <div class="card-body">
                    <canvas id="costosTipoChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Proveedores -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card card-hover">
                <div class="card-header bg-transparent">
                    <h6 class="m-0 fw-bold">Desempeño de Proveedores</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Proveedor</th>
                                    <th>Total Servicios</th>
                                    <th>Costo Total</th>
                                    <th>Tiempo Promedio</th>
                                    <th>Calificación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reporte['proveedores'] as $proveedor): ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($proveedor['proveedor']) ?></td>
                                    <td><?= $proveedor['total_servicios'] ?></td>
                                    <td>$<?= number_format($proveedor['costo_total'], 0, ',', '.') ?></td>
                                    <td>
                                        <span class="badge bg-<?= $proveedor['tiempo_promedio'] <= 3 ? 'success' : ($proveedor['tiempo_promedio'] <= 7 ? 'warning' : 'danger') ?>">
                                            <?= $proveedor['tiempo_promedio'] ?> días
                                        </span>
                                    </td>
                                    <td>
                                        <div class="rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="bi bi-star<?= $i <= $proveedor['calificacion'] ? '-fill text-warning' : '' ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" 
                                                onclick="verDetallesProveedor(<?= $proveedor['id'] ?>)">
                                            <i class="bi bi-eye"></i>
                                        </button>
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
</div>

<script>
// Gráficos de mantenimiento
const solicitudesTipoChart = new Chart(
    document.getElementById('solicitudesTipoChart'),
    <?= json_encode(ChartGenerator::generarChartJSConfig('doughnut', [
        'labels' => ['Preventivo', 'Correctivo', 'Urgente', 'Mejora'],
        'datasets' => [[
            'data' => [
                $reporte['estadisticas']['preventivo'],
                $reporte['estadisticas']['correctivo'],
                $reporte['estadisticas']['urgente'],
                $reporte['estadisticas']['mejora']
            ],
            'backgroundColor' => ['#36A2EB', '#FF6384', '#FFCE56', '#4BC0C0']
        ]]
    ])) ?>
);
</script>