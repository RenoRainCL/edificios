<?php
// 📁 config/routes.php - VERSIÓN ACTUALIZADA CON SISTEMA DE APROBACIÓN DE PAGOS

return function (Router $router) {
    // ==================== RUTAS PÚBLICAS ====================
    $router->get('/', 'AuthController@login');
    $router->post('/login', 'AuthController@login');
    $router->get('/login', 'AuthController@login');
    $router->get('/logout', 'AuthController@logout');

    // ==================== RUTAS PROTEGIDAS ====================

    // Dashboard
    $router->get('/dashboard', 'DashboardController@index');

    // Edificios
    $router->get('/edificios', 'EdificiosController@index');
    $router->get('/edificios/crear', 'EdificiosController@crear');
    $router->post('/edificios/crear', 'EdificiosController@crear');
    $router->get('/edificios/editar/(\d+)', 'EdificiosController@editar');
    $router->post('/edificios/editar/(\d+)', 'EdificiosController@editar');
    $router->get('/edificios/gestionar/(\d+)', 'EdificiosController@gestionar');
    $router->post('/edificios/desactivar/(\d+)', 'EdificiosController@desactivar');

    // Usuarios
    $router->get('/usuarios', 'UserController@index');
    $router->get('/usuarios/crear', 'UserController@crear');
    $router->post('/usuarios/crear', 'UserController@crear');
    $router->get('/usuarios/editar/(\d+)', 'UserController@editar');
    $router->post('/usuarios/editar/(\d+)', 'UserController@editar');
    $router->post('/usuarios/desactivar/(\d+)', 'UserController@desactivar');

    // Gestión de Roles y Permisos
    $router->get('/roles', 'RolesController@index');
    $router->get('/roles/crear', 'RolesController@crear');
    $router->post('/roles/crear', 'RolesController@crear');
    $router->get('/roles/editar/(\d+)', 'RolesController@editar');
    $router->post('/roles/editar/(\d+)', 'RolesController@editar');

    // Departamentos
    $router->get('/departamentos', 'DepartamentosController@index');
    $router->get('/departamentos/crear', 'DepartamentosController@crear');
    $router->post('/departamentos/crear', 'DepartamentosController@crear');
    $router->get('/departamentos/editar/(\d+)', 'DepartamentosController@editar');
    $router->post('/departamentos/editar/(\d+)', 'DepartamentosController@editar');
    $router->get('/departamentos/ver/(\d+)', 'DepartamentosController@ver');
    $router->post('/departamentos/desactivar/(\d+)', 'DepartamentosController@desactivar');

    // ==================== MÓDULO FINANZAS - ACTUALIZADO ====================

    // Finanzas - Gastos Comunes
    $router->get('/finanzas/gastos-comunes', 'FinanzasController@gastosComunes');
    $router->get('/finanzas/gastos-comunes/crear', 'FinanzasController@crearGasto');
    $router->post('/finanzas/gastos-comunes/crear', 'FinanzasController@crearGasto');
    $router->get('/finanzas/gastos-comunes/editar/(\d+)', 'FinanzasController@editarGasto');
    $router->post('/finanzas/gastos-comunes/editar/(\d+)', 'FinanzasController@editarGasto');
    $router->get('/finanzas/gastos-comunes/ver/(\d+)', 'FinanzasController@verGasto');
    $router->post('/finanzas/gastos-comunes/cerrar/(\d+)', 'FinanzasController@cerrarGasto');
    $router->post('/finanzas/gastos-comunes/eliminar/(\d+)', 'FinanzasController@eliminarGasto');

    // Finanzas - Pagos (Administradores)
    $router->get('/finanzas/estado-pagos', 'FinanzasController@estadoPagos');
    $router->get('/finanzas/pagos', 'FinanzasController@pagos');
    $router->get('/finanzas/pagos/registrar', 'FinanzasController@registrarPago');
    $router->post('/finanzas/pagos/registrar', 'FinanzasController@registrarPago');
    $router->post('/finanzas/pagos/marcar-pagado/(\d+)', 'FinanzasController@marcarPagoPagado');

    // Finanzas - Pagos (Propietarios) - RUTAS NUEVAS
    $router->get('/finanzas/mis-pagos', 'FinanzasController@misPagos');
    $router->get('/finanzas/mis-pagos/registrar', 'FinanzasController@registrarMiPago');
    $router->post('/finanzas/mis-pagos/registrar', 'FinanzasController@registrarMiPago');

    // Finanzas - Aprobación de Pagos (Administradores) - RUTAS NUEVAS
    $router->post('/finanzas/pagos/aprobar/(\d+)', 'FinanzasController@aprobarPago');
    $router->post('/finanzas/pagos/rechazar/(\d+)', 'FinanzasController@rechazarPago');

    // Finanzas - Reportes
    $router->get('/finanzas/reportes', 'ReportesController@financieros');
    $router->get('/finanzas/reportes/exportar-pdf', 'ReportesController@exportarPDF');
    $router->get('/finanzas/reportes/exportar-excel', 'ReportesController@exportarExcel');

    // ==================== MÓDULO PRORRATEO - RUTAS UNIFICADAS ====================

    // Gestión Principal de Prorrateo
    $router->get('/finanzas/prorrateo', 'FinanzasController@prorrateoGastos');
    $router->post('/finanzas/prorrateo/calcular', 'FinanzasController@calcularProrrateo');
    $router->post('/finanzas/prorrateo/aprobar/(\d+)', 'FinanzasController@aprobarProrrateo');
    $router->get('/finanzas/prorrateo/ver/(\d+)', 'FinanzasController@verProrrateo');

    // Configuración de Prorrateo
    $router->get('/configuracion/prorrateo', 'ConfiguracionController@prorrateo');
    $router->post('/configuracion/prorrateo', 'ConfiguracionController@prorrateo');
    $router->post('/configuracion/prorrateo/estrategias', 'ConfiguracionController@guardarEstrategia');

    // ==================== RUTAS API - ACTUALIZADAS ====================

    // API Departamentos (RUTAS NUEVAS - SOLUCIÓN AL ERROR)
    $router->get('/api/departamentos/calcular-porcentaje', 'ApiController@calcularPorcentajeDepartamento');
    $router->post('/api/edificios/recalcular-prorrateo', 'ApiController@recalcularProrrateoEdificio');

    // API Prorrateo
    $router->post('/api/prorrateo/calcular', 'ApiController@calcularProrrateo');
    $router->post('/api/prorrateo/aprobar', 'ApiController@aprobarProrrateo');

    // API General
    $router->get('/api/menu', 'ApiController@getMenu');
    $router->get('/api/edificios', 'ApiController@getEdificios');
    $router->get('/api/finanzas/gastos-comunes', 'ApiController@getGastosComunes');
    $router->get('/api/legal/cumplimiento', 'ApiController@getCumplimientoLegal');

    // ==================== MÓDULO MANTENIMIENTO ====================

    $router->get('/mantenimiento', 'MantenimientoController@index');
    $router->get('/mantenimiento/crear', 'MantenimientoController@crear');
    $router->post('/mantenimiento/crear', 'MantenimientoController@crear');
    $router->get('/mantenimiento/editar/(\d+)', 'MantenimientoController@editar');
    $router->post('/mantenimiento/editar/(\d+)', 'MantenimientoController@editar');
    $router->get('/mantenimiento/ver/(\d+)', 'MantenimientoController@ver');
    $router->post('/mantenimiento/cambiar-estado/(\d+)', 'MantenimientoController@cambiarEstado');

    // ==================== MÓDULO AMENITIES ====================

    // Gestión de Amenities
    $router->get('/amenities/gestionar', 'AmenitiesController@gestionar');
    $router->get('/amenities/crear', 'AmenitiesController@crear');
    $router->post('/amenities/crear', 'AmenitiesController@crear');
    $router->get('/amenities/editar/(\d+)', 'AmenitiesController@editar');
    $router->post('/amenities/editar/(\d+)', 'AmenitiesController@editar');
    $router->post('/amenities/desactivar/(\d+)', 'AmenitiesController@desactivar');

    // Configuración de Amenities
    $router->get('/amenities/configuracion', 'AmenitiesController@configuracion');
    $router->post('/amenities/configuracion', 'AmenitiesController@configuracion');

    // Gestión de Imágenes
    $router->post('/amenities/subir-imagen/(\d+)', 'AmenitiesController@subirImagen');
    $router->post('/amenities/eliminar-imagen/(\d+)', 'AmenitiesController@eliminarImagen');
    $router->post('/amenities/ordenar-imagenes/(\d+)', 'AmenitiesController@ordenarImagenes');

    // ==================== MÓDULO RESERVAS ====================

    // Vistas Principales
    $router->get('/amenities/reservas/calendario', 'ReservasController@calendario');
    $router->get('/amenities/reservas/mis-reservas', 'ReservasController@misReservas');
    $router->get('/amenities/reservas/aprobaciones', 'ReservasController@aprobaciones');
    $router->get('/amenities/reservas/crear', 'ReservasController@crear');
    $router->post('/amenities/reservas/crear', 'ReservasController@crear');

    // Acciones CRUD
    $router->post('/amenities/reservas/cancelar/(\d+)', 'ReservasController@cancelar');
    $router->post('/amenities/reservas/aprobar/(\d+)', 'ReservasController@aprobar');
    $router->post('/amenities/reservas/rechazar/(\d+)', 'ReservasController@rechazar');

    // API/Validaciones
    $router->get('/amenities/reservas/verificar-disponibilidad', 'ReservasController@verificarDisponibilidad');
    $router->get('/amenities/reservas/horarios-disponibles', 'ReservasController@getHorariosDisponibles');

    // ==================== CONFIGURACIÓN GENERAL ====================

    // Configuración
    $router->get('/configuracion/general', 'ConfiguracionController@general');
    $router->get('/configuracion/notificaciones', 'ConfiguracionController@notificaciones');
    
    $router->get('configuracion/prorrateo', 'ConfiguracionController@prorrateo');
    $router->post('configuracion/calcularAuto', 'ConfiguracionController@calcularAuto'); // ✅ POST
    // Comunicaciones
    $router->get('/comunicaciones', 'ComunicacionesController@index');

    // Documentos
    $router->get('/documentos', 'DocumentosController@index');

    // ==================== RUTA 404 ====================
    $router->set404(function () {
        http_response_code(404);
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>404 - Página no encontrada</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body class="bg-light">
            <div class="container mt-5">
                <div class="row justify-content-center">
                    <div class="col-md-6 text-center">
                        <h1 class="text-danger">404</h1>
                        <h3>Página no encontrada</h3>
                        <p class="text-muted">La página que buscas no existe.</p>
                        <a href="/proyectos/edificios/" class="btn btn-primary">Volver al Inicio</a>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
    });
};
?>