<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'vendor/PHPMailer/src/Exception.php';
require_once 'vendor/PHPMailer/src/PHPMailer.php';
require_once 'vendor/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

echo "<h1>Prueba de envío de correo</h1>";

// Verificar que los archivos existen
echo "<h2>Verificando archivos:</h2>";
$files = [
    'vendor/PHPMailer/src/Exception.php',
    'vendor/PHPMailer/src/PHPMailer.php',
    'vendor/PHPMailer/src/SMTP.php'
];
foreach($files as $file) {
    if(file_exists($file)) {
        echo "<p style='color:green'>✅ $file existe</p>";
    } else {
        echo "<p style='color:red'>❌ $file NO existe</p>";
    }
}

$mail = new PHPMailer(true);

try {
    // Configuración del servidor SMTP
    $mail->SMTPDebug = SMTP::DEBUG_CONNECTION;  // Cambiado a DEBUG_CONNECTION
    $mail->Debugoutput = 'html';
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'valmejiagarcia@gmail.com';
    $mail->Password   = 'lchyoxrnukydawqu';  // Contraseña de aplicación
    // Cambia estas dos líneas:
$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;  // SSL
$mail->Port       = 465;
    $mail->CharSet    = 'UTF-8';
    
    // Remitente y destinatario
    $mail->setFrom('valmejiagarcia@gmail.com', 'Pastelería Dulces Momentos');
    $mail->addAddress('valemegaestrellalectora@gmail.com', 'Cliente');
    
    // Contenido
    $mail->isHTML(true);
    $mail->Subject = 'Prueba de correo - Pastelería';
    $mail->Body    = '<h2>🍰 Pastelería</h2><p>Este es un <strong>correo de prueba</strong> desde Pastelería.</p>';
    
    $mail->send();
    echo "<p style='color:green'>✅ Correo enviado correctamente a valemegaestrellalectora@gmail.com</p>";
    
} catch (Exception $e) {
    echo "<p style='color:red'>❌ Error: {$mail->ErrorInfo}</p>";
}
?>