<?php
require_once "app/models/Vendedor.php";

class VendedoresController {
    
    public function index() {
        $page = $_GET['page'] ?? 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $pdo = Conexion::getConexion();
        
        // Contar total de registros
        $countStmt = $pdo->query("SELECT COUNT(*) FROM vendedores");
        $totalRecords = $countStmt->fetchColumn();
        $totalPages = ceil($totalRecords / $limit);
        
        // Obtener registros paginados
        $stmt = $pdo->prepare("
            SELECT v.id, v.nombre, 
                   COUNT(o.id) as total_operaciones,
                   COALESCE(SUM(CASE WHEN o.tipo_operacion = 'Venta' THEN o.valor_vendido ELSE 0 END), 0) as total_ventas,
                   COALESCE(SUM(CASE WHEN o.tipo_operacion = 'Devolución' THEN o.valor_vendido ELSE 0 END), 0) as total_devoluciones
            FROM vendedores v
            LEFT JOIN operaciones o ON v.id = o.vendedor_id
            GROUP BY v.id, v.nombre
            ORDER BY v.nombre
            LIMIT $limit OFFSET $offset
        ");
        $stmt->execute();
        $vendedores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        include "app/views/layout/header.php";
        include "app/views/vendedores/index.php";
        include "app/views/layout/footer.php";
    }
    
    public function create() {
        if ($_POST) {
            $nombre = trim($_POST['nombre']);
            
            if (empty($nombre)) {
                header("Location: index.php?controller=Vendedores&action=index&msg=error&error=empty");
                return;
            }
            
            $pdo = Conexion::getConexion();
            
            // Verificar si ya existe
            $stmt = $pdo->prepare("SELECT id FROM vendedores WHERE nombre = ?");
            $stmt->execute([$nombre]);
            
            if ($stmt->fetch()) {
                header("Location: index.php?controller=Vendedores&action=index&msg=error&error=exists");
                return;
            }
            
            // Crear vendedor
            $stmt = $pdo->prepare("INSERT INTO vendedores (nombre) VALUES (?)");
            $stmt->execute([$nombre]);
            
            header("Location: index.php?controller=Vendedores&action=index&msg=success");
        }
    }
    
    public function edit() {
        if ($_POST && isset($_POST['id'])) {
            $id = $_POST['id'];
            $nombre = trim($_POST['nombre']);
            
            if (empty($nombre)) {
                header("Location: index.php?controller=Vendedores&action=index&msg=error&error=empty");
                return;
            }
            
            $pdo = Conexion::getConexion();
            
            // Verificar si ya existe otro vendedor con ese nombre
            $stmt = $pdo->prepare("SELECT id FROM vendedores WHERE nombre = ? AND id != ?");
            $stmt->execute([$nombre, $id]);
            
            if ($stmt->fetch()) {
                header("Location: index.php?controller=Vendedores&action=index&msg=error&error=exists");
                return;
            }
            
            // Actualizar vendedor
            $stmt = $pdo->prepare("UPDATE vendedores SET nombre = ? WHERE id = ?");
            $stmt->execute([$nombre, $id]);
            
            header("Location: index.php?controller=Vendedores&action=index&msg=updated");
        }
    }
    
    public function delete() {
        if (isset($_GET['id'])) {
            $pdo = Conexion::getConexion();
            
            // Verificar si tiene operaciones asociadas
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM operaciones WHERE vendedor_id = ?");
            $stmt->execute([$_GET['id']]);
            $hasOperations = $stmt->fetchColumn() > 0;
            
            if ($hasOperations) {
                header("Location: index.php?controller=Vendedores&action=index&msg=error&error=has_operations");
                return;
            }
            
            $stmt = $pdo->prepare("DELETE FROM vendedores WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            
            header("Location: index.php?controller=Vendedores&action=index&msg=deleted");
        }
    }
    
    public function get() {
        if (isset($_GET['id'])) {
            $pdo = Conexion::getConexion();
            $stmt = $pdo->prepare("SELECT * FROM vendedores WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $vendedor = $stmt->fetch(PDO::FETCH_ASSOC);
            
            header('Content-Type: application/json');
            echo json_encode($vendedor);
        }
    }
}
