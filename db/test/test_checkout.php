<?php
session_start();
echo "<h1>Prueba de Checkout</h1>";

// Verificar sesión
echo "<h2>1. Sesión:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Verificar productos
echo "<h2>2. Productos en BD:</h2>";
require_once 'db/config/database.php';
$database = new Database();
$conn = $database->getConnection();
$stmt = $conn->query("SELECT id, nombre, stock FROM productos LIMIT 5");
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($productos);
echo "</pre>";

// Simular carrito
echo "<h2>3. Simular carrito:</h2>";
$carrito = [
    ['id' => 1, 'nombre' => 'Producto Test', 'cantidad' => 1, 'precio' => 100]
];
echo "<pre>";
print_r($carrito);
echo "</pre>";

// Probar API
echo "<h2>4. Probar API:</h2>";
$testData = [
    'usuario_id' => $_SESSION['user_id'] ?? 1,
    'items' => $carrito,
    'subtotal' => 100,
    'envio' => 89,
    'total' => 189,
    'delivery_type' => 'domicilio',
    'direccion' => 'Calle Test 123',
    'payment_method' => 'tarjeta',
    'nombre' => 'Test User',
    'email' => 'test@test.com',
    'telefono' => '5512345678'
];

$ch = curl_init('http://localhost/eslava/pasteleria/api/procesar_pedido.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p>HTTP Code: $httpCode</p>";
echo "<p>Respuesta: " . htmlspecialchars($response) . "</p>";
?>