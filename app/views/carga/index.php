<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-dark mb-0">
        <i class="bi bi-upload me-2"></i>Cargar Archivo CSV
    </h2>
</div>

<!-- Mensajes -->
<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-<?php echo $_GET['msg'] == 'ok' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
        <?php echo $_GET['msg'] == 'ok' ? 'Archivo CSV cargado exitosamente' : 'Error al cargar el archivo'; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Formulario de carga -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0 text-dark">
            <i class="bi bi-file-earmark-arrow-up me-2"></i>Cargar Archivo CSV
        </h5>
    </div>
    <div class="card-body">
        <form method="post" enctype="multipart/form-data" action="index.php?controller=Carga&action=upload">
            <div class="row">
                <div class="col-md-8">
                    <input type="file" name="csvfile" accept=".csv" class="form-control" required>
                    <div class="form-text text-muted">
                        Seleccione un archivo CSV con datos de ventas y/o devoluciones
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

<!-- Tabla de últimos registros -->
<div class="card">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0 text-dark">
            <i class="bi bi-table me-2"></i>Últimos Registros
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Vendedor</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Valor Vendido</th>
                        <th>Tipo Operación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    require_once "app/models/Conexion.php";
                    $pdo = Conexion::getConexion();
                    $stmt = $pdo->query("
                        SELECT o.fecha, v.nombre, o.producto, o.cantidad, o.valor_vendido, o.tipo_operacion
                        FROM operaciones o
                        JOIN vendedores v ON v.id = o.vendedor_id
                        ORDER BY o.id DESC
                        LIMIT 10
                    ");
                    while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>
                                <td>" . date('d/m/Y', strtotime($fila['fecha'])) . "</td>
                                <td>" . htmlspecialchars($fila['nombre']) . "</td>
                                <td>" . htmlspecialchars($fila['producto']) . "</td>
                                <td>" . number_format($fila['cantidad']) . "</td>
                                <td>$" . number_format($fila['valor_vendido'], 0, ',', '.') . "</td>
                                <td><span class='badge " . ($fila['tipo_operacion'] == 'Venta' ? 'bg-success' : 'bg-warning') . "'>" . $fila['tipo_operacion'] . "</span></td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
