<?php
require_once "Conexion.php";

class Comision {
    
    // Constantes de configuración
    const COMISION_BASE_PORCENTAJE = 5; 
    const BONO_PORCENTAJE = 2; // +2%
    const PENALIZACION_PORCENTAJE = 1; 
    const META_VENTAS_BONO = 50000000; 
    const INDICE_DEVOLUCIONES_LIMITE = 5; 

    public function calcularComisionVendedor($vendedorId, $anio, $mes) {
        $pdo = Conexion::getConexion();
        $vendedor = $this->obtenerDatosVendedor($vendedorId, $anio, $mes);
        
        if (!$vendedor) {
            return false;
        }
        
        // Calcular comisión base (5% del total de ventas)
        $comisionBase = $vendedor['total_ventas'] * (self::COMISION_BASE_PORCENTAJE / 100);
        $indiceDevoluciones = $this->calcularIndiceDevoluciones($vendedor['total_ventas'], $vendedor['total_devoluciones']);
        $bono = 0;
        if ($vendedor['total_ventas'] > self::META_VENTAS_BONO) {
            $bono = $vendedor['total_ventas'] * (self::BONO_PORCENTAJE / 100);
        }
        // Calcular penalización (-1% si índice de devoluciones > 5%)
        $penalizacion = 0;
        if ($indiceDevoluciones > self::INDICE_DEVOLUCIONES_LIMITE) {
            $penalizacion = $vendedor['total_ventas'] * (self::PENALIZACION_PORCENTAJE / 100);
        }
        // Calcular comisión final
        $comisionFinal = $comisionBase + $bono - $penalizacion;
        // Asegurar que la comisión final no sea negativa
        $comisionFinal = max(0, $comisionFinal);
        return [
            'vendedor_id' => $vendedorId,
            'anio' => $anio,
            'mes' => $mes,
            'total_ventas' => $vendedor['total_ventas'],
            'total_devoluciones' => $vendedor['total_devoluciones'],
            'indice_devoluciones' => $indiceDevoluciones,
            'comision_base' => $comisionBase,
            'bono' => $bono,
            'penalizacion' => $penalizacion,
            'comision_final' => $comisionFinal
        ];
    }
    
    private function obtenerDatosVendedor($vendedorId, $anio, $mes) {
        $pdo = Conexion::getConexion();
        
        $stmt = $pdo->prepare("
            SELECT 
                v.id as vendedor_id,
                v.nombre,
                COALESCE(SUM(CASE WHEN o.tipo_operacion = 'Venta' THEN o.valor_vendido ELSE 0 END), 0) as total_ventas,
                COALESCE(SUM(CASE WHEN o.tipo_operacion = 'Devolución' THEN ABS(o.valor_vendido) ELSE 0 END), 0) as total_devoluciones
            FROM vendedores v
            LEFT JOIN operaciones o ON v.id = o.vendedor_id 
                AND YEAR(o.fecha) = ? 
                AND MONTH(o.fecha) = ?
            WHERE v.id = ?
            GROUP BY v.id, v.nombre
        ");
        
        $stmt->execute([$anio, $mes, $vendedorId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function calcularIndiceDevoluciones($totalVentas, $totalDevoluciones) {
        if ($totalVentas == 0) {
            return 0;
        }
        
        return ($totalDevoluciones / $totalVentas) * 100;
    }
    
    public function guardarComision($datosComision) {
        $pdo = Conexion::getConexion();
        
        $stmt = $pdo->prepare("
            SELECT id FROM comisiones 
            WHERE vendedor_id = ? AND anio = ? AND mes = ?
        ");
        $stmt->execute([$datosComision['vendedor_id'], $datosComision['anio'], $datosComision['mes']]);
        
        if ($stmt->fetch()) {
            // Actualizar comisión existente
            $sql = "UPDATE comisiones SET 
                    total_ventas = ?, 
                    total_devoluciones = ?, 
                    indice_devoluciones = ?, 
                    comision_base = ?, 
                    bono = ?, 
                    penalizacion = ?, 
                    comision_final = ?
                    WHERE vendedor_id = ? AND anio = ? AND mes = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $datosComision['total_ventas'],
                $datosComision['total_devoluciones'],
                $datosComision['indice_devoluciones'],
                $datosComision['comision_base'],
                $datosComision['bono'],
                $datosComision['penalizacion'],
                $datosComision['comision_final'],
                $datosComision['vendedor_id'],
                $datosComision['anio'],
                $datosComision['mes']
            ]);
        } else {
            // Insertar nueva comisión
            $sql = "INSERT INTO comisiones 
                    (vendedor_id, anio, mes, total_ventas, total_devoluciones, indice_devoluciones, 
                     comision_base, bono, penalizacion, comision_final) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $datosComision['vendedor_id'],
                $datosComision['anio'],
                $datosComision['mes'],
                $datosComision['total_ventas'],
                $datosComision['total_devoluciones'],
                $datosComision['indice_devoluciones'],
                $datosComision['comision_base'],
                $datosComision['bono'],
                $datosComision['penalizacion'],
                $datosComision['comision_final']
            ]);
        }
        
        return true;
    }
    
    public function calcularComisionesMes($anio, $mes) {
        $pdo = Conexion::getConexion();
        
        // Obtener todos los vendedores que tuvieron operaciones en el mes
        $stmt = $pdo->prepare("
            SELECT DISTINCT v.id 
            FROM vendedores v
            INNER JOIN operaciones o ON v.id = o.vendedor_id
            WHERE YEAR(o.fecha) = ? AND MONTH(o.fecha) = ?
        ");
        $stmt->execute([$anio, $mes]);
        $vendedores = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $resultados = [];
        
        foreach ($vendedores as $vendedorId) {
            $comision = $this->calcularComisionVendedor($vendedorId, $anio, $mes);
            if ($comision) {
                $this->guardarComision($comision);
                $resultados[] = $comision;
            }
        }
        
        return $resultados;
    }
    
    public function obtenerComisionesVendedor($vendedorId, $anio = null, $mes = null) {
        $pdo = Conexion::getConexion();
        
        $sql = "SELECT c.*, v.nombre as vendedor_nombre 
                FROM comisiones c
                JOIN vendedores v ON c.vendedor_id = v.id
                WHERE c.vendedor_id = ?";
        
        $params = [$vendedorId];
        
        if ($anio) {
            $sql .= " AND c.anio = ?";
            $params[] = $anio;
        }
        
        if ($mes) {
            $sql .= " AND c.mes = ?";
            $params[] = $mes;
        }
        
        $sql .= " ORDER BY c.id DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function obtenerComisiones($anio = null, $mes = null, $limit = 50, $offset = 0, $vendedorId = null) {
        $pdo = Conexion::getConexion();
        
        $sql = "SELECT c.*, v.nombre as vendedor_nombre 
                FROM comisiones c
                JOIN vendedores v ON c.vendedor_id = v.id";
        
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
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        $sql .= " ORDER BY c.id DESC";
        $sql .= " LIMIT $limit OFFSET $offset";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function obtenerEstadisticas($anio = null, $mes = null) {
        $pdo = Conexion::getConexion();
        
        $sql = "SELECT 
                    COUNT(*) as total_vendedores,
                    SUM(comision_final) as total_comisiones,
                    AVG(comision_final) as promedio_comisiones,
                    SUM(CASE WHEN bono > 0 THEN 1 ELSE 0 END) as vendedores_con_bono,
                    SUM(CASE WHEN penalizacion > 0 THEN 1 ELSE 0 END) as vendedores_penalizados
                FROM comisiones c";
        
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
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
