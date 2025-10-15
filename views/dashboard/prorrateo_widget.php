<?php
// 📁 views/dashboard/prorrateo_widget.php
?>
<!-- Widget de Prorrateo para Dashboard - MEJORADO -->
<div class="col-lg-6 mb-4">
    <div class="card card-hover border-start border-warning border-4">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <h6 class="card-title mb-0">
                <i class="bi bi-calculator me-2 text-warning"></i>Prorrateo de Gastos
            </h6>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-warning dropdown-toggle" type="button" 
                        data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-three-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="<?= $url->to('finanzas/prorrateo') ?>">
                        <i class="bi bi-calculator me-2"></i>Ir a Prorrateo
                    </a></li>
                    <li><a class="dropdown-item" href="<?= $url->to('configuracion/prorrateo') ?>">
                        <i class="bi bi-gear me-2"></i>Configuración
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" onclick="recargarWidgetProrrateo()">
                        <i class="bi bi-arrow-clockwise me-2"></i>Actualizar
                    </a></li>
                </ul>
            </div>
        </div>
        <div class="card-body">
            <!-- Resumen Rápido -->
            <div class="row text-center mb-3" id="resumenProrrateo">
                <div class="col-4">
                    <div class="border-end">
                        <div class="h5 mb-1 text-warning" id="contadorPendientes">0</div>
                        <small class="text-muted">Pendientes</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="border-end">
                        <div class="h5 mb-1 text-info" id="contadorProceso">0</div>
                        <small class="text-muted">En Proceso</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="h5 mb-1 text-success" id="contadorAprobados">0</div>
                    <small class="text-muted">Aprobados</small>
                </div>
            </div>

            <!-- Alertas y Notificaciones -->
            <div id="alertasProrrateo" class="mb-3">
                <!-- Las alertas se cargan dinámicamente -->
            </div>

            <!-- Lista de Prórrateos Recientes -->
            <div id="listaProrrateos" class="mb-3">
                <div class="d-flex justify-content-center">
                    <div class="spinner-border spinner-border-sm text-warning" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <small class="text-muted ms-2">Cargando prorrateos...</small>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <?php if ($can('prorrateo', 'read')): ?>
            <div class="mt-3 pt-2 border-top">
                <div class="row g-2">
                    <div class="col-6">
                        <a href="<?= $url->to('finanzas/prorrateo') ?>" 
                           class="btn btn-sm btn-outline-warning w-100">
                            <i class="bi bi-calculator"></i> Ver Todos
                        </a>
                    </div>
                    <div class="col-6">
                        <button type="button" class="btn btn-sm btn-outline-primary w-100" 
                                onclick="mostrarCalculoRapido()" id="btnCalculoRapido">
                            <i class="bi bi-lightning"></i> Cálculo Rápido
                        </button>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Badge de Estado en Esquina -->
        <div class="position-absolute top-0 end-0 m-2">
            <span class="badge bg-warning" id="badgeEstado">Cargando...</span>
        </div>
    </div>
</div>

<!-- Modal para Cálculo Rápido -->
<div class="modal fade" id="modalCalculoRapido" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Cálculo Rápido de Prorrateo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formCalculoRapido">
                    <div class="mb-3">
                        <label class="form-label">Seleccionar Gasto Pendiente</label>
                        <select class="form-select" id="gastoRapido" required>
                            <option value="">Cargando gastos...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Estrategia</label>
                        <select class="form-select" id="estrategiaRapida" required>
                            <option value="">Usar estrategia por defecto</option>
                            <?php foreach ($estrategias ?? [] as $estrategia): ?>
                            <option value="<?= $estrategia['id'] ?>"><?= htmlspecialchars($estrategia['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Se usará la configuración del edificio para validaciones legales.
                    </div>
                </form>
                <div id="resultadoRapido" style="display: none;">
                    <hr>
                    <div id="contenidoResultado"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" onclick="ejecutarCalculoRapido()" id="btnEjecutarRapido">
                    <i class="bi bi-lightning"></i> Ejecutar Cálculo
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Estado del widget
const widgetProrrateo = {
    cargando: true,
    datos: null,
    intervalo: null
};

// Inicializar widget cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    inicializarWidgetProrrateo();
});

function inicializarWidgetProrrateo() {
    cargarDatosWidget();
    
    // Actualizar cada 2 minutos
    widgetProrrateo.intervalo = setInterval(cargarDatosWidget, 120000);
    
    // Configurar eventos
    document.getElementById('btnCalculoRapido').addEventListener('click', cargarGastosParaCalculoRapido);
}

async function cargarDatosWidget() {
    try {
        widgetProrrateo.cargando = true;
        actualizarBadgeEstado('Cargando...', 'secondary');
        
        const response = await fetch('/api/prorrateo/estadisticas');
        const data = await response.json();
        
        if (data.success) {
            widgetProrrateo.datos = data.data;
            widgetProrrateo.cargando = false;
            renderizarWidget();
        } else {
            throw new Error(data.error || 'Error al cargar datos');
        }
        
    } catch (error) {
        console.error('Error cargando widget prorrateo:', error);
        mostrarErrorWidget('Error al cargar datos del prorrateo');
    }
}

function renderizarWidget() {
    const datos = widgetProrrateo.datos;
    if (!datos) return;
    
    // Actualizar contadores
    document.getElementById('contadorPendientes').textContent = datos.pendientes || 0;
    document.getElementById('contadorProceso').textContent = datos.en_proceso || 0;
    document.getElementById('contadorAprobados').textContent = datos.aprobados || 0;
    
    // Actualizar badge de estado
    const totalPendientes = datos.pendientes || 0;
    let estado = 'Al día';
    let badgeClass = 'success';
    
    if (totalPendientes > 5) {
        estado = 'Crítico';
        badgeClass = 'danger';
    } else if (totalPendientes > 2) {
        estado = 'Atención';
        badgeClass = 'warning';
    } else if (totalPendientes > 0) {
        estado = 'Pendientes';
        badgeClass = 'info';
    }
    
    actualizarBadgeEstado(estado, badgeClass);
    
    // Renderizar alertas
    renderizarAlertas(datos.alertas || []);
    
    // Renderizar lista de prorrateos
    renderizarListaProrrateos(datos.recientes || []);
}

function renderizarAlertas(alertas) {
    const container = document.getElementById('alertasProrrateo');
    
    if (alertas.length === 0) {
        container.innerHTML = '';
        return;
    }
    
    let html = '';
    alertas.forEach(alerta => {
        const alertClass = alerta.nivel === 'alto' ? 'danger' : 
                          alerta.nivel === 'medio' ? 'warning' : 'info';
        
        html += `
            <div class="alert alert-${alertClass} alert-dismissible fade show py-2" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-${obtenerIconoAlerta(alerta.tipo)} me-2"></i>
                    <small class="flex-grow-1">${alerta.mensaje}</small>
                    <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

function renderizarListaProrrateos(prorrateos) {
    const container = document.getElementById('listaProrrateos');
    
    if (prorrateos.length === 0) {
        container.innerHTML = `
            <div class="text-center py-2">
                <i class="bi bi-check-circle text-muted fs-4"></i>
                <p class="text-muted mb-0 small">No hay prorrateos pendientes</p>
            </div>
        `;
        return;
    }
    
    let html = '<div class="list-group list-group-flush">';
    
    prorrateos.slice(0, 3).forEach(prorrateo => {
        const estadoColor = obtenerColorEstado(prorrateo.estado);
        const estadoIcono = obtenerIconoEstado(prorrateo.estado);
        
        html += `
            <div class="list-group-item d-flex justify-content-between align-items-start px-0 py-2 border-0">
                <div class="ms-2 me-auto">
                    <div class="fw-bold small">${escapeHtml(prorrateo.edificio_nombre)}</div>
                    <small class="text-muted">
                        ${escapeHtml(prorrateo.gasto_nombre)} • 
                        ${formatearFecha(prorrateo.periodo)}
                    </small>
                    <br>
                    <small class="text-${estadoColor}">
                        <i class="bi bi-${estadoIcono}"></i>
                        ${formatearEstado(prorrateo.estado)}
                    </small>
                </div>
                <div class="text-end">
                    <small class="d-block text-muted">$${formatearNumero(prorrateo.monto_total)}</small>
                    ${prorrateo.estado === 'pendiente_aprobacion' && <?= $can('prorrateo', 'approve') ? 'true' : 'false' ?> ? `
                    <button class="btn btn-sm btn-outline-success mt-1" 
                            onclick="aprobarDesdeWidget(${prorrateo.id})">
                        <i class="bi bi-check-lg"></i> Revisar
                    </button>
                    ` : ''}
                </div>
            </div>
        `;
    });
    
    // Mostrar contador si hay más elementos
    if (prorrateos.length > 3) {
        html += `
            <div class="list-group-item px-0 py-1 border-0 text-center">
                <small class="text-muted">
                    +${prorrateos.length - 3} más pendientes
                </small>
            </div>
        `;
    }
    
    html += '</div>';
    container.innerHTML = html;
}

// Funciones de utilidad para el widget
function obtenerColorEstado(estado) {
    const colores = {
        'pendiente_aprobacion': 'warning',
        'aprobado': 'success',
        'rechazado': 'danger',
        'en_proceso': 'info',
        'pendiente': 'secondary'
    };
    return colores[estado] || 'secondary';
}

function obtenerIconoEstado(estado) {
    const iconos = {
        'pendiente_aprobacion': 'clock',
        'aprobado': 'check-circle',
        'rechazado': 'x-circle',
        'en_proceso': 'gear',
        'pendiente': 'file-text'
    };
    return iconos[estado] || 'file-text';
}

function obtenerIconoAlerta(tipo) {
    const iconos = {
        'legal': '-shield-exclamation',
        'vencimiento': 'clock',
        'error': 'exclamation-triangle',
        'info': 'info-circle'
    };
    return iconos[tipo] || 'info-circle';
}

function formatearEstado(estado) {
    return estado.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
}

function actualizarBadgeEstado(texto, clase) {
    const badge = document.getElementById('badgeEstado');
    badge.textContent = texto;
    badge.className = `badge bg-${clase}`;
}

function mostrarErrorWidget(mensaje) {
    const container = document.getElementById('listaProrrateos');
    container.innerHTML = `
        <div class="alert alert-danger py-2">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <small>${mensaje}</small>
            <button class="btn btn-sm btn-outline-danger ms-2" onclick="cargarDatosWidget()">
                Reintentar
            </button>
        </div>
    `;
    actualizarBadgeEstado('Error', 'danger');
}

// Funciones de interacción
function recargarWidgetProrrateo() {
    cargarDatosWidget();
    // Mostrar feedback visual
    const badge = document.getElementById('badgeEstado');
    const originalTexto = badge.textContent;
    const originalClase = badge.className;
    
    badge.textContent = 'Actualizando...';
    badge.className = 'badge bg-info';
    
    setTimeout(() => {
        badge.textContent = originalTexto;
        badge.className = originalClase;
    }, 1000);
}

async function cargarGastosParaCalculoRapido() {
    try {
        const response = await fetch('/api/finanzas/gastos-comunes?estado=pendiente&limite=5');
        const data = await response.json();
        
        const select = document.getElementById('gastoRapido');
        select.innerHTML = '<option value="">Seleccionar gasto...</option>';
        
        if (data.success && data.data.length > 0) {
            data.data.forEach(gasto => {
                const option = document.createElement('option');
                option.value = gasto.id;
                option.textContent = `${gasto.edificio_nombre} - ${gasto.nombre} ($${formatearNumero(gasto.monto_total)})`;
                select.appendChild(option);
            });
        } else {
            select.innerHTML = '<option value="">No hay gastos pendientes</option>';
        }
        
        const modal = new bootstrap.Modal(document.getElementById('modalCalculoRapido'));
        modal.show();
        
    } catch (error) {
        console.error('Error:', error);
        alert('Error al cargar gastos para cálculo rápido');
    }
}

async function ejecutarCalculoRapido() {
    const gastoId = document.getElementById('gastoRapido').value;
    const estrategiaId = document.getElementById('estrategiaRapida').value;
    
    if (!gastoId) {
        alert('Selecciona un gasto para calcular');
        return;
    }
    
    const btnEjecutar = document.getElementById('btnEjecutarRapido');
    btnEjecutar.disabled = true;
    btnEjecutar.innerHTML = '<i class="bi bi-hourglass-split"></i> Calculando...';
    
    try {
        const response = await fetch('/api/prorrateo/calcular', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                gasto_id: gastoId,
                estrategia_id: estrategiaId || null
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            mostrarResultadoRapido(data.data);
        } else {
            throw new Error(data.error || 'Error en el cálculo');
        }
        
    } catch (error) {
        alert('Error en cálculo rápido: ' + error.message);
    } finally {
        btnEjecutar.disabled = false;
        btnEjecutar.innerHTML = '<i class="bi bi-lightning"></i> Ejecutar Cálculo';
    }
}

function mostrarResultadoRapido(resultado) {
    const resultadoDiv = document.getElementById('resultadoRapido');
    const contenidoDiv = document.getElementById('contenidoResultado');
    
    const esValido = resultado.validacion_legal?.es_valida;
    
    contenidoDiv.innerHTML = `
        <div class="alert alert-${esValido ? 'success' : 'warning'}">
            <i class="bi bi-${esValido ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
            <strong>${esValido ? 'Cálculo válido' : 'Atención: Validación legal'}</strong>
            <br>
            <small>${resultado.validacion_legal?.mensaje || 'Cálculo completado'}</small>
        </div>
        <div class="d-grid">
            <a href="<?= $url->to('finanzas/prorrateo') ?>" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-eye"></i> Ver Detalles Completos
            </a>
        </div>
    `;
    
    resultadoDiv.style.display = 'block';
}

async function aprobarDesdeWidget(prorrateoLogId) {
    if (!confirm('¿Aprobar este prorrateo?')) return;
    
    try {
        const response = await fetch('/api/prorrateo/aprobar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ prorrateo_log_id: prorrateoLogId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Recargar widget para reflejar cambios
            cargarDatosWidget();
            // Mostrar notificación de éxito
            mostrarNotificacion('success', 'Prorrateo aprobado correctamente');
        } else {
            throw new Error(data.error);
        }
        
    } catch (error) {
        alert('Error al aprobar: ' + error.message);
    }
}

function mostrarNotificacion(tipo, mensaje) {
    // Implementación simple de notificación
    const notification = document.createElement('div');
    notification.className = `alert alert-${tipo} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 1060; min-width: 300px;';
    notification.innerHTML = `
        ${mensaje}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Funciones de utilidad general
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatearNumero(numero) {
    return new Intl.NumberFormat('es-CL').format(numero);
}

function formatearFecha(fecha) {
    return new Date(fecha).toLocaleDateString('es-CL');
}
</script>