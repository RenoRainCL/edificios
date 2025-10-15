<?php
// 📁 views/finanzas/prorrateo_gastos.php
?>
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="card-title mb-1">
                                <i class="bi bi-calculator me-2"></i>Prorrateo de Gastos Comunes
                            </h2>
                            <p class="card-text mb-0">
                                Distribución automática de gastos entre departamentos
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="btn-group">
                                <a href="<?= $url->to('finanzas/gastos-comunes') ?>" class="btn btn-light">
                                    <i class="bi bi-arrow-left"></i> Volver a Gastos
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

    <!-- Filtros y Controles -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-funnel me-2"></i>Filtros y Acciones
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Edificio</label>
                            <select class="form-select" id="filtroEdificio" onchange="filtrarGastos()">
                                <option value="">Todos los edificios</option>
                                <?php foreach ($edificios as $edificio): ?>
                                <option value="<?= $edificio['id'] ?>"><?= htmlspecialchars($edificio['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Estado</label>
                            <select class="form-select" id="filtroEstado" onchange="filtrarGastos()">
                                <option value="">Todos los estados</option>
                                <option value="pendiente">Pendientes de prorrateo</option>
                                <option value="en_proceso">En proceso</option>
                                <option value="aprobado">Aprobados</option>
                                <option value="rechazado">Rechazados</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Período</label>
                            <input type="month" class="form-control" id="filtroPeriodo" onchange="filtrarGastos()" 
                                   value="<?= date('Y-m') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="button" class="btn btn-outline-primary" onclick="recargarGastos()">
                                    <i class="bi bi-arrow-clockwise"></i> Actualizar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gastos Pendientes de Prorrateo -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-clock-history me-2"></i>Gastos Pendientes de Prorrateo
                        <span class="badge bg-dark ms-2" id="contadorPendientes">0</span>
                    </h6>
                </div>
                <div class="card-body">
                    <div id="gastosPendientes" class="row">
                        <!-- Cargando... -->
                        <div class="col-12 text-center py-4">
                            <div class="spinner-border text-warning" role="status">
                                <span class="visually-hidden">Cargando gastos...</span>
                            </div>
                            <p class="text-muted mt-2">Cargando gastos pendientes...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Prorrateos en Proceso -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-gear me-2"></i>Prorrateos en Proceso
                        <span class="badge bg-dark ms-2" id="contadorProceso">0</span>
                    </h6>
                </div>
                <div class="card-body">
                    <div id="prorrateosProceso" class="row">
                        <!-- Cargando... -->
                        <div class="col-12 text-center py-4">
                            <div class="spinner-border text-info" role="status">
                                <span class="visually-hidden">Cargando prorrateos...</span>
                            </div>
                            <p class="text-muted mt-2">Cargando prorrateos en proceso...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Prorrateos Aprobados -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-check-circle me-2"></i>Prorrateos Aprobados
                        <span class="badge bg-dark ms-2" id="contadorAprobados">0</span>
                    </h6>
                </div>
                <div class="card-body">
                    <div id="prorrateosAprobados" class="row">
                        <!-- Cargando... -->
                        <div class="col-12 text-center py-4">
                            <div class="spinner-border text-success" role="status">
                                <span class="visually-hidden">Cargando aprobados...</span>
                            </div>
                            <p class="text-muted mt-2">Cargando prorrateos aprobados...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Cálculo de Prorrateo -->
<div class="modal fade" id="modalCalcularProrrateo" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Calcular Prorrateo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formCalcularProrrateo">
                    <input type="hidden" id="gastoIdCalculo" name="gasto_id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Gasto Seleccionado</label>
                                <input type="text" class="form-control" id="gastoNombreCalculo" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Monto Total</label>
                                <input type="text" class="form-control" id="gastoMontoCalculo" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Estrategia de Prorrateo</label>
                        <select class="form-select" id="estrategiaCalculo" name="estrategia_id" required>
                            <option value="">Seleccionar estrategia...</option>
                            <?php foreach ($estrategias as $estrategia): ?>
                            <option value="<?= $estrategia['id'] ?>" 
                                    data-descripcion="<?= htmlspecialchars($estrategia['descripcion'] ?? '') ?>">
                                <?= htmlspecialchars($estrategia['nombre']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text" id="descripcionEstrategia"></div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Nota:</strong> El cálculo aplicará la estrategia seleccionada y validará 
                        los límites legales configurados para el edificio.
                    </div>
                </form>
                
                <div id="resultadoCalculo" class="mt-3" style="display: none;">
                    <hr>
                    <h6>Resultado del Cálculo</h6>
                    <div id="detallesCalculo"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" onclick="ejecutarCalculoProrrateo()" id="btnCalcular">
                    <i class="bi bi-calculator"></i> Calcular Prorrateo
                </button>
                <button type="button" class="btn btn-success" onclick="aprobarProrrateo()" id="btnAprobar" style="display: none;">
                    <i class="bi bi-check-lg"></i> Aprobar Distribución
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Ver Detalles -->
<div class="modal fade" id="modalVerDetalles" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Detalles de Prorrateo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="contenidoDetalles"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="imprimirDetalles()">
                    <i class="bi bi-printer"></i> Imprimir
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Estado global de la aplicación
const estadoApp = {
    gastosPendientes: [],
    prorrateosProceso: [],
    prorrateosAprobados: [],
    prorrateoActual: null
};

document.addEventListener('DOMContentLoaded', function() {
    cargarDatosIniciales();
    configurarEventos();
});

function configurarEventos() {
    // Evento para descripción de estrategia
    document.getElementById('estrategiaCalculo').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const descripcion = selectedOption.getAttribute('data-descripcion');
        document.getElementById('descripcionEstrategia').textContent = descripcion || '';
    });
}

function cargarDatosIniciales() {
    cargarGastosPendientes();
    cargarProrrateosProceso();
    cargarProrrateosAprobados();
}

async function cargarGastosPendientes() {
    try {
        const response = await fetch('/api/finanzas/gastos-comunes?estado=pendiente');
        const data = await response.json();
        
        if (data.success) {
            estadoApp.gastosPendientes = data.data;
            renderizarGastosPendientes();
        } else {
            throw new Error(data.error || 'Error al cargar gastos');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarError('gastosPendientes', 'Error al cargar gastos pendientes');
    }
}

function renderizarGastosPendientes() {
    const container = document.getElementById('gastosPendientes');
    const contador = document.getElementById('contadorPendientes');
    
    contador.textContent = estadoApp.gastosPendientes.length;
    
    if (estadoApp.gastosPendientes.length === 0) {
        container.innerHTML = `
            <div class="col-12 text-center py-4">
                <i class="bi bi-check-circle text-muted fs-1"></i>
                <h5 class="text-muted mt-3">No hay gastos pendientes</h5>
                <p class="text-muted">Todos los gastos han sido prorrateados</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    estadoApp.gastosPendientes.forEach(gasto => {
        html += `
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="card h-100 border-warning">
                    <div class="card-header bg-warning bg-opacity-25">
                        <h6 class="card-title mb-0">${escapeHtml(gasto.nombre)}</h6>
                    </div>
                    <div class="card-body">
                        <div class="row small">
                            <div class="col-6">
                                <strong>Edificio:</strong><br>
                                ${escapeHtml(gasto.edificio_nombre)}
                            </div>
                            <div class="col-6">
                                <strong>Período:</strong><br>
                                ${formatearFecha(gasto.periodo)}
                            </div>
                        </div>
                        <div class="row small mt-2">
                            <div class="col-6">
                                <strong>Monto:</strong><br>
                                $${formatearNumero(gasto.monto_total)}
                            </div>
                            <div class="col-6">
                                <strong>Vence:</strong><br>
                                ${formatearFecha(gasto.fecha_vencimiento)}
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent">
                        <button type="button" class="btn btn-warning btn-sm w-100" 
                                onclick="iniciarCalculoProrrateo(${gasto.id}, '${escapeHtml(gasto.nombre)}', ${gasto.monto_total})">
                            <i class="bi bi-calculator"></i> Calcular Prorrateo
                        </button>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

function iniciarCalculoProrrateo(gastoId, gastoNombre, gastoMonto) {
    document.getElementById('gastoIdCalculo').value = gastoId;
    document.getElementById('gastoNombreCalculo').value = gastoNombre;
    document.getElementById('gastoMontoCalculo').value = formatearNumero(gastoMonto);
    document.getElementById('resultadoCalculo').style.display = 'none';
    document.getElementById('btnAprobar').style.display = 'none';
    
    // Resetear formulario
    document.getElementById('estrategiaCalculo').selectedIndex = 0;
    document.getElementById('descripcionEstrategia').textContent = '';
    
    const modal = new bootstrap.Modal(document.getElementById('modalCalcularProrrateo'));
    modal.show();
}

async function ejecutarCalculoProrrateo() {
    const form = document.getElementById('formCalcularProrrateo');
    const formData = new FormData(form);
    const btnCalcular = document.getElementById('btnCalcular');
    const resultadoDiv = document.getElementById('resultadoCalculo');
    const detallesDiv = document.getElementById('detallesCalculo');
    
    // Validar formulario
    if (!formData.get('estrategia_id')) {
        mostrarAlerta('error', 'Selecciona una estrategia de cálculo');
        return;
    }
    
    btnCalcular.disabled = true;
    btnCalcular.innerHTML = '<i class="bi bi-hourglass-split"></i> Calculando...';
    
    try {
        const response = await fetch('/api/prorrateo/calcular', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                gasto_id: formData.get('gasto_id'),
                estrategia_id: formData.get('estrategia_id')
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            estadoApp.prorrateoActual = data.data;
            mostrarResultadoCalculo(data.data);
            document.getElementById('btnAprobar').style.display = 'block';
        } else {
            throw new Error(data.error || 'Error en el cálculo');
        }
        
    } catch (error) {
        console.error('Error:', error);
        mostrarAlerta('error', 'Error en el cálculo: ' + error.message);
    } finally {
        btnCalcular.disabled = false;
        btnCalcular.innerHTML = '<i class="bi bi-calculator"></i> Calcular Prorrateo';
    }
}

function mostrarResultadoCalculo(resultado) {
    const resultadoDiv = document.getElementById('resultadoCalculo');
    const detallesDiv = document.getElementById('detallesCalculo');
    
    let html = `
        <div class="alert alert-success">
            <i class="bi bi-check-circle me-2"></i>
            <strong>Cálculo completado exitosamente</strong>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="card-title mb-0">Resumen</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>Departamentos:</strong> ${resultado.distribucion.length}</p>
                        <p><strong>Total porcentaje:</strong> ${resultado.total_porcentaje?.toFixed(2) || '100.00'}%</p>
                        <p><strong>Estado legal:</strong> 
                            <span class="badge bg-${resultado.validacion_legal?.es_valida ? 'success' : 'danger'}">
                                ${resultado.validacion_legal?.es_valida ? 'Válido' : 'Inválido'}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="card-title mb-0">Validación Legal</h6>
                    </div>
                    <div class="card-body">
                        <p class="small">${resultado.validacion_legal?.mensaje || 'Sin validación'}</p>
                        <p class="small"><strong>Variación:</strong> ${resultado.validacion_legal?.variacion_detectada?.toFixed(2) || '0.00'}%</p>
                        <p class="small"><strong>Límite:</strong> ${resultado.validacion_legal?.variacion_maxima_permitida || '0.00'}%</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-3">
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="verDetallesDistribucion()">
                <i class="bi bi-eye"></i> Ver Distribución Completa
            </button>
        </div>
    `;
    
    detallesDiv.innerHTML = html;
    resultadoDiv.style.display = 'block';
}

async function aprobarProrrateo() {
    if (!estadoApp.prorrateoActual) {
        mostrarAlerta('error', 'No hay prorrateo para aprobar');
        return;
    }
    
    const justificacion = prompt('Ingrese una justificación para la aprobación (opcional):');
    
    try {
        const response = await fetch('/api/prorrateo/aprobar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                prorrateo_log_id: estadoApp.prorrateoActual.prorrateo_log_id,
                justificacion: justificacion
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            mostrarAlerta('success', 'Prorrateo aprobado exitosamente');
            bootstrap.Modal.getInstance(document.getElementById('modalCalcularProrrateo')).hide();
            cargarDatosIniciales(); // Recargar datos
        } else {
            throw new Error(data.error || 'Error al aprobar');
        }
        
    } catch (error) {
        console.error('Error:', error);
        mostrarAlerta('error', 'Error al aprobar: ' + error.message);
    }
}

// Funciones de utilidad
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

function mostrarAlerta(tipo, mensaje) {
    const alertClass = tipo === 'error' ? 'alert-danger' : 'alert-success';
    const alert = document.createElement('div');
    alert.className = `alert ${alertClass} alert-dismissible fade show`;
    alert.innerHTML = `
        ${mensaje}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.querySelector('.container-fluid').insertBefore(alert, document.querySelector('.container-fluid').firstChild);
    
    setTimeout(() => {
        alert.remove();
    }, 5000);
}

// Funciones pendientes de implementación (para completar)
async function cargarProrrateosProceso() {
    // TODO: Implementar carga de prorrateos en proceso
    document.getElementById('prorrateosProceso').innerHTML = `
        <div class="col-12 text-center py-4">
            <i class="bi bi-info-circle text-muted fs-1"></i>
            <p class="text-muted mt-2">Módulo en desarrollo</p>
        </div>
    `;
}

async function cargarProrrateosAprobados() {
    // TODO: Implementar carga de prorrateos aprobados
    document.getElementById('prorrateosAprobados').innerHTML = `
        <div class="col-12 text-center py-4">
            <i class="bi bi-info-circle text-muted fs-1"></i>
            <p class="text-muted mt-2">Módulo en desarrollo</p>
        </div>
    `;
}

function filtrarGastos() {
    // TODO: Implementar filtrado
    console.log('Filtrando gastos...');
}

function recargarGastos() {
    cargarDatosIniciales();
    mostrarAlerta('info', 'Datos actualizados');
}

function verDetallesDistribucion() {
    // TODO: Implementar vista de detalles
    console.log('Mostrando detalles de distribución...');
}

function mostrarError(contenedorId, mensaje) {
    document.getElementById(contenedorId).innerHTML = `
        <div class="col-12 text-center py-4">
            <i class="bi bi-exclamation-triangle text-danger fs-1"></i>
            <h5 class="text-danger mt-3">Error</h5>
            <p class="text-danger">${mensaje}</p>
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="cargarDatosIniciales()">
                Reintentar
            </button>
        </div>
    `;
}
</script>