<?php
// Activar errores para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");  // ← IMPORTANTE

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();
require_once __DIR__ . '/../db/config/database.php';

// Verificar autenticación
if(!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Usuario no autenticado. Session ID: " . session_id()]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

if(!$data || !isset($data['items']) || empty($data['items'])) {
    echo json_encode(["success" => false, "message" => "Datos inválidos o carrito vacío"]);
    exit();
}

$database = new Database();
$conn = $database->getConnection();

try {
    $conn->beginTransaction();
    
    $subtotal = floatval($data['subtotal']);
    $envio = floatval($data['envio']);
    $total = floatval($data['total']);
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
        // Verificar stock
        $stmt = $conn->prepare("SELECT stock FROM productos WHERE id = ?");
        $stmt->execute([$item['id']]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if(!$producto) {
            throw new Exception("Producto no encontrado ID: " . $item['id']);
        }
        
        if($producto['stock'] < $item['cantidad']) {
            throw new Exception("Stock insuficiente para: " . $item['nombre']);
        }
        
        // Insertar detalle
        $stmt = $conn->prepare("INSERT INTO detalle_pedidos (pedido_id, producto_id, cantidad, precio) VALUES (?, ?, ?, ?)");
        $stmt->execute([$pedidoId, $item['id'], $item['cantidad'], $item['precio']]);
        
        // Actualizar stock
        $stmt = $conn->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");
        $stmt->execute([$item['cantidad'], $item['id']]);
    }
    
    $conn->commit();
    
    // Guardar pedido en sesión para confirmación
    $_SESSION['ultimo_pedido'] = [
        'id' => $pedidoId,
        'numero' => $numeroPedido,
        'total' => $total,
        'items' => $data['items']
    ];
    
    echo json_encode(["success" => true, "pedido_id" => $pedidoId, "numero_pedido" => $numeroPedido]);
    
} catch(Exception $e) {
    $conn->rollBack();
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>