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
        
        // Obtener filtros
        $fechaDesde = $_GET['fecha_desde'] ?? '';
        $fechaHasta = $_GET['fecha_hasta'] ?? '';
        $vendedorId = $_GET['vendedor'] ?? '';
        $producto = $_GET['producto'] ?? '';
        
        $pdo = Conexion::getConexion();
        
        // Construir consulta con filtros
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
        
        // Contar total de registros
        $countSql = "SELECT COUNT(*) FROM operaciones o JOIN vendedores v ON v.id = o.vendedor_id WHERE $whereClause";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $totalRecords = $countStmt->fetchColumn();
        $totalPages = ceil($totalRecords / $limit);
        
        // Obtener registros paginados
        $sql = "
            SELECT o.id, o.fecha, v.nombre as vendedor, o.producto, o.referencia, 
                   o.cantidad, o.valor_unitario, o.valor_vendido, o.impuesto
            FROM operaciones o
            JOIN vendedores v ON v.id = o.vendedor_id
            WHERE $whereClause
            ORDER BY o.fecha DESC, o.id DESC
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
}
