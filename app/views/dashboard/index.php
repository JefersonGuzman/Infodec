<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-dark">
                        <i class="bi bi-graph-up me-2"></i>Dashboard VentasPlus
                    </h1>
                    <p class="text-muted mb-0">Análisis integral de ventas y comisiones</p>
                </div>
                <div class="d-flex gap-2">
                    <div class="btn-group" role="group">
                        <button class="btn btn-outline-primary btn-sm" onclick="actualizarDashboard()">
                            <i class="bi bi-arrow-clockwise me-1"></i>Actualizar
                        </button>
                        <button class="btn btn-outline-success btn-sm" onclick="exportarPNG()">
                            <i class="bi bi-image me-1"></i>PNG
                        </button>
                        <button class="btn btn-outline-info btn-sm" onclick="exportarPDF()">
                            <i class="bi bi-file-pdf me-1"></i>PDF
                        </button>
                    </div>
                    <button class="btn btn-primary btn-sm" onclick="toggleFullscreen()">
                        <i class="bi bi-arrows-fullscreen me-1"></i>Pantalla Completa
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <?php if($fechaDesde || $fechaHasta || $vendedorId): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-info d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-funnel me-2"></i>
                            <strong>Filtros activos:</strong>
                            <?php if($fechaDesde): ?>
                                <span class="badge bg-primary me-1">Desde: <?php echo date('d/m/Y', strtotime($fechaDesde)); ?></span>
                            <?php endif; ?>
                            <?php if($fechaHasta): ?>
                                <span class="badge bg-primary me-1">Hasta: <?php echo date('d/m/Y', strtotime($fechaHasta)); ?></span>
                            <?php endif; ?>
                            <?php if($vendedorId): ?>
                                <?php 
                                $vendedorSeleccionado = array_filter($vendedores, function($v) use ($vendedorId) {
                                    return $v['id'] == $vendedorId;
                                });
                                $vendedorSeleccionado = reset($vendedorSeleccionado);
                                ?>
                                <span class="badge bg-success me-1">Vendedor: <?php echo htmlspecialchars($vendedorSeleccionado['nombre']); ?></span>
                            <?php endif; ?>
                        </div>
                        <a href="index.php?controller=Dashboard&action=index" class="btn btn-outline-info btn-sm">
                            <i class="bi bi-x-circle me-1"></i>Limpiar Filtros
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <div class="col-xl-2 col-lg-3 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-funnel me-2"></i>FILTROS AVANZADOS
                    </h6>
                </div>
                <div class="card-body">
                    <form id="filtrosForm">
                        <div class="mb-3">
                            <label for="fechaDesde" class="form-label">Fecha Desde</label>
                            <input type="date" class="form-control" id="fechaDesde" name="fecha_desde">
                        </div>
                        <div class="mb-3">
                            <label for="fechaHasta" class="form-label">Fecha Hasta</label>
                            <input type="date" class="form-control" id="fechaHasta" name="fecha_hasta">
                        </div>
                        <div class="mb-3">
                            <label for="vendedorSelect" class="form-label">Vendedor</label>
                            <select class="form-select" id="vendedorSelect" name="vendedor">
                                <option value="">Todos los vendedores</option>
                                <?php 
                                $vendedores = $this->obtenerVendedores();
                                foreach($vendedores as $vendedor): 
                                ?>
                                <option value="<?php echo $vendedor['id']; ?>">
                                    <?php echo htmlspecialchars($vendedor['nombre']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="tipoGrafico" class="form-label">Tipo de Gráfico</label>
                            <select class="form-select" id="tipoGrafico" onchange="cambiarTipoGrafico()">
                                <option value="line">Líneas</option>
                                <option value="bar">Barras</option>
                                <option value="area">Área</option>
                                <option value="doughnut">Dona</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="periodo" class="form-label">Período</label>
                            <select class="form-select" id="periodo" onchange="aplicarPeriodo()">
                                <option value="custom">Personalizado</option>
                                <option value="today">Hoy</option>
                                <option value="week">Esta Semana</option>
                                <option value="month">Este Mes</option>
                                <option value="quarter">Este Trimestre</option>
                                <option value="year">Este Año</option>
                            </select>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-primary" onclick="aplicarFiltros()">
                                <i class="bi bi-search me-1"></i>Filtrar
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="limpiarFiltros()">
                                <i class="bi bi-x-circle me-1"></i>Limpiar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-lg-5 mb-4">
            <div class="row h-100">
                <div class="col-6 mb-3">
                    <div class="kpi-container">
                        <div class="kpi-card border-left-primary shadow mb-3">
                            <div class="kpi-content">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total Vendedores
                                        </div>
                                        <div class="h6 mb-0 font-weight-bold text-gray-800">
                                            <?php echo number_format($stats['total_vendedores']); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-people-fill fa-lg text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="kpi-card border-left-success shadow mb-3">
                            <div class="kpi-content">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Total Ventas
                                        </div>
                                        <div class="h6 mb-0 font-weight-bold text-gray-800">
                                            <?php echo number_format($stats['total_ventas']); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-cart-check fa-lg text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="kpi-card border-left-info shadow mb-3">
                            <div class="kpi-content">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Valor Ventas
                                        </div>
                                        <div class="h6 mb-0 font-weight-bold text-gray-800">
                                            $<?php echo number_format($stats['valor_total_ventas'], 0, ',', '.'); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-currency-dollar fa-lg text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="kpi-card border-left-warning shadow">
                            <div class="kpi-content">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Devoluciones
                                        </div>
                                        <div class="h6 mb-0 font-weight-bold text-gray-800">
                                            <?php echo number_format($stats['total_devoluciones']); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-arrow-return-left fa-lg text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-6">
                    <div class="card shadow h-100">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">COMISIONES VS DEVOLUCIONES</h6>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <div class="chart-line flex-grow-1">
                                <canvas id="comisionesChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">TOP VENDEDORES</h6>
                </div>
                <div class="card-body d-flex flex-column">
                    <div class="chart-pie pt-4 pb-2 flex-grow-1">
                        <canvas id="vendedoresChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-xl-4 col-lg-6">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">VENTAS POR MES</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots-vertical fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow">
                            <a class="dropdown-item" href="#" onclick="cambiarVistaGrafico('cantidad')">Ver Cantidad</a>
                            <a class="dropdown-item" href="#" onclick="cambiarVistaGrafico('valor')">Ver Valor</a>
                        </div>
                    </div>
                </div>
                <div class="card-body d-flex flex-column">
                    <div class="chart-area flex-grow-1">
                        <canvas id="ventasChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-6">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">TOTAL COMISIONES POR MES</h6>
                </div>
                <div class="card-body d-flex flex-column">
                    <div class="chart-line flex-grow-1">
                        <canvas id="comisionesMesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-6">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">VENTAS POR PRODUCTO</h6>
                </div>
                <div class="card-body d-flex flex-column">
                    <div class="chart-bar flex-grow-1">
                        <canvas id="productosChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">TOP 5 VENDEDORES POR COMISIÓN</h6>
                </div>
                <div class="card-body d-flex flex-column">
                    <div class="chart-bar flex-grow-1">
                        <canvas id="topComisionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-lg-6">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">PORCENTAJE DE VENDEDORES CON BONO</h6>
                </div>
                <div class="card-body d-flex flex-column">
                    <div class="chart-pie flex-grow-1">
                        <canvas id="porcentajeBonosChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">RESUMEN DE ACTIVIDAD</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Vendedor</th>
                                    <th>Ventas</th>
                                    <th>Valor Ventas</th>
                                    <th>Devoluciones</th>
                                    <th>Valor Devoluciones</th>
                                    <th>Bono</th>
                                    <th>Comisión Estimada</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $pdo = Conexion::getConexion();
                                foreach($topVendedores as $vendedor): 
                                    $bonoStmt = $pdo->prepare("SELECT COALESCE(SUM(bono), 0) as total_bono FROM comisiones WHERE vendedor_id = ?");
                                    $bonoStmt->execute([$vendedor['vendedor_id'] ?? 0]);
                                    $bonoData = $bonoStmt->fetch(PDO::FETCH_ASSOC);
                                    $totalBono = $bonoData['total_bono'] ?? 0;
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($vendedor['nombre']); ?></td>
                                    <td><?php echo number_format($vendedor['ventas']); ?></td>
                                    <td>$<?php echo number_format($vendedor['valor_ventas'], 0, ',', '.'); ?></td>
                                    <td><?php echo number_format($vendedor['devoluciones']); ?></td>
                                    <td>$<?php echo number_format($vendedor['valor_devoluciones'], 0, ',', '.'); ?></td>
                                    <td>
                                        <div class="bono-container">
                                            <?php if($totalBono > 0): ?>
                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-success me-2 bono-badge">
                                                        <i class="bi bi-trophy-fill me-1"></i>BONO
                                                    </span>
                                                    <span class="fw-bold text-success bono-amount">
                                                        $<?php echo number_format($totalBono, 0, ',', '.'); ?>
                                                    </span>
                                                </div>
                                            <?php else: ?>
                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-secondary me-2 bono-badge">
                                                        <i class="bi bi-dash-circle me-1"></i>SIN BONO
                                                    </span>
                                                    <span class="text-muted bono-amount">$0</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>$<?php echo number_format(($vendedor['valor_ventas'] - $vendedor['valor_devoluciones']) * 0.05, 0, ',', '.'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script>
let ventasChart, vendedoresChart, productosChart, comisionesChart, comisionesMesChart, topComisionChart, porcentajeBonosChart;
let chartInstances = [];
let isFullscreen = false;

document.addEventListener('DOMContentLoaded', function() {
    initCharts();
    setupAnimations();
    setupRealTimeUpdates();
});

function initCharts() {
    initVentasChart();
    initVendedoresChart();
    initProductosChart();
    initComisionesChart();
    initComisionesMesChart();
    initTopComisionChart();
    initPorcentajeBonosChart();
    
    // Registrar todas las instancias para exportación
    chartInstances = [ventasChart, vendedoresChart, productosChart, comisionesChart, comisionesMesChart, topComisionChart, porcentajeBonosChart];
}

function initVentasChart() {
    const ctx = document.getElementById('ventasChart').getContext('2d');
    const ventasData = <?php echo json_encode($ventasPorMes); ?>;
    
    const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    const labels = [];
    const data = [];
    
    for (let i = 1; i <= 12; i++) {
        labels.push(meses[i-1]);
        const mesData = ventasData.find(v => v.mes == i);
        data.push(mesData ? parseInt(mesData.cantidad) : 0);
    }
    
    ventasChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Ventas',
                data: data,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: 'rgb(75, 192, 192)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8,
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 2000,
                easing: 'easeInOutQuart'
            },
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: 'rgb(75, 192, 192)',
                    borderWidth: 1,
                    cornerRadius: 8,
                    displayColors: false
                }
            },
            scales: {
                x: {
                    grid: {
                        display: true,
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        display: true,
                        color: 'rgba(0, 0, 0, 0.1)'
                    },
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('es-CO').format(value);
                        }
                    }
                }
            }
        }
    });
}

function initVendedoresChart() {
    const ctx = document.getElementById('vendedoresChart').getContext('2d');
    const vendedoresData = <?php echo json_encode($topVendedores); ?>;
    
    const labels = vendedoresData.map(v => v.nombre);
    const data = vendedoresData.map(v => parseInt(v.valor_ventas));
    
    vendedoresChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

function initProductosChart() {
    const ctx = document.getElementById('productosChart').getContext('2d');
    const productosData = <?php echo json_encode($ventasPorProducto); ?>;
    
    const labels = productosData.map(p => p.producto);
    const data = productosData.map(p => parseInt(p.cantidad));
    
    productosChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Cantidad Vendida',
                data: data,
                backgroundColor: 'rgba(54, 162, 235, 0.8)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function initComisionesChart() {
    const ctx = document.getElementById('comisionesChart').getContext('2d');
    const comisionesData = <?php echo json_encode($comisionesPorMes); ?>;
    const devolucionesData = <?php echo json_encode($devolucionesPorMes); ?>;
    
    const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    const labels = [];
    const comisiones = [];
    const devoluciones = [];
    
    for (let i = 1; i <= 12; i++) {
        labels.push(meses[i-1]);
        const comisionMes = comisionesData.find(c => c.mes == i);
        const devolucionMes = devolucionesData.find(d => d.mes == i);
        comisiones.push(comisionMes ? parseInt(comisionMes.total_comisiones) : 0);
        devoluciones.push(devolucionMes ? parseInt(devolucionMes.valor) : 0);
    }
    
    comisionesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Comisiones',
                data: comisiones,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }, {
                label: 'Devoluciones',
                data: devoluciones,
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function initComisionesMesChart() {
    const ctx = document.getElementById('comisionesMesChart').getContext('2d');
    const totalComisionesData = <?php echo json_encode($totalComisionesPorMes); ?>;
    
    const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    const labels = [];
    const data = [];
    
    for (let i = 1; i <= 12; i++) {
        labels.push(meses[i-1]);
        const mesData = totalComisionesData.find(c => c.mes == i);
        data.push(mesData ? parseFloat(mesData.total_comisiones) : 0);
    }
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total Comisiones',
                data: data,
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                tension: 0.1,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + new Intl.NumberFormat('es-CO').format(value);
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Total: $' + new Intl.NumberFormat('es-CO').format(context.parsed.y);
                        }
                    }
                }
            }
        }
    });
}

function initTopComisionChart() {
    const ctx = document.getElementById('topComisionChart').getContext('2d');
    const topComisionData = <?php echo json_encode($topVendedoresComision); ?>;
    
    const labels = topComisionData.map(v => v.nombre);
    const data = topComisionData.map(v => parseFloat(v.total_comision));
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total Comisión',
                data: data,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 205, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(153, 102, 255, 0.8)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 205, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + new Intl.NumberFormat('es-CO').format(value);
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Comisión: $' + new Intl.NumberFormat('es-CO').format(context.parsed.y);
                        }
                    }
                }
            }
        }
    });
}

function initPorcentajeBonosChart() {
    const ctx = document.getElementById('porcentajeBonosChart').getContext('2d');
    const porcentajeBonosData = <?php echo json_encode($porcentajeBonos); ?>;
    
    const conBono = parseInt(porcentajeBonosData.vendedores_con_bono) || 0;
    const sinBono = parseInt(porcentajeBonosData.total_vendedores) - conBono;
    const porcentaje = parseFloat(porcentajeBonosData.porcentaje_bonos) || 0;
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Con Bono', 'Sin Bono'],
            datasets: [{
                data: [conBono, sinBono],
                backgroundColor: [
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(255, 99, 132, 0.8)'
                ],
                borderColor: [
                    'rgba(75, 192, 192, 1)',
                    'rgba(255, 99, 132, 1)'
                ],
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
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                        }
                    }
                },
                title: {
                    display: true,
                    text: 'Porcentaje: ' + porcentaje + '%',
                    font: {
                        size: 16,
                        weight: 'bold'
                    }
                }
            }
        }
    });
}

function cambiarVistaGrafico(tipo) {
    if (ventasChart) {
        const ventasData = <?php echo json_encode($ventasPorMes); ?>;
        const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        const data = [];
        
        for (let i = 1; i <= 12; i++) {
            const mesData = ventasData.find(v => v.mes == i);
            if (tipo === 'cantidad') {
                data.push(mesData ? parseInt(mesData.cantidad) : 0);
            } else {
                data.push(mesData ? parseInt(mesData.valor) : 0);
            }
        }
        
        ventasChart.data.datasets[0].data = data;
        ventasChart.data.datasets[0].label = tipo === 'cantidad' ? 'Cantidad de Ventas' : 'Valor de Ventas';
        ventasChart.update();
    }
}

function aplicarFiltros() {
    const fechaDesde = document.getElementById('fechaDesde').value;
    const fechaHasta = document.getElementById('fechaHasta').value;
    const vendedor = document.getElementById('vendedorSelect').value;
    
    let url = 'index.php?controller=Dashboard&action=index';
    const params = new URLSearchParams();
    
    if (fechaDesde) params.append('fecha_desde', fechaDesde);
    if (fechaHasta) params.append('fecha_hasta', fechaHasta);
    if (vendedor) params.append('vendedor', vendedor);
    
    if (params.toString()) {
        url += '&' + params.toString();
    }
    
    window.location.href = url;
}

function actualizarDashboard() {
    location.reload();
}

function exportarDashboard() {
    window.print();
}

document.addEventListener('DOMContentLoaded', function() {
    const fechaHasta = document.getElementById('fechaHasta');
    const fechaDesde = document.getElementById('fechaDesde');
    
    if (!fechaHasta.value) {
        fechaHasta.value = new Date().toISOString().split('T')[0];
    }
    
    if (!fechaDesde.value) {
        fechaDesde.value = '2025-01-01';
    }
});

// Nuevas funciones avanzadas
function setupAnimations() {
    // Animación de entrada para las tarjetas KPI
    const kpiCards = document.querySelectorAll('.kpi-card');
    kpiCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.6s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
    
    // Animación de entrada para los gráficos
    const chartContainers = document.querySelectorAll('.card');
    chartContainers.forEach((container, index) => {
        container.style.opacity = '0';
        container.style.transform = 'scale(0.95)';
        setTimeout(() => {
            container.style.transition = 'all 0.8s ease';
            container.style.opacity = '1';
            container.style.transform = 'scale(1)';
        }, (index + kpiCards.length) * 150);
    });
}

function setupRealTimeUpdates() {
    // Actualización automática cada 5 minutos
    setInterval(() => {
        if (!isFullscreen) {
            actualizarDashboard();
        }
    }, 300000);
}

function cambiarTipoGrafico() {
    const tipo = document.getElementById('tipoGrafico').value;
    
    // Cambiar tipo de gráfico principal
    if (ventasChart) {
        ventasChart.config.type = tipo;
        ventasChart.update('active');
    }
    
    // Aplicar animación de transición
    const chartContainer = document.getElementById('ventasChart').parentElement;
    chartContainer.style.transform = 'scale(0.95)';
    chartContainer.style.opacity = '0.7';
    
    setTimeout(() => {
        chartContainer.style.transform = 'scale(1)';
        chartContainer.style.opacity = '1';
    }, 300);
}

function aplicarPeriodo() {
    const periodo = document.getElementById('periodo').value;
    const fechaDesde = document.getElementById('fechaDesde');
    const fechaHasta = document.getElementById('fechaHasta');
    const hoy = new Date();
    
    switch(periodo) {
        case 'today':
            fechaDesde.value = hoy.toISOString().split('T')[0];
            fechaHasta.value = hoy.toISOString().split('T')[0];
            break;
        case 'week':
            const inicioSemana = new Date(hoy);
            inicioSemana.setDate(hoy.getDate() - hoy.getDay());
            fechaDesde.value = inicioSemana.toISOString().split('T')[0];
            fechaHasta.value = hoy.toISOString().split('T')[0];
            break;
        case 'month':
            const inicioMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
            fechaDesde.value = inicioMes.toISOString().split('T')[0];
            fechaHasta.value = hoy.toISOString().split('T')[0];
            break;
        case 'quarter':
            const trimestre = Math.floor(hoy.getMonth() / 3);
            const inicioTrimestre = new Date(hoy.getFullYear(), trimestre * 3, 1);
            fechaDesde.value = inicioTrimestre.toISOString().split('T')[0];
            fechaHasta.value = hoy.toISOString().split('T')[0];
            break;
        case 'year':
            const inicioAño = new Date(hoy.getFullYear(), 0, 1);
            fechaDesde.value = inicioAño.toISOString().split('T')[0];
            fechaHasta.value = hoy.toISOString().split('T')[0];
            break;
    }
    
    if (periodo !== 'custom') {
        aplicarFiltros();
    }
}

function limpiarFiltros() {
    document.getElementById('fechaDesde').value = '';
    document.getElementById('fechaHasta').value = '';
    document.getElementById('vendedorSelect').value = '';
    document.getElementById('tipoGrafico').value = 'line';
    document.getElementById('periodo').value = 'custom';
    
    // Aplicar animación de limpieza
    const form = document.getElementById('filtrosForm');
    form.style.transform = 'scale(0.98)';
    form.style.opacity = '0.8';
    
    setTimeout(() => {
        form.style.transform = 'scale(1)';
        form.style.opacity = '1';
        aplicarFiltros();
    }, 200);
}

function exportarPNG() {
    const dashboard = document.querySelector('.container-fluid');
    
    // Mostrar loading
    const loading = document.createElement('div');
    loading.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Exportando...</span></div>';
    loading.style.position = 'fixed';
    loading.style.top = '50%';
    loading.style.left = '50%';
    loading.style.transform = 'translate(-50%, -50%)';
    loading.style.zIndex = '9999';
    loading.style.backgroundColor = 'rgba(255, 255, 255, 0.9)';
    loading.style.padding = '20px';
    loading.style.borderRadius = '10px';
    document.body.appendChild(loading);
    
    html2canvas(dashboard, {
        scale: 2,
        useCORS: true,
        allowTaint: true,
        backgroundColor: '#ffffff'
    }).then(canvas => {
        const link = document.createElement('a');
        link.download = 'dashboard-ventasplus-' + new Date().toISOString().split('T')[0] + '.png';
        link.href = canvas.toDataURL();
        link.click();
        
        document.body.removeChild(loading);
    }).catch(error => {
        console.error('Error al exportar PNG:', error);
        document.body.removeChild(loading);
        alert('Error al exportar la imagen');
    });
}

function exportarPDF() {
    const dashboard = document.querySelector('.container-fluid');
    
    // Mostrar loading
    const loading = document.createElement('div');
    loading.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Exportando PDF...</span></div>';
    loading.style.position = 'fixed';
    loading.style.top = '50%';
    loading.style.left = '50%';
    loading.style.transform = 'translate(-50%, -50%)';
    loading.style.zIndex = '9999';
    loading.style.backgroundColor = 'rgba(255, 255, 255, 0.9)';
    loading.style.padding = '20px';
    loading.style.borderRadius = '10px';
    document.body.appendChild(loading);
    
    html2canvas(dashboard, {
        scale: 2,
        useCORS: true,
        allowTaint: true,
        backgroundColor: '#ffffff'
    }).then(canvas => {
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('p', 'mm', 'a4');
        const imgData = canvas.toDataURL('image/png');
        
        const imgWidth = 210;
        const pageHeight = 295;
        const imgHeight = (canvas.height * imgWidth) / canvas.width;
        let heightLeft = imgHeight;
        
        let position = 0;
        
        pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
        heightLeft -= pageHeight;
        
        while (heightLeft >= 0) {
            position = heightLeft - imgHeight;
            pdf.addPage();
            pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
            heightLeft -= pageHeight;
        }
        
        pdf.save('dashboard-ventasplus-' + new Date().toISOString().split('T')[0] + '.pdf');
        document.body.removeChild(loading);
    }).catch(error => {
        console.error('Error al exportar PDF:', error);
        document.body.removeChild(loading);
        alert('Error al exportar el PDF');
    });
}

function toggleFullscreen() {
    const dashboard = document.querySelector('.container-fluid');
    
    if (!isFullscreen) {
        if (dashboard.requestFullscreen) {
            dashboard.requestFullscreen();
        } else if (dashboard.webkitRequestFullscreen) {
            dashboard.webkitRequestFullscreen();
        } else if (dashboard.msRequestFullscreen) {
            dashboard.msRequestFullscreen();
        }
        isFullscreen = true;
        
        // Ajustar gráficos para pantalla completa
        chartInstances.forEach(chart => {
            if (chart) {
                chart.resize();
            }
        });
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        }
        isFullscreen = false;
        
        // Reajustar gráficos
        setTimeout(() => {
            chartInstances.forEach(chart => {
                if (chart) {
                    chart.resize();
                }
            });
        }, 100);
    }
}

// Escuchar cambios de pantalla completa
document.addEventListener('fullscreenchange', function() {
    isFullscreen = !!document.fullscreenElement;
    if (!isFullscreen) {
        chartInstances.forEach(chart => {
            if (chart) {
                chart.resize();
            }
        });
    }
});
</script>

<style>
.kpi-container {
    max-height: 500px;
    overflow-y: auto;
}

.kpi-container .kpi-card {
    width: 100%;
    height: 90px;
    max-height: 90px;
    margin-bottom: 1rem;
    background-color: white;
    border-radius: 0.35rem;
    border: 1px solid #e3e6f0;
}

.kpi-container .kpi-card:last-child {
    margin-bottom: 0;
}

.kpi-container .kpi-content {
    height: 90px;
    max-height: 90px;
    padding: 0.75rem;
    display: flex;
    align-items: center;
}

.card {
    min-height: 400px;
}

.card-body {
    min-height: 300px;
}

.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.text-xs {
    font-size: 0.7rem;
}

.font-weight-bold {
    font-weight: 700 !important;
}

.text-uppercase {
    text-transform: uppercase !important;
}

.text-gray-800 {
    color: #5a5c69 !important;
}

.text-gray-300 {
    color: #dddfeb !important;
}

.chart-area {
    position: relative;
    height: 15rem;
}

.chart-pie {
    position: relative;
    height: 15rem;
}

.chart-bar {
    position: relative;
    height: 15rem;
}

.chart-line {
    position: relative;
    height: 15rem;
}

.card-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
}

.card {
    border: 1px solid #e3e6f0;
    border-radius: 0.35rem;
}

.shadow {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
}

@media (max-width: 768px) {
    .card {
        min-height: 350px;
    }
    
    .card-body {
        min-height: 250px;
    }
    
    .chart-area,
    .chart-pie,
    .chart-bar,
    .chart-line {
        height: 12rem;
    }
}

/* Estilos para la columna de bono */
.bono-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.bono-amount {
    font-size: 0.9rem;
    font-weight: 600;
}

.bono-container {
    min-width: 120px;
}

/* Responsive para la tabla */
@media (max-width: 992px) {
    .bono-container {
        min-width: 100px;
    }
    
    .bono-badge {
        font-size: 0.7rem;
        padding: 0.2rem 0.4rem;
    }
    
    .bono-amount {
        font-size: 0.8rem;
    }
}

/* Estilos para animaciones avanzadas */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes scaleIn {
    from {
        opacity: 0;
        transform: scale(0.9);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

@keyframes pulse {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
    100% {
        transform: scale(1);
    }
}

.animate-fadeInUp {
    animation: fadeInUp 0.6s ease-out;
}

.animate-scaleIn {
    animation: scaleIn 0.8s ease-out;
}

.animate-pulse {
    animation: pulse 2s infinite;
}

/* Efectos hover mejorados */
.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 2rem rgba(0, 0, 0, 0.15) !important;
    transition: all 0.3s ease;
}

.kpi-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 0.3rem 1rem rgba(0, 0, 0, 0.1) !important;
    transition: all 0.3s ease;
}

/* Estilos para botones de exportación */
.btn-group .btn {
    transition: all 0.3s ease;
}

.btn-group .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.2rem 0.5rem rgba(0, 0, 0, 0.15);
}

/* Efectos de loading */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

/* Mejoras en los gráficos */
.chart-container {
    position: relative;
    transition: all 0.3s ease;
}

.chart-container:hover {
    transform: scale(1.02);
}

/* Estilos para pantalla completa */
.fullscreen-mode {
    background: #f8f9fa;
    padding: 20px;
}

.fullscreen-mode .card {
    margin-bottom: 20px;
}

/* Efectos de transición suaves */
* {
    transition: all 0.3s ease;
}

/* Mejoras en tooltips */
.tooltip {
    font-size: 0.875rem;
    background: rgba(0, 0, 0, 0.9);
    border-radius: 8px;
    padding: 8px 12px;
}

/* Efectos de gradiente en las tarjetas KPI */
.kpi-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border-left: 4px solid;
}

.border-left-primary {
    border-left-color: #4e73df !important;
}

.border-left-success {
    border-left-color: #1cc88a !important;
}

.border-left-info {
    border-left-color: #36b9cc !important;
}

.border-left-warning {
    border-left-color: #f6c23e !important;
}

/* Efectos de sombra mejorados */
.shadow {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
}

.shadow:hover {
    box-shadow: 0 0.25rem 2rem 0 rgba(58, 59, 69, 0.25) !important;
}

/* Responsive mejorado */
@media (max-width: 768px) {
    .btn-group {
        flex-direction: column;
    }
    
    .btn-group .btn {
        margin-bottom: 5px;
    }
    
    .card {
        margin-bottom: 15px;
    }
    
    .kpi-container {
        max-height: 400px;
    }
}
</style>
