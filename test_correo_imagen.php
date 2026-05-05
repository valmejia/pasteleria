<?php
require_once 'vendor/PHPMailer/src/Exception.php';
require_once 'vendor/PHPMailer/src/PHPMailer.php';
require_once 'vendor/PHPMailer/src/SMTP.php';
require_once 'db/config/database.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

echo "<h1>Prueba de envío de correo con imagen</h1>";

// Obtener imagen de la BD
$database = new Database();
$conn = $database->getConnection();

$productoId = 1;
$stmt = $conn->prepare("SELECT imagen, imagen_tipo FROM productos WHERE id = ?");
$stmt->execute([$productoId]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$producto || empty($producto['imagen'])) {
    die("❌ El producto no tiene imagen en la base de datos");
}

$tipo = $producto['imagen_tipo'] ?? 'image/jpeg';
$base64 = base64_encode($producto['imagen']);
$imgSrc = "data:{$tipo};base64,{$base64}";

echo "<p>✅ Imagen convertida a base64. Longitud: " . strlen($base64) . " caracteres</p>";
echo "<img src='{$imgSrc}' style='width:100px;'>";

// Enviar correo
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'valmejiagarcia@gmail.com';
    $mail->Password   = 'rbpenjppeojbdxio';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';
    
    $mail->setFrom('valmejiagarcia@gmail.com', 'Pastelería');
    $mail->addAddress('valemegaestrellalectora@gmail.com', 'Cliente');
    
    $mail->isHTML(true);
    $mail->Subject = 'Prueba de imagen - Pastelería';
    $mail->Body = "
    <h2>🍰 Pastelería</h2>
    <div style='display:flex; align-items:center; gap:15px;'>
        <img src='{$imgSrc}' style='width:80px; height:80px; object-fit:cover; border-radius:10px;'>
        <div>
            <h3>Torta de Chocolate</h3>
            <p>Cantidad: 1 × $350.00</p>
        </div>
    </div>
    <p><strong>Total:</strong> $350.00</p>
    ";
    
    $mail->send();
    echo "<p style='color:green'>✅ Correo enviado correctamente. Revisa tu bandeja de entrada.</p>";
    
} catch(Exception $e) {
    echo "<p style='color:red'>❌ Error: {$mail->ErrorInfo}</p>";
}
?>