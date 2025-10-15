<?php
// 📁 views/edificios/index.php
?>
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Gestión de Edificios</h1>
        <a href="<?= $url->to('edificios/crear') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nuevo Edificio
        </a>
    </div>

    <!-- Mensajes Flash -->
    <?php if (!empty($flash_messages)): ?>
        <?php foreach ($flash_messages as $flash): ?>
            <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show">
                <?= htmlspecialchars($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Tarjetas de Estadísticas -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-hover border-start border-primary border-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                Edificios Activos
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                <?= count($edificios) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-building fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Edificios -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Edificios</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="edificiosTable">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Dirección</th>
                            <th>Comuna</th>
                            <th>Región</th>
                            <th>Departamentos</th>
                            <th>Pisos</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($edificios)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="bi bi-building display-4 d-block mb-2"></i>
                                    No hay edificios registrados
                                    <br>
                                    <a href="<?= $url->to('edificios/crear') ?>" class="btn btn-primary mt-2">
                                        Crear Primer Edificio
                                    </a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($edificios as $edificio): ?>
                            <tr>
                                <td><?= $edificio['id'] ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                            <i class="bi bi-building text-white"></i>
                                        </div>
                                        <div>
                                            <strong><?= htmlspecialchars($edificio['nombre']) ?></strong>
                                            <?php if (isset($edificio['is_primary_admin']) && $edificio['is_primary_admin']): ?>
                                                <span class="badge bg-success ms-1">Admin</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($edificio['direccion']) ?></td>
                                <td>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($edificio['comuna']) ?></span>
                                </td>
                                <td><?= htmlspecialchars($edificio['region']) ?></td>
                                <td>
                                    <span class="badge bg-info"><?= $edificio['total_departamentos'] ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-warning"><?= $edificio['total_pisos'] ?></span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= $url->to('edificios/gestionar/') ?><?= $edificio['id'] ?>" 
                                           class="btn btn-outline-info" 
                                           title="Gestionar edificio">
                                            <i class="bi bi-gear"></i>
                                        </a>
                                        <a href="<?= $url->to('edificios/editar/') ?><?= $edificio['id'] ?>" 
                                           class="btn btn-outline-primary" 
                                           title="Editar edificio">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-outline-danger btn-desactivar" 
                                                data-edificio-id="<?= $edificio['id'] ?>"
                                                data-edificio-nombre="<?= htmlspecialchars($edificio['nombre']) ?>"
                                                title="Desactivar edificio">
                                            <i class="bi bi-building-x"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <a href="<?php echo $url->to('dashboard'); ?>" class="btn btn-primary btn-lg rounded-circle shadow">
            <i class="bi bi-house"></i>
        </a>
    </div>

</div>

<!-- Modal de Confirmación -->
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Desactivación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas desactivar el edificio <strong id="edificioNombreModal"></strong>?</p>
                <p class="text-muted small">El edificio ya no estará disponible en el sistema.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDesactivar">Desactivar</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
    let edificioIdToDesactivate = null;
    
    // Manejar clic en botones de desactivar
    document.querySelectorAll('.btn-desactivar').forEach(btn => {
        btn.addEventListener('click', function() {
            edificioIdToDesactivate = this.dataset.edificioId;
            document.getElementById('edificioNombreModal').textContent = this.dataset.edificioNombre;
            confirmModal.show();
        });
    });
    
    // Confirmar desactivación
    document.getElementById('confirmDesactivar').addEventListener('click', function() {
        if (edificioIdToDesactivate) {
            fetch(`/proyectos/edificios/edificios/desactivar/${edificioIdToDesactivate}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al desactivar el edificio');
            })
            .finally(() => {
                confirmModal.hide();
            });
        }
    });
});
</script>