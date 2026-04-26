<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();
require_once __DIR__ . '/../db/config/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$isAdmin = isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'admin';

// Para GET - cualquiera puede ver productos (públicos para usuarios, todos para admin)
if($method == 'GET') {
    try {
        $database = new Database();
        $conn = $database->getConnection();
        
        if(!$conn) {
            echo json_encode(["error" => "Error de conexión a la base de datos"]);
            exit();
        }
        
        if(isset($_GET['id'])) {
            // Obtener un producto específico
            $id = intval($_GET['id']);
            
            if($isAdmin) {
                $query = "SELECT id, sku, nombre, descripcion, categoria, precio, costo, stock, 
                                 visibilidad, porcion_tamano, tiempo_preparacion, alergenos, 
                                 personalizable, temporada_edicion, destacado, vendidos,
                                 CASE WHEN imagen IS NOT NULL THEN 1 ELSE 0 END as tiene_imagen
                          FROM productos WHERE id = :id";
            } else {
                $query = "SELECT id, sku, nombre, descripcion, categoria, precio, stock, 
                                 visibilidad, porcion_tamano, tiempo_preparacion, alergenos, 
                                 personalizable, temporada_edicion,
                                 CASE WHEN imagen IS NOT NULL THEN 1 ELSE 0 END as tiene_imagen
                          FROM productos WHERE id = :id AND visibilidad = 'publico'";
            }
            $stmt = $conn->prepare($query);
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            $producto = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if($producto && $producto['tiene_imagen']) {
                $producto['imagen_url'] = "../../api/imagen_producto.php?id=" . $producto['id'];
            }
            
            echo json_encode($producto);
        } else {
            // Obtener todos los productos
            $search = isset($_GET['search']) ? $_GET['search'] : '';
            
            if($isAdmin) {
                $query = "SELECT id, sku, nombre, descripcion, categoria, precio, costo, stock, 
                                 visibilidad, porcion_tamano, tiempo_preparacion, alergenos, 
                                 personalizable, temporada_edicion, destacado, vendidos,
                                 CASE WHEN imagen IS NOT NULL THEN 1 ELSE 0 END as tiene_imagen
                          FROM productos";
            } else {
                $query = "SELECT id, sku, nombre, descripcion, categoria, precio, stock, 
                                 visibilidad, porcion_tamano, tiempo_preparacion, alergenos, 
                                 personalizable, temporada_edicion,
                                 CASE WHEN imagen IS NOT NULL THEN 1 ELSE 0 END as tiene_imagen
                          FROM productos WHERE visibilidad = 'publico'";
            }
            
            if(!empty($search)) {
                $query .= " AND (nombre LIKE :search OR descripcion LIKE :search OR sku LIKE :search)";
            }
            $query .= " ORDER BY id DESC";
            
            $stmt = $conn->prepare($query);
            if(!empty($search)) {
                $searchTerm = "%{$search}%";
                $stmt->bindParam(":search", $searchTerm);
            }
            $stmt->execute();
            
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Agregar URL de imagen para cada producto
            foreach($productos as &$producto) {
                if($producto['tiene_imagen']) {
                    $producto['imagen_url'] = "../../api/imagen_producto.php?id=" . $producto['id'];
                }
                // Asegurar valores por defecto
                $producto['descripcion'] = $producto['descripcion'] ?? '';
                $producto['sku'] = $producto['sku'] ?? '';
                $producto['porcion_tamano'] = $producto['porcion_tamano'] ?? 'Individual';
                $producto['tiempo_preparacion'] = $producto['tiempo_preparacion'] ?? '1 día';
                $producto['alergenos'] = $producto['alergenos'] ?? 'Ninguno';
            }
            
            echo json_encode($productos);
        }
        exit();
    } catch(PDOException $e) {
        echo json_encode(["error" => "Error en la base de datos: " . $e->getMessage()]);
        exit();
    } catch(Exception $e) {
        echo json_encode(["error" => "Error: " . $e->getMessage()]);
        exit();
    }
}

// Para POST, PUT, DELETE - requieren autenticación de admin
if(!$isAdmin) {
    echo json_encode(["success" => false, "message" => "Acceso denegado. Se requieren permisos de administrador."]);
    exit();
}

require_once __DIR__ . '/../db/models/Producto.php';
$productoModel = new Producto();

switch($method) {
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        if($productoModel->create($data)) {
            echo json_encode(['success' => true, 'message' => 'Producto creado exitosamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear producto']);
        }
        break;
        
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        if($productoModel->update($data['id'], $data)) {
            echo json_encode(['success' => true, 'message' => 'Producto actualizado exitosamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar producto']);
        }
        break;
        
    case 'DELETE':
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        if($id && $productoModel->delete($id)) {
            echo json_encode(['success' => true, 'message' => 'Producto eliminado exitosamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar producto']);
        }
        break;
        
    default:
        echo json_encode(["success" => false, "message" => "Método no permitido"]);
        break;
}
?>