<?php
require_once "app/models/Operacion.php";
require_once "app/models/Vendedor.php";

class DevolucionesController {
    private $operacion;
    
    public function __construct() {
        $this->operacion = new Operacion();
    }
    
    public function index() {
        $page = $_GET['page'] ?? 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $pdo = Conexion::getConexion();
        
        // Contar total de registros
        $countStmt = $pdo->query("SELECT COUNT(*) FROM operaciones WHERE tipo_operacion = 'Devolución'");
        $totalRecords = $countStmt->fetchColumn();
        $totalPages = ceil($totalRecords / $limit);
        
        // Obtener registros paginados
        $stmt = $pdo->prepare("
            SELECT o.id, o.fecha, v.nombre as vendedor, o.producto, o.referencia, 
                   o.cantidad, o.valor_unitario, o.valor_vendido, o.impuesto, o.motivo
            FROM operaciones o
            JOIN vendedores v ON v.id = o.vendedor_id
            WHERE o.tipo_operacion = 'Devolución'
            ORDER BY o.fecha DESC, o.id DESC
            LIMIT $limit OFFSET $offset
        ");
        $stmt->execute();
        $devoluciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        include "app/views/layout/header.php";
        include "app/views/devoluciones/index.php";
        include "app/views/layout/footer.php";
    }
    
    public function upload() {
        if (isset($_FILES['csvfile'])) {
            $file = $_FILES['csvfile']['tmp_name'];
            $this->operacion->cargarCSV($file);
            header("Location: index.php?controller=Devoluciones&action=index&msg=success");
        } else {
            header("Location: index.php?controller=Devoluciones&action=index&msg=error");
        }
    }
    
    public function delete() {
        if (isset($_GET['id'])) {
            $pdo = Conexion::getConexion();
            $stmt = $pdo->prepare("DELETE FROM operaciones WHERE id = ? AND tipo_operacion = 'Devolución'");
            $stmt->execute([$_GET['id']]);
            header("Location: index.php?controller=Devoluciones&action=index&msg=deleted");
        }
    }
}
