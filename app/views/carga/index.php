<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-dark mb-0">
        <i class="bi bi-upload me-2"></i>Cargar Archivo CSV
    </h2>
</div>

<?php if (isset($_GET['msg'])): ?>
    <?php
    $alertClass = 'danger';
    $message = 'Error al cargar el archivo';
    
    switch($_GET['msg']) {
        case 'ok':
            $alertClass = 'success';
            $message = 'Archivo CSV cargado exitosamente';
            if (isset($_GET['procesados'])) {
                $message .= ' - Procesados: ' . $_GET['procesados'] . ', Nuevos: ' . $_GET['nuevos'];
            }
            break;
        case 'commissions_generated':
            $alertClass = 'success';
            $message = 'Comisiones generadas exitosamente para ' . $_GET['anio'] . '/' . $_GET['mes'] . ' - ' . $_GET['count'] . ' registros';
            break;
        case 'no_data':
            $alertClass = 'warning';
            $message = 'No hay datos para generar comisiones en ' . $_GET['anio'] . '/' . $_GET['mes'];
            break;
        case 'error':
            $message = 'Error: ' . ($_GET['error'] ?? 'Error desconocido');
            break;
    }
    ?>
    <div class="alert alert-<?php echo $alertClass; ?> alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

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

<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0 text-dark">
            <i class="bi bi-calculator me-2"></i>Generar Comisiones
        </h5>
    </div>
    <div class="card-body">
        <form method="post" action="index.php?controller=Carga&action=generarComisiones">
            <div class="row">
                <div class="col-md-4">
                    <label for="anio" class="form-label">Año</label>
                    <select class="form-select" id="anio" name="anio" required>
                        <?php
                        $currentYear = date('Y');
                        for ($i = $currentYear - 2; $i <= $currentYear + 1; $i++) {
                            $selected = ($i == $currentYear) ? 'selected' : '';
                            echo "<option value='$i' $selected>$i</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="mes" class="form-label">Mes</label>
                    <select class="form-select" id="mes" name="mes" required>
                        <?php
                        $meses = [
                            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                        ];
                        $currentMonth = date('n');
                        foreach ($meses as $num => $nombre) {
                            $selected = ($num == $currentMonth) ? 'selected' : '';
                            echo "<option value='$num' $selected>$nombre</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-calculator me-2"></i>Generar Comisiones
                    </button>
                </div>
            </div>
            <div class="form-text text-muted">
                Genera las comisiones para un mes específico basándose en los datos de ventas y devoluciones.
            </div>
        </form>
    </div>
</div>

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
