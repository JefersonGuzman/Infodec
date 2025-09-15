<?php
require_once "Conexion.php";

class ApiService {
    
    private $apiUrl;
    private $timeout;
    private $pdo;
    
    public function __construct() {
        $this->apiUrl = 'https://jsonplaceholder.typicode.com/posts'; // API de productos de ejemplo
        $this->timeout = 30;
        $this->pdo = Conexion::getConexion();
    }
    
    /**
     * Obtiene productos desde la API externa
     */
    public function obtenerProductos($limit = 100) {
        try {
            $url = $this->apiUrl . '?_limit=' . $limit;
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'User-Agent: VentasPlus-Integration/1.0'
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                throw new Exception("Error cURL: " . $error);
            }
            
            if ($httpCode !== 200) {
                throw new Exception("Error HTTP: " . $httpCode);
            }
            
            $data = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Error JSON: " . json_last_error_msg());
            }
            
            // Transformar datos de la API al formato de productos
            $productos = [];
            foreach ($data as $item) {
                $productos[] = [
                    'id' => $item['id'],
                    'titulo' => $item['title'],
                    'descripcion' => $item['body'],
                    'precio_base' => rand(10000, 500000), // Precio aleatorio para simulación
                    'categoria' => $this->categorizarProducto($item['title']),
                    'disponible' => true,
                    'fecha_sincronizacion' => date('Y-m-d H:i:s')
                ];
            }
            
            return $productos;
            
        } catch (Exception $e) {
            error_log("Error en ApiService::obtenerProductos: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Categoriza productos basado en palabras clave del título
     */
    private function categorizarProducto($titulo) {
        $titulo = strtolower($titulo);
        
        if (strpos($titulo, 'laptop') !== false || strpos($titulo, 'computer') !== false) {
            return 'Tecnología';
        } elseif (strpos($titulo, 'phone') !== false || strpos($titulo, 'mobile') !== false) {
            return 'Telefonía';
        } elseif (strpos($titulo, 'book') !== false || strpos($titulo, 'read') !== false) {
            return 'Libros';
        } elseif (strpos($titulo, 'car') !== false || strpos($titulo, 'vehicle') !== false) {
            return 'Automotriz';
        } elseif (strpos($titulo, 'food') !== false || strpos($titulo, 'eat') !== false) {
            return 'Alimentación';
        } else {
            return 'General';
        }
    }
    
    /**
     * Sincroniza productos de la API con la base de datos
     */
    public function sincronizarProductos() {
        try {
            $productos = $this->obtenerProductos();
            
            if (!$productos) {
                return [
                    'success' => false,
                    'message' => 'No se pudieron obtener productos de la API'
                ];
            }
            
            $sincronizados = 0;
            $actualizados = 0;
            $errores = 0;
            
            foreach ($productos as $producto) {
                try {
                    // Verificar si el producto ya existe
                    $stmt = $this->pdo->prepare("
                        SELECT id FROM productos_api 
                        WHERE id_api = ? AND titulo = ?
                    ");
                    $stmt->execute([$producto['id'], $producto['titulo']]);
                    
                    if ($stmt->fetch()) {
                        // Actualizar producto existente
                        $updateSql = "UPDATE productos_api SET 
                                     descripcion = ?, 
                                     precio_base = ?, 
                                     categoria = ?, 
                                     disponible = ?, 
                                     fecha_sincronizacion = ?
                                     WHERE id_api = ? AND titulo = ?";
                        
                        $updateStmt = $this->pdo->prepare($updateSql);
                        $updateStmt->execute([
                            $producto['descripcion'],
                            $producto['precio_base'],
                            $producto['categoria'],
                            $producto['disponible'],
                            $producto['fecha_sincronizacion'],
                            $producto['id'],
                            $producto['titulo']
                        ]);
                        $actualizados++;
                    } else {
                        // Insertar nuevo producto
                        $insertSql = "INSERT INTO productos_api 
                                     (id_api, titulo, descripcion, precio_base, categoria, disponible, fecha_sincronizacion) 
                                     VALUES (?, ?, ?, ?, ?, ?, ?)";
                        
                        $insertStmt = $this->pdo->prepare($insertSql);
                        $insertStmt->execute([
                            $producto['id'],
                            $producto['titulo'],
                            $producto['descripcion'],
                            $producto['precio_base'],
                            $producto['categoria'],
                            $producto['disponible'],
                            $producto['fecha_sincronizacion']
                        ]);
                        $sincronizados++;
                    }
                    
                } catch (Exception $e) {
                    error_log("Error sincronizando producto {$producto['id']}: " . $e->getMessage());
                    $errores++;
                }
            }
            
            return [
                'success' => true,
                'message' => "Sincronización completada",
                'sincronizados' => $sincronizados,
                'actualizados' => $actualizados,
                'errores' => $errores,
                'total' => count($productos)
            ];
            
        } catch (Exception $e) {
            error_log("Error en ApiService::sincronizarProductos: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error en la sincronización: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtiene estadísticas de sincronización
     */
    public function obtenerEstadisticasSincronizacion() {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_productos,
                        COUNT(CASE WHEN disponible = 1 THEN 1 END) as productos_disponibles,
                        COUNT(DISTINCT categoria) as categorias,
                        MAX(fecha_sincronizacion) as ultima_sincronizacion
                    FROM productos_api";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error en ApiService::obtenerEstadisticasSincronizacion: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Valida datos entre CSV y API
     */
    public function validarDatos($productosCsv) {
        try {
            $sql = "SELECT id_api, titulo, precio_base, categoria 
                    FROM productos_api 
                    WHERE disponible = 1";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $productosApi = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $validaciones = [
                'productos_csv' => count($productosCsv),
                'productos_api' => count($productosApi),
                'coincidencias' => 0,
                'discrepancias' => [],
                'productos_nuevos' => []
            ];
            
            foreach ($productosCsv as $productoCsv) {
                $encontrado = false;
                
                foreach ($productosApi as $productoApi) {
                    if (strtolower($productoCsv['producto']) === strtolower($productoApi['titulo'])) {
                        $validaciones['coincidencias']++;
                        $encontrado = true;
                        
                        // Verificar discrepancias de precio
                        $diferenciaPrecio = abs($productoCsv['valor_unitario'] - $productoApi['precio_base']);
                        if ($diferenciaPrecio > ($productoApi['precio_base'] * 0.1)) { // 10% de diferencia
                            $validaciones['discrepancias'][] = [
                                'producto' => $productoCsv['producto'],
                                'precio_csv' => $productoCsv['valor_unitario'],
                                'precio_api' => $productoApi['precio_base'],
                                'diferencia' => $diferenciaPrecio
                            ];
                        }
                        break;
                    }
                }
                
                if (!$encontrado) {
                    $validaciones['productos_nuevos'][] = $productoCsv['producto'];
                }
            }
            
            return $validaciones;
            
        } catch (Exception $e) {
            error_log("Error en ApiService::validarDatos: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene productos de la API con filtros
     */
    public function obtenerProductosFiltrados($categoria = null, $precioMin = null, $precioMax = null, $limit = 50) {
        try {
            $where = ["disponible = 1"];
            $params = [];
            
            if ($categoria) {
                $where[] = "categoria = ?";
                $params[] = $categoria;
            }
            
            if ($precioMin) {
                $where[] = "precio_base >= ?";
                $params[] = $precioMin;
            }
            
            if ($precioMax) {
                $where[] = "precio_base <= ?";
                $params[] = $precioMax;
            }
            
            $whereClause = "WHERE " . implode(" AND ", $where);
            
            // Validar que el límite sea un número entero positivo
            $limit = (int)$limit;
            if ($limit <= 0) {
                $limit = 50;
            }
            
            $sql = "SELECT * FROM productos_api 
                    $whereClause 
                    ORDER BY fecha_sincronizacion DESC 
                    LIMIT " . $limit;
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error en ApiService::obtenerProductosFiltrados: " . $e->getMessage());
            return false;
        }
    }
}
