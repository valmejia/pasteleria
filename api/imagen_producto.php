<?php
// No usar session_start() para imágenes - elimina cualquier output previo
ob_clean();
error_reporting(0);
ini_set('display_errors', 0);

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
            $tipo = $imagen['imagen_tipo'];
            if(empty($tipo)) {
                // Detectar tipo por los primeros bytes
                $bytes = substr($imagen['imagen'], 0, 4);
                if ($bytes === "\xFF\xD8\xFF") $tipo = 'image/jpeg';
                elseif (substr($bytes, 0, 3) === "\x89PNG") $tipo = 'image/png';
                elseif (substr($bytes, 0, 2) === "GI") $tipo = 'image/gif';
                else $tipo = 'image/jpeg';
            }
            
            header("Content-Type: " . $tipo);
            header("Content-Length: " . strlen($imagen['imagen']));
            header("Cache-Control: public, max-age=86400");
            echo $imagen['imagen'];
            exit();
        }
    } catch(Exception $e) {
        // Error silencioso
    }
}

// Placeholder en base64 (no depende de archivos externos)
$placeholder = 'iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAACZSURBVHgB7daxDYAgEEDRtwQWwAI0NEwAExGNrY2tDQPQMIGxhXUS5Ih3yRX/i+uPpMEGg7vjH4M5ERERERERERHxVmW1zpKqkqoytS2laaXHutZVUlVSVWkB6UvvbwGL+ycKkd4LpC+9v3HPEdnWuRF+vgciIiIiIiIiIuKt8gK9nM8ur/fKDgAAAABJRU5ErkJggg==';
header("Content-Type: image/png");
header("Content-Length: " . strlen(base64_decode($placeholder)));
echo base64_decode($placeholder);
exit();
?>