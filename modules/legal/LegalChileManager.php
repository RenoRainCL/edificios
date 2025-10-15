<?php
// 📁 modules/legal/LegalChileManager.php
class LegalChileManager {
    private $db;
    private $googleCloud;
    
    public function __construct() {
        $this->db = DatabaseConnection::getInstance()->getConnection();
        $this->googleCloud = new GoogleCloudManager();
    }
    
    /**
     * Ley 19.537 sobre Copropiedad Inmobiliaria
     */
    public function verificarCumplimientoLeyCopropiedad($edificioId) {
        $requisitos = [
            'reglamento_copropiedad' => $this->tieneReglamentoCopropiedad($edificioId),
            'comite_administracion' => $this->tieneComiteAdministracion($edificioId),
            'libro_edificio' => $this->tieneLibroEdificio($edificioId),
            'balances_actualizados' => $this->tieneBalancesActualizados($edificioId),
            'seguro_obligatorio' => $this->tieneSeguroObligatorio($edificioId)
        ];
        
        return [
            'cumplimiento' => !in_array(false, $requisitos),
            'detalles' => $requisitos,
            'ley' => 'Ley 19.537 sobre Copropiedad Inmobiliaria'
        ];
    }
    
    public function generarActaReunion($edificioId, $datosReunion) {
        $edificio = $this->getEdificioData($edificioId);
        $asistentes = $datosReunion['asistentes'];
        $temas = $datosReunion['temas'];
        $acuerdos = $datosReunion['acuerdos'];
        
        $contenido = $this->generarContenidoActa($edificio, $asistentes, $temas, $acuerdos);
        
        // Guardar como documento legal
        return $this->guardarDocumentoLegal(
            $edificioId,
            'acta',
            "Acta de Reunión - " . date('d-m-Y'),
            $contenido,
            true
        );
    }
    
    private function generarContenidoActa($edificio, $asistentes, $temas, $acuerdos) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Acta de Reunión</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; }
                .section { margin: 20px 0; }
                .firma { margin-top: 50px; border-top: 1px solid #333; padding-top: 10px; }
            </style>
        </head>
        <body>
            <div class="header">
                <h2>ACTA DE REUNIÓN DE COMITÉ DE ADMINISTRACIÓN</h2>
                <h3><?= htmlspecialchars($edificio['nombre']) ?></h3>
                <p><?= htmlspecialchars($edificio['direccion']) ?>, <?= htmlspecialchars($edificio['comuna']) ?></p>
                <p>Fecha: <?= date('d/m/Y') ?></p>
            </div>
            
            <div class="section">
                <h4>ASISTENTES:</h4>
                <ul>
                    <?php foreach ($asistentes as $asistente): ?>
                    <li><?= htmlspecialchars($asistente['nombre']) ?> - <?= htmlspecialchars($asistente['cargo']) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <div class="section">
                <h4>TEMAS TRATADOS:</h4>
                <ol>
                    <?php foreach ($temas as $tema): ?>
                    <li><?= htmlspecialchars($tema) ?></li>
                    <?php endforeach; ?>
                </ol>
            </div>
            
            <div class="section">
                <h4>ACUERDOS:</h4>
                <ol>
                    <?php foreach ($acuerdos as $acuerdo): ?>
                    <li><?= htmlspecialchars($acuerdo) ?></li>
                    <?php endforeach; ?>
                </ol>
            </div>
            
            <div class="firma">
                <p>_________________________</p>
                <p>Presidente Comité de Administración</p>
                <p><?= htmlspecialchars($edificio['nombre']) ?></p>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
    
    public function generarBalanceAnual($edificioId, $anio) {
        $ingresos = $this->getIngresosAnuales($edificioId, $anio);
        $gastos = $this->getGastosAnuales($edificioId, $anio);
        $deudas = $this->getDeudasPendientes($edificioId);
        
        $contenido = $this->generarContenidoBalance($edificioId, $anio, $ingresos, $gastos, $deudas);
        
        return $this->guardarDocumentoLegal(
            $edificioId,
            'balance',
            "Balance Anual {$anio}",
            $contenido,
            true
        );
    }
    
    /**
     * Ley de Protección de Datos Personales
     */
    public function verificarProteccionDatos($edificioId) {
        $security = SecurityManager::getInstance();
        $encryptedFields = $security->getEncryptedFields();
        
        return [
            'ley' => 'Ley 19.628 sobre Protección de Datos Personales',
            'datos_encriptados' => count($encryptedFields),
            'politica_privacidad' => $this->tienePoliticaPrivacidad($edificioId),
            'consentimiento_residentes' => $this->tieneConsentimientoResidentes($edificioId)
        ];
    }
    
    private function tieneReglamentoCopropiedad($edificioId) {
        $sql = "SELECT COUNT(*) as count FROM documentos_legales 
                WHERE edificio_id = ? AND tipo_documento = 'reglamento'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$edificioId]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
    
    private function guardarDocumentoLegal($edificioId, $tipo, $nombre, $contenido, $isPublic = false) {
        // Subir a Google Cloud
        $uploadResult = $this->googleCloud->uploadFile(
            $contenido,
            $nombre . '.html',
            'documentos_legales'
        );
        
        if ($uploadResult['success']) {
            $sql = "INSERT INTO documentos_legales (edificio_id, tipo_documento, nombre, descripcion, archivo_path, google_drive_id, is_public) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $edificioId,
                $tipo,
                $nombre,
                "Documento generado automáticamente",
                $uploadResult['file_path'],
                $uploadResult['google_drive_id'],
                $isPublic
            ]);
            
            return $this->db->lastInsertId();
        }
        
        return false;
    }
}
?>