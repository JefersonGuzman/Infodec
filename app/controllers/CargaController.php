<?php
    require_once "app/models/Operacion.php";
    require_once "app/models/Vendedor.php";
    require_once "app/models/Comision.php";

    class CargaController {
        public function index() {
            include "app/views/layout/header.php";
            include "app/views/carga/index.php";
            include "app/views/layout/footer.php";
        }

        public function upload() {
            if (isset($_FILES['csvfile'])) {
                $file = $_FILES['csvfile']['tmp_name'];
                $operacion = new Operacion();
                $resultado = $operacion->cargarCSV($file);
                
                if ($resultado['success']) {
                    // Generar comisiones automáticamente para los meses con datos nuevos
                    $this->generarComisionesAutomaticas();
                    header("Location: index.php?controller=Carga&action=index&msg=ok&procesados=" . $resultado['procesados'] . "&nuevos=" . $resultado['nuevos']);
                } else {
                    header("Location: index.php?controller=Carga&action=index&msg=error&error=" . urlencode($resultado['error']));
                }
            } else {
                header("Location: index.php?controller=Carga&action=index&msg=error");
            }
        }
        
        private function generarComisionesAutomaticas() {
            $comision = new Comision();
            $pdo = Conexion::getConexion();
            
            // Obtener todos los meses únicos con datos de operaciones
            $sql = "SELECT DISTINCT YEAR(fecha) as anio, MONTH(fecha) as mes 
                    FROM operaciones 
                    ORDER BY anio, mes";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $meses = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($meses as $mes) {
                $comision->calcularComisionesMes($mes['anio'], $mes['mes']);
            }
        }
        
        public function generarComisiones() {
            $anio = $_POST['anio'] ?? date('Y');
            $mes = $_POST['mes'] ?? date('n');
            
            try {
                $comision = new Comision();
                $resultados = $comision->calcularComisionesMes($anio, $mes);
                
                if (empty($resultados)) {
                    header("Location: index.php?controller=Carga&action=index&msg=no_data&anio=$anio&mes=$mes");
                } else {
                    header("Location: index.php?controller=Carga&action=index&msg=commissions_generated&anio=$anio&mes=$mes&count=" . count($resultados));
                }
            } catch (Exception $e) {
                header("Location: index.php?controller=Carga&action=index&msg=error&error=" . urlencode($e->getMessage()));
            }
        }
    }
