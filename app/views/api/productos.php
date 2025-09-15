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
            <i class="bi bi-funnel me-2"></i>Filtros
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
                <label for="limit" class="form-label text-dark">Límite</label>
                <select class="form-select" id="limit" name="limit">
                    <option value="25" <?php echo ($_GET['limit'] ?? '50') == '25' ? 'selected' : ''; ?>>25</option>
                    <option value="50" <?php echo ($_GET['limit'] ?? '50') == '50' ? 'selected' : ''; ?>>50</option>
                    <option value="100" <?php echo ($_GET['limit'] ?? '50') == '100' ? 'selected' : ''; ?>>100</option>
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

<div class="card">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0 text-dark">
            <i class="bi bi-table me-2"></i>Productos Sincronizados
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
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
                    <?php if (!empty($productos)): ?>
                        <?php foreach ($productos as $producto): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-primary"><?php echo $producto['id_api']; ?></span>
                                </td>
                                <td>
                                    <div>
                                        <strong><?php echo htmlspecialchars($producto['titulo']); ?></strong>
                                        <?php if ($producto['descripcion']): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars(substr($producto['descripcion'], 0, 100)) . '...'; ?></small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($producto['categoria']); ?></span>
                                </td>
                                <td>
                                    <strong class="text-success">$<?php echo number_format($producto['precio_base'], 0, ',', '.'); ?></strong>
                                </td>
                                <td>
                                    <?php if ($producto['disponible']): ?>
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle me-1"></i>Disponible
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">
                                            <i class="bi bi-x-circle me-1"></i>No Disponible
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?php echo date('d/m/Y H:i', strtotime($producto['fecha_sincronizacion'])); ?>
                                    </small>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-outline-info btn-sm" 
                                            onclick="verDetalle(<?php echo $producto['id']; ?>)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-inbox me-2"></i>No hay productos disponibles
                            </td>
                        </tr>
                    <?php endif; ?>
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
