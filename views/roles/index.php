<?php
// 📁 views/roles/index.php
?>
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Gestión de Roles y Permisos</h1>
                    <p class="text-muted mb-0">Administra los roles del sistema y sus permisos</p>
                </div>
                <?php if ($can('roles', 'write')): ?>
                <a href="<?= $url->to('roles/crear') ?>" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Crear Nuevo Rol
                </a>
                <?php endif; ?>
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

    <!-- Tarjeta de Roles -->
    <div class="card">
        <div class="card-header bg-transparent">
            <h5 class="card-title mb-0">Roles del Sistema</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="200">Rol</th>
                            <th>Descripción</th>
                            <th width="120">Módulos</th>
                            <th width="100">Tipo</th>
                            <th width="120" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($roles)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <i class="bi bi-people text-muted fs-1"></i>
                                <p class="text-muted mt-2">No hay roles configurados</p>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($roles as $role): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-shield-check text-primary me-2"></i>
                                        <strong><?= htmlspecialchars($role['role_name']) ?></strong>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-muted"><?= htmlspecialchars($role['role_description'] ?? 'Sin descripción') ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-primary"><?= $role['modules_with_permissions'] ?>/<?= $role['total_modules'] ?></span>
                                </td>
                                <td>
                                    <?php if ($role['is_system_role']): ?>
                                        <span class="badge bg-success">Sistema</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Personalizado</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <?php if ($can('roles', 'write')): ?>
                                        <a href="<?= $url->to('roles/editar/' . $role['id']) ?>" 
                                           class="btn btn-outline-primary" 
                                           title="Editar permisos">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($can('roles', 'delete') && $role['is_editable'] && !$role['is_system_role']): ?>
                                        <button type="button" 
                                                class="btn btn-outline-danger" 
                                                title="Eliminar rol"
                                                onclick="confirmDelete(<?= $role['id'] ?>, '<?= htmlspecialchars($role['role_name']) ?>')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <?php endif; ?>
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

    <!-- Información del Sistema -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="card-title"><i class="bi bi-info-circle me-2"></i>Información</h6>
                    <p class="small text-muted mb-0">
                        Los roles del sistema no pueden ser eliminados. Los roles personalizados pueden ser editados y eliminados libremente.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="card-title"><i class="bi bi-shield-check me-2"></i>Permisos</h6>
                    <p class="small text-muted mb-0">
                        Super Admin tiene acceso completo al sistema. Otros roles tienen permisos específicos por módulo.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(roleId, roleName) {
    if (confirm(`¿Estás seguro de que deseas eliminar el rol "${roleName}"? Esta acción no se puede deshacer.`)) {
        // Implementar eliminación via AJAX
        alert('Funcionalidad de eliminación por implementar');
    }
}
</script>