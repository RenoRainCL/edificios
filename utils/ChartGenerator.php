<?php
class ChartGenerator {
    public static function generarChartJSConfig($tipo, $datos, $opciones = []) {
        $configBase = [
            'line' => self::getLineChartConfig($datos, $opciones),
            'bar' => self::getBarChartConfig($datos, $opciones),
            'doughnut' => self::getDoughnutChartConfig($datos, $opciones)
        ];
        return $configBase[$tipo] ?? $configBase['line'];
    }
    // ... métodos restantes
}
?>
