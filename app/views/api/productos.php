<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-dark mb-0">
        <i class="bi bi-list me-2"></i>Productos de API Externa
    </h2>
    <a href="index.php?controller=Api&action=index" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Volver
    </a>
</div>

<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0 text-dark">
            <i class="bi bi-funnel me-2"></i>Filtros de Búsqueda
        </h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <input type="hidden" name="controller" value="Api">
            <input type="hidden" name="action" value="productos">
            
            <div class="col-md-3">
                <label for="categoria" class="form-label text-dark">Categoría</label>
                <select class="form-select" id="categoria" name="categoria">
                    <option value="">Todas las categorías</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat['categoria']); ?>" 
                                <?php echo ($_GET['categoria'] ?? '') == $cat['categoria'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['categoria']); ?> (<?php echo $cat['cantidad']; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="precio_min" class="form-label text-dark">Precio Mín</label>
                <input type="number" class="form-control" id="precio_min" name="precio_min" 
                       value="<?php echo $_GET['precio_min'] ?? ''; ?>" placeholder="0">
            </div>
            
            <div class="col-md-2">
                <label for="precio_max" class="form-label text-dark">Precio Máx</label>
                <input type="number" class="form-control" id="precio_max" name="precio_max" 
                       value="<?php echo $_GET['precio_max'] ?? ''; ?>" placeholder="1000000">
            </div>
            
            <div class="col-md-2">
                <label for="disponible" class="form-label text-dark">Estado</label>
                <select class="form-select" id="disponible" name="disponible">
                    <option value="">Todos</option>
                    <option value="1" <?php echo ($_GET['disponible'] ?? '') == '1' ? 'selected' : ''; ?>>Disponibles</option>
                    <option value="0" <?php echo ($_GET['disponible'] ?? '') == '0' ? 'selected' : ''; ?>>No Disponibles</option>
                </select>
            </div>
            
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="bi bi-search me-2"></i>Filtrar
                </button>
                <a href="index.php?controller=Api&action=productos" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-2"></i>Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0 text-dark">
            <i class="bi bi-table me-2"></i>Productos Sincronizados
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-container">
            <table id="productosTable" class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ID API</th>
                        <th>Título</th>
                        <th>Categoría</th>
                        <th>Precio Base</th>
                        <th>Estado</th>
                        <th>Última Sincronización</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para detalle del producto -->
<div class="modal fade" id="detalleModal" tabindex="-1" aria-labelledby="detalleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-dark" id="detalleModalLabel">
                    <i class="bi bi-info-circle me-2"></i>Detalle del Producto
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
    // Obtener filtros de la URL
    var categoria = '<?php echo $_GET['categoria'] ?? ''; ?>';
    var precioMin = '<?php echo $_GET['precio_min'] ?? ''; ?>';
    var precioMax = '<?php echo $_GET['precio_max'] ?? ''; ?>';
    var disponible = '<?php echo $_GET['disponible'] ?? ''; ?>';
    
    $('#productosTable').DataTable({
        "processing": true,
        "serverSide": true,
        "scrollY": "400px",
        "scrollCollapse": true,
        "paging": true,
        "ajax": {
            "url": "index.php?controller=Api&action=datatable",
            "type": "GET",
            "data": function(d) {
                d.categoria = categoria;
                d.precio_min = precioMin;
                d.precio_max = precioMax;
                d.disponible = disponible;
            }
        },
        "columns": [
            { "data": 0, "title": "ID", "width": "5%" },
            { "data": 1, "title": "ID API", "width": "8%" },
            { "data": 2, "title": "Título", "width": "35%" },
            { "data": 3, "title": "Categoría", "width": "12%" },
            { "data": 4, "title": "Precio Base", "width": "12%" },
            { "data": 5, "title": "Estado", "width": "10%" },
            { "data": 6, "title": "Última Sincronización", "width": "13%" },
            { "data": 7, "title": "Acciones", "width": "5%", "orderable": false }
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
        "order": [[6, "desc"]]
    });
});

function verDetalle(productoId) {
    $('#detalleContent').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Cargando...</span></div></div>');
    
    var modal = new bootstrap.Modal(document.getElementById('detalleModal'));
    modal.show();
    
    // Simular carga de detalle (en una implementación real, harías una llamada AJAX)
    setTimeout(function() {
        $('#detalleContent').html(`
            <div class="row">
                <div class="col-md-6">
                    <h6>Información del Producto</h6>
                    <p><strong>ID:</strong> ${productoId}</p>
                    <p><strong>Estado:</strong> <span class="badge bg-success">Disponible</span></p>
                </div>
                <div class="col-md-6">
                    <h6>Detalles Técnicos</h6>
                    <p><strong>Última actualización:</strong> ${new Date().toLocaleString()}</p>
                    <p><strong>Fuente:</strong> API Externa</p>
                </div>
            </div>
        `);
    }, 1000);
}
</script>

<style>
#productosTable {
    table-layout: fixed;
    width: 100%;
}

#productosTable th {
    text-align: center;
    vertical-align: middle;
    white-space: nowrap;
    font-weight: 600;
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
}

#productosTable td {
    vertical-align: middle;
    padding: 8px 12px;
}

#productosTable .text-center {
    text-align: center !important;
}

#productosTable .text-end {
    text-align: right !important;
}

#productosTable tbody tr:hover {
    background-color: #f8f9fa;
}

.table-container {
    overflow-x: auto;
}

/* Responsive para la tabla */
@media (max-width: 992px) {
    #productosTable th,
    #productosTable td {
        font-size: 0.85rem;
        padding: 6px 8px;
    }
}

@media (max-width: 768px) {
    #productosTable th,
    #productosTable td {
        font-size: 0.8rem;
        padding: 4px 6px;
    }
}
</style>
