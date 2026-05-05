<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'admin') {
    header("Location: ../../../index.php");
    exit();
}

// Obtener producto si es edición
$producto = null;
$isEdit = false;
if(isset($_GET['id'])) {
    $isEdit = true;
    require_once '../../../db/models/Producto.php';
    $productoModel = new Producto();
    $producto = $productoModel->getById($_GET['id']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? 'Editar' : 'Agregar'; ?> Producto - Pastelería</title>
    <link rel="stylesheet" href="../../../assets/css/sidebar.css">
    <link rel="stylesheet" href="../../../assets/css/dashboard.css">
    <link rel="stylesheet" href="../../../assets/css/productos.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar component -->
        <?php include_once '../../components/sidebar.php'; ?>
        <div class="sidebar-overlay" onclick="closeSidebar()"></div>

        <main class="main-content">
             <h1>Productos</h1>

            <div class="form-container">

                <form id="productoForm" class="producto-form" enctype="multipart/form-data">
                    <input type="hidden" id="productoId" value="<?php echo $isEdit ? $producto['id'] : ''; ?>">
                    <input type="hidden" id="eliminarImagen" value="0">
                    
                    <div class="form-row">
                        <div class="form-group required">
                            <label>SKU</label>
                            <input type="text" id="sku" required placeholder="Ej: PST-001" value="<?php echo $isEdit ? htmlspecialchars($producto['sku']) : ''; ?>">
                            <span class="help-text">Código único del producto</span>
                        </div>
                        <div class="form-group required">
                            <label>Nombre</label>
                            <input type="text" id="nombre" required placeholder="Ej: Torta de Chocolate" value="<?php echo $isEdit ? htmlspecialchars($producto['nombre']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea id="descripcion" placeholder="Descripción detallada del producto..."><?php echo $isEdit ? htmlspecialchars($producto['descripcion']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group required">
                            <label>Categoría</label>
                            <select id="categoria" required>
                                <option value="pasteles" <?php echo ($isEdit && $producto['categoria'] == 'pasteles') ? 'selected' : ''; ?>>🍰 Pasteles</option>
                                <option value="galletas" <?php echo ($isEdit && $producto['categoria'] == 'galletas') ? 'selected' : ''; ?>>🍪 Galletas</option>
                                <option value="cupcakes" <?php echo ($isEdit && $producto['categoria'] == 'cupcakes') ? 'selected' : ''; ?>>🧁 Cupcakes</option>
                                <option value="postres_especiales" <?php echo ($isEdit && $producto['categoria'] == 'postres_especiales') ? 'selected' : ''; ?>>🎂 Postres Especiales</option>
                                <option value="panaderia_dulce" <?php echo ($isEdit && $producto['categoria'] == 'panaderia_dulce') ? 'selected' : ''; ?>>🥐 Panadería Dulce</option>
                            </select>
                        </div>
                        <div class="form-group required">
                            <label>Precio (MXN)</label>
                            <input type="number" id="precio" step="0.01" required placeholder="0.00" value="<?php echo $isEdit ? $producto['precio'] : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Costo (opcional)</label>
                            <input type="number" id="costo" step="0.01" placeholder="0.00" value="<?php echo $isEdit ? $producto['costo'] : ''; ?>">
                            <span class="help-text">Costo del producto para control de ganancia</span>
                        </div>
                        <div class="form-group required">
                            <label>Stock</label>
                            <input type="number" id="stock" required placeholder="0" value="<?php echo $isEdit ? $producto['stock'] : ''; ?>">
                        </div>
                    </div>
                    
                    <!-- Campo de subida de imagen -->
                    <div class="form-group">
                        <label>Imagen del Producto</label>
                        <input type="file" id="imagen" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                        <span class="help-text">Selecciona una imagen para el producto (JPG, PNG, GIF, WEBP - Máx. 5MB)</span>
                        
                        <div class="image-preview" id="imagePreview"></div>
                        
                        <?php if($isEdit && isset($producto['tiene_imagen']) && $producto['tiene_imagen']): ?>
                        <div class="current-image" id="currentImageDiv">
                            <label>📷 Imagen actual:</label><br>
                            <img src="../../../api/imagen_producto.php?id=<?php echo $producto['id']; ?>" alt="Imagen actual">
                            <div><button type="button" class="btn-remove-img" onclick="marcarEliminarImagen()">🗑️ Eliminar imagen actual</button></div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Visibilidad</label>
                            <select id="visibilidad">
                                <option value="publico" <?php echo ($isEdit && $producto['visibilidad'] == 'publico') ? 'selected' : ''; ?>>👁️ Público</option>
                                <option value="oculto" <?php echo ($isEdit && $producto['visibilidad'] == 'oculto') ? 'selected' : ''; ?>>👻 Oculto</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Porción/Tamaño</label>
                            <input type="text" id="porcion_tamano" placeholder="Ej: 8 porciones" value="<?php echo $isEdit ? htmlspecialchars($producto['porcion_tamano']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Tiempo de preparación</label>
                            <input type="text" id="tiempo_preparacion" placeholder="Ej: 2 días" value="<?php echo $isEdit ? htmlspecialchars($producto['tiempo_preparacion']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Personalizable</label>
                            <select id="personalizable">
                                <option value="0" <?php echo ($isEdit && !$producto['personalizable']) ? 'selected' : ''; ?>>No</option>
                                <option value="1" <?php echo ($isEdit && $producto['personalizable']) ? 'selected' : ''; ?>>Sí</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Alérgenos</label>
                        <input type="text" id="alergenos" placeholder="Ej: Gluten, Nueces, Lactosa" value="<?php echo $isEdit ? htmlspecialchars($producto['alergenos']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Temporada/Edición limitada</label>
                        <input type="text" id="temporada_edicion" placeholder="Ej: Primavera-Verano 2024" value="<?php echo $isEdit ? htmlspecialchars($producto['temporada_edicion']) : ''; ?>">
                    </div>
                    
                    <div class="form-buttons">
                        <button type="submit" class="btn-submit">💾 Guardar Producto</button>
                        <a href="dashboard.php" class="btn-cancel">Cancelar</a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        let nuevaImagenSeleccionada = false;
        
        document.getElementById('imagen')?.addEventListener('change', function(e) {
            const preview = document.getElementById('imagePreview');
            preview.innerHTML = '';
            
            const file = e.target.files[0];
            if(file) {
                nuevaImagenSeleccionada = true;
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'preview-item';
                    div.innerHTML = `
                        <img src="${e.target.result}" alt="Vista previa">
                        <button type="button" class="remove-image" onclick="this.parentElement.remove(); nuevaImagenSeleccionada = false;">×</button>
                    `;
                    preview.appendChild(div);
                };
                reader.readAsDataURL(file);
            }
        });
        
        function marcarEliminarImagen() {
            if(confirm('¿Eliminar la imagen actual?')) {
                document.getElementById('eliminarImagen').value = '1';
                const currentImageDiv = document.getElementById('currentImageDiv');
                if(currentImageDiv) {
                    currentImageDiv.style.display = 'none';
                }
            }
        }
        
        document.getElementById('productoForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const productoId = document.getElementById('productoId').value;
            const formData = new FormData();
            
            formData.append('id', productoId);
            formData.append('sku', document.getElementById('sku').value);
            formData.append('nombre', document.getElementById('nombre').value);
            formData.append('descripcion', document.getElementById('descripcion').value);
            formData.append('categoria', document.getElementById('categoria').value);
            formData.append('precio', document.getElementById('precio').value);
            formData.append('costo', document.getElementById('costo').value);
            formData.append('stock', document.getElementById('stock').value);
            formData.append('visibilidad', document.getElementById('visibilidad').value);
            formData.append('porcion_tamano', document.getElementById('porcion_tamano').value);
            formData.append('tiempo_preparacion', document.getElementById('tiempo_preparacion').value);
            formData.append('alergenos', document.getElementById('alergenos').value);
            formData.append('personalizable', document.getElementById('personalizable').value);
            formData.append('temporada_edicion', document.getElementById('temporada_edicion').value);
            formData.append('eliminar_imagen', document.getElementById('eliminarImagen').value);
            
            const imagenFile = document.getElementById('imagen').files[0];
            if(imagenFile) {
                formData.append('imagen', imagenFile);
            }
            
            const submitBtn = document.querySelector('.btn-submit');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '💾 Guardando...';
            submitBtn.disabled = true;
            
            try {
                const response = await fetch('../../../api/upload_producto.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if(data.success) {
                    alert('✅ ' + data.message);
                    window.location.href = 'dashboard.php';
                } else {
                    alert('❌ Error: ' + data.message);
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            } catch(error) {
                alert('❌ Error al guardar el producto');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });
        
        function cerrarSesion() {
            if(confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                fetch('../../../api/auth.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({action: 'logout'})
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        localStorage.removeItem('carrito');
                        window.location.href = '../../../index.php';
                    }
                });
            }
        }
        
        function closeSidebar() {
            document.querySelector('.sidebar')?.classList.remove('open');
            document.querySelector('.sidebar-overlay')?.classList.remove('active');
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const skuInput = document.getElementById('sku');
            skuInput?.addEventListener('input', function(e) {
                this.value = this.value.toUpperCase();
            });
            
            const precioInput = document.getElementById('precio');
            precioInput?.addEventListener('change', function() {
                if(this.value < 0) this.value = 0;
            });
            
            const stockInput = document.getElementById('stock');
            stockInput?.addEventListener('change', function() {
                if(this.value < 0) this.value = 0;
            });
        });
    </script>
</body>
</html>