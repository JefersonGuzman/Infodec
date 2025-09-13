<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-dark mb-0">
        <i class="bi bi-arrow-return-left me-2"></i>Gestionar Devoluciones
    </h2>
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

<!-- Formulario de carga -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0 text-dark">
            <i class="bi bi-file-earmark-arrow-up me-2"></i>Cargar Archivo CSV de Devoluciones
        </h5>
    </div>
    <div class="card-body">
        <form method="post" enctype="multipart/form-data" action="index.php?controller=Devoluciones&action=upload">
            <div class="row">
                <div class="col-md-8">
                    <input type="file" name="csvfile" accept=".csv" class="form-control" required>
                    <div class="form-text text-muted">
                        Seleccione un archivo CSV con formato: FechaVenta, Vendedor, Producto, Referencia, Cantidad, ValorUnitario, ValorVendido, Impuesto, TipoOperacion, Motivo
                    </div>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-upload me-2"></i>Subir y Cargar
                    </button>
                </div>
            </div>
        </form>
    </div>
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
        <?php if (empty($devoluciones)): ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox display-1 text-muted"></i>
                <p class="text-muted mt-3">No hay registros de devoluciones disponibles</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
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
                        <?php foreach ($devoluciones as $devolucion): ?>
                            <tr>
                                <td><?php echo $devolucion['id']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($devolucion['fecha'])); ?></td>
                                <td><?php echo htmlspecialchars($devolucion['vendedor']); ?></td>
                                <td><?php echo htmlspecialchars($devolucion['producto']); ?></td>
                                <td><?php echo htmlspecialchars($devolucion['referencia']); ?></td>
                                <td><?php echo number_format($devolucion['cantidad']); ?></td>
                                <td>$<?php echo number_format($devolucion['valor_unitario'], 0, ',', '.'); ?></td>
                                <td>$<?php echo number_format($devolucion['valor_vendido'], 0, ',', '.'); ?></td>
                                <td>$<?php echo number_format($devolucion['impuesto'], 0, ',', '.'); ?></td>
                                <td><?php echo htmlspecialchars($devolucion['motivo'] ?? 'N/A'); ?></td>
                                <td>
                                    <a href="index.php?controller=Devoluciones&action=delete&id=<?php echo $devolucion['id']; ?>" 
                                       class="btn btn-outline-danger btn-sm"
                                       onclick="return confirm('¿Está seguro de eliminar esta devolución?')">
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
            <nav aria-label="Paginación de devoluciones">
                <ul class="pagination pagination-sm justify-content-center mb-0">
                    <?php 
                    $filtros = http_build_query([
                        'fecha_desde' => $_GET['fecha_desde'] ?? '',
                        'fecha_hasta' => $_GET['fecha_hasta'] ?? '',
                        'vendedor' => $_GET['vendedor'] ?? '',
                        'producto' => $_GET['producto'] ?? ''
                    ]);
                    ?>
                    
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link text-dark" href="index.php?controller=Devoluciones&action=index&page=<?php echo $page - 1; ?>&<?php echo $filtros; ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link <?php echo $i == $page ? 'bg-dark text-white' : 'text-dark'; ?>" 
                               href="index.php?controller=Devoluciones&action=index&page=<?php echo $i; ?>&<?php echo $filtros; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link text-dark" href="index.php?controller=Devoluciones&action=index&page=<?php echo $page + 1; ?>&<?php echo $filtros; ?>">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>
