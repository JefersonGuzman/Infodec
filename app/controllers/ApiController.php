<?php
require_once "app/models/ApiService.php";

class ApiController {
    private $apiService;
    
    public function __construct() {
        $this->apiService = new ApiService();
    }
    
    /**
     * Página principal de integración con API
     */
    public function index() {
        $estadisticas = $this->apiService->obtenerEstadisticasSincronizacion();
        $categorias = $this->obtenerCategorias();
        
        include "app/views/layout/header.php";
        include "app/views/api/index.php";
        include "app/views/layout/footer.php";
    }
    
    /**
     * Sincronizar productos desde la API
     */
    public function sincronizar() {
        try {
            $resultado = $this->apiService->sincronizarProductos();
            
            // Registrar log de sincronización
            $this->registrarLog('api', 'sincronizar', $resultado['message'], $resultado);
            
            if ($resultado['success']) {
                $_SESSION['success_message'] = "Sincronización exitosa: {$resultado['sincronizados']} productos nuevos, {$resultado['actualizados']} actualizados, {$resultado['errores']} errores";
            } else {
                $_SESSION['error_message'] = "Error en sincronización: " . $resultado['message'];
            }
            
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Error: " . $e->getMessage();
            $this->registrarLog('api', 'error', $e->getMessage());
        }
        
        header("Location: index.php?controller=Api&action=index");
        exit;
    }
    
    /**
     * Validar datos entre CSV y API
     */
    public function validar() {
        try {
            // Obtener productos del CSV más reciente
            $productosCsv = $this->obtenerProductosCsv();
            
            if (empty($productosCsv)) {
                $_SESSION['warning_message'] = "No hay datos de CSV para validar";
                header("Location: index.php?controller=Api&action=index");
                exit;
            }
            
            $validaciones = $this->apiService->validarDatos($productosCsv);
            
            // Registrar log de validación
            $this->registrarLog('validacion', 'validar', 'Validación completada', $validaciones);
            
            $_SESSION['validacion_data'] = $validaciones;
            $_SESSION['success_message'] = "Validación completada: {$validaciones['coincidencias']} coincidencias encontradas";
            
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Error en validación: " . $e->getMessage();
            $this->registrarLog('validacion', 'error', $e->getMessage());
        }
        
        header("Location: index.php?controller=Api&action=index");
        exit;
    }
    
    /**
     * Obtener productos con filtros
     */
    public function productos() {
        $categoria = $_GET['categoria'] ?? '';
        $precioMin = $_GET['precio_min'] ?? '';
        $precioMax = $_GET['precio_max'] ?? '';
        $limit = $_GET['limit'] ?? 50;
        
        $productos = $this->apiService->obtenerProductosFiltrados($categoria, $precioMin, $precioMax, $limit);
        $categorias = $this->obtenerCategorias();
        
        include "app/views/layout/header.php";
        include "app/views/api/productos.php";
        include "app/views/layout/footer.php";
    }
    
    /**
     * API endpoint para obtener productos (JSON)
     */
    public function apiProductos() {
        header('Content-Type: application/json');
        
        try {
            $categoria = $_GET['categoria'] ?? '';
            $precioMin = $_GET['precio_min'] ?? '';
            $precioMax = $_GET['precio_max'] ?? '';
            $limit = $_GET['limit'] ?? 50;
            
            $productos = $this->apiService->obtenerProductosFiltrados($categoria, $precioMin, $precioMax, $limit);
            
            echo json_encode([
                'success' => true,
                'data' => $productos,
                'total' => count($productos)
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Obtener estadísticas de integración
     */
    public function estadisticas() {
        header('Content-Type: application/json');
        
        try {
            $estadisticas = $this->apiService->obtenerEstadisticasSincronizacion();
            $logs = $this->obtenerLogsRecientes();
            
            echo json_encode([
                'success' => true,
                'estadisticas' => $estadisticas,
                'logs' => $logs
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Endpoint para DataTable de productos
     */
    public function datatable() {
        header('Content-Type: application/json');
        
        try {
            $draw = intval($_GET['draw'] ?? 1);
            $start = intval($_GET['start'] ?? 0);
            $length = intval($_GET['length'] ?? 25);
            $search = $_GET['search']['value'] ?? '';
            $orderColumn = intval($_GET['order'][0]['column'] ?? 0);
            $orderDir = $_GET['order'][0]['dir'] ?? 'desc';
            
            // Filtros adicionales
            $categoria = $_GET['categoria'] ?? '';
            $precioMin = $_GET['precio_min'] ?? '';
            $precioMax = $_GET['precio_max'] ?? '';
            $disponible = $_GET['disponible'] ?? '';
            
            $resultado = $this->apiService->obtenerProductosDataTable(
                $start, $length, $search, $orderColumn, $orderDir,
                $categoria, $precioMin, $precioMax, $disponible
            );
            
            echo json_encode([
                'draw' => $draw,
                'recordsTotal' => $resultado['total'],
                'recordsFiltered' => $resultado['filtered'],
                'data' => $resultado['data']
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'draw' => intval($_GET['draw'] ?? 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Obtener productos del CSV más reciente
     */
    private function obtenerProductosCsv() {
        $pdo = Conexion::getConexion();
        
        $sql = "SELECT DISTINCT producto, AVG(valor_unitario) as valor_unitario 
                FROM operaciones 
                WHERE tipo_operacion = 'Venta' 
                AND fecha >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY producto";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener categorías disponibles
     */
    private function obtenerCategorias() {
        $pdo = Conexion::getConexion();
        
        $sql = "SELECT DISTINCT categoria, COUNT(*) as cantidad 
                FROM productos_api 
                WHERE disponible = 1 
                GROUP BY categoria 
                ORDER BY cantidad DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener logs recientes
     */
    private function obtenerLogsRecientes($limit = 10) {
        $pdo = Conexion::getConexion();
        
        // Validar que el límite sea un número entero positivo
        $limit = (int)$limit;
        if ($limit <= 0) {
            $limit = 10;
        }
        
        $sql = "SELECT * FROM logs_sincronizacion 
                ORDER BY fecha DESC 
                LIMIT " . $limit;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Registrar log de actividad
     */
    private function registrarLog($tipo, $accion, $mensaje, $datos = null) {
        try {
            $pdo = Conexion::getConexion();
            
            $sql = "INSERT INTO logs_sincronizacion (tipo, accion, mensaje, datos_json) 
                    VALUES (?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $tipo,
                $accion,
                $mensaje,
                $datos ? json_encode($datos) : null
            ]);
            
        } catch (Exception $e) {
            error_log("Error registrando log: " . $e->getMessage());
        }
    }
}
