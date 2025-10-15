<?php
// 📁 views/finanzas/mis_pagos.php
?>
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-credit-card me-2"></i>Mis Pagos
            </h1>
            <p class="text-muted">Historial y estado de mis pagos de gastos comunes</p>
        </div>
        <a href="<?= $url->to('finanzas/mis-pagos/registrar') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Registrar Pago
        </a>
    </div>

    <?php if (isset($mensaje)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <!-- Resumen rápido -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-hover border-start border-primary border-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                Total Pagado
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                $<?= number_format(array_sum(array_map(function($p) { 
                                    return $p['estado'] === 'pagado' ? $p['monto'] : 0; 
                                }, $pagos)), 0, ',', '.') ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-check-circle fa-2x text-gray-300"></i>
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
                                <?= count(array_filter($pagos, function($p) { 
                                    return $p['estado'] === 'pendiente'; 
                                })) ?>
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
                                En Aprobación
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                <?= count(array_filter($pagos, function($p) { 
                                    return $p['estado_aprobacion'] === 'pendiente'; 
                                })) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-hourglass-split fa-2x text-gray-300"></i>
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
                                <?= count(array_filter($pagos, function($p) { 
                                    return $p['estado'] === 'atrasado'; 
                                })) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de pagos -->
    <div class="card card-hover">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold">Mis Pagos</h6>
            <div class="btn-group">
                <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-filter me-1"></i>Filtrar
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="filtrarPagos('todos')">Todos</a></li>
                    <li><a class="dropdown-item" href="#" onclick="filtrarPagos('pendiente')">Pendientes</a></li>
                    <li><a class="dropdown-item" href="#" onclick="filtrarPagos('aprobacion')">En Aprobación</a></li>
                    <li><a class="dropdown-item" href="#" onclick="filtrarPagos('pagado')">Pagados</a></li>
                    <li><a class="dropdown-item" href="#" onclick="filtrarPagos('atrasado')">Atrasados</a></li>
                </ul>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (empty($pagos)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-credit-card display-1 text-muted"></i>
                    <h5 class="text-muted mt-3">No hay pagos registrados</h5>
                    <p class="text-muted">No se encontraron pagos para tus departamentos</p>
                    <a href="<?= $url->to('finanzas/mis-pagos/registrar') ?>" class="btn btn-primary mt-3">
                        <i class="bi bi-plus-circle me-2"></i>Registrar Primer Pago
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="tablaPagos">
                        <thead class="table-light">
                            <tr>
                                <th>Edificio/Depto</th>
                                <th>Gasto Común</th>
                                <th>Período</th>
                                <th>Monto</th>
                                <th>Estado Pago</th>
                                <th>Aprobación</th>
                                <th>Vencimiento</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pagos as $pago): ?>
                                <?php
                                $fechaVencimiento = new DateTime($pago['fecha_vencimiento']);
                                $hoy = new DateTime();
                                $estaVencido = $fechaVencimiento < $hoy && $pago['estado'] === 'pendiente';
                                ?>
                                <tr class="fila-pago" data-estado="<?= $pago['estado'] ?>" data-aprobacion="<?= $pago['estado_aprobacion'] ?>">
                                    <td>
                                        <div>
                                            <small class="text-muted"><?= htmlspecialchars($pago['edificio_nombre']) ?></small><br>
                                            <strong>Depto. <?= htmlspecialchars($pago['numero']) ?></strong>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($pago['gasto_nombre']) ?></td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            <?= date('m/Y', strtotime($pago['periodo'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong>$<?= number_format($pago['monto'], 0, ',', '.') ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $pago['estado_color'] ?>">
                                            <?= ucfirst($pago['estado']) ?>
                                            <?php if ($estaVencido): ?>
                                                <i class="bi bi-exclamation-triangle ms-1"></i>
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($pago['estado_aprobacion'] === 'pendiente'): ?>
                                            <span class="badge bg-warning">
                                                <i class="bi bi-hourglass-split me-1"></i>Pendiente
                                            </span>
                                        <?php elseif ($pago['estado_aprobacion'] === 'aprobado'): ?>
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle me-1"></i>Aprobado
                                            </span>
                                        <?php elseif ($pago['estado_aprobacion'] === 'rechazado'): ?>
                                            <span class="badge bg-danger">
                                                <i class="bi bi-x-circle me-1"></i>Rechazado
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="<?= $estaVencido ? 'text-danger fw-bold' : 'text-muted' ?>">
                                            <?= date('d/m/Y', strtotime($pago['fecha_vencimiento'])) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <?php if ($pago['estado'] === 'pendiente' && $pago['estado_aprobacion'] === 'pendiente'): ?>
                                                <a href="<?= $url->to("finanzas/mis-pagos/registrar?pago_id={$pago['id']}") ?>" 
                                                   class="btn btn-outline-primary" 
                                                   title="Registrar pago">
                                                    <i class="bi bi-cash-coin"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($pago['numero_comprobante'])): ?>
                                                <button type="button" class="btn btn-outline-info" 
                                                        title="Ver comprobante: <?= htmlspecialchars($pago['numero_comprobante']) ?>"
                                                        onclick="mostrarComprobante(<?= $pago['id'] ?>)">
                                                    <i class="bi bi-receipt"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if ($pago['estado_aprobacion'] === 'rechazado' && !empty($pago['rejection_reason'])): ?>
                                                <button type="button" class="btn btn-outline-warning" 
                                                        title="Ver motivo de rechazo"
                                                        onclick="mostrarMotivoRechazo('<?= htmlspecialchars($pago['rejection_reason']) ?>')">
                                                    <i class="bi bi-chat-dots"></i>
                                                </button>
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
</div>

<!-- Modal para mostrar comprobante -->
<div class="modal fade" id="modalComprobante" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Información del Comprobante</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalComprobanteBody">
                <!-- Contenido dinámico -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para mostrar motivo de rechazo -->
<div class="modal fade" id="modalMotivoRechazo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Motivo de Rechazo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalMotivoRechazoBody">
                <!-- Contenido dinámico -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
// Filtrar pagos
function filtrarPagos(tipo) {
    const filas = document.querySelectorAll('.fila-pago');
    filas.forEach(fila => {
        let mostrar = false;
        
        switch(tipo) {
            case 'todos':
                mostrar = true;
                break;
            case 'pendiente':
                mostrar = fila.dataset.estado === 'pendiente';
                break;
            case 'aprobacion':
                mostrar = fila.dataset.aprobacion === 'pendiente';
                break;
            case 'pagado':
                mostrar = fila.dataset.estado === 'pagado';
                break;
            case 'atrasado':
                mostrar = fila.dataset.estado === 'atrasado';
                break;
        }
        
        fila.style.display = mostrar ? '' : 'none';
    });
}

// Mostrar información del comprobante
function mostrarComprobante(pagoId) {
    // En una implementación real, aquí harías una petición AJAX para obtener los datos del pago
    const pago = <?= json_encode($pagos) ?>.find(p => p.id == pagoId);
    
    if (pago) {
        const contenido = `
            <div class="row">
                <div class="col-6"><strong>N° Comprobante:</strong></div>
                <div class="col-6">${pago.numero_comprobante || 'No especificado'}</div>
                
                <div class="col-6"><strong>Referencia:</strong></div>
                <div class="col-6">${pago.referencia_bancaria || 'No especificada'}</div>
                
                <div class="col-6"><strong>Método:</strong></div>
                <div class="col-6">${pago.metodo_pago ? pago.metodo_pago.charAt(0).toUpperCase() + pago.metodo_pago.slice(1) : 'No especificado'}</div>
                
                <div class="col-6"><strong>Fecha Pago:</strong></div>
                <div class="col-6">${pago.fecha_pago ? new Date(pago.fecha_pago).toLocaleDateString('es-CL') : 'No registrada'}</div>
                
                ${pago.observaciones ? `
                <div class="col-12 mt-3">
                    <strong>Observaciones:</strong><br>
                    <p class="mt-1">${pago.observaciones}</p>
                </div>
                ` : ''}
            </div>
        `;
        
        document.getElementById('modalComprobanteBody').innerHTML = contenido;
        new bootstrap.Modal(document.getElementById('modalComprobante')).show();
    }
}

// Mostrar motivo de rechazo
function mostrarMotivoRechazo(motivo) {
    document.getElementById('modalMotivoRechazoBody').innerHTML = `
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Motivo proporcionado por el administrador:</strong>
        </div>
        <p>${motivo}</p>
    `;
    new bootstrap.Modal(document.getElementById('modalMotivoRechazo')).show();
}

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    // Ordenar por fecha de vencimiento más próxima primero
    const tabla = document.getElementById('tablaPagos');
    if (tabla) {
        // Implementación básica de ordenación
    }
});
</script>