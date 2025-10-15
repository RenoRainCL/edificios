<?php
// 📁 views/templates/footer.php
?>
            </main>
        </div>
    </div>
  
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // ✅ VERIFICAR Y INICIALIZAR SÓLO GRÁFICOS EXISTENTES
        
        // Gráfico de Dashboard
        var dashboardChart = document.getElementById('dashboardChart');
        if (dashboardChart) {
            try {
                var ctx = dashboardChart.getContext('2d');
                // Tu código existente del gráfico de dashboard aquí
                console.log('Dashboard chart initialized');
            } catch (error) {
                console.error('Error initializing dashboard chart:', error);
            }
        }
        
        // Gráfico de Finanzas  
        var finanzasChart = document.getElementById('finanzasChart');
        if (finanzasChart) {
            try {
                var ctx = finanzasChart.getContext('2d');
                // Tu código existente del gráfico de finanzas aquí
                console.log('Finanzas chart initialized');
            } catch (error) {
                console.error('Error initializing finanzas chart:', error);
            }
        }
        
        // Gráfico de Reservas
        var reservasChart = document.getElementById('reservasChart');
        if (reservasChart) {
            try {
                var ctx = reservasChart.getContext('2d');
                // Tu código existente del gráfico de reservas aquí
                console.log('Reservas chart initialized');
            } catch (error) {
                console.error('Error initializing reservas chart:', error);
            }
        }
        
        // ✅ AGREGAR MÁS VERIFICACIONES PARA TODOS TUS GRÁFICOS
        var chartIds = ['estadisticasChart', 'pagosChart', 'mantenimientoChart', 'amenitiesChart'];
        
        chartIds.forEach(function(chartId) {
            var chartElement = document.getElementById(chartId);
            if (chartElement) {
                try {
                    var ctx = chartElement.getContext('2d');
                    console.log('Chart initialized:', chartId);
                    // Inicializar gráfico específico según el ID
                } catch (error) {
                    console.error('Error initializing chart ' + chartId + ':', error);
                }
            }
        });
        
        // ✅ TU CÓDIGO EXISTENTE DEL MODAL Y BOTONES (si lo tienes en footer)
        const confirmModal = document.getElementById('confirmModal');
        if (confirmModal) {
            // Tu código existente de modales
        }
    });
    </script>
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050;">
        <a href="<?php echo $url->to('dashboard'); ?>" class="btn btn-primary btn-lg rounded-circle shadow-lg d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
            <i class="bi bi-house fs-5"></i>
        </a>
    </div>  
</body>
</html>