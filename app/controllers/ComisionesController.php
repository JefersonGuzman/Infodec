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
        
        // Calcular total de páginas
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
        
        // BOM para UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Encabezados
        fputcsv($output, [
            'Vendedor', 'Año', 'Mes', 'Total Ventas', 'Total Devoluciones', 
            'Índice Devoluciones', 'Comisión Base', 'Bono', 'Penalización', 'Comisión Final'
        ]);
        
        // Datos
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
        // Implementación básica de PDF (se puede mejorar con TCPDF)
        $filename = "comisiones_{$anio}_{$mes}.pdf";
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // Por ahora, redirigir a CSV hasta implementar PDF
        $this->exportarCSV($comisiones, $anio, $mes);
    }
}
