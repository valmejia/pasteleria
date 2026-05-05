<?php
// Descarga PHPMailer desde: https://github.com/PHPMailer/PHPMailer/archive/master.zip
// Coloca la carpeta en: C:\xampp\htdocs\eslava\pasteleria\vendor\PHPMailer

require_once __DIR__ . '/../vendor/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../vendor/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    private $mail;
    
    public function __construct() {
        $this->mail = new PHPMailer(true);
        
        // Configuración del servidor
        $this->mail->isSMTP();
        $this->mail->Host       = 'smtp.gmail.com';
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = 'valmejiagarcia@gmail.com';  // Cambia por tu email
        $this->mail->Password   = 'rbpe njpp eojb dxio';  // Contraseña de aplicación
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port       = 587;
        
        // Configuración del remitente
        $this->mail->setFrom('valmejiagarcia@gmail.com', 'Pastelería Dulces Momentos');
        $this->mail->isHTML(true);
        $this->mail->CharSet = 'UTF-8';
    }
    
    public function enviarTicket($para, $nombre, $pedidoId, $ticketHTML) {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($para, $nombre);
            $this->mail->Subject = "✅ Confirmación de pedido #{$pedidoId} - Pastelería";
            $this->mail->Body    = $ticketHTML;
            $this->mail->AltBody = strip_tags($ticketHTML);
            
            $this->mail->send();
            return ["success" => true, "message" => "Correo enviado a {$para}"];
        } catch(Exception $e) {
            return ["success" => false, "message" => "Error: {$this->mail->ErrorInfo}"];
        }
    }
}
?>