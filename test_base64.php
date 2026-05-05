<?php
require_once 'db/config/database.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 1;

$database = new Database();
$conn = $database->getConnection();

$stmt = $conn->prepare("SELECT id, nombre, imagen, imagen_tipo FROM productos WHERE id = ?");
$stmt->execute([$id]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<h1>Prueba de conversión a Base64</h1>";
echo "<p>Producto: {$producto['nombre']}</p>";

if($producto && !empty($producto['imagen'])) {
    $base64 = base64_encode($producto['imagen']);
    $tipo = $producto['imagen_tipo'] ?? 'image/jpeg';
    $imgSrc = "data:{$tipo};base64,{$base64}";
    
    echo "<h2>Imagen en Base64:</h2>";
    echo "<img src='{$imgSrc}' style='max-width: 200px; border: 1px solid #ccc; padding: 5px;'>";
    echo "<p>Longitud base64: " . strlen($base64) . " caracteres</p>";
} else {
    echo "<p>El producto no tiene imagen en la base de datos</p>";
}
?>