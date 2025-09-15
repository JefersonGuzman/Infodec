-- Borrar base de datos si existe
DROP DATABASE IF EXISTS ventasplus;

-- Crear base de datos
CREATE DATABASE ventasplus;
USE ventasplus;

-- Tabla de vendedores 
CREATE TABLE vendedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE
);

-- Tabla principal de operaciones 
CREATE TABLE operaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL,
    vendedor_id INT NOT NULL,
    producto VARCHAR(100) NOT NULL,
    referencia VARCHAR(50),
    cantidad INT NOT NULL,
    valor_unitario INT NOT NULL,
    valor_vendido INT NOT NULL,
    impuesto INT,
    tipo_operacion ENUM('Venta','Devolución') NOT NULL,
    motivo VARCHAR(255) NULL,
    FOREIGN KEY (vendedor_id) REFERENCES vendedores(id)
);

-- Tabla de comisiones consolidadas
CREATE TABLE comisiones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vendedor_id INT NOT NULL,
    anio INT NOT NULL,
    mes INT NOT NULL,
    total_ventas DECIMAL(15,2) DEFAULT 0,
    total_devoluciones DECIMAL(15,2) DEFAULT 0,
    indice_devoluciones DECIMAL(5,2) DEFAULT 0,
    comision_base DECIMAL(15,2) DEFAULT 0,
    bono DECIMAL(15,2) DEFAULT 0,
    penalizacion DECIMAL(15,2) DEFAULT 0,
    comision_final DECIMAL(15,2) DEFAULT 0,
    FOREIGN KEY (vendedor_id) REFERENCES vendedores(id)
);

-- Tabla para almacenar productos sincronizados desde API externa
CREATE TABLE IF NOT EXISTS productos_api (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_api INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    precio_base DECIMAL(15,2) NOT NULL DEFAULT 0,
    categoria VARCHAR(100) NOT NULL DEFAULT 'General',
    disponible BOOLEAN NOT NULL DEFAULT TRUE,
    fecha_sincronizacion DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_id_api (id_api),
    INDEX idx_categoria (categoria),
    INDEX idx_precio (precio_base),
    INDEX idx_disponible (disponible),
    INDEX idx_fecha_sincronizacion (fecha_sincronizacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla para logs de sincronización
-- 'api', 'csv', 'validacion'
-- 'sincronizar', 'validar', 'error'
CREATE TABLE IF NOT EXISTS logs_sincronizacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(50) NOT NULL,
    accion VARCHAR(100) NOT NULL,
    mensaje TEXT,
    datos_json JSON,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_tipo (tipo),
    INDEX idx_fecha (fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;