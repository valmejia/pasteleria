<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'admin') {
    header("Location: ../../../index.php");
    exit();
}

require_once '../../../db/models/User.php';
$userModel = new User();

// Obtener usuario si es edición
$usuario = null;
$isEdit = false;
if(isset($_GET['id'])) {
    $isEdit = true;
    $usuario = $userModel->getById($_GET['id']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? 'Editar' : 'Agregar'; ?> Usuario - Pastelería</title>
    <link rel="stylesheet" href="../../../assets/css/sidebar.css">
    <link rel="stylesheet" href="../../../assets/css/dashboard.css">
    <link rel="stylesheet" href="../../../assets/css/usuarios.css">
</head>
<body>
    <div class="admin-layout">
        <?php include_once '../../components/sidebar.php'; ?>
        <div class="sidebar-overlay" onclick="closeSidebar()"></div>

        <main class="main-content">
            <div class="page-header">
                <h1>👥 Gestión de Usuarios</h1>
                <a href="usuarios_lista.php" class="btn-back-list">← Volver a la lista</a>
            </div>

            <div class="form-container">
                <form id="usuarioForm" class="usuario-form">
                    <input type="hidden" id="usuarioId" value="<?php echo $isEdit ? $usuario['id'] : ''; ?>">
                    
                    <div class="form-row">
                        <div class="form-group required">
                            <label>Nombre completo</label>
                            <input type="text" id="nombre" required placeholder="Ej: Juan Pérez" value="<?php echo $isEdit ? htmlspecialchars($usuario['nombre']) : ''; ?>">
                        </div>
                        <div class="form-group required">
                            <label>Email</label>
                            <input type="email" id="email" required placeholder="ejemplo@correo.com" value="<?php echo $isEdit ? htmlspecialchars($usuario['email']) : ''; ?>">
                            <span class="help-text">El email será único en el sistema</span>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group <?php echo $isEdit ? '' : 'required'; ?>">
                            <label><?php echo $isEdit ? 'Nueva contraseña (opcional)' : 'Contraseña'; ?></label>
                            <input type="password" id="password" <?php echo $isEdit ? '' : 'required'; ?> placeholder="Mínimo 6 caracteres">
                            <?php if($isEdit): ?>
                                <span class="help-text">Dejar en blanco para mantener la contraseña actual</span>
                            <?php endif; ?>
                        </div>
                        <div class="form-group required">
                            <label>Rol</label>
                            <select id="rol" required>
                                <option value="usuario" <?php echo ($isEdit && $usuario['rol'] == 'usuario') ? 'selected' : ''; ?>>👤 Usuario</option>
                                <option value="admin" <?php echo ($isEdit && $usuario['rol'] == 'admin') ? 'selected' : ''; ?>>👑 Administrador</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-buttons">
                        <button type="submit" class="btn-submit">💾 Guardar Usuario</button>
                        <a href="usuarios_lista.php" class="btn-cancel">Cancelar</a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        document.getElementById('usuarioForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const usuarioId = document.getElementById('usuarioId').value;
            const formData = new FormData();
            
            formData.append('id', usuarioId);
            formData.append('nombre', document.getElementById('nombre').value);
            formData.append('email', document.getElementById('email').value);
            formData.append('password', document.getElementById('password').value);
            formData.append('rol', document.getElementById('rol').value);
            
            const submitBtn = document.querySelector('.btn-submit');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '💾 Guardando...';
            submitBtn.disabled = true;
            
            try {
                const response = await fetch('../../../api/usuarios_api.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if(data.success) {
                    alert('✅ ' + data.message);
                    window.location.href = 'usuarios_lista.php';
                } else {
                    alert('❌ Error: ' + data.message);
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            } catch(error) {
                alert('❌ Error al guardar el usuario');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });
        
        function closeSidebar() {
            document.querySelector('.sidebar')?.classList.remove('open');
            document.querySelector('.sidebar-overlay')?.classList.remove('active');
        }
    </script>
</body>
</html>