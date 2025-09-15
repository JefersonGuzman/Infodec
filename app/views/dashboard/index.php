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
                    <button class="btn btn-outline-primary btn-sm" onclick="actualizarDashboard()">
                        <i class="bi bi-arrow-clockwise me-1"></i>Actualizar
                    </button>
                    <button class="btn btn-primary btn-sm" onclick="exportarDashboard()">
                        <i class="bi bi-download me-1"></i>Exportar
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
                        <i class="bi bi-funnel me-2"></i>FILTROS
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
                        <button type="button" class="btn btn-primary w-100" onclick="aplicarFiltros()">
                            <i class="bi bi-search me-1"></i>Filtrar
                        </button>
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
<script>
let ventasChart, vendedoresChart, productosChart, comisionesChart;

document.addEventListener('DOMContentLoaded', function() {
    initCharts();
});

function initCharts() {
    initVentasChart();
    initVendedoresChart();
    initProductosChart();
    initComisionesChart();
    initComisionesMesChart();
    initTopComisionChart();
    initPorcentajeBonosChart();
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
                tension: 0.1,
                fill: true
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
</style>
