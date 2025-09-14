<?php
    require_once "Conexion.php";
    require_once "Vendedor.php";

    class Operacion {
        public function cargarCSV($csvFile) {
            $pdo = Conexion::getConexion();
            if (($handle = fopen($csvFile, "r")) !== FALSE) {
                $firstRow = true;
                while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    if ($firstRow) { $firstRow = false; continue; }

                        list($fecha, $vendedor, $producto, $referencia, $cantidad,
                            $valorUnitario, $valorVendido, $impuesto) = $row;

                        $tipoOperacion = $row[8] ?? "Venta";

                        $motivo = $row[9] ?? null;


                    $vendedorId = Vendedor::getOrCreate($vendedor);

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
                        $tipoOperacion,
                        $motivo
                    ]);
                }
                fclose($handle);
            }
        }
    }
