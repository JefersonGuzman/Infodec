-- Crear base de datos
CREATE DATABASE IF NOT EXISTS ventasplus;
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
    impuesto INT
    tipo_operacion ENUM('Venta','Devolución') NOT NULL,
    motivo VARCHAR(255) NULL,
    FOREIGN KEY (vendedor_id) REFERENCES vendedores(id)
);

-- Tabla de comisiones consolidadas
CREATE TABLE comisiones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vendedor_id INT NOT NULL,
    mes YEAR(4) NOT NULL,
    mes_numero INT NOT NULL,
    total_ventas DECIMAL(15,2) DEFAULT 0,
    total_devoluciones DECIMAL(15,2) DEFAULT 0,
    indice_devoluciones DECIMAL(5,2) DEFAULT 0,
    comision_base DECIMAL(15,2) DEFAULT 0,
    bono DECIMAL(15,2) DEFAULT 0,
    penalizacion DECIMAL(15,2) DEFAULT 0,
    comision_final DECIMAL(15,2) DEFAULT 0,
    FOREIGN KEY (vendedor_id) REFERENCES vendedores(id)
);
