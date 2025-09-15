<?php
require_once "app/models/Conexion.php";

class DashboardController {
    private $pdo;
    
    public function __construct() {
        $this->pdo = Conexion::getConexion();
    }
    
    public function index() {
        $fechaDesde = $_GET['fecha_desde'] ?? '';
        $fechaHasta = $_GET['fecha_hasta'] ?? '';
        $vendedorId = $_GET['vendedor'] ?? '';
        
        // Generar comisiones automáticamente si no existen
        $this->verificarYGenerarComisiones();
        
        $stats = $this->obtenerEstadisticas($fechaDesde, $fechaHasta, $vendedorId);
        $ventasPorMes = $this->obtenerVentasPorMes($fechaDesde, $fechaHasta, $vendedorId);
        $topVendedores = $this->obtenerTopVendedores($fechaDesde, $fechaHasta, $vendedorId);
        $ventasPorProducto = $this->obtenerVentasPorProducto($fechaDesde, $fechaHasta, $vendedorId);
        $devolucionesPorMes = $this->obtenerDevolucionesPorMes($fechaDesde, $fechaHasta, $vendedorId);
        $comisionesPorMes = $this->obtenerComisionesPorMes($fechaDesde, $fechaHasta, $vendedorId);
        $topVendedoresComision = $this->obtenerTopVendedoresComision($fechaDesde, $fechaHasta, $vendedorId);
        $totalComisionesPorMes = $this->obtenerTotalComisionesPorMes($fechaDesde, $fechaHasta, $vendedorId);
        $porcentajeBonos = $this->obtenerPorcentajeBonos($fechaDesde, $fechaHasta, $vendedorId);
        
        include "app/views/layout/header.php";
        include "app/views/dashboard/index.php";
        include "app/views/layout/footer.php";
    }
    
    private function obtenerVendedores() {
        $sql = "SELECT id, nombre FROM vendedores ORDER BY nombre";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function verificarYGenerarComisiones() {
        // Verificar si existen comisiones
        $countSql = "SELECT COUNT(*) FROM comisiones";
        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute();
        $comisionesExistentes = $countStmt->fetchColumn();
        
        // Si no hay comisiones, generar automáticamente
        if ($comisionesExistentes == 0) {
            require_once "app/models/Comision.php";
            $comision = new Comision();
            
            // Obtener todos los meses únicos con datos de operaciones
            $sql = "SELECT DISTINCT YEAR(fecha) as anio, MONTH(fecha) as mes 
                    FROM operaciones 
                    ORDER BY anio, mes";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $meses = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($meses as $mes) {
                $comision->calcularComisionesMes($mes['anio'], $mes['mes']);
            }
        }
    }
    
    
    private function obtenerEstadisticas($fechaDesde = '', $fechaHasta = '', $vendedorId = '') {
        $where = [];
        $params = [];
        
        if (!$fechaHasta) {
            $fechaHasta = date('Y-m-d');
        }
        
        if (!$fechaDesde) {
            $fechaDesde = '2025-01-01';
        }
        
        $where[] = "o.fecha >= ?";
        $params[] = $fechaDesde;
        
        $where[] = "o.fecha <= ?";
        $params[] = $fechaHasta;
        
        if ($vendedorId) {
            $where[] = "o.vendedor_id = ?";
            $params[] = $vendedorId;
        }
        
        $whereClause = "WHERE " . implode(" AND ", $where);
        
        $sql = "
            SELECT 
                COUNT(DISTINCT v.id) as total_vendedores,
                COUNT(CASE WHEN o.tipo_operacion = 'Venta' THEN 1 END) as total_ventas,
                COUNT(CASE WHEN o.tipo_operacion = 'Devolución' THEN 1 END) as total_devoluciones,
                COALESCE(SUM(CASE WHEN o.tipo_operacion = 'Venta' THEN o.valor_vendido ELSE 0 END), 0) as valor_total_ventas,
                COALESCE(SUM(CASE WHEN o.tipo_operacion = 'Devolución' THEN o.valor_vendido ELSE 0 END), 0) as valor_total_devoluciones
            FROM vendedores v
            LEFT JOIN operaciones o ON v.id = o.vendedor_id
            $whereClause
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function obtenerVentasPorMes($fechaDesde = '', $fechaHasta = '', $vendedorId = '') {
        $where = ["tipo_operacion = 'Venta'"];
        $params = [];
        
        if (!$fechaHasta) {
            $fechaHasta = date('Y-m-d');
        }
        
        if (!$fechaDesde) {
            $fechaDesde = '2025-01-01';
        }
        
        $where[] = "fecha >= ?";
        $params[] = $fechaDesde;
        
        $where[] = "fecha <= ?";
        $params[] = $fechaHasta;
        
        if ($vendedorId) {
            $where[] = "vendedor_id = ?";
            $params[] = $vendedorId;
        }
        
        $whereClause = implode(" AND ", $where);
        
        $sql = "
            SELECT 
                MONTH(fecha) as mes,
                COUNT(*) as cantidad,
                SUM(valor_vendido) as valor
            FROM operaciones 
            WHERE $whereClause
            GROUP BY MONTH(fecha)
            ORDER BY mes
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function obtenerTopVendedores($fechaDesde = '', $fechaHasta = '', $vendedorId = '') {
        $where = [];
        $params = [];
        
        if (!$fechaHasta) {
            $fechaHasta = date('Y-m-d');
        }
        
        if (!$fechaDesde) {
            $fechaDesde = '2025-01-01';
        }
        
        $where[] = "o.fecha >= ?";
        $params[] = $fechaDesde;
        
        $where[] = "o.fecha <= ?";
        $params[] = $fechaHasta;
        
        if ($vendedorId) {
            $where[] = "o.vendedor_id = ?";
            $params[] = $vendedorId;
        }
        
        $whereClause = "WHERE " . implode(" AND ", $where);
        
        $sql = "
            SELECT 
                v.id as vendedor_id,
                v.nombre,
                COUNT(CASE WHEN o.tipo_operacion = 'Venta' THEN 1 END) as ventas,
                COALESCE(SUM(CASE WHEN o.tipo_operacion = 'Venta' THEN o.valor_vendido ELSE 0 END), 0) as valor_ventas,
                COUNT(CASE WHEN o.tipo_operacion = 'Devolución' THEN 1 END) as devoluciones,
                COALESCE(SUM(CASE WHEN o.tipo_operacion = 'Devolución' THEN o.valor_vendido ELSE 0 END), 0) as valor_devoluciones
            FROM vendedores v
            LEFT JOIN operaciones o ON v.id = o.vendedor_id
            $whereClause
            GROUP BY v.id, v.nombre
            ORDER BY valor_ventas DESC
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function obtenerVentasPorProducto($fechaDesde = '', $fechaHasta = '', $vendedorId = '') {
        $where = ["tipo_operacion = 'Venta'"];
        $params = [];
        
        if (!$fechaHasta) {
            $fechaHasta = date('Y-m-d');
        }
        
        if (!$fechaDesde) {
            $fechaDesde = '2025-01-01';
        }
        
        $where[] = "fecha >= ?";
        $params[] = $fechaDesde;
        
        $where[] = "fecha <= ?";
        $params[] = $fechaHasta;
        
        if ($vendedorId) {
            $where[] = "vendedor_id = ?";
            $params[] = $vendedorId;
        }
        
        $whereClause = implode(" AND ", $where);
        
        $sql = "
            SELECT 
                producto,
                COUNT(*) as cantidad,
                SUM(valor_vendido) as valor
            FROM operaciones 
            WHERE $whereClause
            GROUP BY producto
            ORDER BY valor DESC
            LIMIT 10
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function obtenerDevolucionesPorMes($fechaDesde = '', $fechaHasta = '', $vendedorId = '') {
        $where = ["tipo_operacion = 'Devolución'"];
        $params = [];
        
        if (!$fechaHasta) {
            $fechaHasta = date('Y-m-d');
        }
        
        if (!$fechaDesde) {
            $fechaDesde = '2025-01-01';
        }
        
        $where[] = "fecha >= ?";
        $params[] = $fechaDesde;
        
        $where[] = "fecha <= ?";
        $params[] = $fechaHasta;
        
        if ($vendedorId) {
            $where[] = "vendedor_id = ?";
            $params[] = $vendedorId;
        }
        
        $whereClause = implode(" AND ", $where);
        
        $sql = "
            SELECT 
                MONTH(fecha) as mes,
                COUNT(*) as cantidad,
                SUM(valor_vendido) as valor
            FROM operaciones 
            WHERE $whereClause
            GROUP BY MONTH(fecha)
            ORDER BY mes
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function obtenerComisionesPorMes($fechaDesde = '', $fechaHasta = '', $vendedorId = '') {
        $where = [];
        $params = [];
        
        if (!$fechaHasta) {
            $fechaHasta = date('Y-m-d');
        }
        
        if (!$fechaDesde) {
            $fechaDesde = '2025-01-01';
        }
        
        $fechaDesdeYear = date('Y', strtotime($fechaDesde));
        $fechaDesdeMonth = date('n', strtotime($fechaDesde));
        $fechaHastaYear = date('Y', strtotime($fechaHasta));
        $fechaHastaMonth = date('n', strtotime($fechaHasta));
        
        $where[] = "(anio > ? OR (anio = ? AND mes >= ?))";
        $params[] = $fechaDesdeYear;
        $params[] = $fechaDesdeYear;
        $params[] = $fechaDesdeMonth;
        
        $where[] = "(anio < ? OR (anio = ? AND mes <= ?))";
        $params[] = $fechaHastaYear;
        $params[] = $fechaHastaYear;
        $params[] = $fechaHastaMonth;
        
        if ($vendedorId) {
            $where[] = "vendedor_id = ?";
            $params[] = $vendedorId;
        }
        
        $whereClause = "WHERE " . implode(" AND ", $where);
        
        $sql = "
            SELECT 
                mes,
                COUNT(*) as vendedores,
                SUM(comision_final) as total_comisiones
            FROM comisiones 
            $whereClause
            GROUP BY mes
            ORDER BY mes
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function datatable() {
        $draw = intval($_GET['draw']);
        $start = intval($_GET['start']);
        $length = intval($_GET['length']);
        $searchValue = $_GET['search']['value'] ?? '';
        
        $fechaDesde = $_GET['fecha_desde'] ?? '';
        $fechaHasta = $_GET['fecha_hasta'] ?? '';
        $vendedorId = $_GET['vendedor'] ?? '';
        
        $where = [];
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
        
        if ($searchValue) {
            $where[] = "v.nombre LIKE ?";
            $params[] = "%$searchValue%";
        }
        
        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        $sql = "
            SELECT 
                v.nombre,
                COUNT(CASE WHEN o.tipo_operacion = 'Venta' THEN 1 END) as ventas,
                COALESCE(SUM(CASE WHEN o.tipo_operacion = 'Venta' THEN o.valor_vendido ELSE 0 END), 0) as valor_ventas,
                COUNT(CASE WHEN o.tipo_operacion = 'Devolución' THEN 1 END) as devoluciones,
                COALESCE(SUM(CASE WHEN o.tipo_operacion = 'Devolución' THEN o.valor_vendido ELSE 0 END), 0) as valor_devoluciones,
                COALESCE(SUM(c.bono), 0) as total_bono
            FROM vendedores v
            LEFT JOIN operaciones o ON v.id = o.vendedor_id
            LEFT JOIN comisiones c ON v.id = c.vendedor_id
            $whereClause
            GROUP BY v.id, v.nombre
        ";
        
        $countSql = "SELECT COUNT(*) FROM ($sql) as subquery";
        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute($params);
        $totalRecords = $countStmt->fetchColumn();
        
        $sql .= " ORDER BY valor_ventas DESC LIMIT $start, $length";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $result = [];
        foreach ($data as $row) {
            $comision = ($row['valor_ventas'] - $row['valor_devoluciones']) * 0.05;
            $bono = $row['total_bono'] > 0 ? 
                '<span class="text-success"><i class="bi bi-plus-circle"></i> $' . number_format($row['total_bono'], 0, ',', '.') . '</span>' :
                '<span class="text-muted">-</span>';
            
            $result[] = [
                $row['nombre'],
                number_format($row['ventas']),
                '$' . number_format($row['valor_ventas'], 0, ',', '.'),
                number_format($row['devoluciones']),
                '$' . number_format($row['valor_devoluciones'], 0, ',', '.'),
                $bono,
                '$' . number_format($comision, 0, ',', '.')
            ];
        }
        
        echo json_encode([
            "draw" => $draw,
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecords,
            "data" => $result
        ]);
    }
    
    private function obtenerTopVendedoresComision($fechaDesde = '', $fechaHasta = '', $vendedorId = '') {
        $where = [];
        $params = [];
        
        if (!$fechaHasta) {
            $fechaHasta = date('Y-m-d');
        }
        
        if (!$fechaDesde) {
            $fechaDesde = '2025-01-01';
        }
        
        $fechaDesdeYear = date('Y', strtotime($fechaDesde));
        $fechaDesdeMonth = date('n', strtotime($fechaDesde));
        $fechaHastaYear = date('Y', strtotime($fechaHasta));
        $fechaHastaMonth = date('n', strtotime($fechaHasta));
        
        $where[] = "(c.anio > ? OR (c.anio = ? AND c.mes >= ?))";
        $params[] = $fechaDesdeYear;
        $params[] = $fechaDesdeYear;
        $params[] = $fechaDesdeMonth;
        
        $where[] = "(c.anio < ? OR (c.anio = ? AND c.mes <= ?))";
        $params[] = $fechaHastaYear;
        $params[] = $fechaHastaYear;
        $params[] = $fechaHastaMonth;
        
        if ($vendedorId) {
            $where[] = "c.vendedor_id = ?";
            $params[] = $vendedorId;
        }
        
        $whereClause = "WHERE " . implode(" AND ", $where);
        
        $sql = "
            SELECT 
                v.nombre,
                SUM(c.comision_final) as total_comision,
                COUNT(c.id) as meses_con_comision,
                AVG(c.comision_final) as promedio_comision
            FROM vendedores v
            INNER JOIN comisiones c ON v.id = c.vendedor_id
            $whereClause
            GROUP BY v.id, v.nombre
            ORDER BY total_comision DESC
            LIMIT 5
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function obtenerTotalComisionesPorMes($fechaDesde = '', $fechaHasta = '', $vendedorId = '') {
        $where = [];
        $params = [];
        
        if (!$fechaHasta) {
            $fechaHasta = date('Y-m-d');
        }
        
        if (!$fechaDesde) {
            $fechaDesde = '2025-01-01';
        }
        
        $fechaDesdeYear = date('Y', strtotime($fechaDesde));
        $fechaDesdeMonth = date('n', strtotime($fechaDesde));
        $fechaHastaYear = date('Y', strtotime($fechaHasta));
        $fechaHastaMonth = date('n', strtotime($fechaHasta));
        
        $where[] = "(c.anio > ? OR (c.anio = ? AND c.mes >= ?))";
        $params[] = $fechaDesdeYear;
        $params[] = $fechaDesdeYear;
        $params[] = $fechaDesdeMonth;
        
        $where[] = "(c.anio < ? OR (c.anio = ? AND c.mes <= ?))";
        $params[] = $fechaHastaYear;
        $params[] = $fechaHastaYear;
        $params[] = $fechaHastaMonth;
        
        if ($vendedorId) {
            $where[] = "c.vendedor_id = ?";
            $params[] = $vendedorId;
        }
        
        $whereClause = "WHERE " . implode(" AND ", $where);
        
        $sql = "
            SELECT 
                c.anio,
                c.mes,
                SUM(c.comision_final) as total_comisiones,
                COUNT(DISTINCT c.vendedor_id) as vendedores_activos,
                AVG(c.comision_final) as promedio_comision
            FROM comisiones c
            $whereClause
            GROUP BY c.anio, c.mes
            ORDER BY c.anio, c.mes
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function obtenerPorcentajeBonos($fechaDesde = '', $fechaHasta = '', $vendedorId = '') {
        $where = [];
        $params = [];
        
        if (!$fechaHasta) {
            $fechaHasta = date('Y-m-d');
        }
        
        if (!$fechaDesde) {
            $fechaDesde = '2025-01-01';
        }
        
        $fechaDesdeYear = date('Y', strtotime($fechaDesde));
        $fechaDesdeMonth = date('n', strtotime($fechaDesde));
        $fechaHastaYear = date('Y', strtotime($fechaHasta));
        $fechaHastaMonth = date('n', strtotime($fechaHasta));
        
        $where[] = "(c.anio > ? OR (c.anio = ? AND c.mes >= ?))";
        $params[] = $fechaDesdeYear;
        $params[] = $fechaDesdeYear;
        $params[] = $fechaDesdeMonth;
        
        $where[] = "(c.anio < ? OR (c.anio = ? AND c.mes <= ?))";
        $params[] = $fechaHastaYear;
        $params[] = $fechaHastaYear;
        $params[] = $fechaHastaMonth;
        
        if ($vendedorId) {
            $where[] = "c.vendedor_id = ?";
            $params[] = $vendedorId;
        }
        
        $whereClause = "WHERE " . implode(" AND ", $where);
        
        $sql = "
            SELECT 
                COUNT(DISTINCT c.vendedor_id) as total_vendedores,
                COUNT(DISTINCT CASE WHEN c.bono > 0 THEN c.vendedor_id END) as vendedores_con_bono,
                ROUND(
                    (COUNT(DISTINCT CASE WHEN c.bono > 0 THEN c.vendedor_id END) * 100.0 / COUNT(DISTINCT c.vendedor_id)), 2
                ) as porcentaje_bonos
            FROM comisiones c
            $whereClause
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
