<?php
// 📁 views/users/index.php
?>
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Gestión de Usuarios</h1>
        <a href="<?= $url->to('usuarios/crear') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nuevo Usuario
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

    <!-- Tabla de Usuarios -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Usuarios</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="usersTable">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>RUT</th>
                            <th>Rol</th>
                            <th>Fecha Creación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="bi bi-people display-4 d-block mb-2"></i>
                                    No hay usuarios registrados
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= $user['id'] ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                            <i class="bi bi-person text-white"></i>
                                        </div>
                                        <div>
                                            <strong><?= htmlspecialchars($user['nombre']) ?></strong>
                                            <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                                <span class="badge bg-info ms-1">Tú</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= htmlspecialchars($user['rut'] ?? 'No asignado') ?></td>
                                <td>
                                    <span class="badge bg-<?= $user['role_id'] == 1 ? 'danger' : 'secondary' ?>">
                                        <?= htmlspecialchars($user['role_name'] ?? 'Sin rol') ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= $url->to('usuarios/editar/') ?><?= $user['id'] ?>" 
                                           class="btn btn-outline-primary" 
                                           title="Editar usuario">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <button type="button" 
                                                class="btn btn-outline-danger btn-desactivar" 
                                                data-user-id="<?= $user['id'] ?>"
                                                data-user-name="<?= htmlspecialchars($user['nombre']) ?>"
                                                title="Desactivar usuario">
                                            <i class="bi bi-person-x"></i>
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
                <p>¿Estás seguro de que deseas desactivar al usuario <strong id="userNameModal"></strong>?</p>
                <p class="text-muted small">El usuario ya no podrá acceder al sistema.</p>
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
    let userIdToDesactivate = null;
    
    // Manejar clic en botones de desactivar
    document.querySelectorAll('.btn-desactivar').forEach(btn => {
        btn.addEventListener('click', function() {
            userIdToDesactivate = this.dataset.userId;
            document.getElementById('userNameModal').textContent = this.dataset.userName;
            confirmModal.show();
        });
    });
    
    // Confirmar desactivación
    document.getElementById('confirmDesactivar').addEventListener('click', function() {
        if (userIdToDesactivate) {
            fetch(`/proyectos/edificios/usuarios/desactivar/${userIdToDesactivate}`, {
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
                alert('Error al desactivar el usuario');
            })
            .finally(() => {
                confirmModal.hide();
            });
        }
    });
});
</script>