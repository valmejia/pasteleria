<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();

// Verificar si es admin
if(!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'admin') {
    echo json_encode(["success" => false, "message" => "Acceso denegado"]);
    exit();
}

require_once __DIR__ . '/../db/models/Producto.php';

$productoModel = new Producto();
$data = $_POST;

// Procesar imagen
$imagenBinaria = null;
$imagenTipo = null;

// Verificar si se debe eliminar la imagen actual
$eliminarImagen = isset($data['eliminar_imagen']) && $data['eliminar_imagen'] == '1';

// Procesar nueva imagen si se subió
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK && !$eliminarImagen) {
    $archivo = $_FILES['imagen'];
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    
    // Validar extensión
    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($extension, $extensionesPermitidas)) {
        echo json_encode(["success" => false, "message" => "Formato de imagen no permitido"]);
        exit();
    }
    
    // Validar tamaño (5MB máximo)
    if ($archivo['size'] > 5 * 1024 * 1024) {
        echo json_encode(["success" => false, "message" => "La imagen no debe superar los 5MB"]);
        exit();
    }
    
    // Leer el contenido binario de la imagen
    $imagenBinaria = file_get_contents($archivo['tmp_name']);
    $imagenTipo = $archivo['type'];
}

// Preparar datos del producto
$producto = [
    'id' => $data['id'] ?? '',
    'sku' => $data['sku'],
    'nombre' => $data['nombre'],
    'descripcion' => $data['descripcion'],
    'categoria' => $data['categoria'],
    'precio' => floatval($data['precio']),
    'costo' => !empty($data['costo']) ? floatval($data['costo']) : null,
    'stock' => intval($data['stock']),
    'visibilidad' => $data['visibilidad'],
    'porcion_tamano' => $data['porcion_tamano'],
    'tiempo_preparacion' => $data['tiempo_preparacion'] ?? '',
    'alergenos' => $data['alergenos'] ?? '',
    'personalizable' => intval($data['personalizable'] ?? 0),
    'temporada_edicion' => $data['temporada_edicion'] ?? ''
];

// Si hay nueva imagen, agregarla al array
if($imagenBinaria !== null) {
    $producto['imagen'] = $imagenBinaria;
    $producto['imagen_tipo'] = $imagenTipo;
} elseif($eliminarImagen) {
    // Si se marcó eliminar imagen, enviar null para que se borre de la BD
    $producto['imagen'] = null;
    $producto['imagen_tipo'] = null;
}

// Guardar en la base de datos
$method = !empty($producto['id']) ? 'PUT' : 'POST';

if ($method === 'POST') {
    // Asegurar que hay imagen en nuevo producto
    if($imagenBinaria === null) {
        echo json_encode(["success" => false, "message" => "Debes seleccionar una imagen para el producto"]);
        exit();
    }
    $result = $productoModel->create($producto);
} else {
    $result = $productoModel->update($producto['id'], $producto);
}

if ($result) {
    echo json_encode(["success" => true, "message" => "Producto guardado exitosamente"]);
} else {
    echo json_encode(["success" => false, "message" => "Error al guardar el producto"]);
}
?>