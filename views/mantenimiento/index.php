<?php
// Vista principal de mantenimiento
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Gestión de Mantenimiento</h1>
                <a href="<?php echo $this->url('mantenimiento/crear'); ?>" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Nueva Solicitud
                </a>
            </div>
            
            <!-- Filtros y Estadísticas -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h4 class="text-primary"><?= $estadisticas['total'] ?? 0 ?></h4>
                                        <small class="text-muted">Total Solicitudes</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h4 class="text-warning"><?= $estadisticas['pendientes'] ?? 0 ?></h4>
                                        <small class="text-muted">Pendientes</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h4 class="text-danger"><?= $estadisticas['urgentes'] ?? 0 ?></h4>
                                        <small class="text-muted">Urgentes</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h4 class="text-success">$<?= number_format($estadisticas['total_gastado'] ?? 0, 0, ',', '.') ?></h4>
                                        <small class="text-muted">Total Gastado</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <label class="form-label"><strong>Filtrar por Edificio</strong></label>
                            <select class="form-select" id="filtro-edificio">
                                <option value="todos" <?= (empty($filtro_edificio_actual) || $filtro_edificio_actual === 'todos') ? 'selected' : '' ?>>
                                    📊 Todos los Edificios
                                </option>
                                <?php foreach ($edificios as $edificio): ?>
                                <option value="<?= $edificio['id'] ?>" 
                                        <?= ($filtro_edificio_actual == $edificio['id']) ? 'selected' : '' ?>>
                                    🏢 <?= htmlspecialchars($edificio['nombre']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            
                            <!-- Filtros adicionales (opcionales) -->
                            <div class="row mt-2">
                                <div class="col-6">
                                    <select class="form-select form-select-sm" id="filtro-estado" onchange="aplicarFiltros()">
                                        <option value="">Todos los estados</option>
                                        <option value="pendiente" <?= ($_GET['estado'] ?? '') == 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                        <option value="en_proceso" <?= ($_GET['estado'] ?? '') == 'en_proceso' ? 'selected' : '' ?>>En Proceso</option>
                                        <option value="completado" <?= ($_GET['estado'] ?? '') == 'completado' ? 'selected' : '' ?>>Completado</option>
                                        <option value="cancelado" <?= ($_GET['estado'] ?? '') == 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <select class="form-select form-select-sm" id="filtro-prioridad" onchange="aplicarFiltros()">
                                        <option value="">Todas las prioridades</option>
                                        <option value="urgente" <?= ($_GET['prioridad'] ?? '') == 'urgente' ? 'selected' : '' ?>>Urgente</option>
                                        <option value="alta" <?= ($_GET['prioridad'] ?? '') == 'alta' ? 'selected' : '' ?>>Alta</option>
                                        <option value="media" <?= ($_GET['prioridad'] ?? '') == 'media' ? 'selected' : '' ?>>Media</option>
                                        <option value="baja" <?= ($_GET['prioridad'] ?? '') == 'baja' ? 'selected' : '' ?>>Baja</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <!-- Lista de Mantenimientos -->
            <div class="card">
                <div class="card-body">
                    <?php if (empty($mantenimientos)) { ?>
                        <div class="text-center py-5">
                            <i class="bi bi-tools display-1 text-muted"></i>
                            <h4 class="text-muted mt-3">No hay solicitudes de mantenimiento</h4>
                            <p class="text-muted">Comienza creando tu primera solicitud de mantenimiento.</p>
                            <a href="<?php echo $this->url('mantenimiento/crear'); ?>" class="btn btn-primary">
                                Crear Primera Solicitud
                            </a>
                        </div>
                    <?php } else { ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Título</th>
                                        <th>Tipo</th>
                                        <th>Prioridad</th>
                                        <th>Estado</th>
                                        <th>Fecha Programada</th>
                                        <th>Costo Estimado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($mantenimientos as $mant) { ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($mant['titulo']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($mant['edificio_nombre']); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($mant['tipo']); ?></span>
                                        </td>
                                        <td>
                                            <?php
                                            $prioridadClass = [
                                                'baja' => 'bg-success',
                                                'media' => 'bg-warning',
                                                'alta' => 'bg-danger',
                                                'urgente' => 'bg-dark',
                                            ][$mant['prioridad']] ?? 'bg-secondary';
                                        ?>
                                            <span class="badge <?php echo $prioridadClass; ?>">
                                                <?php echo htmlspecialchars($mant['prioridad']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                        $estadoClass = [
                                            'pendiente' => 'bg-warning',
                                            'en_proceso' => 'bg-info',
                                            'completado' => 'bg-success',
                                            'cancelado' => 'bg-secondary',
                                        ][$mant['estado']] ?? 'bg-secondary';
                                        ?>
                                            <span class="badge <?php echo $estadoClass; ?>">
                                                <?php echo htmlspecialchars($mant['estado']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo $mant['fecha_programada'] ? date('d/m/Y', strtotime($mant['fecha_programada'])) : 'No programada'; ?>
                                        </td>
                                        <td>
                                            <?php echo $mant['costo_estimado'] ? '$'.number_format($mant['costo_estimado'], 0, ',', '.') : 'Sin estimar'; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="<?php echo $this->url('mantenimiento/ver/'.$mant['id']); ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="<?php echo $this->url('mantenimiento/editar/'.$mant['id']); ?>" class="btn btn-sm btn-outline-secondary">
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
    </div>

    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <a href="<?php echo $url->to('dashboard'); ?>" class="btn btn-primary btn-lg rounded-circle shadow">
            <i class="bi bi-house"></i>
        </a>
    </div>

</div>

<script>
// Filtro de edificios
document.getElementById('filtro-edificio').addEventListener('change', function() {
    aplicarFiltros();
});

// Función para aplicar todos los filtros
function aplicarFiltros() {
    const edificioId = document.getElementById('filtro-edificio').value;
    const estado = document.getElementById('filtro-estado').value;
    const prioridad = document.getElementById('filtro-prioridad').value;
    
    // Construir URL con parámetros
    let url = '<?= $this->url('mantenimiento'); ?>';
    let params = [];
    
    // Incluir edificio_id siempre
    if (edificioId) {
        params.push('edificio_id=' + encodeURIComponent(edificioId));
    }
    
    if (estado) {
        params.push('estado=' + encodeURIComponent(estado));
    }
    
    if (prioridad) {
        params.push('prioridad=' + encodeURIComponent(prioridad));
    }
    
    // Si no hay parámetros, forzar 'todos' como default
    if (params.length === 0) {
        url += '?edificio_id=todos';
    } else {
        url += '?' + params.join('&');
    }
    
    console.log('🔍 Navegando a:', url);
    window.location.href = url;
}

// Función para limpiar todos los filtros (vuelve a "Todos")
function limpiarFiltros() {
    window.location.href = '<?= $this->url('mantenimiento'); ?>?edificio_id=todos';
}

// Mostrar botón de limpiar filtros si hay filtros activos (excepto "todos")
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const edificioId = urlParams.get('edificio_id');
    const estado = urlParams.get('estado');
    const prioridad = urlParams.get('prioridad');
    
    // Considerar que hay filtros activos si:
    // - edificio_id existe y NO es 'todos', O
    // - hay filtros de estado o prioridad
    const tieneFiltrosActivos = (edificioId && edificioId !== 'todos') || estado || prioridad;
    
    if (tieneFiltrosActivos) {
        const header = document.querySelector('.d-flex.justify-content-between.align-items-center.mb-4');
        if (header) {
            const botonLimpiar = document.createElement('button');
            botonLimpiar.className = 'btn btn-outline-secondary btn-sm ms-2';
            botonLimpiar.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Limpiar Filtros';
            botonLimpiar.onclick = limpiarFiltros;
            header.appendChild(botonLimpiar);
        }
    }
    
    // Debug inicial
    console.log('🔄 Página cargada - Filtro por defecto: Todos los edificios');
    console.log('🔍 Parámetros actuales:', {
        edificio_id: edificioId,
        estado: estado,
        prioridad: prioridad
    });
});
</script>