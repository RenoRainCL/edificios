<?php
// 📁 controllers/ReportesController.php

class ReportesController extends ControllerCore {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function financieros() {
        $userEdificios = $this->getUserEdificios($_SESSION['user_id']);
        $edificioId = $_GET['edificio_id'] ?? null;
        $anio = $_GET['anio'] ?? date('Y');
        $mes = $_GET['mes'] ?? null;
        $tipoReporte = $_GET['tipo_reporte'] ?? 'general';
        
        // Si no se especifica edificio, usar el primero disponible
        if (!$edificioId && !empty($userEdificios)) {
            $edificioId = $userEdificios[0]['id'];
        }
        
        $reporteData = [];
        if ($edificioId) {
            $this->checkEdificioAccess($edificioId);
            $reporteData = $this->generarReporteFinanciero($edificioId, $anio, $mes, $tipoReporte);
        }
        
        $data = [
            'edificios' => $userEdificios,
            'edificio_actual' => $edificioId ? $this->getEdificioById($edificioId) : null,
            'reporte' => $reporteData,
            'filtros' => [
                'anio' => $anio,
                'mes' => $mes,
                'tipo_reporte' => $tipoReporte
            ],
            'user_name' => $_SESSION['user_name'],
            'flash_messages' => $this->getFlashMessages()
        ];
        
        $this->renderView('finanzas/reportes', $data);
    }
    
    public function exportarPDF() {
        $edificioId = $_GET['edificio_id'] ?? null;
        $anio = $_GET['anio'] ?? date('Y');
        $mes = $_GET['mes'] ?? null;
        
        if (!$edificioId) {
            $this->jsonResponse(false, [], 'Selecciona un edificio');
        }
        
        $this->checkEdificioAccess($edificioId);
        
        try {
            $reporteData = $this->generarReporteFinanciero($edificioId, $anio, $mes);
            $edificio = $this->getEdificioById($edificioId);
            
            // Aquí iría la lógica para generar PDF
            // Por ahora retornamos los datos para el PDF
            $this->jsonResponse(true, [
                'reporte' => $reporteData,
                'edificio' => $edificio,
                'filtros' => ['anio' => $anio, 'mes' => $mes]
            ], 'PDF generado exitosamente');
            
        } catch (Exception $e) {
            $this->jsonResponse(false, [], 'Error al generar PDF: ' . $e->getMessage());
        }
    }
    
    public function exportarExcel() {
        $edificioId = $_GET['edificio_id'] ?? null;
        $anio = $_GET['anio'] ?? date('Y');
        $mes = $_GET['mes'] ?? null;
        
        if (!$edificioId) {
            $this->jsonResponse(false, [], 'Selecciona un edificio');
        }
        
        $this->checkEdificioAccess($edificioId);
        
        try {
            $reporteData = $this->generarReporteFinanciero($edificioId, $anio, $mes);
            $edificio = $this->getEdificioById($edificioId);
            
            // Aquí iría la lógica para exportar a Excel
            $this->jsonResponse(true, [
                'reporte' => $reporteData,
                'edificio' => $edificio,
                'filtros' => ['anio' => $anio, 'mes' => $mes]
            ], 'Excel exportado exitosamente');
            
        } catch (Exception $e) {
            $this->jsonResponse(false, [], 'Error al exportar Excel: ' . $e->getMessage());
        }
    }
    
    // ========== MÉTODOS PRIVADOS ==========
    
    private function generarReporteFinanciero($edificioId, $anio, $mes = null, $tipoReporte = 'general') {
        $reporte = [
            'metricas' => $this->getMetricasFinancieras($edificioId, $anio, $mes),
            'tendencia_mensual' => $this->getTendenciaMensual($edificioId, $anio),
            'top_morosos' => $this->getTopMorosos($edificioId, $anio, $mes),
            'mejores_pagadores' => $this->getMejoresPagadores($edificioId, $anio),
            'distribucion_morosidad' => $this->getDistribucionMorosidad($edificioId),
            'comparativa_edificios' => $this->getComparativaEdificios($anio, $mes)
        ];
        
        return $reporte;
    }
    
    private function getMetricasFinancieras($edificioId, $anio, $mes = null) {
        $filtroMes = $mes ? "AND MONTH(gc.periodo) = ?" : "";
        $params = [$edificioId, $anio];
        if ($mes) $params[] = $mes;
        
        $sql = "SELECT 
                SUM(gc.monto_total) as gastos_totales,
                SUM(CASE WHEN p.estado = 'pagado' THEN p.monto ELSE 0 END) as recaudacion,
                SUM(CASE WHEN p.estado IN ('pendiente', 'atrasado') THEN gd.monto ELSE 0 END) as morosidad,
                COUNT(DISTINCT d.id) as total_departamentos,
                COUNT(DISTINCT CASE WHEN p.estado = 'pagado' THEN d.id END) as deptos_pagados,
                COUNT(DISTINCT CASE WHEN p.estado IN ('pendiente', 'atrasado') THEN d.id END) as deptos_morosos
                FROM gastos_comunes gc
                JOIN gasto_departamento gd ON gc.id = gd.gasto_comun_id
                JOIN departamentos d ON gd.departamento_id = d.id
                LEFT JOIN pagos p ON gd.gasto_comun_id = p.gasto_comun_id AND gd.departamento_id = p.departamento_id
                WHERE gc.edificio_id = ? AND YEAR(gc.periodo) = ? $filtroMes";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $metricas = $stmt->fetch();
        
        // Calcular porcentajes
        $metricas['porcentaje_recaudacion'] = $metricas['gastos_totales'] > 0 ? 
            ($metricas['recaudacion'] / $metricas['gastos_totales']) * 100 : 0;
        
        $metricas['promedio_pago'] = $metricas['deptos_pagados'] > 0 ? 
            $metricas['recaudacion'] / $metricas['deptos_pagados'] : 0;
            
        return $metricas;
    }
    
    private function getTendenciaMensual($edificioId, $anio) {
        $sql = "SELECT 
                MONTH(gc.periodo) as mes,
                SUM(gc.monto_total) as gastos_generados,
                SUM(CASE WHEN p.estado = 'pagado' THEN p.monto ELSE 0 END) as recaudacion
                FROM gastos_comunes gc
                LEFT JOIN pagos p ON gc.id = p.gasto_comun_id
                WHERE gc.edificio_id = ? AND YEAR(gc.periodo) = ?
                GROUP BY MONTH(gc.periodo)
                ORDER BY mes";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$edificioId, $anio]);
        $datos = $stmt->fetchAll();
        
        // Completar meses faltantes
        $tendencia = [];
        for ($mes = 1; $mes <= 12; $mes++) {
            $encontrado = false;
            foreach ($datos as $dato) {
                if ($dato['mes'] == $mes) {
                    $tendencia[] = $dato;
                    $encontrado = true;
                    break;
                }
            }
            if (!$encontrado) {
                $tendencia[] = [
                    'mes' => $mes,
                    'gastos_generados' => 0,
                    'recaudacion' => 0
                ];
            }
        }
        
        return $tendencia;
    }
    
    private function getTopMorosos($edificioId, $anio, $mes = null, $limit = 10) {
        $filtroMes = $mes ? "AND MONTH(gc.periodo) = ?" : "";
        $params = [$edificioId, $anio];
        if ($mes) $params[] = $mes;
        $params[] = $limit;
        
        $sql = "SELECT 
                d.id,
                d.numero,
                d.propietario_nombre,
                SUM(gd.monto) as deuda_total,
                COUNT(DISTINCT gc.id) as meses_atraso,
                MAX(gc.periodo) as ultimo_periodo
                FROM departamentos d
                JOIN gasto_departamento gd ON d.id = gd.departamento_id
                JOIN gastos_comunes gc ON gd.gasto_comun_id = gc.id
                LEFT JOIN pagos p ON gd.gasto_comun_id = p.gasto_comun_id AND gd.departamento_id = p.departamento_id
                WHERE d.edificio_id = ? AND YEAR(gc.periodo) = ? 
                AND (p.estado IS NULL OR p.estado IN ('pendiente', 'atrasado'))
                $filtroMes
                GROUP BY d.id, d.numero, d.propietario_nombre
                ORDER BY deuda_total DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    private function getMejoresPagadores($edificioId, $anio, $limit = 10) {
        $sql = "SELECT 
                d.id,
                d.numero,
                d.propietario_nombre,
                COUNT(DISTINCT p.id) as total_pagos,
                SUM(p.monto) as total_pagado,
                (COUNT(DISTINCT p.id) / 
                 (SELECT COUNT(DISTINCT gc.id) 
                  FROM gastos_comunes gc 
                  WHERE gc.edificio_id = ? AND YEAR(gc.periodo) = ?)) * 100 as puntualidad
                FROM departamentos d
                JOIN pagos p ON d.id = p.departamento_id
                JOIN gastos_comunes gc ON p.gasto_comun_id = gc.id
                WHERE d.edificio_id = ? AND YEAR(gc.periodo) = ? AND p.estado = 'pagado'
                GROUP BY d.id, d.numero, d.propietario_nombre
                ORDER BY puntualidad DESC, total_pagado DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$edificioId, $anio, $edificioId, $anio, $limit]);
        return $stmt->fetchAll();
    }
    
    private function getDistribucionMorosidad($edificioId) {
        $sql = "SELECT 
                CASE 
                    WHEN DATEDIFF(CURDATE(), gc.fecha_vencimiento) <= 30 THEN 'Al día (0-30 días)'
                    WHEN DATEDIFF(CURDATE(), gc.fecha_vencimiento) <= 60 THEN 'Morosidad leve (31-60 días)'
                    WHEN DATEDIFF(CURDATE(), gc.fecha_vencimiento) <= 90 THEN 'Morosidad grave (61-90 días)'
                    ELSE 'Morosidad crítica (>90 días)'
                END as categoria,
                COUNT(DISTINCT d.id) as cantidad_deptos,
                SUM(gd.monto) as monto_total
                FROM departamentos d
                JOIN gasto_departamento gd ON d.id = gd.departamento_id
                JOIN gastos_comunes gc ON gd.gasto_comun_id = gc.id
                LEFT JOIN pagos p ON gd.gasto_comun_id = p.gasto_comun_id AND gd.departamento_id = p.departamento_id
                WHERE d.edificio_id = ? 
                AND (p.estado IS NULL OR p.estado IN ('pendiente', 'atrasado'))
                AND gc.fecha_vencimiento < CURDATE()
                GROUP BY categoria";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$edificioId]);
        return $stmt->fetchAll();
    }
    
    private function getComparativaEdificios($anio, $mes = null) {
        $userEdificios = $this->getUserEdificios($_SESSION['user_id']);
        $edificioIds = array_column($userEdificios, 'id');
        
        if (empty($edificioIds)) return [];
        
        $placeholders = str_repeat('?,', count($edificioIds) - 1) . '?';
        $filtroMes = $mes ? "AND MONTH(gc.periodo) = ?" : "";
        
        $params = $edificioIds;
        $params[] = $anio;
        if ($mes) $params[] = $mes;
        
        $sql = "SELECT 
                e.id,
                e.nombre,
                COUNT(DISTINCT d.id) as total_departamentos,
                SUM(gc.monto_total) as gastos_generados,
                SUM(CASE WHEN p.estado = 'pagado' THEN p.monto ELSE 0 END) as recaudacion,
                SUM(CASE WHEN p.estado IN ('pendiente', 'atrasado') THEN gd.monto ELSE 0 END) as morosidad,
                COUNT(DISTINCT CASE WHEN p.estado = 'pagado' THEN d.id END) as deptos_al_dia,
                COUNT(DISTINCT CASE WHEN p.estado IN ('pendiente', 'atrasado') THEN d.id END) as deptos_morosos
                FROM edificios e
                JOIN departamentos d ON e.id = d.edificio_id
                JOIN gastos_comunes gc ON e.id = gc.edificio_id
                JOIN gasto_departamento gd ON gc.id = gd.gasto_comun_id AND d.id = gd.departamento_id
                LEFT JOIN pagos p ON gd.gasto_comun_id = p.gasto_comun_id AND gd.departamento_id = p.departamento_id
                WHERE e.id IN ($placeholders) AND YEAR(gc.periodo) = ? $filtroMes
                GROUP BY e.id, e.nombre";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $resultados = $stmt->fetchAll();
        
        // Calcular porcentajes
        foreach ($resultados as &$edificio) {
            $edificio['porcentaje_recaudacion'] = $edificio['gastos_generados'] > 0 ? 
                ($edificio['recaudacion'] / $edificio['gastos_generados']) * 100 : 0;
        }
        
        return $resultados;
    }
}
?>