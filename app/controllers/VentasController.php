<?php
require_once "app/models/Operacion.php";
require_once "app/models/Vendedor.php";

class VentasController {
    private $operacion;
    
    public function __construct() {
        $this->operacion = new Operacion();
    }
    
    public function index() {
        $page = $_GET['page'] ?? 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $fechaDesde = $_GET['fecha_desde'] ?? '';
        $fechaHasta = $_GET['fecha_hasta'] ?? '';
        $vendedorId = $_GET['vendedor'] ?? '';
        $producto = $_GET['producto'] ?? '';
        
        $pdo = Conexion::getConexion();
        
        $where = ["o.tipo_operacion = 'Venta'"];
        $params = [];
        
        if ($fechaDesde) {
            $where[] = "o.fecha >= ?";
            $params[] = $fechaDesde;
        }
        
        if ($fechaHasta) {
            $where[] = "o.fecha <= ?";
            $params[] = $fechaHasta;
        }
        
        if ($vendedorId) {
            $where[] = "o.vendedor_id = ?";
            $params[] = $vendedorId;
        }
        
        if ($producto) {
            $where[] = "o.producto LIKE ?";
            $params[] = "%$producto%";
        }
        
        $whereClause = implode(" AND ", $where);
        
        $countSql = "SELECT COUNT(*) FROM operaciones o JOIN vendedores v ON v.id = o.vendedor_id WHERE $whereClause";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $totalRecords = $countStmt->fetchColumn();
        $totalPages = ceil($totalRecords / $limit);
        
        $sql = "
            SELECT o.id, o.fecha, v.nombre as vendedor, o.producto, o.referencia, 
                   o.cantidad, o.valor_unitario, o.valor_vendido, o.impuesto
            FROM operaciones o
            JOIN vendedores v ON v.id = o.vendedor_id
            WHERE $whereClause
            ORDER BY o.id DESC
            LIMIT $limit OFFSET $offset
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        include "app/views/layout/header.php";
        include "app/views/ventas/index.php";
        include "app/views/layout/footer.php";
    }
    
    public function upload() {
        if (isset($_FILES['csvfile'])) {
            $file = $_FILES['csvfile']['tmp_name'];
            $this->operacion->cargarCSV($file);
            header("Location: index.php?controller=Ventas&action=index&msg=success");
        } else {
            header("Location: index.php?controller=Ventas&action=index&msg=error");
        }
    }
    
    public function delete() {
        if (isset($_GET['id'])) {
            $pdo = Conexion::getConexion();
            $stmt = $pdo->prepare("DELETE FROM operaciones WHERE id = ? AND tipo_operacion = 'Venta'");
            $stmt->execute([$_GET['id']]);
            header("Location: index.php?controller=Ventas&action=index&msg=deleted");
        }
    }
    
    public function datatable() {
        $pdo = Conexion::getConexion();
        
        $draw = intval($_GET['draw']);
        $start = intval($_GET['start']);
        $length = intval($_GET['length']);
        $searchValue = $_GET['search']['value'] ?? '';
        $orderColumn = intval($_GET['order'][0]['column'] ?? 0);
        $orderDir = $_GET['order'][0]['dir'] ?? 'desc';
        
        $columns = ['o.id', 'o.fecha', 'v.nombre', 'o.producto', 'o.referencia', 'o.cantidad', 'o.valor_unitario', 'o.valor_vendido', 'o.impuesto', 'o.id'];
        $orderBy = $columns[$orderColumn] . ' ' . strtoupper($orderDir);
        
        $where = ["o.tipo_operacion = 'Venta'"];
        $params = [];
        
        if (!empty($searchValue)) {
            $where[] = "(v.nombre LIKE ? OR o.producto LIKE ? OR o.referencia LIKE ?)";
            $searchParam = "%$searchValue%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        $whereClause = implode(" AND ", $where);
        
        $countSql = "SELECT COUNT(*) FROM operaciones o JOIN vendedores v ON v.id = o.vendedor_id WHERE $whereClause";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $totalRecords = $countStmt->fetchColumn();
        
        $sql = "
            SELECT o.id, o.fecha, v.nombre as vendedor, o.producto, o.referencia, 
                   o.cantidad, o.valor_unitario, o.valor_vendido, o.impuesto
            FROM operaciones o
            JOIN vendedores v ON v.id = o.vendedor_id
            WHERE $whereClause
            ORDER BY $orderBy
            LIMIT $length OFFSET $start
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $data = [];
        foreach ($ventas as $venta) {
            $data[] = [
                $venta['id'],
                date('d/m/Y', strtotime($venta['fecha'])),
                htmlspecialchars($venta['vendedor']),
                htmlspecialchars($venta['producto']),
                htmlspecialchars($venta['referencia']),
                number_format($venta['cantidad']),
                '$' . number_format($venta['valor_unitario'], 0, ',', '.'),
                '$' . number_format($venta['valor_vendido'], 0, ',', '.'),
                '$' . number_format($venta['impuesto'], 0, ',', '.'),
                '<a href="index.php?controller=Ventas&action=delete&id=' . $venta['id'] . '" class="btn btn-outline-danger btn-sm" onclick="return confirm(\'¿Está seguro de eliminar esta venta?\')"><i class="bi bi-trash"></i></a>'
            ];
        }
        
        $response = [
            "draw" => $draw,
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecords,
            "data" => $data
        ];
        
        header('Content-Type: application/json');
        echo json_encode($response);
    }
}
