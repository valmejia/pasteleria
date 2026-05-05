<?php
$url = 'http://localhost/eslava/pasteleria/api/productos.php';
$response = file_get_contents($url);
$productos = json_decode($response, true);

echo "<h1>Prueba de API de Productos</h1>";
echo "<h2>Total de productos: " . count($productos) . "</h2>";

if(count($productos) > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Precio</th><th>Stock</th><th>Categoría</th><th>Tiene Imagen</th><th>Visibilidad</th></tr>";
    foreach($productos as $p) {
        echo "<tr>";
        echo "<td>{$p['id']}</td>";
        echo "<td>{$p['nombre']}</td>";
        echo "<td>\${$p['precio']}</td>";
        echo "<td>{$p['stock']}</td>";
        echo "<td>{$p['categoria']}</td>";
        echo "<td>" . ($p['tiene_imagen'] ? '✅ Sí' : '❌ No') . "</td>";
        echo "<td>{$p['visibilidad']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:red'>No hay productos en la base de datos</p>";
}
?>