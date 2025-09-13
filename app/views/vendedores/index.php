<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-dark mb-0">
        <i class="bi bi-people me-2"></i>Gestionar Vendedores
    </h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#vendedorModal" onclick="openModal()">
        <i class="bi bi-plus-circle me-2"></i>Nuevo Vendedor
    </button>
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
        <?php if (empty($vendedores)): ?>
            <div class="text-center py-5">
                <i class="bi bi-people display-1 text-muted"></i>
                <p class="text-muted mt-3">No hay vendedores registrados</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
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
                        <?php foreach ($vendedores as $vendedor): ?>
                            <tr>
                                <td><?php echo $vendedor['id']; ?></td>
                                <td><?php echo htmlspecialchars($vendedor['nombre']); ?></td>
                                <td><?php echo number_format($vendedor['total_operaciones']); ?></td>
                                <td>$<?php echo number_format($vendedor['total_ventas'], 0, ',', '.'); ?></td>
                                <td>$<?php echo number_format($vendedor['total_devoluciones'], 0, ',', '.'); ?></td>
                                <td>
                                    <button type="button" class="btn btn-outline-primary btn-sm me-1" 
                                            onclick="editVendedor(<?php echo $vendedor['id']; ?>)">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <a href="index.php?controller=Vendedores&action=delete&id=<?php echo $vendedor['id']; ?>" 
                                       class="btn btn-outline-danger btn-sm"
                                       onclick="return confirm('¿Está seguro de eliminar este vendedor?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Paginación -->
    <?php if ($totalPages > 1): ?>
        <div class="card-footer bg-light">
            <nav aria-label="Paginación de vendedores">
                <ul class="pagination pagination-sm justify-content-center mb-0">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link text-dark" href="index.php?controller=Vendedores&action=index&page=<?php echo $page - 1; ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link <?php echo $i == $page ? 'bg-dark text-white' : 'text-dark'; ?>" 
                               href="index.php?controller=Vendedores&action=index&page=<?php echo $i; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link text-dark" href="index.php?controller=Vendedores&action=index&page=<?php echo $page + 1; ?>">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
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
</script>
