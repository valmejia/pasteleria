-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS pasteleria;
USE pasteleria;

-- Tabla de usuarios (cambiar rol 'vendedor' a 'usuario')
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'usuario') DEFAULT 'usuario',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertar usuario admin por defecto (contraseña: admin123)
INSERT INTO usuarios (nombre, email, password, rol) VALUES 
('Administrador', 'admin@pasteleria.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');



-- Tabla de productos
DROP TABLE IF EXISTS productos;
CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sku VARCHAR(50) UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    categoria ENUM('pasteles', 'galletas', 'cupcakes', 'postres_especiales', 'panaderia_dulce') NOT NULL,
    precio DECIMAL(10, 2) NOT NULL,
    costo DECIMAL(10, 2),
    stock INT NOT NULL DEFAULT 0,
    imagen VARCHAR(500),
    visibilidad ENUM('publico', 'oculto') DEFAULT 'publico',
    porcion_tamano VARCHAR(50),
    tiempo_preparacion VARCHAR(100),
    alergenos TEXT,
    personalizable BOOLEAN DEFAULT FALSE,
    temporada_edicion VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insertar productos de ejemplo
INSERT INTO productos (sku, nombre, descripcion, categoria, precio, costo, stock, imagen, visibilidad, porcion_tamano, tiempo_preparacion, alergenos, personalizable, temporada_edicion) VALUES
('PST-001', 'Torta de Chocolate', 'Deliciosa torta de chocolate con cobertura de ganache', 'pasteles', 350.00, 150.00, 10, 'https://via.placeholder.com/300x200?text=Torta+Chocolate', 'publico', '8 porciones', '2 días de anticipación', 'Gluten, Huevo, Lactosa', 1, 'Todo el año'),
('PST-002', 'Cupcake de Vainilla', 'Cupcake esponjoso con frosting de vainilla', 'cupcakes', 45.00, 15.00, 50, 'https://via.placeholder.com/300x200?text=Cupcake+Vainilla', 'publico', 'Individual', '1 día de anticipación', 'Gluten, Huevo, Lactosa', 1, 'Todo el año'),
('GLT-001', 'Galletas de Mantequilla', 'Galletas artesanales de mantequilla', 'galletas', 89.00, 30.00, 30, 'https://via.placeholder.com/300x200?text=Galletas', 'publico', '6 piezas', '1 día de anticipación', 'Gluten, Lactosa', 0, 'Todo el año'),
('PST-003', 'Cheesecake de Fresa', 'Cheesecake cremoso con topping de fresa', 'postres_especiales', 420.00, 180.00, 5, 'https://via.placeholder.com/300x200?text=Cheesecake', 'publico', '12 porciones', '2 días de anticipación', 'Gluten, Huevo, Lactosa', 1, 'Primavera-Verano');

-- Agregar campos adicionales a la tabla productos si no existen
ALTER TABLE productos ADD COLUMN IF NOT EXISTS destacado BOOLEAN DEFAULT FALSE;
ALTER TABLE productos ADD COLUMN IF NOT EXISTS vendidos INT DEFAULT 0;

USE pasteleria;

-- Tabla de pedidos
CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    direccion TEXT NOT NULL,
    delivery_type ENUM('domicilio', 'pickup') DEFAULT 'domicilio',
    payment_method VARCHAR(50) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    envio DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('pendiente', 'pagado', 'enviado', 'entregado', 'cancelado') DEFAULT 'pendiente',
    fecha DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Tabla de detalles de pedidos
CREATE TABLE IF NOT EXISTS detalle_pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id)
);


USE pasteleria;

-- Agregar columna numero_pedido si no existe
ALTER TABLE pedidos ADD COLUMN IF NOT EXISTS numero_pedido VARCHAR(50) UNIQUE;

-- Verificar estructura de la tabla
DESCRIBE pedidos;


USE pasteleria;

-- Crear tabla pedidos si no existe
CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    numero_pedido VARCHAR(50) UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    direccion TEXT NOT NULL,
    delivery_type ENUM('domicilio', 'pickup') DEFAULT 'domicilio',
    payment_method VARCHAR(50) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    envio DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('pendiente', 'pagado', 'enviado', 'entregado', 'cancelado') DEFAULT 'pendiente',
    fecha DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Crear tabla detalle_pedidos si no existe
CREATE TABLE IF NOT EXISTS detalle_pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
);

-- Verificar productos de ejemplo
SELECT id, nombre, stock FROM productos LIMIT 5;


USE pasteleria;

-- Crear tabla de bitácora de inicios de sesión
CREATE TABLE IF NOT EXISTS bitacora_sesiones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NULL,
    usuario_email VARCHAR(100),
    usuario_nombre VARCHAR(100),
    ip_address VARCHAR(45),
    user_agent TEXT,
    fecha DATETIME NOT NULL,
    estado ENUM('exitoso', 'fallido') DEFAULT 'fallido',
    mensaje VARCHAR(255),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Crear índice para búsquedas rápidas
CREATE INDEX idx_fecha ON bitacora_sesiones(fecha);
CREATE INDEX idx_estado ON bitacora_sesiones(estado);
CREATE INDEX idx_usuario ON bitacora_sesiones(usuario_id);