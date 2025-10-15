<?php
// 📁 views/amenities/imagenes.php
// Nota: Esta vista requiere métodos adicionales en el controller
?>
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Gestión de Imágenes</h1>
                    <p class="text-muted mb-0">Administra las imágenes del amenity: <?= htmlspecialchars($amenity['nombre']) ?></p>
                </div>
                <div>
                    <a href="<?= $url->to('amenities/editar/' . $amenity['id']) ?>" 
                       class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Volver a Editar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Área de Upload -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">Subir Nueva Imagen</h5>
                </div>
                <div class="card-body">
                    <form id="formUploadImagen" enctype="multipart/form-data">
                        <div class="mb-3">
                            <input type="file" name="imagen" id="inputImagen" 
                                   class="form-control" accept="image/jpeg,image/png,image/webp" required>
                            <small class="form-text text-muted">
                                Formatos permitidos: JPG, PNG, WEBP. Tamaño máximo: 5MB
                            </small>
                        </div>
                        <button type="submit" class="btn btn-primary" id="btnSubir">
                            <i class="bi bi-upload"></i> Subir Imagen
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Galería de Imágenes -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Imágenes Actuales</h5>
                    <small class="text-muted">Arrastra para reordenar</small>
                </div>
                <div class="card-body">
                    <?php if (empty($imagenes)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-images display-1 text-muted"></i>
                            <h4 class="text-muted mt-3">No hay imágenes</h4>
                            <p class="text-muted">Sube la primera imagen para este amenity</p>
                        </div>
                    <?php else: ?>
                        <div id="galeriaImagenes" class="row g-3">
                            <?php foreach ($imagenes as $imagen): ?>
                            <div class="col-xl-2 col-md-3 col-4" data-imagen-id="<?= $imagen['id'] ?>">
                                <div class="card card-hover h-100">
                                    <img src="<?= $url->asset('uploads/amenities/' . $amenity['id'] . '/' . $imagen['ruta_archivo']) ?>" 
                                         class="card-img-top" 
                                         style="height: 150px; object-fit: cover;"
                                         alt="<?= htmlspecialchars($imagen['nombre_archivo']) ?>">
                                    <div class="card-body p-2">
                                        <small class="text-muted d-block text-truncate">
                                            <?= htmlspecialchars($imagen['nombre_archivo']) ?>
                                        </small>
                                        <div class="btn-group w-100 mt-2">
                                            <button type="button" 
                                                    class="btn btn-sm <?= $imagen['is_principal'] ? 'btn-warning' : 'btn-outline-warning' ?>"
                                                    onclick="marcarPrincipal(<?= $imagen['id'] ?>)"
                                                    <?= $imagen['is_principal'] ? 'disabled' : '' ?>>
                                                <i class="bi bi-star<?= $imagen['is_principal'] ? '-fill' : '' ?>"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="eliminarImagen(<?= $imagen['id'] ?>)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                        <?php if ($imagen['is_principal']): ?>
                                            <small class="text-success d-block text-center mt-1">
                                                <i class="bi bi-check-circle"></i> Principal
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Upload de imagen
document.getElementById('formUploadImagen').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const btnSubir = document.getElementById('btnSubir');
    
    btnSubir.disabled = true;
    btnSubir.innerHTML = '<i class="bi bi-arrow-repeat spinner-border spinner-border-sm"></i> Subiendo...';

    fetch('<?= $url->to('amenities/subir-imagen/' . $amenity['id']) ?>', {
        method: 'POST',
        body: formData
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
        alert('Error al subir la imagen');
    })
    .finally(() => {
        btnSubir.disabled = false;
        btnSubir.innerHTML = '<i class="bi bi-upload"></i> Subir Imagen';
        this.reset();
    });
});

// Marcar imagen como principal
function marcarPrincipal(imagenId) {
    fetch('<?= $url->to('amenities/marcar-principal/') ?>' + imagenId, {
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
        alert('Error al marcar como principal');
    });
}

// Eliminar imagen
function eliminarImagen(imagenId) {
    if (confirm('¿Estás seguro de que deseas eliminar esta imagen?')) {
        fetch('<?= $url->to('amenities/eliminar-imagen/') ?>' + imagenId, {
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
            alert('Error al eliminar la imagen');
        });
    }
}

// Reordenamiento con SortableJS (requiere incluir la librería)
if (typeof Sortable !== 'undefined') {
    new Sortable(document.getElementById('galeriaImagenes'), {
        animation: 150,
        onEnd: function(evt) {
            const orden = Array.from(evt.from.children).map((child, index) => 
                child.getAttribute('data-imagen-id')
            );
            
            fetch('<?= $url->to('amenities/ordenar-imagenes/' . $amenity['id']) ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'orden=' + JSON.stringify(orden)
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert('Error al guardar el orden: ' + data.message);
                    location.reload(); // Recargar para restaurar orden original
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al guardar el orden');
                location.reload();
            });
        }
    });
}
</script>