<?php
require_once 'db/config/database.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 1;

$database = new Database();
$conn = $database->getConnection();

// Verificar si el producto tiene imagen
$stmt = $conn->prepare("SELECT id, nombre, 
                        CASE WHEN imagen IS NOT NULL THEN 1 ELSE 0 END as tiene_imagen,
                        LENGTH(imagen) as bytes
                        FROM productos WHERE id = ?");
$stmt->execute([$id]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<h1>Producto ID: $id</h1>";
echo "<p>Nombre: {$producto['nombre']}</p>";
echo "<p>Tiene imagen: " . ($producto['tiene_imagen'] ? '✅ SI' : '❌ NO') . "</p>";
echo "<p>Tamaño: " . number_format($producto['bytes']) . " bytes</p>";

if($producto['tiene_imagen']) {
    echo "<h3>Imagen desde API:</h3>";
    echo "<img src='api/imagen_producto.php?id=$id' style='max-width:200px; border:1px solid #ccc; padding:5px;' onerror=\"this.style.border='2px solid red'\">";
    
    echo "<h3>URL directa:</h3>";
    echo "<p><a href='api/imagen_producto.php?id=$id' target='_blank'>api/imagen_producto.php?id=$id</a></p>";
}
?>