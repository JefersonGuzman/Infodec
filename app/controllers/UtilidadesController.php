<?php
require_once "app/models/Operacion.php";
require_once "app/models/Comision.php";

class UtilidadesController {
    
    public function limpiarDuplicados() {
        $operacion = new Operacion();
        $resultado = $operacion->limpiarDuplicados();
        
        if ($resultado['success']) {
            $mensaje = "Duplicados eliminados exitosamente. ";
            $mensaje .= "Registros eliminados: {$resultado['duplicados_eliminados']}";
            header("Location: index.php?controller=Dashboard&action=index&msg=success&details=" . urlencode($mensaje));
        } else {
            header("Location: index.php?controller=Dashboard&action=index&msg=error&error=" . urlencode($resultado['error']));
        }
    }
    
    public function recalcularComisiones() {
        $anio = $_POST['anio'] ?? date('Y');
        $mes = $_POST['mes'] ?? date('n');
        
        try {
            $comision = new Comision();
            
            // Limpiar comisiones existentes para el período
            $pdo = Conexion::getConexion();
            $stmt = $pdo->prepare("DELETE FROM comisiones WHERE anio = ? AND mes = ?");
            $stmt->execute([$anio, $mes]);
            
            // Recalcular comisiones
            $resultados = $comision->calcularComisionesMes($anio, $mes);
            
            $mensaje = "Comisiones recalculadas exitosamente para {$anio}/{$mes}. ";
            $mensaje .= "Vendedores procesados: " . count($resultados);
            
            header("Location: index.php?controller=Comisiones&action=index&msg=success&details=" . urlencode($mensaje) . "&anio={$anio}&mes={$mes}");
            
        } catch (Exception $e) {
            header("Location: index.php?controller=Comisiones&action=index&msg=error&error=" . urlencode($e->getMessage()));
        }
    }
    
    public function estadisticas() {
        $operacion = new Operacion();
        $estadisticas = $operacion->obtenerEstadisticas();
        
        header('Content-Type: application/json');
        echo json_encode($estadisticas);
    }
    
    public function limpiarTodo() {
        try {
            $operacion = new Operacion();
            
            // Limpiar duplicados
            $resultadoDuplicados = $operacion->limpiarDuplicados();
            
            if (!$resultadoDuplicados['success']) {
                throw new Exception($resultadoDuplicados['error']);
            }
            
            // Limpiar comisiones
            $pdo = Conexion::getConexion();
            $pdo->exec("DELETE FROM comisiones");
            
            // Recalcular comisiones para junio y julio 2025
            $comision = new Comision();
            $resultadosJunio = $comision->calcularComisionesMes(2025, 6);
            $resultadosJulio = $comision->calcularComisionesMes(2025, 7);
            
            $mensaje = "Limpieza completa realizada. ";
            $mensaje .= "Duplicados eliminados: {$resultadoDuplicados['duplicados_eliminados']}. ";
            $mensaje .= "Comisiones recalculadas para junio: " . count($resultadosJunio) . " vendedores. ";
            $mensaje .= "Comisiones recalculadas para julio: " . count($resultadosJulio) . " vendedores.";
            
            header("Location: index.php?controller=Dashboard&action=index&msg=success&details=" . urlencode($mensaje));
            
        } catch (Exception $e) {
            header("Location: index.php?controller=Dashboard&action=index&msg=error&error=" . urlencode($e->getMessage()));
        }
    }
}
