<?php
// 📁 views/roles/editar.php
?>
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Editar Rol: <?= htmlspecialchars($role['role_name']) ?></h1>
                    <p class="text-muted mb-0">Modifica los permisos del rol</p>
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

    <!-- Información del Rol -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <strong>Nombre:</strong> <?= htmlspecialchars($role['role_name']) ?><br>
                    <strong>Descripción:</strong> <?= htmlspecialchars($role['role_description'] ?? 'Sin descripción') ?>
                </div>
                <div class="col-md-6">
                    <strong>Tipo:</strong> 
                    <?php if ($role['is_system_role']): ?>
                        <span class="badge bg-success">Rol del Sistema</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Rol Personalizado</span>
                    <?php endif; ?>
                    <br>
                    <strong>Editabilidad:</strong> 
                    <?php if ($role['is_editable']): ?>
                        <span class="badge bg-primary">Editable</span>
                    <?php else: ?>
                        <span class="badge bg-warning">No Editable</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario de Edición -->
    <form method="POST" action="<?= $url->to('roles/editar/' . $role['id']) ?>">
        <div class="row">
            <!-- Permisos por Módulo -->
            <div class="col-12">
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
                                                <button class="accordion-button" type="button" 
                                                        data-bs-toggle="collapse" 
                                                        data-bs-target="#collapse-<?= $moduleKey ?>" 
                                                        aria-expanded="true" 
                                                        aria-controls="collapse-<?= $moduleKey ?>">
                                                    <i class="<?= $module['module_icon'] ?> me-2 text-primary"></i>
                                                    <strong><?= htmlspecialchars($module['module_name']) ?></strong>
                                                    <small class="text-muted ms-2"><?= htmlspecialchars($module['module_description']) ?></small>
                                                </button>
                                            </h2>
                                            <div id="collapse-<?= $moduleKey ?>" 
                                                 class="accordion-collapse collapse show" 
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
                                                                $currentPermissions = $role_permissions[$moduleKey] ?? [];
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
                                                                    $isChecked = in_array($action, array_keys($currentPermissions)) && $currentPermissions[$action] === true;
                                                                ?>
                                                                <div class="form-check">
                                                                    <input class="form-check-input module-permission" 
                                                                           type="checkbox" 
                                                                           name="permissions[<?= $moduleKey ?>][]" 
                                                                           value="<?= $action ?>"
                                                                           id="perm-<?= $moduleKey ?>-<?= $action ?>"
                                                                           data-module="<?= $moduleKey ?>"
                                                                           data-action="<?= $action ?>"
                                                                           <?= $isChecked ? 'checked' : '' ?>>
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
                                                                        $subCurrentPermissions = $role_permissions[$submodule['module_key']] ?? [];
                                                                        foreach ($subActions as $action): 
                                                                            $isChecked = in_array($action, array_keys($subCurrentPermissions)) && $subCurrentPermissions[$action] === true;
                                                                        ?>
                                                                        <div class="form-check">
                                                                            <input class="form-check-input module-permission" 
                                                                                   type="checkbox" 
                                                                                   name="permissions[<?= $submodule['module_key'] ?>][]" 
                                                                                   value="<?= $action ?>"
                                                                                   id="perm-<?= $submodule['module_key'] ?>-<?= $action ?>"
                                                                                   data-module="<?= $submodule['module_key'] ?>"
                                                                                   data-action="<?= $action ?>"
                                                                                   <?= $isChecked ? 'checked' : '' ?>>
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
                            <div>
                                <button type="reset" class="btn btn-outline-warning me-2">
                                    <i class="bi bi-arrow-clockwise me-2"></i>Restablecer
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-2"></i>Guardar Cambios
                                </button>
                            </div>
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
    
    // Marcar select-all si todos los read están seleccionados
    const allReadChecked = Array.from(readCheckboxes).every(checkbox => checkbox.checked);
    selectAllRead.checked = allReadChecked;
    
    selectAllRead.addEventListener('change', function() {
        readCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // Actualizar select-all cuando cambien los checkboxes individuales
    readCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const allChecked = Array.from(readCheckboxes).every(cb => cb.checked);
            selectAllRead.checked = allChecked;
        });
    });
});
</script>