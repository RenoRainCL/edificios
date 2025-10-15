<?php
// 📁 core/ProrrateoCalculator.php

class ProrrateoCalculator {
    
    // ==================== MÉTODOS DE CÁLCULO PRINCIPALES ====================
    
    /**
     * CALCULAR DISTRIBUCIÓN MIXTA
     */
    public static function calcularMixto($departamentos, $montoTotal, $config, $edificioId) {
        $pesoCopropiedad = $config['peso_copropiedad'] ?? 50;
        $pesoMetros = $config['peso_metros_cuadrados'] ?? 50;
        
        // Validar que sumen 100%
        if (($pesoCopropiedad + $pesoMetros) != 100) {
            throw new Exception("Los pesos deben sumar 100%");
        }
        
        // Calcular distribución por copropiedad
        $distCopropiedad = self::calcularPorCopropiedad($departamentos, $montoTotal, $config);
        
        // Calcular distribución por metros cuadrados
        $distMetros = self::calcularPorMetrosCuadrados($departamentos, $montoTotal, $config, $edificioId);
        
        // Combinar según pesos
        $distribucionFinal = [];
        foreach ($departamentos as $index => $depto) {
            $montoCopropiedad = $distCopropiedad[$index]['monto'];
            $montoMetros = $distMetros[$index]['monto'];
            
            $montoFinal = ($montoCopropiedad * $pesoCopropiedad / 100) + 
                         ($montoMetros * $pesoMetros / 100);
            
            $porcentajeFinal = ($montoFinal / $montoTotal) * 100;
            
            $distribucionFinal[] = [
                'departamento_id' => $depto['id'],
                'numero' => $depto['numero'],
                'propietario' => $depto['propietario_nombre'],
                'monto_copropiedad' => $montoCopropiedad,
                'monto_metros' => $montoMetros,
                'monto_final' => round($montoFinal, 2),
                'porcentaje_aplicado' => round($porcentajeFinal, 2),
                'peso_copropiedad' => $pesoCopropiedad,
                'peso_metros' => $pesoMetros
            ];
        }
        
        return $distribucionFinal;
    }
    
    /**
     * CALCULAR POR PORCENTAJE DE COPROPIEDAD
     */
    public static function calcularPorCopropiedad($departamentos, $montoTotal, $config) {
        $distribucion = [];
        $totalPorcentaje = 0;
        
        foreach ($departamentos as $depto) {
            $porcentaje = $depto['porcentaje_copropiedad'] ?? 0;
            $monto = ($montoTotal * $porcentaje) / 100;
            $totalPorcentaje += $porcentaje;
            
            $distribucion[] = [
                'departamento_id' => $depto['id'],
                'numero' => $depto['numero'],
                'propietario' => $depto['propietario_nombre'],
                'porcentaje_copropiedad' => $porcentaje,
                'porcentaje_aplicado' => $porcentaje,
                'monto' => round($monto, 2),
                'es_exento' => false
            ];
        }
        
        // Redistribuir si no suma 100%
        if (abs($totalPorcentaje - 100) > 0.01) {
            $distribucion = self::redistribuirMontos($distribucion, $montoTotal, $totalPorcentaje);
        }
        
        return $distribucion;
    }
    
    /**
     * CALCULAR POR METROS CUADRADOS
     */
    public static function calcularPorMetrosCuadrados($departamentos, $montoTotal, $config, $edificioId) {
        $tipoSuperficie = $config['superficie_considerar'] ?? 'util';
        $distribucion = [];
        $totalMetros = 0;
        
        // Calcular total de metros
        foreach ($departamentos as $depto) {
            $metros = self::obtenerMetrosDepartamento($depto, $tipoSuperficie);
            $totalMetros += $metros;
        }
        
        // Calcular montos proporcionales
        foreach ($departamentos as $depto) {
            $metros = self::obtenerMetrosDepartamento($depto, $tipoSuperficie);
            $porcentaje = ($totalMetros > 0) ? ($metros / $totalMetros) * 100 : 0;
            $monto = ($montoTotal * $porcentaje) / 100;
            
            $distribucion[] = [
                'departamento_id' => $depto['id'],
                'numero' => $depto['numero'],
                'propietario' => $depto['propietario_nombre'],
                'metros_cuadrados' => $metros,
                'porcentaje_aplicado' => $porcentaje,
                'monto' => round($monto, 2),
                'tipo_superficie' => $tipoSuperficie
            ];
        }
        
        return $distribucion;
    }
    
    /**
     * CALCULAR DISTRIBUCIÓN EQUITATIVA
     */
    public static function calcularEquitativo($departamentos, $montoTotal, $config) {
        $departamentosConsiderar = array_filter($departamentos, function($depto) use ($config) {
            if (($config['excluir_comerciales'] ?? false) && self::esComercial($depto)) {
                return false;
            }
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
    
    // ==================== MÉTODOS DE REDISTRIBUCIÓN ====================
    
    /**
     * REDISTRIBUIR MONTOS CUANDO HAY EXENCIONES
     */
    public static function redistribuirMontos($distribucion, $montoTotal, $totalPorcentajeActual) {
        if (abs($totalPorcentajeActual - 100) < 0.01) {
            return $distribucion;
        }
        
        $factorRedistribucion = 100 / $totalPorcentajeActual;
        $nuevaDistribucion = [];
        $nuevoTotal = 0;
        
        foreach ($distribucion as $item) {
            $nuevoPorcentaje = $item['porcentaje_aplicado'] * $factorRedistribucion;
            $nuevoMonto = ($montoTotal * $nuevoPorcentaje) / 100;
            $nuevoTotal += $nuevoPorcentaje;
            
            $item['porcentaje_aplicado'] = round($nuevoPorcentaje, 4);
            $item['monto'] = round($nuevoMonto, 2);
            $item['redistribuido'] = true;
            
            $nuevaDistribucion[] = $item;
        }
        
        // Ajuste final por redondeo
        if (abs($nuevoTotal - 100) > 0.01) {
            $nuevaDistribucion = self::ajustarRedondeo($nuevaDistribucion, $montoTotal, $nuevoTotal);
        }
        
        return $nuevaDistribucion;
    }
    
    /**
     * AJUSTAR DIFERENCIAS POR REDONDEO
     */
    public static function ajustarRedondeo($distribucion, $montoTotal, $totalPorcentaje) {
        $diferencia = 100 - $totalPorcentaje;
        $ajustePorDepartamento = $diferencia / count($distribucion);
        
        foreach ($distribucion as &$item) {
            $item['porcentaje_aplicado'] += $ajustePorDepartamento;
            $item['monto'] = ($montoTotal * $item['porcentaje_aplicado']) / 100;
            $item['ajuste_redondeo'] = true;
        }
        
        return $distribucion;
    }
    
    // ==================== MÉTODOS DE UTILIDAD ====================
    
    /**
     * GENERAR HASH DE INTEGRIDAD PARA DISTRIBUCIÓN
     */
    public static function generarHashDistribucion($distribucion) {
        $datosParaHash = array_map(function($item) {
            return [
                'departamento_id' => $item['departamento_id'],
                'monto' => $item['monto'],
                'porcentaje' => $item['porcentaje_aplicado']
            ];
        }, $distribucion);
        
        // Ordenar para consistencia
        usort($datosParaHash, function($a, $b) {
            return $a['departamento_id'] <=> $b['departamento_id'];
        });
        
        return hash('sha256', json_encode($datosParaHash));
    }
    
    /**
     * VALIDAR SUMA DE PORCENTAJES
     */
    public static function validarSumaPorcentajes($distribucion, $tolerancia = 0.01) {
        $total = array_sum(array_column($distribucion, 'porcentaje_aplicado'));
        return abs($total - 100) <= $tolerancia;
    }
    
    /**
     * OBTENER MÁXIMA VARIACIÓN EN DISTRIBUCIÓN
     */
    public static function obtenerMaximaVariacion($distribucion) {
        $porcentajes = array_column($distribucion, 'porcentaje_aplicado');
        return max($porcentajes) - min($porcentajes);
    }
    
    // ==================== MÉTODOS AUXILIARES ====================
    
    private static function obtenerMetrosDepartamento($depto, $tipoSuperficie) {
        // Por simplicidad, asumimos metros_cuadrados
        // En sistema real, manejar diferentes tipos de superficie
        return floatval($depto['metros_cuadrados'] ?? 0);
    }
    
    private static function esComercial($depto) {
        // Determinar si es comercial basado en número
        return strpos(strtoupper($depto['numero'] ?? ''), 'L') === 0;
    }
    
    /**
     * FORMATEAR DISTRIBUCIÓN PARA VISUALIZACIÓN
     */
    public static function formatearDistribucionParaVista($distribucion) {
        return array_map(function($item) {
            return [
                'departamento' => $item['numero'],
                'propietario' => $item['propietario'],
                'porcentaje' => number_format($item['porcentaje_aplicado'], 2) . '%',
                'monto' => '$' . number_format($item['monto'], 0, ',', '.'),
                'detalles' => $item['metodo'] ?? 'estándar'
            ];
        }, $distribucion);
    }
}
?>