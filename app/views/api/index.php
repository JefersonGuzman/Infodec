<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-dark mb-0">
        <i class="bi bi-cloud-download me-2"></i>Integración con API Externa
    </h2>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary" onclick="sincronizarProductos()">
            <i class="bi bi-arrow-clockwise me-2"></i>Sincronizar Productos
        </button>
        <button type="button" class="btn btn-outline-info" onclick="validarDatos()">
            <i class="bi bi-check-circle me-2"></i>Validar Datos
        </button>
        <a href="index.php?controller=Api&action=productos" class="btn btn-outline-success">
            <i class="bi bi-list me-2"></i>Ver Productos
        </a>
    </div>
</div>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i><?php echo $_SESSION['success_message']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i><?php echo $_SESSION['error_message']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['warning_message'])): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-circle me-2"></i><?php echo $_SESSION['warning_message']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['warning_message']); ?>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-dark">Total Productos</h5>
                <h3 class="text-primary"><?php echo $estadisticas['total_productos'] ?? 0; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-dark">Disponibles</h5>
                <h3 class="text-success"><?php echo $estadisticas['productos_disponibles'] ?? 0; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-dark">Categorías</h5>
                <h3 class="text-info"><?php echo $estadisticas['categorias'] ?? 0; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-dark">Última Sincronización</h5>
                <h6 class="text-muted"><?php echo $estadisticas['ultima_sincronizacion'] ? date('d/m/Y H:i', strtotime($estadisticas['ultima_sincronizacion'])) : 'Nunca'; ?></h6>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0 text-dark">
                    <i class="bi bi-graph-up me-2"></i>Estado de Integración
                </h5>
            </div>
            <div class="card-body">
                <div id="integrationChart" style="height: 300px; position: relative;">
                    <div class="text-center" id="chartSpinner">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0 text-dark">
                    <i class="bi bi-tags me-2"></i>Categorías de Productos
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($categorias)): ?>
                    <?php foreach ($categorias as $categoria): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-secondary"><?php echo htmlspecialchars($categoria['categoria']); ?></span>
                            <span class="text-muted"><?php echo $categoria['cantidad']; ?> productos</span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted text-center">No hay categorías disponibles</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['validacion_data'])): ?>
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0 text-dark">
                    <i class="bi bi-search me-2"></i>Resultados de Validación
                </h5>
            </div>
            <div class="card-body">
                <?php $validacion = $_SESSION['validacion_data']; ?>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-primary"><?php echo $validacion['productos_csv']; ?></h4>
                            <small class="text-muted">Productos CSV</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-info"><?php echo $validacion['productos_api']; ?></h4>
                            <small class="text-muted">Productos API</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-success"><?php echo $validacion['coincidencias']; ?></h4>
                            <small class="text-muted">Coincidencias</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-warning"><?php echo count($validacion['discrepancias']); ?></h4>
                            <small class="text-muted">Discrepancias</small>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($validacion['discrepancias'])): ?>
                    <h6>Discrepancias de Precio:</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Precio CSV</th>
                                    <th>Precio API</th>
                                    <th>Diferencia</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($validacion['discrepancias'] as $discrepancia): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($discrepancia['producto']); ?></td>
                                        <td>$<?php echo number_format($discrepancia['precio_csv'], 0, ',', '.'); ?></td>
                                        <td>$<?php echo number_format($discrepancia['precio_api'], 0, ',', '.'); ?></td>
                                        <td class="text-danger">$<?php echo number_format($discrepancia['diferencia'], 0, ',', '.'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php unset($_SESSION['validacion_data']); ?>
<?php endif; ?>

<script>
function sincronizarProductos() {
    if (confirm('¿Está seguro de que desea sincronizar los productos desde la API?')) {
        window.location.href = 'index.php?controller=Api&action=sincronizar';
    }
}

function validarDatos() {
    if (confirm('¿Desea validar los datos entre CSV y API?')) {
        window.location.href = 'index.php?controller=Api&action=validar';
    }
}

// Cargar gráfico de integración
document.addEventListener('DOMContentLoaded', function() {
    cargarGraficoIntegracion();
});

function cargarGraficoIntegracion() {
    const chartContainer = document.getElementById('integrationChart');
    const spinner = document.getElementById('chartSpinner');
    
    fetch('index.php?controller=Api&action=estadisticas')
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.estadisticas) {
                // Ocultar spinner
                spinner.style.display = 'none';
                
                // Crear canvas
                const canvas = document.createElement('canvas');
                canvas.id = 'integrationChartCanvas';
                canvas.style.width = '100%';
                canvas.style.height = '100%';
                chartContainer.appendChild(canvas);
                
                const ctx = canvas.getContext('2d');
                
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Disponibles', 'No Disponibles'],
                        datasets: [{
                            data: [
                                data.estadisticas.productos_disponibles || 0,
                                (data.estadisticas.total_productos || 0) - (data.estadisticas.productos_disponibles || 0)
                            ],
                            backgroundColor: ['#28a745', '#dc3545'],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            },
                            title: {
                                display: true,
                                text: 'Distribución de Productos'
                            }
                        }
                    }
                });
            } else {
                throw new Error(data.error || 'No se pudieron obtener las estadísticas');
            }
        })
        .catch(error => {
            console.error('Error cargando estadísticas:', error);
            spinner.innerHTML = `
                <div class="text-center text-muted py-4">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <p>Error cargando datos del gráfico</p>
                    <small>${error.message}</small>
                </div>
            `;
        });
}
</script>
