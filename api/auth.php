<?php
date_default_timezone_set('America/Mexico_City');
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
require_once __DIR__ . '/../db/config/database.php';
require_once __DIR__ . '/../db/models/User.php';

// Función para registrar en bitácora
function registrarBitacora($conn, $usuario_id, $usuario_email, $usuario_nombre, $estado, $mensaje = null) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido';
    $fecha = date('Y-m-d H:i:s');
    
    $stmt = $conn->prepare("INSERT INTO bitacora_sesiones (usuario_id, usuario_email, usuario_nombre, ip_address, user_agent, fecha, estado, mensaje) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$usuario_id, $usuario_email, $usuario_nombre, $ip, $user_agent, $fecha, $estado, $mensaje]);
}

// Logout por GET
if(isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../index.php");
    exit();
}

$database = new Database();
$conn = $database->getConnection();
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
            
            // Registrar login exitoso
            registrarBitacora($conn, $result['user']['id'], $result['user']['email'], $result['user']['nombre'], 'exitoso', 'Inicio de sesión correcto');
        } else {
            // Registrar login fallido
            registrarBitacora($conn, null, $data['email'], null, 'fallido', $result['message']);
        }
        
        echo json_encode($result);
        break;
        
    case 'register':
        $result = $userModel->register($data['nombre'], $data['email'], $data['password']);
        echo json_encode($result);
        break;
        
    case 'logout':
        // Registrar cierre de sesión si hay usuario logueado
        if(isset($_SESSION['user_id'])) {
            registrarBitacora($conn, $_SESSION['user_id'], $_SESSION['user_email'], $_SESSION['user_nombre'], 'exitoso', 'Cierre de sesión');
        }
        session_destroy();
        echo json_encode(["success" => true, "message" => "Sesión cerrada"]);
        break;
        
    default:
        echo json_encode(["success" => false, "message" => "Acción no válida"]);
        break;
}
?>