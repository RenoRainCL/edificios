<?php
// 📁 views/departamentos/editar.php
?>
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="card-title mb-1">
                                <i class="bi bi-house-door me-2"></i>Editar Departamento
                            </h2>
                            <p class="card-text mb-0">
                                <?php echo htmlspecialchars($departamento['edificio_nombre']); ?> - 
                                Departamento <?php echo htmlspecialchars($departamento['numero']); ?>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="btn-group">
                                <a href="<?php echo $url->to('departamentos'); ?>" class="btn btn-light">
                                    <i class="bi bi-arrow-left"></i> Volver
                                </a>
                                <a href="<?php echo $url->to("departamentos/ver/{$departamento['id']}"); ?>" class="btn btn-outline-light">
                                    <i class="bi bi-eye"></i> Ver Detalle
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mensajes Flash -->
    <?php if (!empty($flash_messages)) { ?>
        <?php foreach ($flash_messages as $flash) { ?>
            <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show">
                <i class="bi bi-<?php echo $flash['type'] == 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                <?php echo htmlspecialchars($flash['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php } ?>
    <?php } ?>

    <!-- Formulario de Edición -->
    <form method="POST" action="<?php echo $url->to("departamentos/editar/{$departamento['id']}"); ?>">
        <div class="row">
            <!-- Información Básica -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="card-title mb-0">
                            <i class="bi bi-info-circle me-2"></i>Información Básica
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Número de Departamento *</label>
                                    <input type="text" class="form-control" name="numero" 
                                           value="<?php echo htmlspecialchars($departamento['numero'] ?? ''); ?>" 
                                           required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Piso</label>
                                    <input type="number" class="form-control" name="piso" 
                                           value="<?php echo htmlspecialchars($departamento['piso'] ?? ''); ?>" 
                                           min="1" max="50">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Metros Cuadrados</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="metros_cuadrados" 
                                               value="<?php echo htmlspecialchars($departamento['metros_cuadrados'] ?? ''); ?>" 
                                               step="0.01" min="0">
                                        <span class="input-group-text">m²</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Orientación</label>
                                    <select class="form-select" name="orientacion">
                                        <option value="">Seleccionar...</option>
                                        <option value="Norte" <?php echo ($departamento['orientacion'] ?? '') == 'Norte' ? 'selected' : ''; ?>>Norte</option>
                                        <option value="Nororiente" <?php echo ($departamento['orientacion'] ?? '') == 'Nororiente' ? 'selected' : ''; ?>>Nororiente</option>
                                        <option value="Oriente" <?php echo ($departamento['orientacion'] ?? '') == 'Oriente' ? 'selected' : ''; ?>>Oriente</option>
                                        <option value="Suroriente" <?php echo ($departamento['orientacion'] ?? '') == 'Suroriente' ? 'selected' : ''; ?>>Suroriente</option>
                                        <option value="Sur" <?php echo ($departamento['orientacion'] ?? '') == 'Sur' ? 'selected' : ''; ?>>Sur</option>
                                        <option value="Suroccidente" <?php echo ($departamento['orientacion'] ?? '') == 'Suroccidente' ? 'selected' : ''; ?>>Suroccidente</option>
                                        <option value="Occidente" <?php echo ($departamento['orientacion'] ?? '') == 'Occidente' ? 'selected' : ''; ?>>Occidente</option>
                                        <option value="Noroccidente" <?php echo ($departamento['orientacion'] ?? '') == 'Noroccidente' ? 'selected' : ''; ?>>Noroccidente</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Dormitorios</label>
                                    <input type="number" class="form-control" name="dormitorios" 
                                           value="<?php echo htmlspecialchars($departamento['dormitorios'] ?? 1); ?>" 
                                           min="0" max="10">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Baños</label>
                                    <input type="number" class="form-control" name="banos" 
                                           value="<?php echo htmlspecialchars($departamento['banos'] ?? 1); ?>" 
                                           min="0" max="10">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Estacionamientos</label>
                                    <input type="number" class="form-control" name="estacionamientos" 
                                           value="<?php echo htmlspecialchars($departamento['estacionamientos'] ?? 0); ?>" 
                                           min="0" max="10">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Bodegas</label>
                            <input type="number" class="form-control" name="bodegas" 
                                   value="<?php echo htmlspecialchars($departamento['bodegas'] ?? 0); ?>" 
                                   min="0" max="10">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información del Propietario -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="card-title mb-0">
                            <i class="bi bi-person me-2"></i>Información del Propietario
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">RUT Propietario</label>
                            <input type="text" class="form-control" name="propietario_rut" 
                                   value="<?php echo htmlspecialchars($departamento['propietario_rut'] ?? ''); ?>" 
                                   placeholder="12.345.678-9">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nombre Completo *</label>
                            <input type="text" class="form-control" name="propietario_nombre" 
                                   value="<?php echo htmlspecialchars($departamento['propietario_nombre'] ?? ''); ?>" 
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="propietario_email" 
                                   value="<?php echo htmlspecialchars($departamento['propietario_email'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" name="propietario_telefono" 
                                   value="<?php echo htmlspecialchars($departamento['propietario_telefono'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Observaciones</label>
                            <textarea class="form-control" name="observaciones" rows="3"><?php echo htmlspecialchars($departamento['observaciones'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección de Cálculo de Prorrateo -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="card-title mb-0">
                            <i class="bi bi-calculator me-2"></i>Cálculo de Prorrateo
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Porcentaje de Copropiedad</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="porcentaje_copropiedad" 
                                               id="porcentaje_copropiedad" 
                                               value="<?php echo htmlspecialchars($departamento['porcentaje_copropiedad'] ?? 0); ?>" 
                                               step="0.0001" min="0" max="100" required>
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <div class="form-text">
                                        <small class="text-muted" id="calculo_info">
                                            <?php if ($departamento['porcentaje_calculado_auto'] ?? false) { ?>
                                                <i class="bi bi-robot text-success"></i> Calculado automáticamente
                                            <?php } else { ?>
                                                <i class="bi bi-person-check text-info"></i> Valor manual
                                            <?php } ?>
                                        </small>
                                    </div>
                                </div>
                                
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="calculo_automatico" 
                                           id="calculo_automatico" value="1" checked>
                                    <label class="form-check-label" for="calculo_automatico">
                                        Calcular porcentaje automáticamente al guardar
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-outline-warning" id="btn_calcular_auto">
                                        <i class="bi bi-calculator"></i> Calcular Automáticamente
                                    </button>
                                    <button type="button" class="btn btn-outline-info" id="btn_recalcular_edificio">
                                        <i class="bi bi-buildings"></i> Recalcular Todo el Edificio
                                    </button>
                                    <a href="<?php echo $url->to("configuracion/prorrateo?edificio_id={$departamento['edificio_id']}"); ?>" 
                                       class="btn btn-outline-secondary">
                                        <i class="bi bi-gear"></i> Configurar Estrategia
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Información de Cálculo -->
                        <div id="info_calculo" class="mt-3 p-3 bg-light rounded d-none">
                            <h6><i class="bi bi-info-circle me-2"></i>Información de Cálculo</h6>
                            <div id="detalle_calculo"></div>
                        </div>
                        
                        <!-- Estado de Configuración -->
                        <div class="mt-3">
                            <?php if ($info_calculo['calculo_automatico'] ?? true) { ?>
                                <div class="alert alert-success py-2">
                                    <small>
                                        <i class="bi bi-check-circle"></i> 
                                        Cálculo automático activado. Estrategia: 
                                        <strong><?php echo htmlspecialchars($info_calculo['estrategia_activa'] ?? 'metros_cuadrados'); ?></strong>
                                    </small>
                                </div>
                            <?php } else { ?>
                                <div class="alert alert-warning py-2">
                                    <small>
                                        <i class="bi bi-exclamation-triangle"></i> 
                                        Cálculo automático desactivado para este edificio
                                    </small>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <a href="<?php echo $url->to('departamentos'); ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <div class="btn-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> Guardar Cambios
                                </button>
                                <a href="<?php echo $url->to("departamentos/ver/{$departamento['id']}"); ?>" class="btn btn-outline-primary">
                                    <i class="bi bi-eye"></i> Ver Detalle
                                </a>
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
    const porcentajeInput = document.getElementById('porcentaje_copropiedad');
    const btnCalcularAuto = document.getElementById('btn_calcular_auto');
    const btnRecalcularEdificio = document.getElementById('btn_recalcular_edificio');
    const infoCalculo = document.getElementById('info_calculo');
    const detalleCalculo = document.getElementById('detalle_calculo');
    const calculoInfo = document.getElementById('calculo_info');
    const calculoAutomatico = document.getElementById('calculo_automatico');
    
    // Calcular automáticamente al cargar si está en 0
    if (parseFloat(porcentajeInput.value) === 0) {
        calcularPorcentajeAuto();
    }
    
    // Botón calcular automático
    btnCalcularAuto.addEventListener('click', calcularPorcentajeAuto);
    
    // Botón recalcular edificio
    btnRecalcularEdificio.addEventListener('click', function() {
        if (confirm('¿Recalcular porcentajes para TODOS los departamentos del edificio?\n\nEsto sobrescribirá los valores manuales y aplicará el cálculo automático a todos los departamentos.')) {
            recalcularTodoEdificio();
        }
    });
    
    // Cambio manual del porcentaje
    porcentajeInput.addEventListener('change', function() {
        if (parseFloat(this.value) > 0) {
            calculoInfo.innerHTML = '<i class="bi bi-person-check text-info"></i> Valor manual';
            calculoAutomatico.checked = false;
        }
    });
    
    function calcularPorcentajeAuto() {
        const pathParts = window.location.pathname.split('/');
        const edificioId = pathParts.find(function(part) {
            return part && !isNaN(part);
        });
        
        console.log('📍 Calculando para edificio:', edificioId);
        
        if (!edificioId) {
            alert('❌ No se pudo obtener el ID del edificio');
            return;
        }
        
        fetch('<?= UrlHelper::to("configuracion/calcularAuto") ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ 
                edificio_id: edificioId
            })
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            console.log('📍 Respuesta:', data);
            
            if (data.success) {
                console.log('✅ Cálculo REAL exitoso!', data.data);
                
                // Mostrar resultados simples
                var resultado = data.data;
                alert('✅ ¡Cálculo completado!\n\n' +
                    'Departamentos: ' + (resultado.departamentos_procesados || 0) + '\n' +
                    'Total porcentaje: ' + (resultado.total_porcentaje || 0) + '%\n\n' +
                    'Revisa la consola para ver los detalles por departamento.');
                
                // Mostrar en consola los porcentajes calculados
                if (resultado.porcentajes_calculados && resultado.porcentajes_calculados.length > 0) {
                    console.log('📊 PORCENTAJES CALCULADOS:');
                    resultado.porcentajes_calculados.forEach(function(depto) {
                        console.log('   ' + (depto.numero || 'N/A') + ': ' + 
                                (depto.porcentaje || '0') + '% - ' + 
                                (depto.superficie_base || '0') + 'm²');
                    });
                }
            } else {
                throw new Error(data.error || 'Error del servidor');
            }
        })
        .catch(function(error) {
            console.error('❌ Error:', error);
            alert('❌ Error: ' + error.message);
        });
    }
        
    function recalcularTodoEdificio() {
        const edificioId = <?php echo $departamento['edificio_id'] ?? 0; ?>;
        
        btnRecalcularEdificio.disabled = true;
        btnRecalcularEdificio.innerHTML = '<i class="bi bi-hourglass-split"></i> Recalculando...';
        
        fetch(`<?php echo $url->to('api/edificios/recalcular-prorrateo'); ?>?edificio_id=${edificioId}`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarMensajeExito('Edificio recalculado exitosamente: ' + data.message);
                // Recargar después de 2 segundos
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                mostrarError('Error: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarError('Error al recalcular edificio');
        })
        .finally(() => {
            btnRecalcularEdificio.disabled = false;
            btnRecalcularEdificio.innerHTML = '<i class="bi bi-buildings"></i> Recalcular Todo el Edificio';
        });
    }
    
    function mostrarDetalleCalculo(data) {
        infoCalculo.classList.remove('d-none');
        
        let factoresHTML = '';
        if (data.factores_aplicados) {
            factoresHTML = `<p><strong>Factores aplicados:</strong> ${data.factores_aplicados}</p>`;
        }
        
        detalleCalculo.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Método:</strong> ${data.metodo}</p>
                    <p><strong>Porcentaje calculado:</strong> ${data.porcentaje_calculado}%</p>
                    <p><strong>Metros cuadrados:</strong> ${data.metros_cuadrados || 'N/A'} m²</p>
                </div>
                <div class="col-md-6">
                    ${factoresHTML}
                    ${data.porcentaje_anterior ? `<p><strong>Anterior:</strong> ${data.porcentaje_anterior}%</p>` : ''}
                    <p class="text-success mb-0">
                        <i class="bi bi-check-circle"></i> Cálculo automático aplicado
                    </p>
                </div>
            </div>
        `;
    }
    
    function mostrarMensajeExito(mensaje) {
        // Crear alerta temporal de éxito
        const alerta = document.createElement('div');
        alerta.className = 'alert alert-success alert-dismissible fade show';
        alerta.innerHTML = `
            <i class="bi bi-check-circle me-2"></i>${mensaje}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.querySelector('.container-fluid').insertBefore(alerta, document.querySelector('.row.mb-4').nextSibling);
        
        // Auto-remover después de 5 segundos
        setTimeout(() => {
            if (alerta.parentNode) {
                alerta.parentNode.removeChild(alerta);
            }
        }, 5000);
    }
    
    function mostrarError(mensaje) {
        // SOLUCIÓN para el error insertBefore:
        const contenedor = document.getElementById('contenedor-errores');
        if (!contenedor) return; // Si no existe, salir silenciosamente
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = mensaje;
        
        // Agregar al final en lugar de insertBefore problemático
        contenedor.appendChild(errorDiv);
    }
    
    // Validación de RUT en tiempo real
    const rutInput = document.querySelector('input[name="propietario_rut"]');
    if (rutInput) {
        rutInput.addEventListener('blur', function() {
            const rut = this.value.trim();
            if (rut && !validarRUT(rut)) {
                this.classList.add('is-invalid');
                mostrarError('El RUT ingresado no es válido');
            } else {
                this.classList.remove('is-invalid');
            }
        });
    }
    
    function validarRUT(rut) {
        // Implementación básica de validación de RUT chileno
        if (!rut || rut.length < 3) return false;
        
        const rutLimpio = rut.replace(/[^0-9kK]/g, '');
        if (rutLimpio.length < 2) return false;
        
        return true; // Validación simplificada
    }
    /*
    function mostrarResultadosDetallados(resultado) {
        console.log('📊 RESULTADOS DETALLADOS:', resultado);
        
        let mensaje = `🎉 ¡CÁLCULO REAL COMPLETADO!\n\n` +
                    `🏢 Edificio: ${resultado.edificio_id}\n` +
                    `📋 Departamentos: ${resultado.departamentos_procesados}\n` +
                    `📊 Total porcentaje: ${resultado.total_porcentaje}%\n` +
                    `📐 Superficie total ajustada: ${resultado.total_superficie_ajustada.toFixed(2)} m²\n\n` +
                    `⚙️ Configuración usada:\n` +
                    `   - Tipo superficie: ${resultado.configuracion_usada.tipo_superficie}\n` +
                    `   - Factores: ${resultado.configuracion_usada.factores_aplicados.join(', ')}\n\n` +
                    `📈 PORCENTAJES CALCULADOS:\n`;
        
        resultado.porcentajes_calculados.forEach(depto => {
            mensaje += `   • ${depto.numero}: ${depto.porcentaje}% (${depto.superficie_base}m² × ${depto.factor_aplicado})\n`;
        });
        
        alert(mensaje);
        
        // Actualizar la función de éxito en el fetch
        .then(data => {
            if (data.success) {
                console.log('✅ Cálculo REAL exitoso!', data.data);
                mostrarResultadosDetallados(data.data);
            } else {
                throw new Error(data.error);
            }
        })
    }*/
});
</script>

<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.card-hover:hover {
    transform: translateY(-2px);
    transition: transform 0.2s ease-in-out;
}

.form-check-input:checked {
    background-color: #198754;
    border-color: #198754;
}

.btn:disabled {
    cursor: not-allowed;
}

.alert {
    border: none;
    border-radius: 0.5rem;
}
</style>