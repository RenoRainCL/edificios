<?php
// 📁 views/finanzas/pagos.php
?>
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-credit-card me-2"></i>Gestión de Pagos
            </h1>
            <p class="text-muted">Pagos pendientes y atrasados</p>
        </div>
        <div class="btn-group">
            <a href="<?php echo $url->to('finanzas/pagos/registrar'); ?>" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Registrar Pago
            </a>
            <a href="<?php echo $url->to('finanzas/estado-pagos'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-graph-up me-2"></i>Estado General
            </a>
        </div>
    </div>

    <!-- Mensajes -->
    <?php if (isset($_SESSION['success_message'])) { ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>
            <?php echo $_SESSION['success_message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php } ?>

    <!-- Filtros -->
    <div class="card card-hover mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small text-uppercase text-muted fw-bold">Edificio</label>
                    <select name="edificio_id" class="form-select">
                        <option value="">Todos los edificios</option>
                        <?php foreach ($edificios as $edificio) { ?>
                            <option value="<?php echo $edificio['id']; ?>" 
                                <?php echo isset($_GET['edificio_id']) && $_GET['edificio_id'] == $edificio['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($edificio['nombre']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-uppercase text-muted fw-bold">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">Todos los estados</option>
                        <option value="pendiente" <?php echo isset($_GET['estado']) && $_GET['estado'] == 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                        <option value="atrasado" <?php echo isset($_GET['estado']) && $_GET['estado'] == 'atrasado' ? 'selected' : ''; ?>>Atrasado</option>
                        <option value="pagado" <?php echo isset($_GET['estado']) && $_GET['estado'] == 'pagado' ? 'selected' : ''; ?>>Pagado</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-uppercase text-muted fw-bold">Período</label>
                    <input type="month" name="periodo" class="form-control" 
                           value="<?php echo $_GET['periodo'] ?? date('Y-m'); ?>">
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
            <div class="card card-hover border-start border-warning border-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">
                                Pendientes
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                <?php echo count(array_filter($pagos, fn ($p) => $p['estado'] === 'pendiente')); ?>
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
            <div class="card card-hover border-start border-danger border-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-danger text-uppercase mb-1">
                                Atrasados
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                <?php echo count(array_filter($pagos, fn ($p) => $p['estado'] === 'atrasado')); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-exclamation-triangle fa-2x text-gray-300"></i>
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
                                Monto Pendiente
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                $<?php echo number_format(array_sum(array_column($pagos, 'monto')), 0, ',', '.'); ?>
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
            <div class="card card-hover border-start border-info border-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-info text-uppercase mb-1">
                                Vencen Pronto
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                <?php echo count(array_filter($pagos, fn ($p) => strtotime($p['fecha_vencimiento']) <= strtotime('+3 days')
                                    && $p['estado'] === 'pendiente'
                                )); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-calendar-x fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de pagos -->
    <div class="card card-hover">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold">Pagos Pendientes y Atrasados</h6>
            <span class="badge bg-primary">
                Total: <?php echo count($pagos); ?> pagos
            </span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($pagos)) { ?>
                <div class="text-center py-5">
                    <i class="bi bi-check-circle display-1 text-success"></i>
                    <h5 class="text-success mt-3">¡Excelente!</h5>
                    <p class="text-muted">No hay pagos pendientes o atrasados</p>
                    <a href="<?php echo $url->to('finanzas/gastos-comunes'); ?>" class="btn btn-outline-primary">
                        <i class="bi bi-receipt me-2"></i>Ver Gastos Comunes
                    </a>
                </div>
            <?php } else { ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Departamento</th>
                                <th>Edificio</th>
                                <th>Gasto</th>
                                <th>Período</th>
                                <th>Monto</th>
                                <th>Vencimiento</th>
                                <th>Estado</th>
                                <th>Días</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pagos as $pago) { ?>
                                <?php
                                $diasVencimiento = floor((time() - strtotime($pago['fecha_vencimiento'])) / (60 * 60 * 24));
                                $esAtrasado = $diasVencimiento > 0 && $pago['estado'] === 'pendiente';
                                ?>
                                <tr class="<?php echo $esAtrasado ? 'table-danger' : ''; ?>">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-house-door text-muted me-2"></i>
                                            <div>
                                                <div><?php echo htmlspecialchars($pago['numero']); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($pago['propietario_nombre']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($pago['edificio_nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($pago['gasto_nombre']); ?></td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            <?php echo date('m/Y', strtotime($pago['periodo'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong>$<?php echo number_format($pago['monto'], 0, ',', '.'); ?></strong>
                                    </td>
                                    <td>
                                        <?php echo date('d/m/Y', strtotime($pago['fecha_vencimiento'])); ?>
                                        <?php if ($esAtrasado) { ?>
                                            <br><small class="text-danger">Hace <?php echo $diasVencimiento; ?> días</small>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $pago['estado_color']; ?>">
                                            <?php echo ucfirst($pago['estado']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($esAtrasado) { ?>
                                            <span class="badge bg-danger">+<?php echo $diasVencimiento; ?>d</span>
                                        <?php } elseif ($pago['estado'] === 'pendiente') { ?>
                                            <?php
                                            $diasRestantes = floor((strtotime($pago['fecha_vencimiento']) - time()) / (60 * 60 * 24));
                                            if ($diasRestantes <= 3 && $diasRestantes >= 0) {
                                                echo '<span class="badge bg-warning">'.$diasRestantes.'d</span>';
                                            } elseif ($diasRestantes > 3) {
                                                echo '<span class="badge bg-success">'.$diasRestantes.'d</span>';
                                            }
                                            ?>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <form method="POST" 
                                                  action="/proyectos/edificios/finanzas/pagos/marcar-pagado/<?php echo $pago['id']; ?>" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('¿Marcar este pago como pagado?')">
                                                <button type="submit" class="btn btn-outline-success" title="Marcar como pagado">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                            </form>
                                            <a href="<?php echo $url->to('finanzas/pagos/registrar'); ?>?pago_id=<?php echo $pago['id']; ?>') ?>" 
                                               class="btn btn-outline-primary" 
                                               title="Registrar pago manual">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>
        </div>
    </div>
</div>