<?php
// 📁 views/departamentos/ver.php
?>
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="card-title mb-1">
                                <i class="bi bi-house-door me-2"></i>Detalle de Departamento
                            </h2>
                            <p class="card-text mb-0">
                                <?php echo htmlspecialchars($departamento['edificio_nombre'] ?? 'Edificio no disponible'); ?> - 
                                Departamento <?php echo htmlspecialchars($departamento['numero'] ?? 'N/A'); ?>
                            </p>
                            <?php if (isset($departamento['edificio_direccion']) && !empty($departamento['edificio_direccion'])) { ?>
                                <small class="opacity-75">
                                    <i class="bi bi-geo-alt"></i> 
                                    <?php echo htmlspecialchars($departamento['edificio_direccion']); ?>
                                </small>
                            <?php } ?>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="btn-group">
                                <a href="<?php echo $url->to('departamentos'); ?>" class="btn btn-light">
                                    <i class="bi bi-arrow-left"></i> Volver
                                </a>
                                <a href="<?php echo $url->to("departamentos/editar/{$departamento['id']}"); ?>" class="btn btn-outline-light">
                                    <i class="bi bi-pencil"></i> Editar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mensajes Flash -->
    <?php if (!empty($flash_messages)) { ?>
        <?php foreach ($flash_messages as $flash) { ?>
            <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show">
                <i class="bi bi-<?php echo $flash['type'] == 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                <?php echo htmlspecialchars($flash['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php } ?>
    <?php } ?>

    <div class="row">
        <!-- Información del Departamento -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-info-circle me-2"></i>Información del Departamento
                    </h6>
                    <span class="badge bg-<?php echo ($departamento['is_habitado'] ?? 1) ? 'success' : 'secondary'; ?>">
                        <?php echo ($departamento['is_habitado'] ?? 1) ? 'Habitado' : 'No Habitado'; ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th class="text-muted">Número:</th>
                                    <td><?php echo htmlspecialchars($departamento['numero'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Piso:</th>
                                    <td>
                                        <?php if (isset($departamento['piso']) && $departamento['piso']) { ?>
                                            <?php echo htmlspecialchars($departamento['piso']); ?>
                                        <?php } else { ?>
                                            <span class="text-muted">No especificado</span>
                                        <?php } ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Metros Cuadrados:</th>
                                    <td>
                                        <?php if (isset($departamento['metros_cuadrados']) && $departamento['metros_cuadrados']) { ?>
                                            <?php echo htmlspecialchars($departamento['metros_cuadrados']); ?> m²
                                        <?php } else { ?>
                                            <span class="text-muted">No especificado</span>
                                        <?php } ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Orientación:</th>
                                    <td>
                                        <?php if (isset($departamento['orientacion']) && $departamento['orientacion']) { ?>
                                            <?php echo htmlspecialchars($departamento['orientacion']); ?>
                                        <?php } else { ?>
                                            <span class="text-muted">No especificado</span>
                                        <?php } ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th class="text-muted">Dormitorios:</th>
                                    <td><?php echo htmlspecialchars($departamento['dormitorios'] ?? 1); ?></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Baños:</th>
                                    <td><?php echo htmlspecialchars($departamento['banos'] ?? 1); ?></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Estacionamientos:</th>
                                    <td><?php echo htmlspecialchars($departamento['estacionamientos'] ?? 0); ?></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Bodegas:</th>
                                    <td><?php echo htmlspecialchars($departamento['bodegas'] ?? 0); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Porcentaje de Copropiedad -->
                    <div class="mt-4 p-3 bg-light rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Porcentaje de Copropiedad</h6>
                                <p class="mb-0">
                                    <span class="h4 text-primary"><?php echo htmlspecialchars($departamento['porcentaje_copropiedad'] ?? 0); ?>%</span>
                                    <?php if ($departamento['porcentaje_calculado_auto'] ?? false) { ?>
                                        <small class="text-success ms-2">
                                            <i class="bi bi-robot"></i> Calculado automáticamente
                                        </small>
                                    <?php } else { ?>
                                        <small class="text-info ms-2">
                                            <i class="bi bi-person-check"></i> Valor manual
                                        </small>
                                    <?php } ?>
                                </p>
                            </div>
                            <div class="text-end">
                                <small class="text-muted d-block">Participación en</small>
                                <small class="text-muted d-block">gastos comunes</small>
                            </div>
                        </div>
                    </div>

                    <?php if (isset($departamento['observaciones']) && !empty($departamento['observaciones'])) { ?>
                        <div class="mt-3">
                            <h6 class="text-muted">Observaciones</h6>
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($departamento['observaciones'])); ?></p>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <!-- Información del Edificio -->
            <div class="card mt-4">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-building me-2"></i>Información del Edificio
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th class="text-muted" width="40%">Edificio:</th>
                            <td><?php echo htmlspecialchars($departamento['edificio_nombre'] ?? 'N/A'); ?></td>
                        </tr>
                        <?php if (isset($departamento['edificio_direccion']) && !empty($departamento['edificio_direccion'])) { ?>
                        <tr>
                            <th class="text-muted">Dirección:</th>
                            <td><?php echo htmlspecialchars($departamento['edificio_direccion']); ?></td>
                        </tr>
                        <?php } ?>
                        <?php if (isset($departamento['edificio_comuna']) && !empty($departamento['edificio_comuna'])) { ?>
                        <tr>
                            <th class="text-muted">Comuna:</th>
                            <td><?php echo htmlspecialchars($departamento['edificio_comuna']); ?></td>
                        </tr>
                        <?php } ?>
                        <?php if (isset($departamento['edificio_region']) && !empty($departamento['edificio_region'])) { ?>
                        <tr>
                            <th class="text-muted">Región:</th>
                            <td><?php echo htmlspecialchars($departamento['edificio_region']); ?></td>
                        </tr>
                        <?php } ?>
                    </table>
                </div>
            </div>
        </div>

        <!-- Información del Propietario -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-person me-2"></i>Información del Propietario
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th class="text-muted" width="40%">Nombre:</th>
                            <td><?php echo htmlspecialchars($departamento['propietario_nombre'] ?? 'No especificado'); ?></td>
                        </tr>
                        <?php if (isset($departamento['propietario_rut']) && !empty($departamento['propietario_rut'])) { ?>
                        <tr>
                            <th class="text-muted">RUT:</th>
                            <td><?php echo htmlspecialchars($departamento['propietario_rut']); ?></td>
                        </tr>
                        <?php } ?>
                        <?php if (isset($departamento['propietario_email']) && !empty($departamento['propietario_email'])) { ?>
                        <tr>
                            <th class="text-muted">Email:</th>
                            <td>
                                <a href="mailto:<?php echo htmlspecialchars($departamento['propietario_email']); ?>">
                                    <?php echo htmlspecialchars($departamento['propietario_email']); ?>
                                </a>
                            </td>
                        </tr>
                        <?php } ?>
                        <?php if (isset($departamento['propietario_telefono']) && !empty($departamento['propietario_telefono'])) { ?>
                        <tr>
                            <th class="text-muted">Teléfono:</th>
                            <td>
                                <a href="tel:<?php echo htmlspecialchars($departamento['propietario_telefono']); ?>">
                                    <?php echo htmlspecialchars($departamento['propietario_telefono']); ?>
                                </a>
                            </td>
                        </tr>
                        <?php } ?>
                    </table>

                    <!-- Información de Arrendatario (si existe) -->
                    <?php if (isset($departamento['arrendatario_nombre']) && !empty($departamento['arrendatario_nombre'])) { ?>
                    <div class="mt-4 pt-3 border-top">
                        <h6 class="text-muted mb-3">
                            <i class="bi bi-person-badge me-2"></i>Información del Arrendatario
                        </h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th class="text-muted" width="40%">Nombre:</th>
                                <td><?php echo htmlspecialchars($departamento['arrendatario_nombre']); ?></td>
                            </tr>
                            <?php if (isset($departamento['arrendatario_rut']) && !empty($departamento['arrendatario_rut'])) { ?>
                            <tr>
                                <th class="text-muted">RUT:</th>
                                <td><?php echo htmlspecialchars($departamento['arrendatario_rut']); ?></td>
                            </tr>
                            <?php } ?>
                            <?php if (isset($departamento['arrendatario_email']) && !empty($departamento['arrendatario_email'])) { ?>
                            <tr>
                                <th class="text-muted">Email:</th>
                                <td>
                                    <a href="mailto:<?php echo htmlspecialchars($departamento['arrendatario_email']); ?>">
                                        <?php echo htmlspecialchars($departamento['arrendatario_email']); ?>
                                    </a>
                                </td>
                            </tr>
                            <?php } ?>
                            <?php if (isset($departamento['arrendatario_telefono']) && !empty($departamento['arrendatario_telefono'])) { ?>
                            <tr>
                                <th class="text-muted">Teléfono:</th>
                                <td>
                                    <a href="tel:<?php echo htmlspecialchars($departamento['arrendatario_telefono']); ?>">
                                        <?php echo htmlspecialchars($departamento['arrendatario_telefono']); ?>
                                    </a>
                                </td>
                            </tr>
                            <?php } ?>
                        </table>
                    </div>
                    <?php } ?>
                </div>
            </div>

            <!-- Gastos y Pagos Recientes -->
            <div class="card mt-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-receipt me-2"></i>Gastos Comunes Recientes
                    </h6>
                    <span class="badge bg-primary">
                        <?php echo count($gastos_departamento ?? []); ?> registros
                    </span>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($gastos_departamento)) { ?>
                        <div class="list-group list-group-flush">
                            <?php foreach (array_slice($gastos_departamento, 0, 5) as $gasto) { ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($gasto['nombre'] ?? 'Gasto'); ?></h6>
                                    <small class="text-<?php echo ($gasto['estado_pago'] ?? 'pendiente') == 'pagado' ? 'success' :
                                        (($gasto['estado_pago'] ?? 'pendiente') == 'atrasado' ? 'danger' : 'warning');
                                ?>">
                                        <?php echo ucfirst($gasto['estado_pago'] ?? 'pendiente'); ?>
                                    </small>
                                </div>
                                <p class="mb-1 small">
                                    Período: <?php echo date('m/Y', strtotime($gasto['periodo'] ?? '')); ?> | 
                                    Monto: $<?php echo number_format($gasto['monto_depto'] ?? 0, 0, ',', '.'); ?>
                                </p>
                                <small class="text-muted">
                                    <?php echo htmlspecialchars($gasto['edificio_nombre'] ?? ''); ?>
                                    <?php if (isset($gasto['fecha_pago']) && $gasto['fecha_pago']) { ?>
                                        | Pagado: <?php echo date('d/m/Y', strtotime($gasto['fecha_pago'])); ?>
                                    <?php } ?>
                                </small>
                            </div>
                            <?php } ?>
                        </div>
                        <?php if (count($gastos_departamento) > 5) { ?>
                            <div class="card-footer text-center">
                                <a href="<?php echo $url->to('finanzas/gastos-comunes'); ?>" class="btn btn-sm btn-outline-primary">
                                    Ver todos los gastos
                                </a>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="text-center p-4">
                            <i class="bi bi-receipt text-muted fs-4"></i>
                            <p class="text-muted mt-2 mb-0">No hay gastos registrados</p>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <!-- Historial de Prorrateo -->
            <?php if (!empty($historial_prorrateo)) { ?>
            <div class="card mt-4">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-clock-history me-2"></i>Historial de Prorrateo
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php foreach (array_slice($historial_prorrateo, 0, 3) as $historial) { ?>
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <small class="text-muted"><?php echo htmlspecialchars($historial['justificacion'] ?? 'Modificación'); ?></small>
                                <small class="text-muted">
                                    <?php echo date('d/m/Y H:i', strtotime($historial['created_at'] ?? '')); ?>
                                </small>
                            </div>
                            <small>
                                Por: <?php echo htmlspecialchars($historial['usuario_nombre'] ?? 'Sistema'); ?>
                            </small>
                        </div>
                        <?php } ?>
                    </div>
                    <?php if (count($historial_prorrateo) > 3) { ?>
                        <div class="card-footer text-center">
                            <small class="text-muted">
                                +<?php echo count($historial_prorrateo) - 3; ?> modificaciones más
                            </small>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>

    <!-- Acciones Rápidas -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Acciones Rápidas</h6>
                            <p class="text-muted mb-0 small">Gestiona este departamento</p>
                        </div>
                        <div class="btn-group">
                            <a href="<?php echo $url->to("departamentos/editar/{$departamento['id']}"); ?>" class="btn btn-primary">
                                <i class="bi bi-pencil"></i> Editar Departamento
                            </a>
                            <a href="<?php echo $url->to('finanzas/gastos-comunes'); ?>" class="btn btn-outline-primary">
                                <i class="bi bi-receipt"></i> Ver Gastos
                            </a>
                            <?php if ($departamento['is_habitado'] ?? 1) { ?>
                                <a href="<?php echo $url->to("departamentos/desactivar/{$departamento['id']}"); ?>" 
                                   class="btn btn-outline-warning"
                                   onclick="return confirm('¿Marcar este departamento como no habitado?')">
                                    <i class="bi bi-house-x"></i> No Habitado
                                </a>
                            <?php } else { ?>
                                <button class="btn btn-outline-success" disabled>
                                    <i class="bi bi-house-check"></i> No Habitado
                                </button>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.table-borderless th {
    font-weight: 500;
}

.list-group-item {
    border-left: none;
    border-right: none;
}

.list-group-item:first-child {
    border-top: none;
}

.list-group-item:last-child {
    border-bottom: none;
}

.badge {
    font-size: 0.75em;
}
</style>