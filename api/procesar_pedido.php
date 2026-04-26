<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();
require_once __DIR__ . '/../db/config/database.php';

// Debug: Verificar sesión
error_log("Session ID: " . session_id());
error_log("Session data: " . print_r($_SESSION, true));

// Verificar autenticación
if(!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Usuario no autenticado. Por favor inicia sesión nuevamente."]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

if(!$data) {
    echo json_encode(["success" => false, "message" => "No se recibieron datos"]);
    exit();
}

if(!isset($data['items']) || empty($data['items'])) {
    echo json_encode(["success" => false, "message" => "El carrito está vacío"]);
    exit();
}

$database = new Database();
$conn = $database->getConnection();

try {
    $conn->beginTransaction();
    
    // Calcular totales
    $subtotal = floatval($data['subtotal']);
    $envio = floatval($data['envio']);
    $total = floatval($data['total']);
    
    // Generar número de pedido único
    $numeroPedido = 'PED-' . date('Ymd') . '-' . rand(1000, 9999);
    
    // Insertar pedido
    $sql = "INSERT INTO pedidos (usuario_id, numero_pedido, nombre, email, telefono, direccion, delivery_type, payment_method, subtotal, envio, total, status, fecha) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente', NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $_SESSION['user_id'],
        $numeroPedido,
        $data['nombre'],
        $data['email'],
        $data['telefono'],
        $data['direccion'],
        $data['delivery_type'],
        $data['payment_method'],
        $subtotal,
        $envio,
        $total
    ]);
    
    $pedidoId = $conn->lastInsertId();
    
    // Insertar detalles y actualizar stock
    foreach($data['items'] as $item) {
        // Verificar stock actual
        $stmt = $conn->prepare("SELECT stock FROM productos WHERE id = ?");
        $stmt->execute([$item['id']]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if(!$producto) {
            throw new Exception("Producto no encontrado ID: " . $item['id']);
        }
        
        if($producto['stock'] < $item['cantidad']) {
            throw new Exception("Stock insuficiente para: " . $item['nombre'] . ". Disponible: " . $producto['stock']);
        }
        
        // Insertar detalle
        $stmt = $conn->prepare("INSERT INTO detalle_pedidos (pedido_id, producto_id, cantidad, precio) VALUES (?, ?, ?, ?)");
        $stmt->execute([$pedidoId, $item['id'], $item['cantidad'], $item['precio']]);
        
        // Actualizar stock
        $stmt = $conn->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");
        $stmt->execute([$item['cantidad'], $item['id']]);
    }
    
    $conn->commit();
    
    // Limpiar carrito
    unset($_SESSION['carrito']);
    
    echo json_encode(["success" => true, "pedido_id" => $pedidoId, "numero_pedido" => $numeroPedido]);
    
} catch(Exception $e) {
    $conn->rollBack();
    error_log("Error en procesar_pedido: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>