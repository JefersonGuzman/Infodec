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
        <?php if (empty($comisiones)): ?>
            <div class="text-center py-5">
                <i class="bi bi-calculator display-1 text-muted"></i>
                <p class="text-muted mt-3">No hay comisiones calculadas para el período seleccionado</p>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#calcularModal">
                    <i class="bi bi-calculator me-2"></i>Calcular Comisiones
                </button>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
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
                        <?php foreach ($comisiones as $comision): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($comision['vendedor_nombre']); ?></strong>
                                    <br>
                                    <small class="text-muted"><?php echo $comision['anio']; ?>/<?php echo str_pad($comision['mes'], 2, '0', STR_PAD_LEFT); ?></small>
                                </td>
                                <td>$<?php echo number_format($comision['total_ventas'], 0, ',', '.'); ?></td>
                                <td>$<?php echo number_format($comision['total_devoluciones'], 0, ',', '.'); ?></td>
                                <td>
                                    <span class="badge <?php echo $comision['indice_devoluciones'] > 5 ? 'bg-danger' : 'bg-success'; ?>">
                                        <?php echo number_format($comision['indice_devoluciones'], 2, ',', '.'); ?>%
                                    </span>
                                </td>
                                <td>$<?php echo number_format($comision['comision_base'], 0, ',', '.'); ?></td>
                                <td>
                                    <?php if ($comision['bono'] > 0): ?>
                                        <span class="text-success">
                                            <i class="bi bi-plus-circle"></i> $<?php echo number_format($comision['bono'], 0, ',', '.'); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($comision['penalizacion'] > 0): ?>
                                        <span class="text-danger">
                                            <i class="bi bi-dash-circle"></i> $<?php echo number_format($comision['penalizacion'], 0, ',', '.'); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong class="text-primary">$<?php echo number_format($comision['comision_final'], 0, ',', '.'); ?></strong>
                                </td>
                                <td>
                                    <a href="index.php?controller=Comisiones&action=vendedor&id=<?php echo $comision['vendedor_id']; ?>" 
                                       class="btn btn-outline-info btn-sm me-1" title="Ver detalle">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="index.php?controller=Comisiones&action=recalcular&id=<?php echo $comision['id']; ?>" 
                                       class="btn btn-outline-warning btn-sm" title="Recalcular"
                                       onclick="return confirm('¿Recalcular esta comisión?')">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if ($totalPages > 1): ?>
        <div class="card-footer bg-light">
            <nav aria-label="Paginación de comisiones">
                <ul class="pagination pagination-sm justify-content-center mb-0">
                    <?php 
                    $filtros = http_build_query([
                        'anio' => $_GET['anio'] ?? '',
                        'mes' => $_GET['mes'] ?? '',
                        'vendedor' => $_GET['vendedor'] ?? ''
                    ]);
                    ?>
                    
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link text-dark" href="index.php?controller=Comisiones&action=index&page=<?php echo $page - 1; ?>&<?php echo $filtros; ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link <?php echo $i == $page ? 'bg-dark text-white' : 'text-dark'; ?>" 
                               href="index.php?controller=Comisiones&action=index&page=<?php echo $i; ?>&<?php echo $filtros; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link text-dark" href="index.php?controller=Comisiones&action=index&page=<?php echo $page + 1; ?>&<?php echo $filtros; ?>">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
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
