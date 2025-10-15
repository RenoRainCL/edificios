<?php

// 📁 controllers/FinanzasController.php

class FinanzasController extends ControllerCore
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Vista principal de Gastos Comunes.
     */
    public function gastosComunes()
    {
        $this->requirePermission('finanzas', 'read');

        $userId = $_SESSION['user_id'];
        $userEdificios = $this->getUserAccessibleEdificios();

        // Obtener gastos comunes para los edificios del usuario
        $edificioIds = array_column($userEdificios, 'id');
        $gastos = $this->getGastosComunes($edificioIds);

        $data = [
            'edificios' => $userEdificios,
            'gastos' => $gastos,
            'user_name' => $_SESSION['user_name'],
            'page_title' => 'Gestión de Gastos Comunes',
        ];

        $this->renderView('finanzas/gastos_comunes', $data);
    }

    /**
     * Formulario para crear nuevo gasto común.
     */
    public function crearGasto()
    {
        $this->requirePermission('finanzas', 'write');

        $userId = $_SESSION['user_id'];
        $userEdificios = $this->getUserAccessibleEdificios();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->procesarCrearGasto($userId);

            return;
        }

        $data = [
            'edificios' => $userEdificios,
            'user_name' => $_SESSION['user_name'],
            'page_title' => 'Crear Gasto Común',
            'periodo_actual' => date('Y-m'),
            'fecha_vencimiento' => date('Y-m-d', strtotime('+10 days')),
        ];

        $this->renderView('finanzas/crear_gasto', $data);
    }

    /**
     * Procesar creación de gasto común.
     */
    private function procesarCrearGasto($userId)
    {
        try {
            $edificioId = $_POST['edificio_id'] ?? null;
            $nombre = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $montoTotal = floatval($_POST['monto_total'] ?? 0);
            $periodo = $_POST['periodo'] ?? '';
            $fechaVencimiento = $_POST['fecha_vencimiento'] ?? '';

            // Validaciones
            $errors = [];
            if (empty($edificioId)) {
                $errors[] = 'Debe seleccionar un edificio';
            }
            if (empty($nombre)) {
                $errors[] = 'El nombre del gasto es requerido';
            }
            if ($montoTotal <= 0) {
                $errors[] = 'El monto debe ser mayor a 0';
            }
            if (empty($periodo)) {
                $errors[] = 'El período es requerido';
            }
            if (empty($fechaVencimiento)) {
                $errors[] = 'La fecha de vencimiento es requerida';
            }

            // Verificar que el usuario tenga acceso al edificio
            $userEdificios = $this->getUserAccessibleEdificios();
            $edificioIds = array_column($userEdificios, 'id');

            if (!in_array($edificioId, $edificioIds)) {
                $errors[] = 'No tiene permisos para gestionar este edificio';
            }

            if (!empty($errors)) {
                $_SESSION['form_errors'] = $errors;
                $_SESSION['form_data'] = $_POST;
                $this->redirect('finanzas/gastos-comunes/crear');

                return;
            }

            // Iniciar transacción
            $this->db->beginTransaction();

            // Insertar gasto común
            $sqlGasto = "INSERT INTO gastos_comunes 
                        (edificio_id, nombre, descripcion, monto_total, periodo, fecha_vencimiento, estado, created_by) 
                        VALUES (?, ?, ?, ?, ?, ?, 'pendiente', ?)";

            $stmt = $this->db->prepare($sqlGasto);
            $stmt->execute([
                $edificioId, $nombre, $descripcion, $montoTotal, $periodo, $fechaVencimiento, $userId,
            ]);

            $gastoId = $this->db->lastInsertId();

            // Calcular y distribuir gastos por departamento
            $this->distribuirGastoDepartamentos($gastoId, $edificioId, $montoTotal);

            // Confirmar transacción
            $this->db->commit();

            $_SESSION['success_message'] = 'Gasto común creado exitosamente';
            $this->redirect('finanzas/gastos-comunes');
            exit;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('Error al crear gasto común: '.$e->getMessage());
            $_SESSION['form_errors'] = ['Error al crear el gasto común: '.$e->getMessage()];
            $this->redirect('finanzas/gastos-comunes/crear');
        }
    }

    /**
     * Distribuir gasto entre departamentos.
     */
    private function distribuirGastoDepartamentos($gastoId, $edificioId, $montoTotal)
    {
        // Obtener departamentos del edificio con su porcentaje de copropiedad
        $sqlDeptos = 'SELECT id, porcentaje_copropiedad 
                     FROM departamentos 
                     WHERE edificio_id = ? AND is_habitado = 1';

        $stmt = $this->db->prepare($sqlDeptos);
        $stmt->execute([$edificioId]);
        $departamentos = $stmt->fetchAll();

        if (empty($departamentos)) {
            throw new Exception('No hay departamentos registrados en este edificio');
        }

        // Calcular suma total de porcentajes para normalizar
        $totalPorcentaje = array_sum(array_column($departamentos, 'porcentaje_copropiedad'));

        // Insertar distribución por departamento
        $sqlDistribucion = 'INSERT INTO gasto_departamento 
                           (gasto_comun_id, departamento_id, monto, porcentaje) 
                           VALUES (?, ?, ?, ?)';

        $stmt = $this->db->prepare($sqlDistribucion);

        foreach ($departamentos as $depto) {
            $porcentaje = $depto['porcentaje_copropiedad'];

            // Normalizar si la suma no es 100%
            if ($totalPorcentaje != 100) {
                $porcentaje = ($porcentaje / $totalPorcentaje) * 100;
            }

            $montoDepto = ($montoTotal * $porcentaje) / 100;

            $stmt->execute([
                $gastoId,
                $depto['id'],
                round($montoDepto, 2),
                round($porcentaje, 2),
            ]);
        }
        $this->generarPagosPendientes($gastoId, $edificioId);
    }

    /**
     * Editar gasto común.
     */
    public function editarGasto($gastoId)
    {
        $this->requirePermission('finanzas', 'write');

        $userId = $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->procesarEditarGasto($gastoId, $userId);

            return;
        }

        // Obtener datos del gasto
        $gasto = $this->getGastoComunById($gastoId, $userId);

        if (!$gasto) {
            $_SESSION['error_message'] = 'Gasto común no encontrado';
            $this->redirect('finanzas/gastos-comunes');
            exit;
        }

        $userEdificios = $this->getUserAccessibleEdificios();

        $data = [
            'gasto' => $gasto,
            'edificios' => $userEdificios,
            'user_name' => $_SESSION['user_name'],
            'page_title' => 'Editar Gasto Común',
        ];

        $this->renderView('finanzas/editar_gasto', $data);
    }

    /**
     * Procesar edición de gasto común.
     */
    private function procesarEditarGasto($gastoId, $userId)
    {
        try {
            $gasto = $this->getGastoComunById($gastoId, $userId);

            if (!$gasto) {
                $_SESSION['error_message'] = 'Gasto común no encontrado';
                $this->redirect('finanzas/gastos-comunes');
            }

            // Solo permitir edición si está pendiente
            if ($gasto['estado'] !== 'pendiente') {
                $_SESSION['error_message'] = 'Solo se pueden editar gastos en estado pendiente';
                $this->redirect('finanzas/gastos-comunes');
            }

            $nombre = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $montoTotal = floatval($_POST['monto_total'] ?? 0);
            $fechaVencimiento = $_POST['fecha_vencimiento'] ?? '';

            // Validaciones
            $errors = [];
            if (empty($nombre)) {
                $errors[] = 'El nombre del gasto es requerido';
            }
            if ($montoTotal <= 0) {
                $errors[] = 'El monto debe ser mayor a 0';
            }
            if (empty($fechaVencimiento)) {
                $errors[] = 'La fecha de vencimiento es requerida';
            }

            if (!empty($errors)) {
                $_SESSION['form_errors'] = $errors;
                $_SESSION['form_data'] = $_POST;
                $this->redirect("finanzas/gastos-comunes/editar/$gastoId");
            }

            // Iniciar transacción
            $this->db->beginTransaction();

            // Actualizar gasto común
            $sqlUpdate = 'UPDATE gastos_comunes 
                         SET nombre = ?, descripcion = ?, monto_total = ?, fecha_vencimiento = ?
                         WHERE id = ?';

            $stmt = $this->db->prepare($sqlUpdate);
            $stmt->execute([$nombre, $descripcion, $montoTotal, $fechaVencimiento, $gastoId]);

            // Recalcular distribución si el monto cambió
            if ($montoTotal != $gasto['monto_total']) {
                // Eliminar distribución anterior
                $sqlDeleteDist = 'DELETE FROM gasto_departamento WHERE gasto_comun_id = ?';
                $stmt = $this->db->prepare($sqlDeleteDist);
                $stmt->execute([$gastoId]);

                // Crear nueva distribución
                $this->distribuirGastoDepartamentos($gastoId, $gasto['edificio_id'], $montoTotal);
            }

            // Confirmar transacción
            $this->db->commit();

            $_SESSION['success_message'] = 'Gasto común actualizado exitosamente';
            $this->redirect('finanzas/gastos-comunes');
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('Error al editar gasto común: '.$e->getMessage());
            $_SESSION['form_errors'] = ['Error al actualizar el gasto común: '.$e->getMessage()];
            $this->redirect("finanzas/gastos-comunes/editar/$gastoId");
        }
    }

    /**
     * Ver detalle de gasto común.
     */
    public function verGasto($gastoId)
    {
        $this->requirePermission('finanzas', 'read');

        $userId = $_SESSION['user_id'];

        $gasto = $this->getGastoComunById($gastoId, $userId);

        if (!$gasto) {
            $_SESSION['error_message'] = 'Gasto común no encontrado';
            $this->redirect('finanzas/gastos-comunes');
        }

        // Obtener distribución por departamento
        $distribucion = $this->getDistribucionGasto($gastoId);

        // Obtener estado de pagos
        $estadoPagos = $this->getEstadoPagosGasto($gastoId);

        $data = [
            'gasto' => $gasto,
            'distribucion' => $distribucion,
            'estado_pagos' => $estadoPagos,
            'user_name' => $_SESSION['user_name'],
            'page_title' => 'Detalle de Gasto Común',
        ];

        $this->renderView('finanzas/ver_gasto', $data);
    }

    /**
     * Cerrar gasto común (marcar como cerrado).
     */
    public function cerrarGasto($gastoId)
    {
        $this->requirePermission('finanzas', 'write');

        $userId = $_SESSION['user_id'];

        try {
            $gasto = $this->getGastoComunById($gastoId, $userId);

            if (!$gasto) {
                $_SESSION['error_message'] = 'Gasto común no encontrado';
                $this->redirect('finanzas/gastos-comunes');
            }

            $sql = "UPDATE gastos_comunes SET estado = 'cerrado' WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$gastoId]);

            $_SESSION['success_message'] = 'Gasto común cerrado exitosamente';
        } catch (Exception $e) {
            error_log('Error al cerrar gasto común: '.$e->getMessage());
            $_SESSION['error_message'] = 'Error al cerrar el gasto común';
        }

        $this->redirect('finanzas/gastos-comunes');
    }

    /**
     * Estado de Pagos - Vista general.
     */
    public function estadoPagos()
    {
        $this->requirePermission('finanzas', 'read');

        $userId = $_SESSION['user_id'];
        $userEdificios = $this->getUserAccessibleEdificios();

        // Obtener estado de pagos para los edificios del usuario
        $estadoPagos = $this->getEstadoPagosGeneral($userEdificios);

        $data = [
            'edificios' => $userEdificios,
            'estado_pagos' => $estadoPagos,
            'user_name' => $_SESSION['user_name'],
            'page_title' => 'Estado de Pagos',
        ];

        $this->renderView('finanzas/estado_pagos', $data);
    }

    /**
     * Gestión de Pagos.
     */
    public function pagos()
    {
        $this->requirePermission('finanzas', 'read');

        $userId = $_SESSION['user_id'];
        $userEdificios = $this->getUserAccessibleEdificios();

        $pagos = $this->getPagosPendientes($userEdificios);

        $data = [
            'edificios' => $userEdificios,
            'pagos' => $pagos,
            'user_name' => $_SESSION['user_name'],
            'page_title' => 'Gestión de Pagos',
        ];

        $this->renderView('finanzas/pagos', $data);
    }

    /**
     * Registrar pago manual.
     */
    public function registrarPago()
    {
        $this->requirePermission('finanzas', 'write');

        $userId = $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->procesarRegistroPago($userId);

            return;
        }

        $userEdificios = $this->getUserAccessibleEdificios();
        $pagosPendientes = $this->getPagosPendientesParaRegistro($userEdificios);

        $data = [
            'edificios' => $userEdificios,
            'pagos_pendientes' => $pagosPendientes,
            'user_name' => $_SESSION['user_name'],
            'page_title' => 'Registrar Pago',
        ];

        $this->renderView('finanzas/registrar_pago', $data);
    }

    /**
     * Marcar pago como pagado.
     */
    public function marcarPagoPagado($pagoId)
    {
        $this->requirePermission('finanzas', 'write');

        $userId = $_SESSION['user_id'];

        try {
            // Verificar que el pago existe y el usuario tiene acceso
            $sql = 'SELECT p.*, d.edificio_id 
                    FROM pagos p 
                    JOIN departamentos d ON p.departamento_id = d.id 
                    JOIN user_edificio_relations uer ON d.edificio_id = uer.edificio_id 
                    WHERE p.id = ? AND uer.user_id = ?';

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$pagoId, $userId]);
            $pago = $stmt->fetch();

            if (!$pago) {
                $_SESSION['error_message'] = 'Pago no encontrado o sin permisos';
                $this->redirect('finanzas/pagos');
                exit;
            }

            // Actualizar pago
            $sqlUpdate = "UPDATE pagos 
                         SET estado = 'pagado', fecha_pago = NOW(), created_by = ?
                         WHERE id = ?";

            $stmt = $this->db->prepare($sqlUpdate);
            $stmt->execute([$userId, $pagoId]);

            $_SESSION['success_message'] = 'Pago marcado como pagado exitosamente';
        } catch (Exception $e) {
            error_log('Error al marcar pago como pagado: '.$e->getMessage());
            $_SESSION['error_message'] = 'Error al procesar el pago';
        }

        $this->redirect('finanzas/pagos');
    }

    /**
     * Reportes financieros.
     */
    public function reportes()
    {
        $this->requirePermission('finanzas', 'read');

        $userId = $_SESSION['user_id'];
        $userEdificios = $this->getUserAccessibleEdificios();

        $data = [
            'edificios' => $userEdificios,
            'user_name' => $_SESSION['user_name'],
            'page_title' => 'Reportes Financieros',
        ];

        $this->renderView('finanzas/reportes', $data);
    }

    /**
     * Balances.
     */
    public function balances()
    {
        $this->requirePermission('finanzas', 'read');

        $userId = $_SESSION['user_id'];
        $userEdificios = $this->getUserAccessibleEdificios();

        $balances = $this->getBalancesEdificios($userEdificios);

        $data = [
            'edificios' => $userEdificios,
            'balances' => $balances,
            'user_name' => $_SESSION['user_name'],
            'page_title' => 'Balances Financieros',
        ];

        $this->renderView('finanzas/balances', $data);
    }

    // ==================== MÓDULO PRORRATEO - MÉTODOS IMPLEMENTADOS ====================

    /**
     * VISTA DE PRORRATEO DE GASTOS.
     */
    public function prorrateoGastos()
    {
        $this->requirePermission('prorrateo', 'read');

        $userId = $_SESSION['user_id'];
        $userEdificios = $this->getUserAccessibleEdificios();

        // Obtener gastos pendientes de prorrateo
        $gastosPendientes = $this->getGastosPendientesProrrateo($userEdificios);

        // Obtener estrategias disponibles
        $estrategias = $this->getEstrategiasProrrateo();

        $data = [
            'edificios' => $userEdificios,
            'gastos_pendientes' => $gastosPendientes,
            'estrategias' => $estrategias,
            'user_name' => $_SESSION['user_name'],
            'page_title' => 'Prorrateo de Gastos Comunes',
            'flash_messages' => $this->getFlashMessages(),
        ];

        $this->renderView('finanzas/prorrateo_gastos', $data);
    }

    /**
     * CALCULAR PRORRATEO AUTOMÁTICO.
     */
    public function calcularProrrateo()
    {
        $this->requirePermission('prorrateo', 'write');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, [], 'Método no permitido');

            return;
        }

        $gastoId = $_POST['gasto_id'] ?? null;
        $estrategiaId = $_POST['estrategia_id'] ?? null;
        $userId = $_SESSION['user_id'];

        if (!$gastoId || !$estrategiaId) {
            $this->jsonResponse(false, [], 'Datos incompletos');

            return;
        }

        try {
            $prorrateoManager = new ProrrateoManager();
            $resultado = $prorrateoManager->calcularDistribucionAutomatica($gastoId, $estrategiaId, $userId);

            if ($resultado['success']) {
                $this->jsonResponse(true, $resultado, 'Cálculo de prorrateo completado');
            } else {
                $this->jsonResponse(false, [], $resultado['error'] ?? 'Error en el cálculo');
            }
        } catch (Exception $e) {
            error_log('Error en calcularProrrateo: '.$e->getMessage());
            $this->jsonResponse(false, [], 'Error interno del sistema: '.$e->getMessage());
        }
    }

    /**
     * APROBAR DISTRIBUCIÓN DE PRORRATEO.
     */
    public function aprobarProrrateo($prorrateoLogId)
    {
        $this->requirePermission('prorrateo', 'approve');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->addFlashMessage('error', 'Método no permitido');
            $this->redirect('finanzas/prorrateo');

            return;
        }

        $justificacion = $_POST['justificacion'] ?? null;
        $userId = $_SESSION['user_id'];

        try {
            $prorrateoManager = new ProrrateoManager();
            $resultado = $prorrateoManager->aprobarProrrateo($prorrateoLogId, $userId, $justificacion);

            if ($resultado['success']) {
                $this->addFlashMessage('success', $resultado['message']);
            } else {
                $this->addFlashMessage('error', $resultado['error']);
            }
        } catch (Exception $e) {
            error_log('Error en aprobarProrrateo: '.$e->getMessage());
            $this->addFlashMessage('error', 'Error al aprobar prorrateo: '.$e->getMessage());
        }

        $this->redirect('finanzas/prorrateo');
    }

    /**
     * VER DETALLE DE PRORRATEO.
     */
    public function verProrrateo($prorrateoLogId)
    {
        $this->requirePermission('prorrateo', 'read');

        $userId = $_SESSION['user_id'];

        try {
            $prorrateoManager = new ProrrateoManager();
            $prorrateo = $prorrateoManager->obtenerProrrateoLog($prorrateoLogId);

            if (!$prorrateo) {
                $this->addFlashMessage('error', 'Registro de prorrateo no encontrado');
                $this->redirect('finanzas/prorrateo');

                return;
            }

            // Verificar acceso al edificio del gasto
            $this->checkEdificioAccess($prorrateo['edificio_id']);

            $detalles = json_decode($prorrateo['detalles_json'], true);

            $data = [
                'prorrateo' => $prorrateo,
                'detalles' => $detalles,
                'user_name' => $_SESSION['user_name'],
                'page_title' => 'Detalle de Prorrateo',
            ];

            $this->renderView('finanzas/ver_prorrateo', $data);
        } catch (Exception $e) {
            error_log('Error en verProrrateo: '.$e->getMessage());
            $this->addFlashMessage('error', 'Error al cargar detalle: '.$e->getMessage());
            $this->redirect('finanzas/prorrateo');
        }
    }

    // ==================== MÉTODOS PRIVADOS DE CONSULTA ====================

    private function getGastosComunes($edificioIds)
    {
        if (empty($edificioIds)) {
            return [];
        }

        $placeholders = str_repeat('?,', count($edificioIds) - 1).'?';

        $sql = "SELECT gc.*, e.nombre as edificio_nombre,
                COUNT(DISTINCT gd.departamento_id) as total_departamentos,
                COUNT(DISTINCT CASE WHEN p.estado = 'pagado' THEN p.id END) as pagos_realizados,
                SUM(CASE WHEN p.estado = 'pagado' THEN p.monto ELSE 0 END) as monto_recaudado
                FROM gastos_comunes gc
                JOIN edificios e ON gc.edificio_id = e.id
                LEFT JOIN gasto_departamento gd ON gc.id = gd.gasto_comun_id
                LEFT JOIN pagos p ON gc.id = p.gasto_comun_id
                WHERE gc.edificio_id IN ($placeholders)
                GROUP BY gc.id
                ORDER BY gc.periodo DESC, gc.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($edificioIds);

        return $stmt->fetchAll();
    }

    private function getGastoComunById($gastoId, $userId)
    {
        $sql = 'SELECT gc.*, e.nombre as edificio_nombre
                FROM gastos_comunes gc
                JOIN edificios e ON gc.edificio_id = e.id
                JOIN user_edificio_relations uer ON e.id = uer.edificio_id
                WHERE gc.id = ? AND uer.user_id = ?';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$gastoId, $userId]);

        return $stmt->fetch();
    }

    private function getDistribucionGasto($gastoId)
    {
        $sql = 'SELECT gd.*, d.numero, d.propietario_nombre
                FROM gasto_departamento gd
                JOIN departamentos d ON gd.departamento_id = d.id
                WHERE gd.gasto_comun_id = ?
                ORDER BY d.numero';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$gastoId]);

        return $stmt->fetchAll();
    }

    private function getEstadoPagosGasto($gastoId)
    {
        $sql = "SELECT p.*, d.numero, d.propietario_nombre,
                CASE 
                    WHEN p.estado = 'pagado' THEN 'success'
                    WHEN p.estado = 'atrasado' THEN 'danger'
                    ELSE 'warning'
                END as estado_color
                FROM pagos p
                JOIN departamentos d ON p.departamento_id = d.id
                WHERE p.gasto_comun_id = ?
                ORDER BY d.numero";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$gastoId]);

        return $stmt->fetchAll();
    }

    private function getEstadoPagosGeneral($userEdificios)
    {
        if (empty($userEdificios)) {
            return [];
        }

        $edificioIds = array_column($userEdificios, 'id');
        $placeholders = str_repeat('?,', count($edificioIds) - 1).'?';

        $sql = "SELECT e.id as edificio_id, e.nombre as edificio_nombre,
                COUNT(DISTINCT d.id) as total_departamentos,
                COUNT(DISTINCT gc.id) as total_gastos_periodo,
                SUM(CASE WHEN p.estado = 'pagado' THEN p.monto ELSE 0 END) as total_recaudado,
                SUM(gd.monto) as total_esperado,
                ROUND((SUM(CASE WHEN p.estado = 'pagado' THEN p.monto ELSE 0 END) / SUM(gd.monto)) * 100, 2) as porcentaje_recaudado
                FROM edificios e
                JOIN departamentos d ON e.id = d.edificio_id
                JOIN gastos_comunes gc ON e.id = gc.edificio_id AND gc.periodo = DATE_FORMAT(NOW(), '%Y-%m')
                JOIN gasto_departamento gd ON gc.id = gd.gasto_comun_id AND d.id = gd.departamento_id
                LEFT JOIN pagos p ON d.id = p.departamento_id AND gc.id = p.gasto_comun_id
                WHERE e.id IN ($placeholders)
                GROUP BY e.id, e.nombre";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($edificioIds);

        return $stmt->fetchAll();
    }

    private function getPagosPendientes($userEdificios)
    {
        if (empty($userEdificios)) {
            return [];
        }

        $edificioIds = array_column($userEdificios, 'id');
        $placeholders = str_repeat('?,', count($edificioIds) - 1).'?';

        $sql = "SELECT p.*, d.numero, d.propietario_nombre, 
                e.nombre as edificio_nombre, gc.nombre as gasto_nombre,
                gc.periodo, gc.fecha_vencimiento,
                CASE 
                    WHEN p.estado = 'pagado' THEN 'success'
                    WHEN p.estado = 'atrasado' THEN 'danger'
                    WHEN gc.fecha_vencimiento < CURDATE() THEN 'danger'
                    ELSE 'warning'
                END as estado_color
                FROM pagos p
                JOIN departamentos d ON p.departamento_id = d.id
                JOIN edificios e ON d.edificio_id = e.id
                JOIN gastos_comunes gc ON p.gasto_comun_id = gc.id
                WHERE e.id IN ($placeholders) AND p.estado IN ('pendiente', 'atrasado')
                ORDER BY gc.fecha_vencimiento ASC, d.numero ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($edificioIds);

        return $stmt->fetchAll();
    }

    private function getPagosPendientesParaRegistro($userEdificios)
    {
        if (empty($userEdificios)) {
            return [];
        }

        $edificioIds = array_column($userEdificios, 'id');
        $placeholders = str_repeat('?,', count($edificioIds) - 1).'?';

        $sql = "SELECT p.id as pago_id, d.numero, d.propietario_nombre,
                e.nombre as edificio_nombre, gc.nombre as gasto_nombre,
                gc.periodo, p.monto, p.estado
                FROM pagos p
                JOIN departamentos d ON p.departamento_id = d.id
                JOIN edificios e ON d.edificio_id = e.id
                JOIN gastos_comunes gc ON p.gasto_comun_id = gc.id
                WHERE e.id IN ($placeholders) AND p.estado IN ('pendiente', 'atrasado')
                ORDER BY e.nombre, d.numero";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($edificioIds);

        return $stmt->fetchAll();
    }

    private function getBalancesEdificios($userEdificios)
    {
        if (empty($userEdificios)) {
            return [];
        }

        $edificioIds = array_column($userEdificios, 'id');
        $placeholders = str_repeat('?,', count($edificioIds) - 1).'?';

        $sql = "SELECT e.id, e.nombre,
                COUNT(DISTINCT gc.id) as total_gastos,
                SUM(gc.monto_total) as total_gastos_generados,
                SUM(CASE WHEN p.estado = 'pagado' THEN p.monto ELSE 0 END) as total_recaudado,
                SUM(CASE WHEN p.estado IN ('pendiente', 'atrasado') THEN p.monto ELSE 0 END) as total_pendiente,
                (SUM(gc.monto_total) - SUM(CASE WHEN p.estado = 'pagado' THEN p.monto ELSE 0 END)) as diferencia
                FROM edificios e
                LEFT JOIN gastos_comunes gc ON e.id = gc.edificio_id AND gc.periodo >= DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 6 MONTH), '%Y-%m')
                LEFT JOIN pagos p ON gc.id = p.gasto_comun_id
                WHERE e.id IN ($placeholders)
                GROUP BY e.id, e.nombre
                ORDER BY e.nombre";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($edificioIds);

        return $stmt->fetchAll();
    }

    /**
     * Registrar pago manual (para administradores - aprobación automática)
     */
    private function procesarRegistroPago($userId)
    {
        try {
            $pagoId = $_POST['pago_id'] ?? null;
            $metodoPago = $_POST['metodo_pago'] ?? 'transferencia';
            $numeroComprobante = trim($_POST['numero_comprobante'] ?? '');
            $referenciaBancaria = trim($_POST['referencia_bancaria'] ?? '');
            $observaciones = trim($_POST['observaciones'] ?? '');
            $fechaPago = $_POST['fecha_pago'] ?? date('Y-m-d');

            if (empty($pagoId)) {
                throw new Exception('Debe seleccionar un pago');
            }

            // Verificar permisos (admin puede registrar en cualquier edificio que gestione)
            $sql = 'SELECT p.*, d.edificio_id 
                    FROM pagos p 
                    JOIN departamentos d ON p.departamento_id = d.id 
                    JOIN user_edificio_relations uer ON d.edificio_id = uer.edificio_id 
                    WHERE p.id = ? AND uer.user_id = ?';

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$pagoId, $userId]);
            $pago = $stmt->fetch();

            if (!$pago) {
                throw new Exception('Pago no encontrado o sin permisos');
            }

            // ADMIN: Aprobación automática
            $sqlUpdate = "UPDATE pagos 
                        SET estado = 'pagado', 
                            fecha_pago = ?,
                            metodo_pago = ?,
                            numero_comprobante = ?,
                            referencia_bancaria = ?,
                            observaciones = ?,
                            estado_aprobacion = 'aprobado',
                            approved_by = ?,
                            approved_at = NOW(),
                            created_by = ?
                        WHERE id = ?";

            $stmt = $this->db->prepare($sqlUpdate);
            $stmt->execute([
                $fechaPago, $metodoPago, $numeroComprobante,
                $referenciaBancaria, $observaciones, $userId, $userId, $pagoId,
            ]);

            $this->addFlashMessage('success', 'Pago registrado y aprobado exitosamente');
            $this->redirect('finanzas/pagos');
        } catch (Exception $e) {
            error_log('Error al registrar pago: ' . $e->getMessage());
            $this->addFlashMessage('error', 'Error al registrar el pago: ' . $e->getMessage());
            $this->redirect('finanzas/pagos/registrar');
        }
    }

    /**
     * OBTENER GASTOS PENDIENTES DE PRORRATEO.
     */
    private function getGastosPendientesProrrateo($userEdificios)
    {
        if (empty($userEdificios)) {
            return [];
        }

        $edificioIds = array_column($userEdificios, 'id');
        $placeholders = str_repeat('?,', count($edificioIds) - 1).'?';

        $sql = "SELECT gc.*, e.nombre as edificio_nombre,
                (SELECT COUNT(*) FROM gasto_departamento gd WHERE gd.gasto_comun_id = gc.id) as distribucion_existente,
                (SELECT estado FROM gasto_prorrateo_log gpl WHERE gpl.gasto_comun_id = gc.id ORDER BY created_at DESC LIMIT 1) as estado_prorrateo
                FROM gastos_comunes gc
                JOIN edificios e ON gc.edificio_id = e.id
                WHERE gc.edificio_id IN ($placeholders)
                AND gc.estado = 'pendiente'
                AND gc.distribucion_confirmada = 0
                ORDER BY gc.periodo DESC, gc.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($edificioIds);

        return $stmt->fetchAll();
    }

    /**
     * OBTENER ESTRATEGIAS DE PRORRATEO DISPONIBLES.
     */
    private function getEstrategiasProrrateo()
    {
        try {
            $sql = 'SELECT * FROM prorrateo_strategies WHERE is_active = 1 ORDER BY nombre';
            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log('Error al obtener estrategias de prorrateo: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Mis Pagos - Vista para propietarios
     */
    public function misPagos()
    {
        $this->requirePermission('pagos', 'read');
        
        $userId = $_SESSION['user_id'];
        
        // Obtener departamentos del usuario
        $departamentosUsuario = $this->getDepartamentosUsuario($userId);
        
        if (empty($departamentosUsuario)) {
            $data = [
                'pagos' => [],
                'user_name' => $_SESSION['user_name'],
                'page_title' => 'Mis Pagos',
                'mensaje' => 'No tienes departamentos asignados'
            ];
            $this->renderView('finanzas/mis_pagos', $data);
            return;
        }
        
        $departamentoIds = array_column($departamentosUsuario, 'id');
        $pagos = $this->getPagosPorDepartamentos($departamentoIds);
        
        $data = [
            'pagos' => $pagos,
            'departamentos' => $departamentosUsuario,
            'user_name' => $_SESSION['user_name'],
            'page_title' => 'Mis Pagos'
        ];
        
        $this->renderView('finanzas/mis_pagos', $data);
    }

    /**
     * Registrar pago como propietario
     */
    public function registrarMiPago()
    {
        $this->requirePermission('pagos', 'register');
        
        $userId = $_SESSION['user_id'];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->procesarRegistroMiPago($userId);
            return;
        }
        
        $departamentosUsuario = $this->getDepartamentosUsuario($userId);
        $pagosPendientes = $this->getPagosPendientesUsuario($departamentosUsuario);
        
        $data = [
            'departamentos' => $departamentosUsuario,
            'pagos_pendientes' => $pagosPendientes,
            'user_name' => $_SESSION['user_name'],
            'page_title' => 'Registrar Mi Pago'
        ];
        
        $this->renderView('finanzas/registrar_mi_pago', $data);
    }

    /**
     * Aprobar pago (para administradores)
     */
    public function aprobarPago($pagoId)
    {
        $this->requirePermission('pagos', 'process');
        
        $userId = $_SESSION['user_id'];
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->addFlashMessage('error', 'Método no permitido');
            $this->redirect('finanzas/pagos');
            return;
        }
        
        try {
            $justificacion = trim($_POST['justificacion'] ?? '');
            
            // Verificar que el pago existe y está pendiente
            $pago = $this->getPagoParaAprobacion($pagoId, $userId);
            
            if (!$pago) {
                $this->addFlashMessage('error', 'Pago no encontrado o sin permisos');
                $this->redirect('finanzas/pagos');
                return;
            }
            
            if ($pago['estado_aprobacion'] !== 'pendiente') {
                $this->addFlashMessage('error', 'El pago ya fue procesado');
                $this->redirect('finanzas/pagos');
                return;
            }
            
            // Aprobar pago
            $sql = "UPDATE pagos 
                    SET estado_aprobacion = 'aprobado', 
                        estado = 'pagado',
                        approved_by = ?,
                        approved_at = NOW(),
                        fecha_pago = COALESCE(fecha_pago, NOW())
                    WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId, $pagoId]);
            
            $this->addFlashMessage('success', 'Pago aprobado exitosamente');
            
        } catch (Exception $e) {
            error_log('Error al aprobar pago: ' . $e->getMessage());
            $this->addFlashMessage('error', 'Error al aprobar el pago');
        }
        
        $this->redirect('finanzas/pagos');
    }

    /**
     * Rechazar pago (para administradores)
     */
    public function rechazarPago($pagoId)
    {
        $this->requirePermission('pagos', 'process');
        
        $userId = $_SESSION['user_id'];
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->addFlashMessage('error', 'Método no permitido');
            $this->redirect('finanzas/pagos');
            return;
        }
        
        try {
            $motivo = trim($_POST['motivo_rechazo'] ?? '');
            
            if (empty($motivo)) {
                $this->addFlashMessage('error', 'Debe proporcionar un motivo para el rechazo');
                $this->redirect('finanzas/pagos');
                return;
            }
            
            // Verificar que el pago existe y está pendiente
            $pago = $this->getPagoParaAprobacion($pagoId, $userId);
            
            if (!$pago) {
                $this->addFlashMessage('error', 'Pago no encontrado o sin permisos');
                $this->redirect('finanzas/pagos');
                return;
            }
            
            if ($pago['estado_aprobacion'] !== 'pendiente') {
                $this->addFlashMessage('error', 'El pago ya fue procesado');
                $this->redirect('finanzas/pagos');
                return;
            }
            
            // Rechazar pago
            $sql = "UPDATE pagos 
                    SET estado_aprobacion = 'rechazado', 
                        estado = 'pendiente',
                        approved_by = ?,
                        approved_at = NOW(),
                        rejection_reason = ?
                    WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId, $motivo, $pagoId]);
            
            $this->addFlashMessage('success', 'Pago rechazado exitosamente');
            
        } catch (Exception $e) {
            error_log('Error al rechazar pago: ' . $e->getMessage());
            $this->addFlashMessage('error', 'Error al rechazar el pago');
        }
        
        $this->redirect('finanzas/pagos');
    }

    // ==================== MÉTODOS PRIVADOS NUEVOS ====================

    private function getDepartamentosUsuario($userId)
    {
        $sql = "SELECT d.*, e.nombre as edificio_nombre
                FROM departamentos d
                JOIN edificios e ON d.edificio_id = e.id
                JOIN user_edificio_relations uer ON e.id = uer.edificio_id
                WHERE uer.user_id = ? AND d.is_habitado = 1
                ORDER BY e.nombre, d.numero";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    private function getPagosPorDepartamentos($departamentoIds)
    {
        if (empty($departamentoIds)) {
            return [];
        }
        
        $placeholders = str_repeat('?,', count($departamentoIds) - 1) . '?';
        
        $sql = "SELECT p.*, d.numero, d.propietario_nombre, 
                e.nombre as edificio_nombre, gc.nombre as gasto_nombre,
                gc.periodo, gc.fecha_vencimiento,
                CASE 
                    WHEN p.estado_aprobacion = 'aprobado' THEN 'success'
                    WHEN p.estado_aprobacion = 'rechazado' THEN 'danger'
                    WHEN gc.fecha_vencimiento < CURDATE() THEN 'warning'
                    ELSE 'info'
                END as estado_color
                FROM pagos p
                JOIN departamentos d ON p.departamento_id = d.id
                JOIN edificios e ON d.edificio_id = e.id
                JOIN gastos_comunes gc ON p.gasto_comun_id = gc.id
                WHERE p.departamento_id IN ($placeholders)
                ORDER BY gc.periodo DESC, p.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($departamentoIds);
        return $stmt->fetchAll();
    }

    private function getPagosPendientesUsuario($departamentosUsuario)
    {
        if (empty($departamentosUsuario)) {
            return [];
        }
        
        $departamentoIds = array_column($departamentosUsuario, 'id');
        $placeholders = str_repeat('?,', count($departamentoIds) - 1) . '?';
        
        // CORRECCIÓN: Usar p.id en lugar de pago_id
        $sql = "SELECT p.id as pago_id, d.numero, d.propietario_nombre,
                e.nombre as edificio_nombre, gc.nombre as gasto_nombre,
                gc.periodo, p.monto, p.estado, p.estado_aprobacion,
                gc.fecha_vencimiento
                FROM pagos p
                JOIN departamentos d ON p.departamento_id = d.id
                JOIN edificios e ON d.edificio_id = e.id
                JOIN gastos_comunes gc ON p.gasto_comun_id = gc.id
                WHERE p.departamento_id IN ($placeholders) 
                AND p.estado IN ('pendiente', 'atrasado')
                AND p.estado_aprobacion = 'pendiente'
                ORDER BY e.nombre, d.numero";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($departamentoIds);
        return $stmt->fetchAll();
    }

    private function getPagoParaAprobacion($pagoId, $userId)
    {
        $sql = "SELECT p.*, d.edificio_id 
                FROM pagos p 
                JOIN departamentos d ON p.departamento_id = d.id 
                JOIN user_edificio_relations uer ON d.edificio_id = uer.edificio_id 
                WHERE p.id = ? AND uer.user_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$pagoId, $userId]);
        return $stmt->fetch();
    }

    private function procesarRegistroMiPago($userId)
    {
        try {
            $pagoId = $_POST['pago_id'] ?? null;
            $metodoPago = $_POST['metodo_pago'] ?? 'transferencia';
            $numeroComprobante = trim($_POST['numero_comprobante'] ?? '');
            $referenciaBancaria = trim($_POST['referencia_bancaria'] ?? '');
            $observaciones = trim($_POST['observaciones'] ?? '');
            $fechaPago = $_POST['fecha_pago'] ?? date('Y-m-d');
            
            if (empty($pagoId)) {
                throw new Exception('Debe seleccionar un pago');
            }
            
            // Verificar que el pago pertenece al usuario
            $departamentosUsuario = $this->getDepartamentosUsuario($userId);
            $departamentoIds = array_column($departamentosUsuario, 'id');
            
            $sql = "SELECT p.*, d.numero, gc.nombre as gasto_nombre
                    FROM pagos p
                    JOIN departamentos d ON p.departamento_id = d.id
                    JOIN gastos_comunes gc ON p.gasto_comun_id = gc.id
                    WHERE p.id = ? AND p.departamento_id IN (" . str_repeat('?,', count($departamentoIds) - 1) . "?)";
            
            $params = array_merge([$pagoId], $departamentoIds);
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $pago = $stmt->fetch();
            
            if (!$pago) {
                throw new Exception('Pago no encontrado o no pertenece a tus departamentos');
            }
            
            if ($pago['estado_aprobacion'] !== 'pendiente') {
                throw new Exception('Este pago ya fue procesado');
            }
            
            // Registrar pago (queda pendiente de aprobación)
            $sqlUpdate = "UPDATE pagos 
                        SET metodo_pago = ?,
                            numero_comprobante = ?,
                            referencia_bancaria = ?,
                            observaciones = ?,
                            fecha_pago = ?,
                            estado_aprobacion = 'pendiente',
                            created_by = ?
                        WHERE id = ?";
            
            $stmt = $this->db->prepare($sqlUpdate);
            $stmt->execute([
                $metodoPago, $numeroComprobante, $referenciaBancaria, 
                $observaciones, $fechaPago, $userId, $pagoId
            ]);
            
            $this->addFlashMessage('success', 'Pago registrado exitosamente. Esperando aprobación del administrador.');
            $this->redirect('finanzas/mis-pagos');
            
        } catch (Exception $e) {
            error_log('Error al registrar pago: ' . $e->getMessage());
            $this->addFlashMessage('error', 'Error al registrar el pago: ' . $e->getMessage());
            $this->redirect('finanzas/mis-pagos/registrar');
        }
    }   
    
    /**
     * Generar pagos pendientes automáticamente al crear gasto común
     */
    private function generarPagosPendientes($gastoId, $edificioId)
    {
        try {
            // Obtener distribución del gasto
            $sqlDistribucion = 'SELECT gd.departamento_id, gd.monto 
                            FROM gasto_departamento gd 
                            WHERE gd.gasto_comun_id = ?';
            
            $stmt = $this->db->prepare($sqlDistribucion);
            $stmt->execute([$gastoId]);
            $distribuciones = $stmt->fetchAll();
            
            // Crear pagos pendientes para cada departamento
            $sqlPago = 'INSERT INTO pagos 
                    (departamento_id, gasto_comun_id, monto, estado, estado_aprobacion, created_at) 
                    VALUES (?, ?, ?, "pendiente", "pendiente", NOW())';
            
            $stmtPago = $this->db->prepare($sqlPago);
            
            foreach ($distribuciones as $distribucion) {
                $stmtPago->execute([
                    $distribucion['departamento_id'],
                    $gastoId,
                    $distribucion['monto']
                ]);
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log('Error al generar pagos pendientes: ' . $e->getMessage());
            return false;
        }
    }    


}
