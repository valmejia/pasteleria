<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();

if(!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Usuario no autenticado"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

if(!$data || !isset($data['email']) || !isset($data['ticket_html'])) {
    echo json_encode(["success" => false, "message" => "Datos incompletos"]);
    exit();
}

require_once __DIR__ . '/../db/config/database.php';

// Función para obtener imagen en base64
function obtenerImagenBase64($productoId) {
    try {
        $database = new Database();
        $conn = $database->getConnection();
        
        $stmt = $conn->prepare("SELECT imagen, imagen_tipo FROM productos WHERE id = ? AND imagen IS NOT NULL");
        $stmt->execute([$productoId]);
        $imagen = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($imagen && !empty($imagen['imagen'])) {
            $tipo = $imagen['imagen_tipo'] ?? 'image/jpeg';
            $base64 = base64_encode($imagen['imagen']);
            return "data:{$tipo};base64,{$base64}";
        }
    } catch(Exception $e) {
        error_log("Error: " . $e->getMessage());
    }
    return null;
}

$email = $data['email'];
$nombre = $data['nombre'] ?? 'Cliente';
$pedidoId = $data['pedido_id'] ?? 'N/A';
$ticketHTML = $data['ticket_html'];

// Extraer IDs de productos del ticket HTML
preg_match_all('/data-producto-id="(\d+)"/', $ticketHTML, $matches);
$productosIds = array_unique($matches[1]);

// Reemplazar cada imagen por su base64
foreach($productosIds as $id) {
    $imgBase64 = obtenerImagenBase64($id);
    if($imgBase64) {
        // Reemplazar la etiqueta img completa
        $ticketHTML = preg_replace(
            '/<img[^>]*data-producto-id="' . $id . '"[^>]*src=["\'][^"\']*["\'][^>]*>/i',
            '<img src="' . $imgBase64 . '" style="width: 60px; height: 60px; object-fit: cover; border-radius: 10px;" data-producto-id="' . $id . '">',
            $ticketHTML
        );
        // También reemplazar por si el atributo está en otro orden
        $ticketHTML = preg_replace(
            '/<img[^>]*src=["\'][^"\']*imagen_producto\.php\?id=' . $id . '[^"\']*["\'][^>]*>/i',
            '<img src="' . $imgBase64 . '" style="width: 60px; height: 60px; object-fit: cover; border-radius: 10px;">',
            $ticketHTML
        );
        error_log("✅ Imagen convertida a base64 para producto ID $id");
    } else {
        error_log("❌ No hay imagen para producto ID $id, usando placeholder");
        // Placeholder con emoji
        $placeholder = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 60 60"%3E%3Crect width="60" height="60" fill="%23f0f0f0"/%3E%3Ctext x="50%25" y="50%25" text-anchor="middle" dy=".3em" fill="%23999" font-size="30"%3E🍰%3C/text%3E%3C/svg%3E';
        $ticketHTML = preg_replace(
            '/<img[^>]*src=["\'][^"\']*imagen_producto\.php\?id=' . $id . '[^"\']*["\'][^>]*>/i',
            '<img src="' . $placeholder . '" style="width: 60px; height: 60px; object-fit: cover; border-radius: 10px;">',
            $ticketHTML
        );
    }
}

// Construir el HTML completo del ticket
$fullTicketHTML = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Ticket de Compra - Pastelería</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #6B3E6B 0%, #8B5E8B 100%); color: white; padding: 25px; text-align: center; }
        .header h2 { margin: 0; font-size: 1.8rem; }
        .content { padding: 25px; }
        .footer { text-align: center; padding: 15px; font-size: 12px; color: #999; background: #f9f5f9; }
        .producto-ticket { display: flex; align-items: center; gap: 15px; padding: 12px; border-bottom: 1px solid #eee; }
        .producto-ticket img { width: 60px; height: 60px; object-fit: cover; border-radius: 10px; }
        .producto-info-ticket { flex: 1; }
        .producto-info-ticket h4 { margin: 0 0 5px; color: #6B3E6B; }
        .ticket-totales { text-align: right; padding: 15px; background: #f9f5f9; border-radius: 10px; margin-top: 15px; }
        .total-grande { font-size: 1.3rem; font-weight: bold; color: #D4AF37; }
        .info-row { display: flex; justify-content: space-between; margin: 10px 0; flex-wrap: wrap; gap: 10px; }
        h3 { color: #6B3E6B; border-bottom: 2px solid #E8D5E8; padding-bottom: 5px; margin-bottom: 15px; }
        @media (max-width: 600px) {
            .producto-ticket { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>🍰 Pastelería</h2>
            <p>¡Hola ' . htmlspecialchars($nombre) . ', gracias por tu compra!</p>
        </div>
        <div class="content">
            ' . $ticketHTML . '
        </div>
        <div class="footer">
            <p>Pastelería - Dulces momentos desde 2020</p>
            <p>Av. Principal #123, Ciudad de México | Tel: 55 1234 5678</p>
            <p>Este es un correo automático, por favor no responder.</p>
        </div>
    </div>
</body>
</html>
';

// Incluir PHPMailer
require_once __DIR__ . '/../vendor/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../vendor/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
    
    $mail->setFrom('valmejiagarcia@gmail.com', 'Pastelería Dulces Momentos');
    $mail->addAddress($email, $nombre);
    
    $mail->isHTML(true);
    $mail->Subject = "✅ Confirmación de pedido #{$pedidoId} - Pastelería";
    $mail->Body    = $fullTicketHTML;
    $mail->AltBody = strip_tags($fullTicketHTML);
    
    $mail->send();
    echo json_encode(["success" => true, "message" => "Ticket enviado a {$email}"]);
    
} catch(Exception $e) {
    echo json_encode(["success" => false, "message" => "Error: {$mail->ErrorInfo}"]);
}
?>