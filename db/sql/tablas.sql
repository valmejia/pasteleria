-- ============================================
-- BASE DE DATOS PARA PASTELERÍA
-- ============================================

-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS pasteleria;
USE pasteleria;

-- ============================================
-- TABLA: usuarios
-- ============================================
CREATE TABLE usuarios (
    id INT(11) NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'usuario') DEFAULT 'usuario',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================
-- TABLA: productos
-- ============================================
CREATE TABLE productos (
    id INT(11) NOT NULL AUTO_INCREMENT,
    sku VARCHAR(50) DEFAULT NULL,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT DEFAULT NULL,
    categoria ENUM('pasteles', 'galletas', 'cupcakes', 'postres_especiales', 'panaderia_dulce') NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    costo DECIMAL(10,2) DEFAULT NULL,
    stock INT(11) NOT NULL DEFAULT 0,
    imagen LONGBLOB DEFAULT NULL,
    visibilidad ENUM('publico', 'oculto') DEFAULT 'publico',
    porcion_tamano VARCHAR(50) DEFAULT NULL,
    tiempo_preparacion VARCHAR(100) DEFAULT NULL,
    alergenos TEXT DEFAULT NULL,
    personalizable TINYINT(1) DEFAULT 0,
    temporada_edicion VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    imagen_tipo VARCHAR(50) DEFAULT 'image/jpeg',
    destacado TINYINT(1) DEFAULT 0,
    vendidos INT(11) DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY sku (sku)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================
-- TABLA: pedidos
-- ============================================
CREATE TABLE pedidos (
    id INT(11) NOT NULL AUTO_INCREMENT,
    usuario_id INT(11) NOT NULL,
    numero_pedido VARCHAR(50) DEFAULT NULL,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    direccion TEXT NOT NULL,
    delivery_type ENUM('domicilio', 'pickup') DEFAULT 'domicilio',
    payment_method VARCHAR(50) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    envio DECIMAL(10,2) DEFAULT 0.00,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('pendiente', 'pagado', 'enviado', 'entregado', 'cancelado') DEFAULT 'pendiente',
    fecha DATETIME NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY usuario_id (usuario_id),
    KEY numero_pedido (numero_pedido),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================
-- TABLA: detalle_pedidos
-- ============================================
CREATE TABLE detalle_pedidos (
    id INT(11) NOT NULL AUTO_INCREMENT,
    pedido_id INT(11) NOT NULL,
    producto_id INT(11) NOT NULL,
    cantidad INT(11) NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (id),
    KEY pedido_id (pedido_id),
    KEY producto_id (producto_id),
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

