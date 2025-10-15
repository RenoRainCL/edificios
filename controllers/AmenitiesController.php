<?php
// 📁 controllers/AmenitiesController.php

class AmenitiesController extends ControllerCore {
    
    public function __construct() {
        parent::__construct();
    }

    // ==================== VISTAS PRINCIPALES ====================

    /**
     * Vista principal de gestión de amenities
     */
    public function gestionar() {
        $userEdificios = $this->getUserEdificios($_SESSION['user_id']);
        $edificioId = $_GET['edificio_id'] ?? null;
        
        // Si no se especifica edificio, usar el primero disponible
        if (!$edificioId && !empty($userEdificios)) {
            $edificioId = $userEdificios[0]['id'];
        }
        
        $amenities = [];
        if ($edificioId) {
            $this->checkEdificioAccess($edificioId);
            $amenities = $this->getAmenitiesEdificio($edificioId);
        }
        
        $data = [
            'amenities' => $amenities,
            'edificios' => $userEdificios,
            'edificio_actual' => $edificioId ? $this->getEdificioById($edificioId) : null,
            'tipos_amenities' => $this->getTiposAmenities(),
            'user_name' => $_SESSION['user_name'],
            'flash_messages' => $this->getFlashMessages()
        ];
        
        $this->renderView('amenities/gestionar', $data);
    }

    /**
     * Formulario de creación de amenity
     */
    public function crear() {
        $userEdificios = $this->getUserEdificios($_SESSION['user_id']);

        if (empty($userEdificios)) {
            $this->addFlashMessage('error', 'Primero debes crear un edificio');
            $this->redirect('edificios/crear');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->crearAmenity($_POST);
        }

        $data = [
            'user_name' => $_SESSION['user_name'],
            'edificios' => $userEdificios,
            'tipos_amenities' => $this->getTiposAmenities(),
            'flash_messages' => $this->getFlashMessages(),
            'config_global' => $this->getConfiguracionGlobal()
        ];

        $this->renderView('amenities/crear', $data);
    }

    /**
     * Formulario de edición de amenity
     */
    public function editar($amenityId) {
        $amenity = $this->getAmenityById($amenityId);
        
        if (!$amenity) {
            $this->addFlashMessage('error', 'Amenity no encontrado');
            $this->redirect('amenities/gestionar');
        }

        $this->checkEdificioAccess($amenity['edificio_id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->actualizarAmenity($amenityId, $_POST);
        }

        $userEdificios = $this->getUserEdificios($_SESSION['user_id']);

        $data = [
            'amenity' => $amenity,
            'imagenes' => $this->getImagenesAmenity($amenityId),
            'edificios' => $userEdificios,
            'tipos_amenities' => $this->getTiposAmenities(),
            'user_name' => $_SESSION['user_name'],
            'flash_messages' => $this->getFlashMessages(),
            'config_amenity' => $this->getConfiguracionAmenity($amenityId)
        ];

        $this->renderView('amenities/editar', $data);
    }

    /**
     * Vista de configuración de amenities
     */
    public function configuracion() {
        $userEdificios = $this->getUserEdificios($_SESSION['user_id']);
        $edificioId = $_GET['edificio_id'] ?? null;
        
        if (!$edificioId && !empty($userEdificios)) {
            $edificioId = $userEdificios[0]['id'];
        }
        
        $configGlobal = $this->getConfiguracionGlobal();
        $configEdificio = $edificioId ? $this->getConfiguracionEdificio($edificioId) : null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->guardarConfiguracion($_POST, $edificioId);
        }

        $data = [
            'edificios' => $userEdificios,
            'edificio_actual' => $edificioId ? $this->getEdificioById($edificioId) : null,
            'config_global' => $configGlobal,
            'config_edificio' => $configEdificio,
            'user_name' => $_SESSION['user_name'],
            'flash_messages' => $this->getFlashMessages()
        ];

        $this->renderView('amenities/configuracion', $data);
    }

    // ==================== MÉTODOS DE IMÁGENES ====================

    /**
     * Subir imágenes para amenity
     */
    public function subirImagen($amenityId) {
        $amenity = $this->getAmenityById($amenityId);
        
        if (!$amenity) {
            $this->jsonResponse(false, [], 'Amenity no encontrado');
        }

        $this->checkEdificioAccess($amenity['edificio_id']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, [], 'Método no permitido');
        }

        $resultado = $this->procesarUploadImagen($amenityId, $_FILES['imagen']);
        
        if ($resultado['success']) {
            $this->jsonResponse(true, $resultado, 'Imagen subida exitosamente');
        } else {
            $this->jsonResponse(false, [], $resultado['error']);
        }
    }

    /**
     * Eliminar imagen de amenity
     */
    public function eliminarImagen($imagenId) {
        $imagen = $this->getImagenById($imagenId);
        
        if (!$imagen) {
            $this->jsonResponse(false, [], 'Imagen no encontrada');
        }

        $amenity = $this->getAmenityById($imagen['amenity_id']);
        $this->checkEdificioAccess($amenity['edificio_id']);

        $resultado = $this->eliminarImagenFS($imagen);
        
        if ($resultado) {
            $this->eliminarImagenBD($imagenId);
            $this->jsonResponse(true, [], 'Imagen eliminada exitosamente');
        } else {
            $this->jsonResponse(false, [], 'Error al eliminar la imagen');
        }
    }

    /**
     * Ordenar imágenes del amenity
     */
    public function ordenarImagenes($amenityId) {
        $amenity = $this->getAmenityById($amenityId);
        
        if (!$amenity) {
            $this->jsonResponse(false, [], 'Amenity no encontrado');
        }

        $this->checkEdificioAccess($amenity['edificio_id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $orden = json_decode($_POST['orden'], true);
            $this->actualizarOrdenImagenes($amenityId, $orden);
            $this->jsonResponse(true, [], 'Orden actualizado exitosamente');
        }
    }

    // ==================== MÉTODOS PRIVADOS - CRUD ====================

    /**
     * Crear nuevo amenity
     */
    private function crearAmenity($data) {
        $errors = $this->validateInput($data, [
            'edificio_id' => 'required|numeric',
            'nombre' => 'required|min:3',
            'tipo' => 'required',
            'capacidad' => 'numeric'
        ]);

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->addFlashMessage('error', $error);
            }
            $this->redirect('amenities/crear');
        }

        try {
            $this->checkEdificioAccess($data['edificio_id']);

            $sql = 'INSERT INTO amenities (
                    edificio_id, nombre, tipo, descripcion, capacidad,
                    horario_apertura, horario_cierre, horario_lunes_apertura, horario_lunes_cierre,
                    horario_sabado_apertura, horario_sabado_cierre, horario_domingo_apertura, horario_domingo_cierre,
                    horario_verano_inicio, horario_verano_fin, horario_verano_apertura, horario_verano_cierre,
                    horario_invierno_inicio, horario_invierno_fin, horario_invierno_apertura, horario_invierno_cierre,
                    reglas_uso, costo_uso, requiere_aprobacion, max_reservas_semana, max_reservas_mismo_dia,
                    antelacion_maxima_dias, duracion_minima_reserva, duracion_maxima_reserva, bloques_horarios
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['edificio_id'],
                $data['nombre'],
                $data['tipo'],
                $data['descripcion'] ?? null,
                !empty($data['capacidad']) ? $data['capacidad'] : null,
                $data['horario_apertura'] ?? null,
                $data['horario_cierre'] ?? null,
                $data['horario_lunes_apertura'] ?? null,
                $data['horario_lunes_cierre'] ?? null,
                $data['horario_sabado_apertura'] ?? null,
                $data['horario_sabado_cierre'] ?? null,
                $data['horario_domingo_apertura'] ?? null,
                $data['horario_domingo_cierre'] ?? null,
                !empty($data['horario_verano_inicio']) ? $data['horario_verano_inicio'] : null,
                !empty($data['horario_verano_fin']) ? $data['horario_verano_fin'] : null,
                $data['horario_verano_apertura'] ?? null,
                $data['horario_verano_cierre'] ?? null,
                !empty($data['horario_invierno_inicio']) ? $data['horario_invierno_inicio'] : null,
                !empty($data['horario_invierno_fin']) ? $data['horario_invierno_fin'] : null,
                $data['horario_invierno_apertura'] ?? null,
                $data['horario_invierno_cierre'] ?? null,
                $data['reglas_uso'] ?? null,
                !empty($data['costo_uso']) ? $data['costo_uso'] : 0.00,
                isset($data['requiere_aprobacion']) ? 1 : 0,
                !empty($data['max_reservas_semana']) ? $data['max_reservas_semana'] : 2,
                !empty($data['max_reservas_mismo_dia']) ? $data['max_reservas_mismo_dia'] : 1,
                !empty($data['antelacion_maxima_dias']) ? $data['antelacion_maxima_dias'] : 30,
                !empty($data['duracion_minima_reserva']) ? $data['duracion_minima_reserva'] : 60,
                !empty($data['duracion_maxima_reserva']) ? $data['duracion_maxima_reserva'] : 240,
                !empty($data['bloques_horarios']) ? json_encode($data['bloques_horarios']) : null
            ]);

            $amenityId = $this->db->lastInsertId();

            // Crear configuración específica si se proporciona
            if (isset($data['config_especifica']) && $data['config_especifica']) {
                $this->crearConfiguracionAmenity($amenityId, $data);
            }

            $this->addFlashMessage('success', 'Amenity creado exitosamente');
            $this->redirect('amenities/gestionar?edificio_id=' . $data['edificio_id']);

        } catch (Exception $e) {
            error_log('Error al crear amenity: ' . $e->getMessage());
            $this->addFlashMessage('error', 'Error al crear el amenity: ' . $e->getMessage());
            $this->redirect('amenities/crear');
        }
    }

    /**
     * Actualizar amenity existente
     */
    private function actualizarAmenity($amenityId, $data) {
        $errors = $this->validateInput($data, [
            'edificio_id' => 'required|numeric',
            'nombre' => 'required|min:3',
            'tipo' => 'required',
            'capacidad' => 'numeric'
        ]);

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->addFlashMessage('error', $error);
            }
            $this->redirect('amenities/editar/' . $amenityId);
        }

        try {
            $this->checkEdificioAccess($data['edificio_id']);

            $sql = "UPDATE amenities SET 
                    edificio_id = ?, nombre = ?, tipo = ?, descripcion = ?, capacidad = ?,
                    horario_apertura = ?, horario_cierre = ?, horario_lunes_apertura = ?, horario_lunes_cierre = ?,
                    horario_sabado_apertura = ?, horario_sabado_cierre = ?, horario_domingo_apertura = ?, horario_domingo_cierre = ?,
                    horario_verano_inicio = ?, horario_verano_fin = ?, horario_verano_apertura = ?, horario_verano_cierre = ?,
                    horario_invierno_inicio = ?, horario_invierno_fin = ?, horario_invierno_apertura = ?, horario_invierno_cierre = ?,
                    reglas_uso = ?, costo_uso = ?, requiere_aprobacion = ?, max_reservas_semana = ?, max_reservas_mismo_dia = ?,
                    antelacion_maxima_dias = ?, duracion_minima_reserva = ?, duracion_maxima_reserva = ?, bloques_horarios = ?,
                    updated_at = NOW()
                    WHERE id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['edificio_id'],
                $data['nombre'],
                $data['tipo'],
                $data['descripcion'] ?? null,
                !empty($data['capacidad']) ? $data['capacidad'] : null,
                $data['horario_apertura'] ?? null,
                $data['horario_cierre'] ?? null,
                $data['horario_lunes_apertura'] ?? null,
                $data['horario_lunes_cierre'] ?? null,
                $data['horario_sabado_apertura'] ?? null,
                $data['horario_sabado_cierre'] ?? null,
                $data['horario_domingo_apertura'] ?? null,
                $data['horario_domingo_cierre'] ?? null,
                !empty($data['horario_verano_inicio']) ? $data['horario_verano_inicio'] : null,
                !empty($data['horario_verano_fin']) ? $data['horario_verano_fin'] : null,
                $data['horario_verano_apertura'] ?? null,
                $data['horario_verano_cierre'] ?? null,
                !empty($data['horario_invierno_inicio']) ? $data['horario_invierno_inicio'] : null,
                !empty($data['horario_invierno_fin']) ? $data['horario_invierno_fin'] : null,
                $data['horario_invierno_apertura'] ?? null,
                $data['horario_invierno_cierre'] ?? null,
                $data['reglas_uso'] ?? null,
                !empty($data['costo_uso']) ? $data['costo_uso'] : 0.00,
                isset($data['requiere_aprobacion']) ? 1 : 0,
                !empty($data['max_reservas_semana']) ? $data['max_reservas_semana'] : 2,
                !empty($data['max_reservas_mismo_dia']) ? $data['max_reservas_mismo_dia'] : 1,
                !empty($data['antelacion_maxima_dias']) ? $data['antelacion_maxima_dias'] : 30,
                !empty($data['duracion_minima_reserva']) ? $data['duracion_minima_reserva'] : 60,
                !empty($data['duracion_maxima_reserva']) ? $data['duracion_maxima_reserva'] : 240,
                !empty($data['bloques_horarios']) ? json_encode($data['bloques_horarios']) : null,
                $amenityId
            ]);

            $this->addFlashMessage('success', 'Amenity actualizado exitosamente');
            $this->redirect('amenities/gestionar?edificio_id=' . $data['edificio_id']);

        } catch (Exception $e) {
            error_log('Error al actualizar amenity: ' . $e->getMessage());
            $this->addFlashMessage('error', 'Error al actualizar el amenity: ' . $e->getMessage());
            $this->redirect('amenities/editar/' . $amenityId);
        }
    }

    /**
     * Desactivar amenity
     */
    public function desactivar($amenityId) {
        $amenity = $this->getAmenityById($amenityId);
        
        if (!$amenity) {
            $this->jsonResponse(false, [], 'Amenity no encontrado');
        }

        $this->checkEdificioAccess($amenity['edificio_id']);

        try {
            $sql = "UPDATE amenities SET is_active = 0, updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$amenityId]);

            $this->jsonResponse(true, [], 'Amenity desactivado exitosamente');
        } catch (Exception $e) {
            error_log('Error al desactivar amenity: ' . $e->getMessage());
            $this->jsonResponse(false, [], 'Error al desactivar el amenity');
        }
    }

    // ==================== MÉTODOS PRIVADOS - CONFIGURACIÓN ====================

    /**
     * Guardar configuración de amenities
     */
    private function guardarConfiguracion($data, $edificioId = null) {
        try {
            $nivel = $edificioId ? 'edificio' : 'global';
            $entidadId = $edificioId ?: null;

            $configuracion = [
                'max_horas_por_reserva' => $data['max_horas_por_reserva'] ?? 4,
                'dias_anticipacion_reserva' => $data['dias_anticipacion_reserva'] ?? 7,
                'notificar_conflictos' => isset($data['notificar_conflictos']) ? 1 : 0,
                'auto_aprobar_reservas' => isset($data['auto_aprobar_reservas']) ? 1 : 0,
                'permisos_reserva' => $data['permisos_reserva'] ?? ['propietario', 'arrendatario'],
                'horario_global_apertura' => $data['horario_global_apertura'] ?? '08:00',
                'horario_global_cierre' => $data['horario_global_cierre'] ?? '22:00',
                'bloques_horarios_default' => $data['bloques_horarios_default'] ?? []
            ];

            // Verificar si ya existe configuración
            $configExistente = $this->getConfiguracion($nivel, $entidadId);
            
            if ($configExistente) {
                $sql = "UPDATE amenity_config SET configuracion = ?, updated_at = NOW() WHERE nivel = ? AND entidad_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([json_encode($configuracion), $nivel, $entidadId]);
            } else {
                $sql = "INSERT INTO amenity_config (nivel, entidad_id, configuracion) VALUES (?, ?, ?)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$nivel, $entidadId, json_encode($configuracion)]);
            }

            $this->addFlashMessage('success', 'Configuración guardada exitosamente');
            $this->redirect('amenities/configuracion' . ($edificioId ? '?edificio_id=' . $edificioId : ''));

        } catch (Exception $e) {
            error_log('Error al guardar configuración: ' . $e->getMessage());
            $this->addFlashMessage('error', 'Error al guardar la configuración');
            $this->redirect('amenities/configuracion' . ($edificioId ? '?edificio_id=' . $edificioId : ''));
        }
    }

    // ==================== MÉTODOS PRIVADOS - CONSULTAS BD ====================

    /**
     * Obtener amenity por ID
     */
    private function getAmenityById($amenityId) {
        $sql = 'SELECT a.*, e.nombre as edificio_nombre 
                FROM amenities a
                JOIN edificios e ON a.edificio_id = e.id
                WHERE a.id = ? AND a.is_active = 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$amenityId]);

        return $stmt->fetch();
    }

    /**
     * Obtener amenities por edificio
     */
    private function getAmenitiesEdificio($edificioId) {
        $sql = 'SELECT a.*, 
                COUNT(DISTINCT r.id) as total_reservas,
                COUNT(DISTINCT CASE WHEN r.estado = "confirmada" AND r.fecha_reserva >= CURDATE() THEN r.id END) as reservas_activas
                FROM amenities a
                LEFT JOIN reservas r ON a.id = r.amenity_id
                WHERE a.edificio_id = ? AND a.is_active = 1
                GROUP BY a.id
                ORDER BY a.nombre';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$edificioId]);

        return $stmt->fetchAll();
    }

    /**
     * Obtener imágenes de un amenity
     */
    private function getImagenesAmenity($amenityId) {
        $sql = 'SELECT * FROM amenity_imagenes 
                WHERE amenity_id = ? 
                ORDER BY orden ASC, is_principal DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$amenityId]);

        return $stmt->fetchAll();
    }

    /**
     * Obtener imagen por ID
     */
    private function getImagenById($imagenId) {
        $sql = 'SELECT * FROM amenity_imagenes WHERE id = ?';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$imagenId]);
        return $stmt->fetch();
    }

    /**
     * Obtener configuración global
     */
    private function getConfiguracionGlobal() {
        return $this->getConfiguracion('global', null);
    }

    /**
     * Obtener configuración de edificio
     */
    private function getConfiguracionEdificio($edificioId) {
        return $this->getConfiguracion('edificio', $edificioId);
    }

    /**
     * Obtener configuración de amenity
     */
    private function getConfiguracionAmenity($amenityId) {
        return $this->getConfiguracion('amenity', $amenityId);
    }

    /**
     * Obtener configuración por nivel y entidad
     */
    private function getConfiguracion($nivel, $entidadId) {
        $sql = 'SELECT configuracion FROM amenity_config 
                WHERE nivel = ? AND entidad_id = ?';
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$nivel, $entidadId]);
        $result = $stmt->fetch();

        if ($result && $result['configuracion']) {
            return json_decode($result['configuracion'], true);
        }

        return null;
    }

    // ==================== MÉTODOS PRIVADOS - UTILIDADES ====================

    /**
     * Obtener tipos de amenities disponibles
     */
    private function getTiposAmenities() {
        return [
            'gimnasio' => 'Gimnasio',
            'piscina' => 'Piscina',
            'quincho' => 'Quincho',
            'sala_eventos' => 'Sala de Eventos',
            'lavanderia' => 'Lavandería',
            'juegos_infantiles' => 'Juegos Infantiles',
            'terraza' => 'Terraza',
            'otro' => 'Otro'
        ];
    }

    /**
     * Procesar upload de imagen
     */
    private function procesarUploadImagen($amenityId, $archivo) {
        // Validar archivo
        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'Error en la subida del archivo'];
        }

        // Validar tipo de archivo
        $tiposPermitidos = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($archivo['type'], $tiposPermitidos)) {
            return ['success' => false, 'error' => 'Tipo de archivo no permitido'];
        }

        // Validar tamaño (máximo 5MB)
        if ($archivo['size'] > 5 * 1024 * 1024) {
            return ['success' => false, 'error' => 'El archivo es demasiado grande (máximo 5MB)'];
        }

        try {
            // Crear directorio si no existe
            $directorio = __DIR__ . '/../uploads/amenities/' . $amenityId;
            if (!is_dir($directorio)) {
                mkdir($directorio, 0755, true);
            }

            // Generar nombre único
            $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
            $nombreArchivo = uniqid() . '.' . $extension;
            $rutaCompleta = $directorio . '/' . $nombreArchivo;

            // Mover archivo
            if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
                // Obtener último orden
                $sql = 'SELECT COALESCE(MAX(orden), 0) as max_orden FROM amenity_imagenes WHERE amenity_id = ?';
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$amenityId]);
                $result = $stmt->fetch();
                $nuevoOrden = $result['max_orden'] + 1;

                // Insertar en BD
                $sql = 'INSERT INTO amenity_imagenes (amenity_id, nombre_archivo, ruta_archivo, orden) VALUES (?, ?, ?, ?)';
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$amenityId, $archivo['name'], $nombreArchivo, $nuevoOrden]);

                $imagenId = $this->db->lastInsertId();

                return [
                    'success' => true,
                    'imagen_id' => $imagenId,
                    'nombre_archivo' => $archivo['name'],
                    'ruta_archivo' => $nombreArchivo,
                    'orden' => $nuevoOrden
                ];
            } else {
                return ['success' => false, 'error' => 'Error al mover el archivo'];
            }

        } catch (Exception $e) {
            error_log('Error al procesar imagen: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Error interno al procesar la imagen'];
        }
    }

    /**
     * Eliminar imagen del filesystem
     */
    private function eliminarImagenFS($imagen) {
        $rutaArchivo = __DIR__ . '/../uploads/amenities/' . $imagen['amenity_id'] . '/' . $imagen['ruta_archivo'];
        
        if (file_exists($rutaArchivo)) {
            return unlink($rutaArchivo);
        }
        
        return true; // Si no existe, considerar eliminado
    }

    /**
     * Eliminar imagen de la BD
     */
    private function eliminarImagenBD($imagenId) {
        $sql = 'DELETE FROM amenity_imagenes WHERE id = ?';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$imagenId]);
    }

    /**
     * Actualizar orden de imágenes
     */
    private function actualizarOrdenImagenes($amenityId, $orden) {
        try {
            $this->db->beginTransaction();

            foreach ($orden as $posicion => $imagenId) {
                $sql = 'UPDATE amenity_imagenes SET orden = ? WHERE id = ? AND amenity_id = ?';
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$posicion, $imagenId, $amenityId]);
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('Error al actualizar orden de imágenes: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Crear configuración específica para amenity
     */
    private function crearConfiguracionAmenity($amenityId, $data) {
        $configuracion = [
            'config_especifica' => true,
            'horarios_personalizados' => isset($data['horarios_personalizados']),
            'reglas_especiales' => $data['reglas_especiales'] ?? null
        ];

        $sql = "INSERT INTO amenity_config (nivel, entidad_id, configuracion) VALUES ('amenity', ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$amenityId, json_encode($configuracion)]);
    }

    /**
     * Obtiene el color de badge Bootstrap según el tipo de amenity
     * 
     * @param string $tipo Tipo del amenity
     * @param string|null $estado Estado del amenity (opcional)
     * @return string Clase CSS de color Bootstrap
     */
    public function getAmenityBadgeColor($tipo, $estado = null) {
        // Si se proporciona estado, priorizar colores por estado
        if ($estado !== null) {
            switch ($estado) {
                case 'activo':
                case 'disponible':
                case 'confirmada':
                    return 'success';
                case 'mantenimiento':
                case 'bloqueado':
                case 'cancelada':
                    return 'danger';
                case 'pendiente':
                case 'en_revision':
                    return 'warning';
                case 'inactivo':
                case 'no_disponible':
                    return 'secondary';
                case 'reservada':
                case 'en_uso':
                    return 'info';
                default:
                    // Continuar con colores por tipo
                    break;
            }
        }
        
        // Colores por tipo de amenity
        switch ($tipo) {
            case 'gimnasio':
                return 'primary'; // Azul - Deporte/Energía
            case 'piscina':
                return 'info';    // Celeste - Agua/Refrescante
            case 'quincho':
                return 'warning'; // Naranja - Fuego/Comida
            case 'sala_eventos':
                return 'success'; // Verde - Social/Naturaleza
            case 'lavanderia':
                return 'secondary'; // Gris - Servicio/Utilidad
            case 'juegos_infantiles':
                return 'success';  // Verde - Diversión/Niños
            case 'terraza':
                return 'info';     // Celeste - Exterior/Cielo
            case 'estacionamiento':
                return 'dark';     // Negro/Gris - Vehículos
            case 'sala_reuniones':
                return 'primary';  // Azul - Profesional
            case 'biblioteca':
                return 'warning';  // Naranja - Conocimiento
            case 'spa':
                return 'danger';   // Rojo - Relax/Lujo
            case 'cancha_tenis':
                return 'success';  // Verde - Deporte
            case 'cancha_futbol':
                return 'success';  // Verde - Deporte
            case 'jacuzzi':
                return 'info';     // Celeste - Agua/Relax
            case 'sauna':
                return 'danger';   // Rojo - Calor/Salud
            default:
                return 'secondary'; // Gris por defecto
        }
    }
    
}
?>