<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();
require_once __DIR__ . '/../db/models/User.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'admin') {
    echo json_encode(["success" => false, "message" => "Acceso denegado"]);
    exit();
}

$userModel = new User();

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? $_POST['id'] : '';
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $rol = $_POST['rol'];
    
    if(empty($id)) {
        // Crear nuevo usuario
        if(empty($password)) {
            echo json_encode(["success" => false, "message" => "La contraseña es obligatoria"]);
            exit();
        }
        $result = $userModel->create($nombre, $email, $password, $rol);
        echo json_encode($result);
    } else {
        // Actualizar usuario existente
        $result = $userModel->update($id, $nombre, $email, $rol, $password ?: null);
        echo json_encode($result);
    }
} 
elseif($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data['id'] ?? 0;
    
    if($id == $_SESSION['user_id']) {
        echo json_encode(["success" => false, "message" => "No puedes eliminarte a ti mismo"]);
        exit();
    }
    
    if($userModel->delete($id)) {
        echo json_encode(["success" => true, "message" => "Usuario eliminado"]);
    } else {
        echo json_encode(["success" => false, "message" => "Error al eliminar usuario"]);
    }
}
?>