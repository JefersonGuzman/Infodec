<?php
    require_once "Conexion.php";
    require_once "Vendedor.php";

    class Operacion {
    public function cargarCSV($csvFile, $tipoOperacion = 'Venta') {
        $pdo = Conexion::getConexion();
        $registrosProcesados = 0;
        $registrosDuplicados = 0;
        $registrosNuevos = 0;
        
        if (($handle = fopen($csvFile, "r")) !== FALSE) {
            $firstRow = true;
            $pdo->beginTransaction();
            
            try {
                while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    if ($firstRow) { 
                        $firstRow = false; 
                        continue; 
                    }

                    if (count($row) < 8) {
                        continue; // Saltar filas incompletas
                    }

                    list($fecha, $vendedor, $producto, $referencia, $cantidad,
                        $valorUnitario, $valorVendido, $impuesto) = $row;

                    $tipoOperacionCSV = $row[8] ?? $tipoOperacion;
                    $motivo = $row[9] ?? null;

                    // Normalizar tipo de operación (manejar variaciones de escritura)
                    $tipoOperacionCSV = $this->normalizarTipoOperacion($tipoOperacionCSV);
                    $tipoOperacionEsperado = $this->normalizarTipoOperacion($tipoOperacion);

                    // Validar que el tipo de operación coincida
                    if ($tipoOperacionCSV !== $tipoOperacionEsperado) {
                        continue;
                    }

                    $vendedorId = Vendedor::getOrCreate($vendedor);

                    // Verificar si ya existe un registro idéntico
                    $checkSql = "SELECT COUNT(*) FROM operaciones 
                                WHERE fecha = ? AND vendedor_id = ? AND producto = ? 
                                AND referencia = ? AND cantidad = ? AND valor_unitario = ? 
                                AND valor_vendido = ? AND impuesto = ? AND tipo_operacion = ?";
                    
                    $checkStmt = $pdo->prepare($checkSql);
                    $checkStmt->execute([
                        $fecha, $vendedorId, $producto, $referencia, (int)$cantidad,
                        (int)$valorUnitario, (int)$valorVendido, (int)$impuesto, $tipoOperacionCSV
                    ]);
                    
                    $existe = $checkStmt->fetchColumn() > 0;
                    
                    if ($existe) {
                        $registrosDuplicados++;
                    } else {
                        $sql = "INSERT INTO operaciones 
                                (fecha, vendedor_id, producto, referencia, cantidad, valor_unitario, valor_vendido, impuesto, tipo_operacion, motivo)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([
                            $fecha,
                            $vendedorId,
                            $producto,
                            $referencia,
                            (int)$cantidad,
                            (int)$valorUnitario,  
                            (int)$valorVendido,   
                            (int)$impuesto,       
                            $tipoOperacionCSV,
                            $motivo
                        ]);
                        $registrosNuevos++;
                    }
                    $registrosProcesados++;
                }
                
                $pdo->commit();
                fclose($handle);
                
                return [
                    'success' => true,
                    'procesados' => $registrosProcesados,
                    'nuevos' => $registrosNuevos,
                    'duplicados' => $registrosDuplicados
                ];
                
            } catch (Exception $e) {
                $pdo->rollback();
                fclose($handle);
                return [
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return [
            'success' => false,
            'error' => 'No se pudo abrir el archivo CSV'
        ];
    }
    
    public function limpiarDuplicados() {
        $pdo = Conexion::getConexion();
        
        try {
            $pdo->beginTransaction();
            
            // Eliminar duplicados manteniendo solo el registro con ID más bajo
            $sql = "DELETE o1 FROM operaciones o1
                    INNER JOIN operaciones o2 
                    WHERE o1.id > o2.id 
                    AND o1.fecha = o2.fecha 
                    AND o1.vendedor_id = o2.vendedor_id 
                    AND o1.producto = o2.producto 
                    AND o1.referencia = o2.referencia 
                    AND o1.cantidad = o2.cantidad 
                    AND o1.valor_unitario = o2.valor_unitario 
                    AND o1.valor_vendido = o2.valor_vendido 
                    AND o1.impuesto = o2.impuesto 
                    AND o1.tipo_operacion = o2.tipo_operacion 
                    AND o1.motivo = o2.motivo";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $duplicadosEliminados = $stmt->rowCount();
            
            $pdo->commit();
            
            return [
                'success' => true,
                'duplicados_eliminados' => $duplicadosEliminados
            ];
            
        } catch (Exception $e) {
            $pdo->rollback();
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function obtenerEstadisticas() {
        $pdo = Conexion::getConexion();
        
        $sql = "SELECT 
                    COUNT(*) as total_operaciones,
                    COUNT(CASE WHEN tipo_operacion = 'Venta' THEN 1 END) as total_ventas,
                    COUNT(CASE WHEN tipo_operacion = 'Devolución' THEN 1 END) as total_devoluciones,
                    COALESCE(SUM(CASE WHEN tipo_operacion = 'Venta' THEN valor_vendido ELSE 0 END), 0) as valor_total_ventas,
                    COALESCE(SUM(CASE WHEN tipo_operacion = 'Devolución' THEN valor_vendido ELSE 0 END), 0) as valor_total_devoluciones
                FROM operaciones";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function normalizarTipoOperacion($tipo) {
        $tipo = trim($tipo);
        $tipo = strtolower($tipo);
        
        // Mapear variaciones comunes
        $mapeo = [
            'venta' => 'Venta',
            'ventas' => 'Venta',
            'devolucion' => 'Devolución',
            'devoluciones' => 'Devolución',
            'devolución' => 'Devolución',
            'return' => 'Devolución',
            'returns' => 'Devolución'
        ];
        
        return $mapeo[$tipo] ?? ucfirst($tipo);
    }
    }
