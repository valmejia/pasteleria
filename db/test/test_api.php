<?php
$url = 'http://localhost/eslava/pasteleria/api/productos.php';
$response = file_get_contents($url);
$data = json_decode($response, true);

echo "<h1>Prueba de API</h1>";
echo "<pre>";
print_r($data);
echo "</pre>";

if(empty($data)) {
    echo "<p style='color:red'>No hay datos o error en la API</p>";
} else {
    echo "<p style='color:green'>✅ API funciona correctamente. " . count($data) . " productos encontrados.</p>";
}
?>