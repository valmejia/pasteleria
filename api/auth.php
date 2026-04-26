<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();
require_once __DIR__ . '/../db/models/User.php';

// Logout por GET (redirección directa)
if(isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../index.php");
    exit();
}

$userModel = new User();
$data = json_decode(file_get_contents("php://input"), true);

if(!isset($data['action'])) {
    echo json_encode(["success" => false, "message" => "Acción no especificada"]);
    exit();
}

switch($data['action']) {
    case 'login':
        $result = $userModel->login($data['email'], $data['password']);
        
        if($result['success']) {
            $_SESSION['user_id'] = $result['user']['id'];
            $_SESSION['user_nombre'] = $result['user']['nombre'];
            $_SESSION['user_email'] = $result['user']['email'];
            $_SESSION['user_rol'] = $result['user']['rol'];
        }
        
        echo json_encode($result);
        break;
        
    case 'register':
        $result = $userModel->register($data['nombre'], $data['email'], $data['password']);
        echo json_encode($result);
        break;
        
    case 'logout':
        // Destruir la sesión completamente
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        echo json_encode(["success" => true, "message" => "Sesión cerrada"]);
        break;
        
    default:
        echo json_encode(["success" => false, "message" => "Acción no válida"]);
        break;
}
?>