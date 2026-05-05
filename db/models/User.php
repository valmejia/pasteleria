<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $conn;
    private $table = "usuarios";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAll($search = '') {
        $query = "SELECT id, nombre, email, rol, created_at FROM " . $this->table;
        if(!empty($search)) {
            $query .= " WHERE nombre LIKE :search OR email LIKE :search";
        }
        $query .= " ORDER BY id DESC";
        
        $stmt = $this->conn->prepare($query);
        if(!empty($search)) {
            $searchTerm = "%{$search}%";
            $stmt->bindParam(":search", $searchTerm);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT id, nombre, email, rol, created_at FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($nombre, $email, $password, $rol) {
        // Verificar si el email ya existe
        $checkQuery = "SELECT id FROM " . $this->table . " WHERE email = :email";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bindParam(":email", $email);
        $checkStmt->execute();
        
        if($checkStmt->rowCount() > 0) {
            return ["success" => false, "message" => "El email ya está registrado"];
        }
        
        $query = "INSERT INTO " . $this->table . " (nombre, email, password, rol) 
                  VALUES (:nombre, :email, :password, :rol)";
        
        $stmt = $this->conn->prepare($query);
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":password", $hashed_password);
        $stmt->bindParam(":rol", $rol);
        
        if($stmt->execute()) {
            return ["success" => true, "message" => "Usuario creado exitosamente"];
        }
        
        return ["success" => false, "message" => "Error al crear usuario"];
    }

    public function update($id, $nombre, $email, $rol, $password = null) {
        if($password) {
            $query = "UPDATE " . $this->table . " SET nombre = :nombre, email = :email, rol = :rol, password = :password WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt->bindParam(":password", $hashed_password);
        } else {
            $query = "UPDATE " . $this->table . " SET nombre = :nombre, email = :email, rol = :rol WHERE id = :id";
            $stmt = $this->conn->prepare($query);
        }
        
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":rol", $rol);
        
        if($stmt->execute()) {
            return ["success" => true, "message" => "Usuario actualizado exitosamente"];
        }
        
        return ["success" => false, "message" => "Error al actualizar usuario"];
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function register($nombre, $email, $password) {
        $checkQuery = "SELECT id FROM " . $this->table . " WHERE email = :email";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bindParam(":email", $email);
        $checkStmt->execute();
        
        if($checkStmt->rowCount() > 0) {
            return ["success" => false, "message" => "El email ya está registrado"];
        }
        
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