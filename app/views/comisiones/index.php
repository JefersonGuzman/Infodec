<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-dark mb-0">
        <i class="bi bi-calculator me-2"></i>Gestión de Comisiones
    </h2>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#calcularModal">
            <i class="bi bi-calculator me-2"></i>Calcular Comisiones
        </button>
        <a href="index.php?controller=Comisiones&action=exportar&anio=<?php echo $anio; ?>&mes=<?php echo $mes; ?>&formato=csv" 
           class="btn btn-outline-success">
            <i class="bi bi-download me-2"></i>Exportar CSV
        </a>
    </div>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-<?php 
        echo $_GET['msg'] == 'success' ? 'success' : 
            ($_GET['msg'] == 'recalculated' ? 'info' : 
            ($_GET['msg'] == 'no_data' ? 'warning' : 'danger')); 
    ?> alert-dismissible fade show" role="alert">
        <?php 
        switch($_GET['msg']) {
            case 'success': echo 'Comisiones calculadas exitosamente'; break;
            case 'recalculated': echo 'Comisión recalculada correctamente'; break;
            case 'no_data': echo 'No hay datos para calcular comisiones en el período seleccionado'; break;
            case 'error': echo 'Error al procesar las comisiones'; break;
            case 'not_found': echo 'Comisión no encontrada'; break;
        }
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0 text-dark">
            <i class="bi bi-funnel me-2"></i>Filtros
        </h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <input type="hidden" name="controller" value="Comisiones">
            <input type="hidden" name="action" value="index">
            
            <div class="col-md-3">
                <label for="anio" class="form-label text-dark">Año</label>
                <select class="form-select" id="anio" name="anio">
                    <?php for ($i = date('Y') - 2; $i <= date('Y') + 1; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo $i == $anio ? 'selected' : ''; ?>>
                            <?php echo $i; ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="mes" class="form-label text-dark">Mes</label>
                <select class="form-select" id="mes" name="mes">
                    <?php 
                    $meses = [
                        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                    ];
                    foreach ($meses as $num => $nombre): 
                    ?>
                        <option value="<?php echo $num; ?>" <?php echo $num == $mes ? 'selected' : ''; ?>>
                            <?php echo $nombre; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="vendedor" class="form-label text-dark">Vendedor</label>
                <select class="form-select" id="vendedor" name="vendedor">
                    <option value="">Todos</option>
                    <?php
                    $pdo = Conexion::getConexion();
                    $stmt = $pdo->query("SELECT DISTINCT v.id, v.nombre FROM vendedores v ORDER BY v.nombre");
                    while ($v = $stmt->fetch(PDO::FETCH_ASSOC)): 
                    ?>
                        <option value="<?php echo $v['id']; ?>" <?php echo ($_GET['vendedor'] ?? '') == $v['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($v['nombre']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search me-2"></i>Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<?php if ($estadisticas): ?>
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-dark">Total Vendedores</h5>
                <h3 class="text-primary"><?php echo $estadisticas['total_vendedores']; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-dark">Total Comisiones</h5>
                <h3 class="text-success">$<?php echo number_format($estadisticas['total_comisiones'], 0, ',', '.'); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-dark">Con Bono</h5>
                <h3 class="text-warning"><?php echo $estadisticas['vendedores_con_bono']; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-dark">Penalizados</h5>
                <h3 class="text-danger"><?php echo $estadisticas['vendedores_penalizados']; ?></h3>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0 text-dark">
            <i class="bi bi-table me-2"></i>Comisiones Calculadas
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-container">
            <table id="comisionesTable" class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Vendedor</th>
                        <th>Total Ventas</th>
                        <th>Total Devoluciones</th>
                        <th>Índice Dev.</th>
                        <th>Comisión Base</th>
                        <th>Bono</th>
                        <th>Penalización</th>
                        <th>Comisión Final</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
    
</div>

<div class="modal fade" id="calcularModal" tabindex="-1" aria-labelledby="calcularModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-dark" id="calcularModalLabel">
                    <i class="bi bi-calculator me-2"></i>Calcular Comisiones
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="index.php?controller=Comisiones&action=calcular">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="modal_anio" class="form-label text-dark">Año</label>
                        <select class="form-select" id="modal_anio" name="anio" required>
                            <?php for ($i = date('Y') - 2; $i <= date('Y') + 1; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo $i == date('Y') ? 'selected' : ''; ?>>
                                    <?php echo $i; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="modal_mes" class="form-label text-dark">Mes</label>
                        <select class="form-select" id="modal_mes" name="mes" required>
                            <?php foreach ($meses as $num => $nombre): ?>
                                <option value="<?php echo $num; ?>" <?php echo $num == date('n') ? 'selected' : ''; ?>>
                                    <?php echo $nombre; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Reglas de cálculo:</strong><br>
                        • Comisión base: 5% del total de ventas<br>
                        • Bono: +2% si supera $50,000,000 COP<br>
                        • Penalización: -1% si devoluciones > 5%
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-calculator me-2"></i>Calcular
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="detalleModal" tabindex="-1" aria-labelledby="detalleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-dark" id="detalleModalLabel">
                    <i class="bi bi-eye me-2"></i>Detalle de Comisiones
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="detalleContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i>Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    var anio = '<?php echo $anio; ?>';
    var mes = '<?php echo $mes; ?>';
    var vendedor = '<?php echo $_GET['vendedor'] ?? ''; ?>';
    
    $('#comisionesTable').DataTable({
        "processing": true,
        "serverSide": true,
        "scrollY": "400px",
        "scrollCollapse": true,
        "paging": true,
        "autoWidth": false,
        "responsive": true,
        "ajax": {
            "url": "index.php?controller=Comisiones&action=datatable",
            "type": "GET",
            "data": function(d) {
                d.anio = anio;
                d.mes = mes;
                d.vendedor = vendedor;
            }
        },
        "columns": [
            { "data": 0, "title": "Vendedor", "width": "20%" },
            { "data": 1, "title": "Total Ventas", "width": "12%" },
            { "data": 2, "title": "Total Devoluciones", "width": "12%" },
            { "data": 3, "title": "Índice Dev.", "width": "8%" },
            { "data": 4, "title": "Comisión Base", "width": "10%" },
            { "data": 5, "title": "Bono", "width": "8%" },
            { "data": 6, "title": "Penalización", "width": "10%" },
            { "data": 7, "title": "Comisión Final", "width": "12%" },
            { "data": 8, "title": "Acciones", "width": "8%", "orderable": false }
        ],
        "pageLength": 25,
        "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        "language": {
            "lengthMenu": "Mostrar _MENU_ registros por página",
            "zeroRecords": "No se encontraron resultados",
            "info": "Mostrando página _PAGE_ de _PAGES_",
            "infoEmpty": "No hay registros disponibles",
            "infoFiltered": "(filtrado de _MAX_ registros totales)",
            "search": "Buscar:",
            "processing": "Procesando...",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            }
        },
        "order": [[0, "asc"]]
    });
});

function verDetalleComision(vendedorId, anio, mes) {
    $('#detalleContent').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Cargando...</span></div></div>');
    
    var modal = new bootstrap.Modal(document.getElementById('detalleModal'));
    modal.show();
    
    fetch('index.php?controller=Comisiones&action=detalle&id=' + vendedorId + '&anio=' + anio + '&mes=' + mes)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                $('#detalleContent').html('<div class="alert alert-danger">' + data.error + '</div>');
                return;
            }
            
            var html = '<div class="row mb-4">';
            html += '<div class="col-md-12">';
            html += '<h4 class="text-dark">' + data.vendedor.nombre + '</h4>';
            html += '<p class="text-muted">Período: ' + data.comision.anio + '/' + String(data.comision.mes).padStart(2, '0') + '</p>';
            html += '</div></div>';
            
            html += '<div class="row mb-4">';
            html += '<div class="col-md-3"><div class="card text-center"><div class="card-body"><h6 class="card-title">Total Ventas</h6><h5 class="text-primary">$' + new Intl.NumberFormat('es-CO').format(parseFloat(data.comision.total_ventas)) + '</h5></div></div></div>';
            html += '<div class="col-md-3"><div class="card text-center"><div class="card-body"><h6 class="card-title">Total Devoluciones</h6><h5 class="text-warning">$' + new Intl.NumberFormat('es-CO').format(parseFloat(data.comision.total_devoluciones)) + '</h5></div></div></div>';
            html += '<div class="col-md-3"><div class="card text-center"><div class="card-body"><h6 class="card-title">Índice Devoluciones</h6><h5 class="' + (parseFloat(data.comision.indice_devoluciones) > 5 ? 'text-danger' : 'text-success') + '">' + parseFloat(data.comision.indice_devoluciones).toFixed(2) + '%</h5></div></div></div>';
            html += '<div class="col-md-3"><div class="card text-center"><div class="card-body"><h6 class="card-title">Comisión Final</h6><h5 class="text-success">$' + new Intl.NumberFormat('es-CO').format(parseFloat(data.comision.comision_final)) + '</h5></div></div></div>';
            html += '</div>';
            
            html += '<div class="row">';
            html += '<div class="col-md-6">';
            html += '<h5 class="text-dark mb-3"><i class="bi bi-upload me-2"></i>Ventas del Período</h5>';
            html += '<div class="table-responsive" style="max-height: 300px; overflow-y: auto;">';
            html += '<table class="table table-sm table-hover">';
            html += '<thead><tr><th>Fecha</th><th>Producto</th><th>Cantidad</th><th>Valor</th></tr></thead>';
            html += '<tbody>';
            data.ventas.forEach(function(venta) {
                html += '<tr>';
                html += '<td>' + new Date(venta.fecha).toLocaleDateString('es-CO') + '</td>';
                html += '<td>' + venta.producto + '</td>';
                html += '<td>' + new Intl.NumberFormat('es-CO').format(parseFloat(venta.cantidad)) + '</td>';
                html += '<td>$' + new Intl.NumberFormat('es-CO').format(parseFloat(venta.valor_vendido)) + '</td>';
                html += '</tr>';
            });
            html += '</tbody></table></div></div>';
            
            html += '<div class="col-md-6">';
            html += '<h5 class="text-dark mb-3"><i class="bi bi-arrow-return-left me-2"></i>Devoluciones del Período</h5>';
            html += '<div class="table-responsive" style="max-height: 300px; overflow-y: auto;">';
            html += '<table class="table table-sm table-hover">';
            html += '<thead><tr><th>Fecha</th><th>Producto</th><th>Cantidad</th><th>Valor</th><th>Motivo</th></tr></thead>';
            html += '<tbody>';
            data.devoluciones.forEach(function(devolucion) {
                html += '<tr>';
                html += '<td>' + new Date(devolucion.fecha).toLocaleDateString('es-CO') + '</td>';
                html += '<td>' + devolucion.producto + '</td>';
                html += '<td>' + new Intl.NumberFormat('es-CO').format(parseFloat(devolucion.cantidad)) + '</td>';
                html += '<td>$' + new Intl.NumberFormat('es-CO').format(parseFloat(devolucion.valor_vendido)) + '</td>';
                html += '<td>' + (devolucion.motivo || 'N/A') + '</td>';
                html += '</tr>';
            });
            html += '</tbody></table></div></div>';
            html += '</div>';
            
            $('#detalleContent').html(html);
        })
        .catch(error => {
            console.error('Error:', error);
            $('#detalleContent').html('<div class="alert alert-danger">Error al cargar los detalles</div>');
        });
}
</script>

<style>
#comisionesTable {
    table-layout: fixed;
    width: 100%;
}

#comisionesTable th {
    text-align: center;
    vertical-align: middle;
    white-space: nowrap;
    font-weight: 600;
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
}

#comisionesTable td {
    vertical-align: middle;
    padding: 8px 12px;
}

#comisionesTable .text-center {
    text-align: center !important;
}

#comisionesTable .text-end {
    text-align: right !important;
}

#comisionesTable tbody tr:hover {
    background-color: #f8f9fa;
}

.table-container {
    overflow-x: auto;
}

/* Estilos mejorados para la columna de bono */
.bono-badge {
    font-size: 0.7rem;
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
}

.bono-amount {
    font-size: 0.85rem;
    font-weight: 600;
    margin-top: 0.25rem;
}

.bono-container {
    min-width: 80px;
    max-width: 120px;
}

/* Responsive para la columna de bono */
@media (max-width: 992px) {
    .bono-container {
        min-width: 70px;
        max-width: 100px;
    }
    
    .bono-badge {
        font-size: 0.65rem;
        padding: 0.2rem 0.4rem;
    }
    
    .bono-amount {
        font-size: 0.8rem;
    }
}
</style>