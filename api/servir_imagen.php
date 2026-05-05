<?php
// No usar session_start() para evitar problemas con la caché
ob_clean();
error_reporting(0);

require_once __DIR__ . '/../db/config/database.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if($id > 0) {
    try {
        $database = new Database();
        $conn = $database->getConnection();
        
        $stmt = $conn->prepare("SELECT imagen, imagen_tipo FROM productos WHERE id = ? AND imagen IS NOT NULL");
        $stmt->execute([$id]);
        $imagen = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($imagen && !empty($imagen['imagen'])) {
            header("Content-Type: " . ($imagen['imagen_tipo'] ?? 'image/jpeg'));
            header("Content-Length: " . strlen($imagen['imagen']));
            header("Cache-Control: public, max-age=86400");
            echo $imagen['imagen'];
            exit();
        }
    } catch(Exception $e) {
        // Error silencioso
    }
}

// Placeholder con emoji en SVG
header("Content-Type: image/svg+xml");
echo '<svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 60 60"><rect width="60" height="60" fill="#f0f0f0"/><text x="30" y="38" text-anchor="middle" font-size="30" fill="#999">🍰</text></svg>';
exit();
?>