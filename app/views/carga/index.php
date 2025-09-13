<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cargar CSV - VentasPlus</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="p-4">
<div class="container">
    <h2 class="mb-4">Cargar archivo CSV</h2>
    <form method="post" enctype="multipart/form-data" action="index.php?controller=Carga&action=upload">
        <div class="mb-3">
            <input type="file" name="csvfile" accept=".csv" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Subir y Cargar</button>
    </form>

    <hr>
    <h3>Últimos registros</h3>
    <table class="table table-striped">
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
                        <td>{$fila['fecha']}</td>
                        <td>{$fila['nombre']}</td>
                        <td>{$fila['producto']}</td>
                        <td>{$fila['cantidad']}</td>
                        <td>" . number_format($fila['valor_vendido'],0,",",".") . "</td>
                        <td>{$fila['tipo_operacion']}</td>
                      </tr>";
            }
            ?>
        </tbody>
    </table>
</div>
</body>
</html>
