<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-dark mb-0">
        <i class="bi bi-people me-2"></i>Gestionar Vendedores
    </h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#vendedorModal" onclick="openModal()">
        <i class="bi bi-plus-circle me-2"></i>Nuevo Vendedor
    </button>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0 text-dark">
            <i class="bi bi-funnel me-2"></i>Filtros de Búsqueda
        </h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <input type="hidden" name="controller" value="Vendedores">
            <input type="hidden" name="action" value="index">
            
            <div class="col-md-4">
                <label for="buscar" class="form-label text-dark">Buscar Vendedor</label>
                <input type="text" class="form-control" id="buscar" name="buscar" 
                       placeholder="Nombre del vendedor..." value="<?php echo htmlspecialchars($_GET['buscar'] ?? ''); ?>">
            </div>
            
            <div class="col-md-4">
                <label for="orden" class="form-label text-dark">Ordenar por</label>
                <select class="form-select" id="orden" name="orden">
                    <option value="" <?php echo ($_GET['orden'] ?? '') == '' ? 'selected' : ''; ?>>ID (Más recientes)</option>
                    <option value="nombre" <?php echo ($_GET['orden'] ?? '') == 'nombre' ? 'selected' : ''; ?>>Nombre</option>
                    <option value="ventas" <?php echo ($_GET['orden'] ?? '') == 'ventas' ? 'selected' : ''; ?>>Total Ventas</option>
                    <option value="operaciones" <?php echo ($_GET['orden'] ?? '') == 'operaciones' ? 'selected' : ''; ?>>Total Operaciones</option>
                </select>
            </div>
            
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="bi bi-search me-2"></i>Filtrar
                </button>
                <a href="index.php?controller=Vendedores&action=index" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-2"></i>Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Mensajes -->
<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-<?php 
        echo $_GET['msg'] == 'success' ? 'success' : 
            ($_GET['msg'] == 'updated' ? 'info' : 
            ($_GET['msg'] == 'deleted' ? 'warning' : 'danger')); 
    ?> alert-dismissible fade show" role="alert">
        <?php 
        switch($_GET['msg']) {
            case 'success': echo 'Vendedor creado exitosamente'; break;
            case 'updated': echo 'Vendedor actualizado correctamente'; break;
            case 'deleted': echo 'Vendedor eliminado correctamente'; break;
            case 'error': 
                $error = $_GET['error'] ?? 'general';
                switch($error) {
                    case 'empty': echo 'El nombre del vendedor es requerido'; break;
                    case 'exists': echo 'Ya existe un vendedor con ese nombre'; break;
                    case 'has_operations': echo 'No se puede eliminar un vendedor con operaciones asociadas'; break;
                    default: echo 'Error al procesar la solicitud'; break;
                }
                break;
        }
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Tabla de vendedores -->
<div class="card">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0 text-dark">
            <i class="bi bi-table me-2"></i>Lista de Vendedores
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-container">
            <table id="vendedoresTable" class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Total Operaciones</th>
                        <th>Total Ventas</th>
                        <th>Total Devoluciones</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
    
</div>

<!-- Modal para crear/editar vendedor -->
<div class="modal fade" id="vendedorModal" tabindex="-1" aria-labelledby="vendedorModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-dark" id="vendedorModalLabel">
                    <i class="bi bi-person-plus me-2"></i>Nuevo Vendedor
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="vendedorForm" method="POST">
                <div class="modal-body">
                    <input type="hidden" id="vendedorId" name="id">
                    <div class="mb-3">
                        <label for="nombre" class="form-label text-dark">Nombre del Vendedor</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                        <div class="form-text text-muted">Ingrese el nombre completo del vendedor</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openModal() {
    document.getElementById('vendedorForm').reset();
    document.getElementById('vendedorId').value = '';
    document.getElementById('vendedorModalLabel').innerHTML = '<i class="bi bi-person-plus me-2"></i>Nuevo Vendedor';
    document.getElementById('vendedorForm').action = 'index.php?controller=Vendedores&action=create';
}

function editVendedor(id) {
    fetch('index.php?controller=Vendedores&action=get&id=' + id)
        .then(response => response.json())
        .then(data => {
            document.getElementById('vendedorId').value = data.id;
            document.getElementById('nombre').value = data.nombre;
            document.getElementById('vendedorModalLabel').innerHTML = '<i class="bi bi-pencil me-2"></i>Editar Vendedor';
            document.getElementById('vendedorForm').action = 'index.php?controller=Vendedores&action=edit';
            
            var modal = new bootstrap.Modal(document.getElementById('vendedorModal'));
            modal.show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los datos del vendedor');
        });
}

$(document).ready(function() {
    $('#vendedoresTable').DataTable({
        "processing": true,
        "serverSide": true,
        "scrollY": "400px",
        "scrollCollapse": true,
        "paging": true,
        "ajax": {
            "url": "index.php?controller=Vendedores&action=datatable",
            "type": "GET"
        },
        "columns": [
            { "data": 0 },
            { "data": 1 },
            { "data": 2 },
            { "data": 3 },
            { "data": 4 },
            { "data": 5, "orderable": false }
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
