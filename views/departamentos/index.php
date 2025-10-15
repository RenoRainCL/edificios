<?php
// 📁 views/departamentos/index.php
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
                                <i class="bi bi-house-door me-2"></i>Gestión de Departamentos
                            </h2>
                            <p class="card-text mb-0">
                                Administra los departamentos de tus edificios
                                <?php if (!empty($edificios) && count($edificios) > 0): ?>
                                    - <?= count($edificios) ?> edificio(s) asignado(s)
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="btn-group">
                                <a href="<?= $url->to('departamentos/crear') ?>" class="btn btn-light">
                                    <i class="bi bi-plus-circle"></i> Nuevo Departamento
                                </a>
                                <a href="<?= $url->to('edificios') ?>" class="btn btn-outline-light">
                                    <i class="bi bi-building"></i> Edificios
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mensajes Flash -->
    <?php if (!empty($flash_messages)): ?>
        <?php foreach ($flash_messages as $flash): ?>
            <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show">
                <i class="bi bi-<?= $flash['type'] == 'success' ? 'check-circle' : 'exclamation-triangle' ?> me-2"></i>
                <?= htmlspecialchars($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form id="filtroDepartamentos" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Edificio</label>
                            <select name="edificio_id" class="form-select" id="filtroEdificio">
                                <option value="">Todos los edificios</option>
                                <?php foreach ($edificios as $edificio): ?>
                                <option value="<?= $edificio['id'] ?>"><?= htmlspecialchars($edificio['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Estado</label>
                            <select name="estado" class="form-select" id="filtroEstado">
                                <option value="">Todos los estados</option>
                                <option value="habitado">Habitados</option>
                                <option value="no_habitado">No Habitados</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Piso</label>
                            <input type="number" name="piso" class="form-control" placeholder="Filtrar por piso" min="1">
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-funnel"></i> Filtrar
                            </button>
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise"></i> Limpiar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjetas de Resumen -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card card-hover border-start border-primary border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0" id="totalDepartamentos"><?= count($departamentos) ?></h4>
                            <small class="text-muted">Total Departamentos</small>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-house-door text-primary fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card card-hover border-start border-success border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0" id="totalHabitados">
                                <?= count(array_filter($departamentos, function($depto) { return $depto['is_habitado'] ?? true; })) ?>
                            </h4>
                            <small class="text-muted">Habitados</small>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-house-check text-success fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card card-hover border-start border-warning border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0" id="totalNoHabitados">
                                <?= count(array_filter($departamentos, function($depto) { return !($depto['is_habitado'] ?? true); })) ?>
                            </h4>
                            <small class="text-muted">No Habitados</small>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-house-x text-warning fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card card-hover border-start border-info border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0" id="totalEdificios"><?= count($edificios ?? []) ?></h4>
                            <small class="text-muted">Edificios</small>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-building text-info fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Departamentos -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-list-check me-2"></i>Lista de Departamentos
                    </h5>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-primary" onclick="recargarLista()">
                            <i class="bi bi-arrow-clockwise"></i> Actualizar
                        </button>
                        <a href="<?= $url->to('departamentos/crear') ?>" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-circle"></i> Nuevo
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0" id="tablaDepartamentos">
                            <thead class="table-light">
                                <tr>
                                    <th width="80">Número</th>
                                    <th>Edificio</th>
                                    <th>Propietario</th>
                                    <th width="100">Piso</th>
                                    <th width="120">m²</th>
                                    <th width="120">Porcentaje</th>
                                    <th width="100">Estado</th>
                                    <th width="120">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($departamentos)): ?>
                                    <?php foreach ($departamentos as $departamento): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($departamento['numero']) ?></strong>
                                        </td>
                                        <td>
                                            <div class="fw-bold"><?= htmlspecialchars($departamento['edificio_nombre'] ?? 'N/A') ?></div>
                                            <small class="text-muted">
                                                <?= htmlspecialchars($departamento['edificio_comuna'] ?? '') ?>
                                                <?= isset($departamento['edificio_region']) ? ', ' . htmlspecialchars($departamento['edificio_region']) : '' ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="fw-bold"><?= htmlspecialchars($departamento['propietario_nombre'] ?? 'No especificado') ?></div>
                                            <small class="text-muted">
                                                <?php if (isset($departamento['propietario_rut']) && !empty($departamento['propietario_rut'])): ?>
                                                    <?= htmlspecialchars($departamento['propietario_rut']) ?>
                                                <?php endif; ?>
                                            </small>
                                        </td>
                                        <td>
                                            <?php if (isset($departamento['piso']) && $departamento['piso']): ?>
                                                <span class="badge bg-light text-dark">Piso <?= htmlspecialchars($departamento['piso']) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (isset($departamento['metros_cuadrados']) && $departamento['metros_cuadrados']): ?>
                                                <span class="fw-bold"><?= htmlspecialchars($departamento['metros_cuadrados']) ?> m²</span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="fw-bold text-primary me-1">
                                                    <?= htmlspecialchars($departamento['porcentaje_copropiedad'] ?? 0) ?>%
                                                </span>
                                                <?php if ($departamento['porcentaje_calculado_auto'] ?? false): ?>
                                                    <i class="bi bi-robot text-success" title="Calculado automáticamente"></i>
                                                <?php else: ?>
                                                    <i class="bi bi-person-check text-info" title="Valor manual"></i>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= ($departamento['is_habitado'] ?? true) ? 'success' : 'secondary' ?>">
                                                <?= ($departamento['is_habitado'] ?? true) ? 'Habitado' : 'No Habitado' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= $url->to("departamentos/ver/{$departamento['id']}") ?>" 
                                                   class="btn btn-outline-primary" title="Ver detalle">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="<?= $url->to("departamentos/editar/{$departamento['id']}") ?>" 
                                                   class="btn btn-outline-warning" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <?php if ($departamento['is_habitado'] ?? true): ?>
                                                    <a href="<?= $url->to("departamentos/desactivar/{$departamento['id']}") ?>" 
                                                       class="btn btn-outline-secondary" 
                                                       title="Marcar como no habitado"
                                                       onclick="return confirm('¿Marcar departamento <?= htmlspecialchars($departamento['numero']) ?> como no habitado?')">
                                                        <i class="bi bi-house-x"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <button class="btn btn-outline-success" disabled title="Ya está no habitado">
                                                        <i class="bi bi-house-check"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="bi bi-inbox text-muted fs-1"></i>
                                            <p class="text-muted mt-2">No se encontraron departamentos</p>
                                            <a href="<?= $url->to('departamentos/crear') ?>" class="btn btn-primary">
                                                <i class="bi bi-plus-circle"></i> Crear Primer Departamento
                                            </a>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php if (!empty($departamentos) && count($departamentos) > 10): ?>
                <div class="card-footer bg-transparent">
                    <nav aria-label="Paginación">
                        <ul class="pagination justify-content-center mb-0">
                            <li class="page-item disabled">
                                <a class="page-link" href="#">Anterior</a>
                            </li>
                            <li class="page-item active">
                                <a class="page-link" href="#">1</a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="#">2</a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="#">3</a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="#">Siguiente</a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filtros
    const filtroForm = document.getElementById('filtroDepartamentos');
    if (filtroForm) {
        filtroForm.addEventListener('submit', function(e) {
            e.preventDefault();
            aplicarFiltros();
        });
    }

    // Búsqueda en tiempo real
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            aplicarFiltros();
        });
    }
});

function aplicarFiltros() {
    const formData = new FormData(document.getElementById('filtroDepartamentos'));
    const params = new URLSearchParams(formData);
    
    // Simular filtrado (en una implementación real sería AJAX)
    const tabla = document.getElementById('tablaDepartamentos');
    const filas = tabla.querySelectorAll('tbody tr');
    
    let visibleCount = 0;
    
    filas.forEach(fila => {
        if (fila.cells.length > 1) { // Evitar fila de "no results"
            const edificio = fila.cells[1].textContent.toLowerCase();
            const estado = fila.cells[6].textContent.toLowerCase();
            const piso = fila.cells[3].textContent.toLowerCase();
            
            const filtroEdificio = document.getElementById('filtroEdificio').value;
            const filtroEstado = document.getElementById('filtroEstado').value;
            const filtroPiso = document.querySelector('input[name="piso"]').value;
            
            let mostrar = true;
            
            if (filtroEdificio && !edificio.includes(filtroEdificio.toLowerCase())) {
                mostrar = false;
            }
            
            if (filtroEstado === 'habitado' && !estado.includes('habitado')) {
                mostrar = false;
            }
            
            if (filtroEstado === 'no_habitado' && !estado.includes('no habitado')) {
                mostrar = false;
            }
            
            if (filtroPiso && !piso.includes(filtroPiso)) {
                mostrar = false;
            }
            
            if (mostrar) {
                fila.style.display = '';
                visibleCount++;
            } else {
                fila.style.display = 'none';
            }
        }
    });
    
    // Actualizar contadores
    document.getElementById('totalDepartamentos').textContent = visibleCount;
}

function recargarLista() {
    location.reload();
}

// Inicializar tooltips
document.addEventListener('DOMContentLoaded', function() {
    const tooltips = document.querySelectorAll('[title]');
    tooltips.forEach(element => {
        new bootstrap.Tooltip(element);
    });
});
</script>

<style>
.card-hover:hover {
    transform: translateY(-2px);
    transition: transform 0.2s ease-in-out;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.border-4 {
    border-width: 4px !important;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
    background-color: #f8f9fa;
}

.btn-group-sm > .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

.badge {
    font-size: 0.7em;
    font-weight: 500;
}
</style>