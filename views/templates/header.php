<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Edificios Chile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .sidebar { background: linear-gradient(135deg, var(--primary-color), var(--dark-color)); min-height: 100vh; }
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { transform: translateY(-5px); }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar y contenido -->
            <?php if ($can('roles', 'read')): ?>
            <li class="nav-item">
                <a class="nav-link" href="<?= $url->to('roles') ?>">
                    <i class="bi bi-shield-check me-2"></i>
                    Gestión de Roles
                </a>
            </li>
            <?php endif; ?>             
        </div>
    </div>
</body>
</html>
