<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $conn;
    private $table = "usuarios";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function register($nombre, $email, $password) {
        // Verificar si el email ya existe
        $checkQuery = "SELECT id FROM " . $this->table . " WHERE email = :email";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bindParam(":email", $email);
        $checkStmt->execute();
        
        if($checkStmt->rowCount() > 0) {
            return ["success" => false, "message" => "El email ya está registrado"];
        }
        
        // Registrar nuevo usuario (rol por defecto: 'usuario')
        $query = "INSERT INTO " . $this->table . " (nombre, email, password, rol) 
                  VALUES (:nombre, :email, :password, 'usuario')";
        
        $stmt = $this->conn->prepare($query);
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":password", $hashed_password);
        
        if($stmt->execute()) {
            return ["success" => true, "message" => "Usuario registrado exitosamente"];
        }
        
        return ["success" => false, "message" => "Error al registrar usuario"];
    }

    public function login($email, $password) {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if(password_verify($password, $user['password'])) {
                return [
                    "success" => true, 
                    "user" => [
                        "id" => $user['id'],
                        "nombre" => $user['nombre'],
                        "email" => $user['email'],
                        "rol" => $user['rol']
                    ]
                ];
            }
        }
        
        return ["success" => false, "message" => "Email o contraseña incorrectos"];
    }
    
    public function getUserById($id) {
        $query = "SELECT id, nombre, email, rol FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return null;
    }
}
?>