<?php
require_once 'db/models/User.php';

$userModel = new User();

echo "<h2>🔐 Prueba de Login</h2>";

// Probar login con admin
$email = "admin@pasteleria.com";
$password = "admin123";

echo "Intentando login con: $email / $password<br>";
$result = $userModel->login($email, $password);

echo "<pre>";
print_r($result);
echo "</pre>";

if($result['success']) {
    echo "✅ Login exitoso!<br>";
} else {
    echo "❌ Login fallido: " . $result['message'] . "<br>";
}

echo "<hr>";

// Probar login con vendedor
$email2 = "vendedor@pasteleria.com";
$password2 = "vendedor123";

echo "Intentando login con: $email2 / $password2<br>";
$result2 = $userModel->login($email2, $password2);

echo "<pre>";
print_r($result2);
echo "</pre>";

if($result2['success']) {
    echo "✅ Login exitoso!<br>";
} else {
    echo "❌ Login fallido: " . $result2['message'] . "<br>";
}
?>