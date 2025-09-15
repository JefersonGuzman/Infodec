<?php
require_once "app/models/Comision.php";

class ComisionesController {
    private $comision;
    
    public function __construct() {
        $this->comision = new Comision();
    }
    
    public function index() {
        $page = $_GET['page'] ?? 1;
        $anio = $_GET['anio'] ?? date('Y');
        $mes = $_GET['mes'] ?? date('n');
        $vendedorId = $_GET['vendedor'] ?? '';
        
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $comisiones = $this->comision->obtenerComisiones($anio, $mes, $limit, $offset, $vendedorId);
        $estadisticas = $this->comision->obtenerEstadisticas($anio, $mes);
        
        $pdo = Conexion::getConexion();
        $countSql = "SELECT COUNT(*) FROM comisiones c";
        $params = [];
        $where = [];
        
        if ($anio) {
            $where[] = "c.anio = ?";
            $params[] = $anio;
        }
        
        if ($mes) {
            $where[] = "c.mes = ?";
            $params[] = $mes;
        }
        
        if ($vendedorId) {
            $where[] = "c.vendedor_id = ?";
            $params[] = $vendedorId;
        }
        
        if (!empty($where)) {
            $countSql .= " WHERE " . implode(" AND ", $where);
        }
        
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $totalRecords = $countStmt->fetchColumn();
        $totalPages = ceil($totalRecords / $limit);
        
        include "app/views/layout/header.php";
        include "app/views/comisiones/index.php";
        include "app/views/layout/footer.php";
    }
    
    public function calcular() {
        $anio = $_POST['anio'] ?? date('Y');
        $mes = $_POST['mes'] ?? date('n');
        
        try {
            $resultados = $this->comision->calcularComisionesMes($anio, $mes);
            
            if (empty($resultados)) {
                header("Location: index.php?controller=Comisiones&action=index&msg=no_data&anio=$anio&mes=$mes");
            } else {
                header("Location: index.php?controller=Comisiones&action=index&msg=success&anio=$anio&mes=$mes");
            }
        } catch (Exception $e) {
            header("Location: index.php?controller=Comisiones&action=index&msg=error&anio=$anio&mes=$mes");
        }
    }
    
    public function recalcular() {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            header("Location: index.php?controller=Comisiones&action=index&msg=error");
            return;
        }
        
        try {
            $pdo = Conexion::getConexion();
            $stmt = $pdo->prepare("SELECT vendedor_id, anio, mes FROM comisiones WHERE id = ?");
            $stmt->execute([$id]);
            $comision = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($comision) {
                $nuevaComision = $this->comision->calcularComisionVendedor(
                    $comision['vendedor_id'], 
                    $comision['anio'], 
                    $comision['mes']
                );
                
                if ($nuevaComision) {
                    $this->comision->guardarComision($nuevaComision);
                    header("Location: index.php?controller=Comisiones&action=index&msg=recalculated");
                } else {
                    header("Location: index.php?controller=Comisiones&action=index&msg=error");
                }
            } else {
                header("Location: index.php?controller=Comisiones&action=index&msg=not_found");
            }
        } catch (Exception $e) {
            header("Location: index.php?controller=Comisiones&action=index&msg=error");
        }
    }
    
    public function vendedor() {
        $vendedorId = $_GET['id'] ?? null;
        $anio = $_GET['anio'] ?? null;
        $mes = $_GET['mes'] ?? null;
        
        if (!$vendedorId) {
            header("Location: index.php?controller=Comisiones&action=index&msg=error");
            return;
        }
        
        $comisiones = $this->comision->obtenerComisionesVendedor($vendedorId, $anio, $mes);
        
        include "app/views/layout/header.php";
        include "app/views/comisiones/vendedor.php";
        include "app/views/layout/footer.php";
    }
    
    public function detalle() {
        $vendedorId = $_GET['id'] ?? null;
        $anio = $_GET['anio'] ?? date('Y');
        $mes = $_GET['mes'] ?? date('n');
        
        if (!$vendedorId) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'ID de vendedor requerido']);
            return;
        }
        
        $pdo = Conexion::getConexion();
        
        $vendedorSql = "SELECT nombre FROM vendedores WHERE id = ?";
        $vendedorStmt = $pdo->prepare($vendedorSql);
        $vendedorStmt->execute([$vendedorId]);
        $vendedor = $vendedorStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$vendedor) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Vendedor no encontrado']);
            return;
        }
        
        $comisionSql = "
            SELECT c.*, v.nombre as vendedor_nombre
            FROM comisiones c
            JOIN vendedores v ON v.id = c.vendedor_id
            WHERE c.vendedor_id = ? AND c.anio = ? AND c.mes = ?
        ";
        $comisionStmt = $pdo->prepare($comisionSql);
        $comisionStmt->execute([$vendedorId, $anio, $mes]);
        $comision = $comisionStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$comision) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No hay comisión calculada para este período']);
            return;
        }
        
        $ventasSql = "
            SELECT o.fecha, o.producto, o.referencia, o.cantidad, o.valor_unitario, o.valor_vendido, o.impuesto
            FROM operaciones o
            WHERE o.vendedor_id = ? AND o.tipo_operacion = 'Venta' 
            AND YEAR(o.fecha) = ? AND MONTH(o.fecha) = ?
            ORDER BY o.fecha DESC
        ";
        $ventasStmt = $pdo->prepare($ventasSql);
        $ventasStmt->execute([$vendedorId, $anio, $mes]);
        $ventas = $ventasStmt->fetchAll(PDO::FETCH_ASSOC);
        
        $devolucionesSql = "
            SELECT o.fecha, o.producto, o.referencia, o.cantidad, o.valor_unitario, o.valor_vendido, o.impuesto, o.motivo
            FROM operaciones o
            WHERE o.vendedor_id = ? AND o.tipo_operacion = 'Devolución' 
            AND YEAR(o.fecha) = ? AND MONTH(o.fecha) = ?
            ORDER BY o.fecha DESC
        ";
        $devolucionesStmt = $pdo->prepare($devolucionesSql);
        $devolucionesStmt->execute([$vendedorId, $anio, $mes]);
        $devoluciones = $devolucionesStmt->fetchAll(PDO::FETCH_ASSOC);
        
        $response = [
            'vendedor' => $vendedor,
            'comision' => $comision,
            'ventas' => $ventas,
            'devoluciones' => $devoluciones
        ];
        
        header('Content-Type: application/json');
        echo json_encode($response);
    }
    
    public function exportar() {
        $anio = $_GET['anio'] ?? date('Y');
        $mes = $_GET['mes'] ?? date('n');
        $formato = $_GET['formato'] ?? 'csv';
        
        $comisiones = $this->comision->obtenerComisiones($anio, $mes, 1000, 0);
        
        if ($formato === 'csv') {
            $this->exportarCSV($comisiones, $anio, $mes);
        } else {
            $this->exportarPDF($comisiones, $anio, $mes);
        }
    }
    
    private function exportarCSV($comisiones, $anio, $mes) {
        $filename = "comisiones_{$anio}_{$mes}.csv";
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($output, [
            'Vendedor', 'Año', 'Mes', 'Total Ventas', 'Total Devoluciones', 
            'Índice Devoluciones', 'Comisión Base', 'Bono', 'Penalización', 'Comisión Final'
        ]);
        
        foreach ($comisiones as $comision) {
            fputcsv($output, [
                $comision['vendedor_nombre'],
                $comision['anio'],
                $comision['mes'],
                number_format($comision['total_ventas'], 0, ',', '.'),
                number_format($comision['total_devoluciones'], 0, ',', '.'),
                number_format($comision['indice_devoluciones'], 2, ',', '.') . '%',
                number_format($comision['comision_base'], 0, ',', '.'),
                number_format($comision['bono'], 0, ',', '.'),
                number_format($comision['penalizacion'], 0, ',', '.'),
                number_format($comision['comision_final'], 0, ',', '.')
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    private function exportarPDF($comisiones, $anio, $mes) {
        $filename = "comisiones_{$anio}_{$mes}.pdf";
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $this->exportarCSV($comisiones, $anio, $mes);
    }
    
    public function datatable() {
        $pdo = Conexion::getConexion();
        
        $draw = intval($_GET['draw']);
        $start = intval($_GET['start']);
        $length = intval($_GET['length']);
        $searchValue = $_GET['search']['value'] ?? '';
        $orderColumn = intval($_GET['order'][0]['column'] ?? 0);
        $orderDir = $_GET['order'][0]['dir'] ?? 'desc';
        
        $anio = $_GET['anio'] ?? date('Y');
        $mes = $_GET['mes'] ?? date('n');
        $vendedorId = $_GET['vendedor'] ?? '';
        
        $columns = ['v.nombre', 'c.total_ventas', 'c.total_devoluciones', 'c.indice_devoluciones', 'c.comision_base', 'c.bono', 'c.penalizacion', 'c.comision_final', 'c.id'];
        $orderBy = $columns[$orderColumn] . ' ' . strtoupper($orderDir);
        
        $where = ["c.anio = ?", "c.mes = ?"];
        $params = [$anio, $mes];
        
        if ($vendedorId) {
            $where[] = "c.vendedor_id = ?";
            $params[] = $vendedorId;
        }
        
        if (!empty($searchValue)) {
            $where[] = "v.nombre LIKE ?";
            $params[] = "%$searchValue%";
        }
        
        $whereClause = implode(" AND ", $where);
        
        $countSql = "SELECT COUNT(*) FROM comisiones c JOIN vendedores v ON v.id = c.vendedor_id WHERE $whereClause";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $totalRecords = $countStmt->fetchColumn();
        
        $sql = "
            SELECT c.id, c.vendedor_id, v.nombre as vendedor_nombre, c.anio, c.mes,
                   c.total_ventas, c.total_devoluciones, c.indice_devoluciones,
                   c.comision_base, c.bono, c.penalizacion, c.comision_final
            FROM comisiones c
            JOIN vendedores v ON v.id = c.vendedor_id
            WHERE $whereClause
            ORDER BY $orderBy
            LIMIT $length OFFSET $start
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $comisiones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $data = [];
        foreach ($comisiones as $comision) {
            $vendedorInfo = '<strong>' . htmlspecialchars($comision['vendedor_nombre']) . '</strong><br>' .
                           '<small class="text-muted">' . $comision['anio'] . '/' . str_pad($comision['mes'], 2, '0', STR_PAD_LEFT) . '</small>';
            
            $indiceBadge = $comision['indice_devoluciones'] > 5 ? 
                '<span class="badge bg-danger">' . number_format($comision['indice_devoluciones'], 2, ',', '.') . '%</span>' :
                '<span class="badge bg-success">' . number_format($comision['indice_devoluciones'], 2, ',', '.') . '%</span>';
            
            $bono = $comision['bono'] > 0 ? 
                '<div class="bono-container text-end">
                    <span class="badge bg-success bono-badge mb-1 d-block">
                        <i class="bi bi-trophy-fill me-1"></i>BONO
                    </span>
                    <div class="bono-amount text-success">
                        $' . number_format($comision['bono'], 0, ',', '.') . '
                    </div>
                </div>' :
                '<div class="bono-container text-end">
                    <span class="badge bg-light text-dark bono-badge mb-1 d-block">
                        <i class="bi bi-dash-circle me-1"></i>SIN BONO
                    </span>
                    <div class="text-muted small">$0</div>
                </div>';
            
            $penalizacion = $comision['penalizacion'] > 0 ? 
                '<span class="text-danger"><i class="bi bi-dash-circle"></i> $' . number_format($comision['penalizacion'], 0, ',', '.') . '</span>' :
                '<span class="text-muted">-</span>';
            
            $acciones = '<button type="button" class="btn btn-outline-info btn-sm me-1" title="Ver detalle" ' .
                       'onclick="verDetalleComision(' . $comision['vendedor_id'] . ', ' . $comision['anio'] . ', ' . $comision['mes'] . ')">' .
                       '<i class="bi bi-eye"></i></button>' .
                       '<a href="index.php?controller=Comisiones&action=recalcular&id=' . $comision['id'] . '" ' .
                       'class="btn btn-outline-warning btn-sm" title="Recalcular" ' .
                       'onclick="return confirm(\'¿Recalcular esta comisión?\')"><i class="bi bi-arrow-clockwise"></i></a>';
            
            $data[] = [
                $vendedorInfo,
                '<div class="text-end">$' . number_format($comision['total_ventas'], 0, ',', '.') . '</div>',
                '<div class="text-end">$' . number_format($comision['total_devoluciones'], 0, ',', '.') . '</div>',
                '<div class="text-center">' . $indiceBadge . '</div>',
                '<div class="text-end">$' . number_format($comision['comision_base'], 0, ',', '.') . '</div>',
                '<div class="text-end">' . $bono . '</div>',
                '<div class="text-end">' . $penalizacion . '</div>',
                '<div class="text-end"><strong class="text-primary">$' . number_format($comision['comision_final'], 0, ',', '.') . '</strong></div>',
                '<div class="text-center">' . $acciones . '</div>'
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
