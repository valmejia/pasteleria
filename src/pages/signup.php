<?php
session_start();
if(isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Pastelería</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>
<body>
    <div class="container">
        <div class="auth-box">
            <div class="logo">
                <h1>🍰 PASTELERÍA</h1>
                <p class="subtitle">Dulces momentos</p>
            </div>
            <h2>Crear cuenta nueva</h2>
            
            <form id="signupForm">
                <div class="form-group">
                    <label for="nombre">Nombre completo</label>
                    <input type="text" id="nombre" name="nombre" required placeholder="María González">
                </div>
                
                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <input type="email" id="email" name="email" required placeholder="maria@email.com">
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required placeholder="Mínimo 6 caracteres">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmar contraseña</label>
                    <input type="password" id="confirm_password" name="confirm_password" required placeholder="Repite tu contraseña">
                </div>
                
                <button type="submit" class="btn-primary">Registrarse</button>
            </form>
            
            <p class="auth-link">
                ¿Ya tienes cuenta? <a href="login.php">Iniciar sesión</a>
            </p>
            
            <div id="message" class="message"></div>
        </div>
    </div>

    <script>
        document.getElementById('signupForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const nombre = document.getElementById('nombre').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const confirm_password = document.getElementById('confirm_password').value;
            
            if(password !== confirm_password) {
                showMessage('✗ Las contraseñas no coinciden', 'error');
                return;
            }
            
            if(password.length < 6) {
                showMessage('✗ La contraseña debe tener al menos 6 caracteres', 'error');
                return;
            }
            
            if(nombre.trim().length < 3) {
                showMessage('✗ El nombre debe tener al menos 3 caracteres', 'error');
                return;
            }
            
            const submitBtn = document.querySelector('.btn-primary');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Registrando...';
            submitBtn.disabled = true;
            
            try {
                const response = await fetch('../../api/auth.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'register',
                        nombre: nombre,
                        email: email,
                        password: password
                    })
                });
                
                const data = await response.json();
                
                if(data.success) {
                    showMessage('✓ ' + data.message, 'success');
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 2000);
                } else {
                    showMessage('✗ ' + data.message, 'error');
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                }
            } catch(error) {
                showMessage('✗ Error al conectar con el servidor', 'error');
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }
        });
        
        function showMessage(message, type) {
            const messageDiv = document.getElementById('message');
            messageDiv.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
            setTimeout(() => {
                if(messageDiv.innerHTML.includes(message)) {
                    messageDiv.innerHTML = '';
                }
            }, 3000);
        }
    </script>
</body>
</html>