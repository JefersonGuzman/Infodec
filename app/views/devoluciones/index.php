<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-dark mb-0">
        <i class="bi bi-arrow-return-left me-2"></i>Gestionar Devoluciones
    </h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#cargarModal">
        <i class="bi bi-upload me-2"></i>Cargar Datos
    </button>
</div>

<!-- Mensajes -->
<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-<?php echo $_GET['msg'] == 'success' ? 'success' : ($_GET['msg'] == 'deleted' ? 'info' : 'danger'); ?> alert-dismissible fade show" role="alert">
        <?php 
        switch($_GET['msg']) {
            case 'success': echo 'Archivo CSV cargado exitosamente'; break;
            case 'deleted': echo 'Devolución eliminada correctamente'; break;
            case 'error': echo 'Error al cargar el archivo'; break;
        }
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>


<!-- Filtros -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0 text-dark">
            <i class="bi bi-funnel me-2"></i>Filtros de Búsqueda
        </h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <input type="hidden" name="controller" value="Devoluciones">
            <input type="hidden" name="action" value="index">
            
            <div class="col-md-3">
                <label for="fecha_desde" class="form-label text-dark">Fecha Desde</label>
                <input type="date" class="form-control" id="fecha_desde" name="fecha_desde" value="<?php echo $_GET['fecha_desde'] ?? ''; ?>">
            </div>
            
            <div class="col-md-3">
                <label for="fecha_hasta" class="form-label text-dark">Fecha Hasta</label>
                <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta" value="<?php echo $_GET['fecha_hasta'] ?? ''; ?>">
            </div>
            
            <div class="col-md-3">
                <label for="vendedor" class="form-label text-dark">Vendedor</label>
                <select class="form-select" id="vendedor" name="vendedor">
                    <option value="">Todos los vendedores</option>
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
            
            <div class="col-md-3">
                <label for="producto" class="form-label text-dark">Producto</label>
                <input type="text" class="form-control" id="producto" name="producto" 
                       placeholder="Buscar producto..." value="<?php echo htmlspecialchars($_GET['producto'] ?? ''); ?>">
            </div>
            
            <div class="col-12">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="bi bi-search me-2"></i>Filtrar
                </button>
                <a href="index.php?controller=Devoluciones&action=index" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-2"></i>Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de devoluciones -->
<div class="card">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0 text-dark">
            <i class="bi bi-table me-2"></i>Registros de Devoluciones
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-container">
            <table id="devolucionesTable" class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Vendedor</th>
                        <th>Producto</th>
                        <th>Referencia</th>
                        <th>Cantidad</th>
                        <th>Valor Unitario</th>
                        <th>Valor Vendido</th>
                        <th>Impuesto</th>
                        <th>Motivo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
    
</div>

<!-- Modal para cargar datos -->
<div class="modal fade" id="cargarModal" tabindex="-1" aria-labelledby="cargarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-dark" id="cargarModalLabel">
                    <i class="bi bi-file-earmark-arrow-up me-2"></i>Cargar Archivo CSV de Devoluciones
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" enctype="multipart/form-data" action="index.php?controller=Devoluciones&action=upload">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="csvfile" class="form-label text-dark">Seleccionar Archivo CSV</label>
                        <input type="file" name="csvfile" accept=".csv" class="form-control" id="csvfile" required>
                        <div class="form-text text-muted">
                            Formato requerido: FechaVenta, Vendedor, Producto, Referencia, Cantidad, ValorUnitario, ValorVendido, Impuesto, TipoOperacion, Motivo
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload me-2"></i>Subir y Cargar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#devolucionesTable').DataTable({
        "processing": true,
        "serverSide": true,
        "scrollY": "400px",
        "scrollCollapse": true,
        "paging": true,
        "ajax": {
            "url": "index.php?controller=Devoluciones&action=datatable",
            "type": "GET"
        },
        "columns": [
            { "data": 0 },
            { "data": 1 },
            { "data": 2 },
            { "data": 3 },
            { "data": 4 },
            { "data": 5 },
            { "data": 6 },
            { "data": 7 },
            { "data": 8 },
            { "data": 9 },
            { "data": 10, "orderable": false }
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
        "order": [[0, "desc"]]
    });
});
</script>
