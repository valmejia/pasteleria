<?php
echo "<h1>Prueba de API de Pedidos</h1>";

// Probar conexión a la base de datos
try {
    $conn = new PDO("mysql:host=localhost;dbname=pasteleria", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color:green'>✅ Conexión a BD exitosa</p>";
    
    // Verificar tablas
    $tables = ['pedidos', 'detalle_pedidos', 'productos'];
    foreach($tables as $table) {
        $stmt = $conn->query("SHOW TABLES LIKE '$table'");
        if($stmt->rowCount() > 0) {
            echo "<p>✅ Tabla '$table' existe</p>";
        } else {
            echo "<p style='color:red'>❌ Tabla '$table' NO existe</p>";
        }
    }
    
    // Verificar productos con stock
    $stmt = $conn->query("SELECT id, nombre, stock FROM productos LIMIT 5");
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<h3>Productos disponibles:</h3>";
    echo "<pre>";
    print_r($productos);
    echo "</pre>";
    
} catch(PDOException $e) {
    echo "<p style='color:red'>❌ Error: " . $e->getMessage() . "</p>";
}

// Probar la API directamente
echo "<h3>Probando API de pedidos:</h3>";
$testData = [
    'usuario_id' => 1,
    'items' => [
        ['id' => 1, 'nombre' => 'Producto Test', 'cantidad' => 1, 'precio' => 100]
    ],
    'subtotal' => 100,
    'envio' => 89,
    'total' => 189,
    'delivery_type' => 'domicilio',
    'direccion' => 'Calle Test 123',
    'payment_method' => 'tarjeta',
    'nombre' => 'Test User',
    'email' => 'test@test.com',
    'telefono' => '1234567890'
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