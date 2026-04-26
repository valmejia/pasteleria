<?php
require_once __DIR__ . '/../config/database.php';

class Producto {
    private $conn;
    private $table = "productos";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAll($search = '') {
        $query = "SELECT id, sku, nombre, descripcion, categoria, precio, costo, stock, 
                         visibilidad, porcion_tamano, tiempo_preparacion, alergenos, 
                         personalizable, temporada_edicion,
                         CASE WHEN imagen IS NOT NULL THEN 1 ELSE 0 END as tiene_imagen
                  FROM " . $this->table;
        
        if(!empty($search)) {
            $query .= " WHERE nombre LIKE :search OR descripcion LIKE :search OR sku LIKE :search";
        }
        $query .= " ORDER BY id DESC";
        
        $stmt = $this->conn->prepare($query);
        if(!empty($search)) {
            $searchTerm = "%{$search}%";
            $stmt->bindParam(":search", $searchTerm);
        }
        $stmt->execute();
        
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Agregar URL de imagen para cada producto
        foreach($productos as &$producto) {
            if($producto['tiene_imagen']) {
                $producto['imagen_url'] = "api/imagen_producto.php?id=" . $producto['id'];
            } else {
                $producto['imagen_url'] = null;
            }
        }
        
        return $productos;
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getImagen($id) {
        $query = "SELECT imagen, imagen_tipo FROM " . $this->table . " WHERE id = :id AND imagen IS NOT NULL";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (sku, nombre, descripcion, categoria, precio, costo, stock, imagen, imagen_tipo,
                   visibilidad, porcion_tamano, tiempo_preparacion, alergenos, personalizable, temporada_edicion) 
                  VALUES 
                  (:sku, :nombre, :descripcion, :categoria, :precio, :costo, :stock, :imagen, :imagen_tipo,
                   :visibilidad, :porcion_tamano, :tiempo_preparacion, :alergenos, :personalizable, :temporada_edicion)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":sku", $data['sku']);
        $stmt->bindParam(":nombre", $data['nombre']);
        $stmt->bindParam(":descripcion", $data['descripcion']);
        $stmt->bindParam(":categoria", $data['categoria']);
        $stmt->bindParam(":precio", $data['precio']);
        $stmt->bindParam(":costo", $data['costo']);
        $stmt->bindParam(":stock", $data['stock']);
        
        // Manejar imagen (puede ser BLOB o null)
        if(isset($data['imagen']) && $data['imagen'] !== null) {
            $stmt->bindParam(":imagen", $data['imagen'], PDO::PARAM_LOB);
            $stmt->bindParam(":imagen_tipo", $data['imagen_tipo']);
        } else {
            $nullImagen = null;
            $nullTipo = null;
            $stmt->bindParam(":imagen", $nullImagen, PDO::PARAM_LOB);
            $stmt->bindParam(":imagen_tipo", $nullTipo);
        }
        
        $stmt->bindParam(":visibilidad", $data['visibilidad']);
        $stmt->bindParam(":porcion_tamano", $data['porcion_tamano']);
        $stmt->bindParam(":tiempo_preparacion", $data['tiempo_preparacion']);
        $stmt->bindParam(":alergenos", $data['alergenos']);
        $stmt->bindParam(":personalizable", $data['personalizable']);
        $stmt->bindParam(":temporada_edicion", $data['temporada_edicion']);
        
        return $stmt->execute();
    }

    public function update($id, $data) {
        // Construir query dinámicamente según si hay imagen o no
        $query = "UPDATE " . $this->table . " SET 
                  sku = :sku,
                  nombre = :nombre,
                  descripcion = :descripcion,
                  categoria = :categoria,
                  precio = :precio,
                  costo = :costo,
                  stock = :stock,
                  visibilidad = :visibilidad,
                  porcion_tamano = :porcion_tamano,
                  tiempo_preparacion = :tiempo_preparacion,
                  alergenos = :alergenos,
                  personalizable = :personalizable,
                  temporada_edicion = :temporada_edicion";
        
        // Si se proporciona nueva imagen, actualizarla
        if(isset($data['imagen']) && $data['imagen'] !== null) {
            $query .= ", imagen = :imagen, imagen_tipo = :imagen_tipo";
        } elseif(isset($data['imagen']) && $data['imagen'] === null) {
            // Si se envió null, eliminar la imagen
            $query .= ", imagen = NULL, imagen_tipo = NULL";
        }
        
        $query .= " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":sku", $data['sku']);
        $stmt->bindParam(":nombre", $data['nombre']);
        $stmt->bindParam(":descripcion", $data['descripcion']);
        $stmt->bindParam(":categoria", $data['categoria']);
        $stmt->bindParam(":precio", $data['precio']);
        $stmt->bindParam(":costo", $data['costo']);
        $stmt->bindParam(":stock", $data['stock']);
        $stmt->bindParam(":visibilidad", $data['visibilidad']);
        $stmt->bindParam(":porcion_tamano", $data['porcion_tamano']);
        $stmt->bindParam(":tiempo_preparacion", $data['tiempo_preparacion']);
        $stmt->bindParam(":alergenos", $data['alergenos']);
        $stmt->bindParam(":personalizable", $data['personalizable']);
        $stmt->bindParam(":temporada_edicion", $data['temporada_edicion']);
        
        if(isset($data['imagen']) && $data['imagen'] !== null) {
            $stmt->bindParam(":imagen", $data['imagen'], PDO::PARAM_LOB);
            $stmt->bindParam(":imagen_tipo", $data['imagen_tipo']);
        }
        
        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
}
?>