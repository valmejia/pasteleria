<?php
// Simular que no hay sesión de admin
session_start();
// Si hay sesión, la guardamos temporalmente
$tempSession = $_SESSION;
session_destroy();

echo "<h1>Prueba de API como Usuario Normal</h1>";

$url = 'http://localhost/eslava/pasteleria/api/productos.php';
$response = file_get_contents($url);
$productos = json_decode($response, true);

if(is_array($productos)) {
    echo "<p style='color:green'>✅ Total productos para usuarios: " . count($productos) . "</p>";
    
    if(count($productos) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Precio</th><th>Stock</th><th>Categoría</th><th>Visibilidad</th></tr>";
        foreach($productos as $p) {
            echo "<tr>";
            echo "<td>{$p['id']}</td>";
            echo "<td>{$p['nombre']}</td>";
            echo "<td>\${$p['precio']}</td>";
            echo "<td>{$p['stock']}</td>";
            echo "<td>{$p['categoria']}</td>";
            echo "<td>{$p['visibilidad']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color:orange'>⚠️ No hay productos públicos disponibles</p>";
    }
} else {
    echo "<p style='color:red'>❌ Error: " . htmlspecialchars($response) . "</p>";
}

// Restaurar sesión
if(isset($tempSession['user_id'])) {
    $_SESSION = $tempSession;
}
?>