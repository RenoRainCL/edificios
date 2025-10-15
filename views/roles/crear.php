<?php
// 📁 views/roles/crear.php
?>
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Crear Nuevo Rol</h1>
                    <p class="text-muted mb-0">Define un nuevo rol y sus permisos en el sistema</p>
                </div>
                <a href="<?= $url->to('roles') ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Volver a Roles
                </a>
            </div>
        </div>
    </div>

    <!-- Mensajes Flash -->
    <?php if (!empty($flash_messages)): ?>
        <?php foreach ($flash_messages as $flash): ?>
            <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show">
                <i class="bi bi-<?= $flash['type'] == 'success' ? 'check-circle' : 'info-circle' ?> me-2"></i>
                <?= htmlspecialchars($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Formulario de Creación -->
    <form method="POST" action="<?= $url->to('roles/crear') ?>">
        <div class="row">
            <!-- Información Básica -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-transparent">
                        <h5 class="card-title mb-0">Información del Rol</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="role_name" class="form-label">Nombre del Rol *</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="role_name" 
                                   name="role_name" 
                                   required
                                   placeholder="Ej: Coordinador de Áreas Comunes">
                            <div class="form-text">Nombre descriptivo para el rol</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="role_description" class="form-label">Descripción *</label>
                            <textarea class="form-control" 
                                      id="role_description" 
                                      name="role_description" 
                                      rows="3"
                                      required
                                      placeholder="Describe las funciones y responsabilidades de este rol"></textarea>
                            <div class="form-text">Descripción detallada del propósito del rol</div>
                        </div>
                    </div>
                </div>

                <!-- Resumen de Permisos -->
                <div class="card mt-3">
                    <div class="card-header bg-transparent">
                        <h5 class="card-title mb-0">Resumen de Permisos</h5>
                    </div>
                    <div class="card-body">
                        <div id="permisos-resumen">
                            <p class="text-muted small">Selecciona permisos en los módulos para ver el resumen</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Permisos por Módulo -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Permisos por Módulo</h5>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="select-all-read">
                            <label class="form-check-label small" for="select-all-read">Seleccionar Lectura</label>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($modules)): ?>
                            <div class="text-center py-4">
                                <i class="bi bi-gear text-muted fs-1"></i>
                                <p class="text-muted mt-2">No hay módulos configurados</p>
                            </div>
                        <?php else: ?>
                            <div class="accordion" id="modulesAccordion">
                                <?php foreach ($modules as $moduleKey => $module): ?>
                                    <?php if (empty($module['parent_module'])): ?>
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="heading-<?= $moduleKey ?>">
                                                <button class="accordion-button collapsed" type="button" 
                                                        data-bs-toggle="collapse" 
                                                        data-bs-target="#collapse-<?= $moduleKey ?>" 
                                                        aria-expanded="false" 
                                                        aria-controls="collapse-<?= $moduleKey ?>">
                                                    <i class="<?= $module['module_icon'] ?> me-2 text-primary"></i>
                                                    <strong><?= htmlspecialchars($module['module_name']) ?></strong>
                                                    <small class="text-muted ms-2"><?= htmlspecialchars($module['module_description']) ?></small>
                                                </button>
                                            </h2>
                                            <div id="collapse-<?= $moduleKey ?>" 
                                                 class="accordion-collapse collapse" 
                                                 aria-labelledby="heading-<?= $moduleKey ?>" 
                                                 data-bs-parent="#modulesAccordion">
                                                <div class="accordion-body">
                                                    <!-- Módulo Principal -->
                                                    <div class="row mb-3">
                                                        <div class="col-12">
                                                            <label class="form-label fw-bold"><?= htmlspecialchars($module['module_name']) ?></label>
                                                            <div class="d-flex flex-wrap gap-3">
                                                                <?php 
                                                                $actions = json_decode($module['actions'], true) ?? [];
                                                                foreach ($actions as $action): 
                                                                    $actionLabels = [
                                                                        'read' => 'Lectura', 
                                                                        'write' => 'Escritura', 
                                                                        'delete' => 'Eliminar',
                                                                        'approve' => 'Aprobar',
                                                                        'cancel' => 'Cancelar',
                                                                        'configure' => 'Configurar',
                                                                        'assign' => 'Asignar',
                                                                        'publish' => 'Publicar',
                                                                        'share' => 'Compartir',
                                                                        'process' => 'Procesar',
                                                                        'confirm' => 'Confirmar',
                                                                        'register' => 'Registrar',
                                                                        'report' => 'Reportes',
                                                                        'assign_roles' => 'Asignar Roles',
                                                                        'assign_permissions' => 'Asignar Permisos'
                                                                    ];
                                                                ?>
                                                                <div class="form-check">
                                                                    <input class="form-check-input module-permission" 
                                                                           type="checkbox" 
                                                                           name="permissions[<?= $moduleKey ?>][]" 
                                                                           value="<?= $action ?>"
                                                                           id="perm-<?= $moduleKey ?>-<?= $action ?>"
                                                                           data-module="<?= $moduleKey ?>"
                                                                           data-action="<?= $action ?>">
                                                                    <label class="form-check-label small" for="perm-<?= $moduleKey ?>-<?= $action ?>">
                                                                        <?= $actionLabels[$action] ?? ucfirst($action) ?>
                                                                    </label>
                                                                </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Submódulos -->
                                                    <?php if (!empty($module['children'])): ?>
                                                        <hr>
                                                        <h6 class="mb-3">Submódulos</h6>
                                                        <?php foreach ($module['children'] as $submodule): ?>
                                                            <div class="row mb-3">
                                                                <div class="col-12">
                                                                    <label class="form-label">
                                                                        <i class="<?= $submodule['module_icon'] ?> me-2 text-secondary"></i>
                                                                        <?= htmlspecialchars($submodule['module_name']) ?>
                                                                    </label>
                                                                    <div class="d-flex flex-wrap gap-3">
                                                                        <?php 
                                                                        $subActions = json_decode($submodule['actions'], true) ?? [];
                                                                        foreach ($subActions as $action): 
                                                                        ?>
                                                                        <div class="form-check">
                                                                            <input class="form-check-input module-permission" 
                                                                                   type="checkbox" 
                                                                                   name="permissions[<?= $submodule['module_key'] ?>][]" 
                                                                                   value="<?= $action ?>"
                                                                                   id="perm-<?= $submodule['module_key'] ?>-<?= $action ?>"
                                                                                   data-module="<?= $submodule['module_key'] ?>"
                                                                                   data-action="<?= $action ?>">
                                                                            <label class="form-check-label small" for="perm-<?= $submodule['module_key'] ?>-<?= $action ?>">
                                                                                <?= $actionLabels[$action] ?? ucfirst($action) ?>
                                                                            </label>
                                                                        </div>
                                                                        <?php endforeach; ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="card mt-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <a href="<?= $url->to('roles') ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i>Crear Rol
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select All para permiso de lectura
    const selectAllRead = document.getElementById('select-all-read');
    const readCheckboxes = document.querySelectorAll('input[type="checkbox"][value="read"]');
    
    selectAllRead.addEventListener('change', function() {
        readCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
            updateSummary();
        });
    });

    // Actualizar resumen cuando cambien los checkboxes
    const allCheckboxes = document.querySelectorAll('.module-permission');
    allCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSummary);
    });

    function updateSummary() {
        const summary = document.getElementById('permisos-resumen');
        const selectedPermissions = {};
        
        allCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                const module = checkbox.dataset.module;
                const action = checkbox.dataset.action;
                
                if (!selectedPermissions[module]) {
                    selectedPermissions[module] = [];
                }
                selectedPermissions[module].push(action);
            }
        });

        if (Object.keys(selectedPermissions).length === 0) {
            summary.innerHTML = '<p class="text-muted small">Selecciona permisos en los módulos para ver el resumen</p>';
            return;
        }

        let html = '';
        for (const [module, actions] of Object.entries(selectedPermissions)) {
            html += `<div class="mb-2">
                <strong class="small">${module}</strong><br>
                <span class="badge bg-primary me-1">${actions.join('</span> <span class="badge bg-primary me-1">')}</span>
            </div>`;
        }
        
        summary.innerHTML = html;
    }

    // Inicializar resumen
    updateSummary();
});
</script>