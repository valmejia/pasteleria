<?php
    session_start();
    require_once __DIR__ . '/../db/models/Producto.php';

    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if($id > 0) {
        $productoModel = new Producto();
        $imagen = $productoModel->getImagen($id);
        
        if($imagen && !empty($imagen['imagen'])) {
            header("Content-Type: " . $imagen['imagen_tipo']);
            header("Content-Length: " . strlen($imagen['imagen']));
            echo $imagen['imagen'];
            exit();
        }
    }

    header("Content-Type: image/png");
    readfile(__DIR__ . '/../assets/img/no-image.png');
?>