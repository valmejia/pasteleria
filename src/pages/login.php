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
    <title>Login - Pastelería</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>
<body>
    <div class="container">
        <div class="auth-box">
            <div class="logo">
                <h1>🍰 PASTELERÍA</h1>
                <p class="subtitle">Dulces momentos</p>
            </div>
            <h2>Bienvenido de vuelta</h2>
            
            <form id="loginForm">
                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <input type="email" id="email" name="email" required placeholder="tu@email.com">
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required placeholder="••••••">
                </div>
                
                <button type="submit" class="btn-primary">Ingresar</button>
            </form>
            
            <p class="auth-link">
                ¿No tienes cuenta? <a href="signup.php">Crear cuenta</a>
            </p>
            
            <div id="message" class="message"></div>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            const submitBtn = document.querySelector('.btn-primary');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Ingresando...';
            submitBtn.disabled = true;
            
            try {
                const response = await fetch('../../api/auth.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'login',
                        email: email,
                        password: password
                    })
                });
                
                const data = await response.json();
                
                if(data.success) {
                    showMessage('✓ Inicio de sesión exitoso', 'success');
                    setTimeout(() => {
                        window.location.href = '../../index.php';
                    }, 1500);
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