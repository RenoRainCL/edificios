<?php
// 📁 core/ProrrateoManager.php

class ProrrateoManager {
    private $db;
    private $security;
    private $legalManager;
    
    public function __construct() {
        $this->db = DatabaseConnection::getInstance()->getConnection();
        $this->security = SecurityManager::getInstance();
        $this->legalManager = new LegalChileManager();
    }
    
    /**
     * CALCULAR DISTRIBUCIÓN AUTOMÁTICA SEGÚN ESTRATEGIA
     */
    public function calcularDistribucionAutomatica($gastoId, $estrategiaId, $userId) {
        try {
            // Obtener datos del gasto
            $gasto = $this->obtenerGastoCompleto($gastoId, $userId);
            if (!$gasto) {
                throw new Exception("Gasto no encontrado o sin permisos");
            }
            
            // Obtener estrategia configurada
            $estrategia = $this->obtenerEstrategia($estrategiaId);
            if (!$estrategia) {
                throw new Exception("Estrategia de prorrateo no encontrada");
            }
            
            // Obtener departamentos con datos relevantes
            $departamentos = $this->obtenerDepartamentosParaProrrateo($gasto['edificio_id']);
            
            // Aplicar método de cálculo según estrategia
            $distribucion = $this->aplicarMetodoCalculo(
                $departamentos, 
                $gasto['monto_total'], 
                $estrategia, 
                $gasto['edificio_id']
            );
            
            // Validar límites legales si está activado
            if ($estrategia['config_json']['validar_limites'] ?? true) {
                $validacionLegal = $this->validarDistribucionLegal($distribucion, $gasto['edificio_id']);
                if (!$validacionLegal['es_valida']) {
                    throw new Exception("Distribución excede límites legales: " . $validacionLegal['mensaje']);
                }
            }
            
            // Crear registro de prorrateo
            $prorrateoLogId = $this->crearRegistroProrrateo([
                'gasto_comun_id' => $gastoId,
                'estrategia_id' => $estrategiaId,
                'distribucion' => $distribucion,
                'validacion_legal' => $validacionLegal ?? null,
                'usuario_id' => $userId
            ]);
            
            return [
                'success' => true,
                'prorrateo_log_id' => $prorrateoLogId,
                'distribucion' => $distribucion,
                'validacion_legal' => $validacionLegal ?? null
            ];
            
        } catch (Exception $e) {
            error_log("Error en cálculo de prorrateo: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * APLICAR MÉTODO DE CÁLCULO ESPECÍFICO
     */
    private function aplicarMetodoCalculo($departamentos, $montoTotal, $estrategia, $edificioId) {
        $metodo = $estrategia['metodo_calculo'];
        $config = $estrategia['config_json'];
        
        switch ($metodo) {
            case 'porcentaje_copropiedad':
                return $this->calcularPorCopropiedad($departamentos, $montoTotal, $config);
                
            case 'metros_cuadrados':
                return $this->calcularPorMetrosCuadrados($departamentos, $montoTotal, $config, $edificioId);
                
            case 'equitativo':
                return $this->calcularEquitativo($departamentos, $montoTotal, $config);
                
            case 'mixto':
                return $this->calcularMixto($departamentos, $montoTotal, $config, $edificioId);
                
            case 'personalizado':
                return $this->calcularPersonalizado($departamentos, $montoTotal, $config);
                
            default:
                throw new Exception("Método de cálculo no implementado: " . $metodo);
        }
    }
    
    /**
     * CÁLCULO POR PORCENTAJE DE COPROPIEDAD
     */
    private function calcularPorCopropiedad($departamentos, $montoTotal, $config) {
        $distribucion = [];
        $totalPorcentaje = 0;
        
        foreach ($departamentos as $depto) {
            $porcentaje = $depto['porcentaje_copropiedad'] ?? 0;
            
            // Aplicar exenciones si están configuradas
            if ($config['considerar_exenciones'] ?? true) {
                $exencion = $this->obtenerExencionActiva($depto['id']);
                if ($exencion) {
                    $porcentaje = $this->aplicarExencion($porcentaje, $exencion);
                }
            }
            
            $monto = ($montoTotal * $porcentaje) / 100;
            $totalPorcentaje += $porcentaje;
            
            $distribucion[] = [
                'departamento_id' => $depto['id'],
                'numero' => $depto['numero'],
                'propietario' => $depto['propietario_nombre'],
                'porcentaje_copropiedad' => $depto['porcentaje_copropiedad'],
                'porcentaje_aplicado' => $porcentaje,
                'monto' => round($monto, 2),
                'es_exento' => ($porcentaje != $depto['porcentaje_copropiedad']),
                'exencion_aplicada' => $exencion ?? null
            ];
        }
        
        // Redistribuir si hay exenciones y está configurado
        if ($config['redistribuir_exentos'] ?? true && $totalPorcentaje != 100) {
            $distribucion = $this->redistribuirMontos($distribucion, $montoTotal, $totalPorcentaje);
        }
        
        return $distribucion;
    }
    
    /**
     * CÁLCULO POR METROS CUADRADOS
     */
    private function calcularPorMetrosCuadrados($departamentos, $montoTotal, $config, $edificioId) {
        $configEdificio = $this->obtenerConfiguracionEdificio($edificioId);
        $tipoSuperficie = $configEdificio['superficie_considerar'] ?? 'util';
        
        $distribucion = [];
        $totalMetros = 0;
        
        // Calcular total de metros según configuración
        foreach ($departamentos as $depto) {
            $metros = $this->obtenerMetrosDepartamento($depto, $tipoSuperficie);
            $totalMetros += $metros;
        }
        
        // Calcular montos proporcionales
        foreach ($departamentos as $depto) {
            $metros = $this->obtenerMetrosDepartamento($depto, $tipoSuperficie);
            $porcentaje = ($totalMetros > 0) ? ($metros / $totalMetros) * 100 : 0;
            $monto = ($montoTotal * $porcentaje) / 100;
            
            // Ajustar para unidades comerciales si está configurado
            $porcentajeFinal = $this->aplicarAjusteComercial($porcentaje, $depto, $configEdificio);
            $montoFinal = ($montoTotal * $porcentajeFinal) / 100;
            
            $distribucion[] = [
                'departamento_id' => $depto['id'],
                'numero' => $depto['numero'],
                'propietario' => $depto['propietario_nombre'],
                'metros_cuadrados' => $metros,
                'porcentaje_calculado' => $porcentaje,
                'porcentaje_aplicado' => $porcentajeFinal,
                'monto' => round($montoFinal, 2),
                'tipo_superficie' => $tipoSuperficie,
                'ajuste_comercial' => ($porcentajeFinal != $porcentaje)
            ];
        }
        
        return $distribucion;
    }
    
    /**
     * CÁLCULO EQUITATIVO
     */
    private function calcularEquitativo($departamentos, $montoTotal, $config) {
        $departamentosConsiderar = array_filter($departamentos, function($depto) use ($config) {
            // Excluir comerciales si está configurado
            if (($config['excluir_comerciales'] ?? false) && $this->esComercial($depto)) {
                return false;
            }
            // Considerar solo habitados si está configurado
            if (($config['considerar_habitados'] ?? true) && !$depto['is_habitado']) {
                return false;
            }
            return true;
        });
        
        $cantidad = count($departamentosConsiderar);
        if ($cantidad == 0) {
            throw new Exception("No hay departamentos para distribución equitativa");
        }
        
        $montoIndividual = $montoTotal / $cantidad;
        $porcentajeIndividual = 100 / $cantidad;
        
        $distribucion = [];
        foreach ($departamentosConsiderar as $depto) {
            $distribucion[] = [
                'departamento_id' => $depto['id'],
                'numero' => $depto['numero'],
                'propietario' => $depto['propietario_nombre'],
                'porcentaje_aplicado' => $porcentajeIndividual,
                'monto' => round($montoIndividual, 2),
                'es_equitativo' => true
            ];
        }
        
        return $distribucion;
    }
    
    /**
     * VALIDACIÓN LEGAL DE DISTRIBUCIÓN
     */
    public function validarDistribucionLegal($distribucion, $edificioId) {
        $configEdificio = $this->obtenerConfiguracionEdificio($edificioId);
        $maxVariacion = $configEdificio['max_variacion_porcentual'] ?? 20.00;
        
        $porcentajes = array_column($distribucion, 'porcentaje_aplicado');
        $minPorcentaje = min($porcentajes);
        $maxPorcentaje = max($porcentajes);
        $variacion = $maxPorcentaje - $minPorcentaje;
        
        $esValida = $variacion <= $maxVariacion;
        
        return [
            'es_valida' => $esValida,
            'variacion_detectada' => $variacion,
            'variacion_maxima_permitida' => $maxVariacion,
            'departamento_minimo' => $minPorcentaje,
            'departamento_maximo' => $maxPorcentaje,
            'mensaje' => $esValida ? 
                "Distribución cumple con límites legales (variación: {$variacion}%)" :
                "Distribución excede variación máxima permitida ({$variacion}% > {$maxVariacion}%)"
        ];
    }
    
    /**
     * APROBAR DISTRIBUCIÓN DE PRORRATEO
     */
    public function aprobarProrrateo($prorrateoLogId, $userId, $justificacion = null) {
        try {
            $this->db->beginTransaction();
            
            // Verificar que existe y está pendiente
            $prorrateo = $this->obtenerProrrateoLog($prorrateoLogId);
            if (!$prorrateo) {
                throw new Exception("Registro de prorrateo no encontrado");
            }
            
            if ($prorrateo['estado'] != 'pendiente_aprobacion') {
                throw new Exception("El prorrateo no está pendiente de aprobación");
            }
            
            // Actualizar estado
            $sql = "UPDATE gasto_prorrateo_log 
                    SET estado = 'aprobado', 
                        approved_by = ?, 
                        approved_at = NOW(),
                        justificacion_cambios = ?
                    WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId, $justificacion, $prorrateoLogId]);
            
            // Aplicar distribución a la base de datos
            $this->aplicarDistribucion($prorrateoLogId, $userId);
            
            // Registrar en historial
            $this->registrarModificacionHistorial(
                $prorrateoLogId,
                'estado',
                ['anterior' => 'pendiente_aprobacion', 'nuevo' => 'aprobado'],
                'aprobacion',
                "Distribución aprobada por usuario ID: $userId" . ($justificacion ? " - $justificacion" : ""),
                $userId
            );
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Prorrateo aprobado y aplicado exitosamente'
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error al aprobar prorrateo: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * APLICAR DISTRIBUCIÓN A GASTO_DEPARTAMENTO
     */
    private function aplicarDistribucion($prorrateoLogId, $userId) {
        $prorrateo = $this->obtenerProrrateoLog($prorrateoLogId);
        $distribucion = json_decode($prorrateo['distribucion_final_json'], true);
        $gastoId = $prorrateo['gasto_comun_id'];
        
        // Eliminar distribución anterior si existe
        $sqlDelete = "DELETE FROM gasto_departamento WHERE gasto_comun_id = ?";
        $stmt = $this->db->prepare($sqlDelete);
        $stmt->execute([$gastoId]);
        
        // Insertar nueva distribución
        $sqlInsert = "INSERT INTO gasto_departamento 
                     (gasto_comun_id, departamento_id, monto, porcentaje, es_exento, motivo_exencion, porcentaje_original) 
                     VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sqlInsert);
        
        foreach ($distribucion as $item) {
            $stmt->execute([
                $gastoId,
                $item['departamento_id'],
                $item['monto'],
                $item['porcentaje_aplicado'],
                $item['es_exento'] ?? 0,
                $item['exencion_aplicada']['motivo'] ?? null,
                $item['porcentaje_original'] ?? $item['porcentaje_aplicado']
            ]);
        }
        
        // Marcar gasto como distribuido
        $sqlUpdateGasto = "UPDATE gastos_comunes 
                          SET distribucion_confirmada = 1, 
                              hash_distribucion_actual = ?
                          WHERE id = ?";
        
        $hash = $this->generarHashDistribucion($distribucion);
        $stmt = $this->db->prepare($sqlUpdateGasto);
        $stmt->execute([$hash, $gastoId]);
    }
    
    // ... (métodos auxiliares continuarán)
}
?>