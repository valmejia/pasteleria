<?php
// Determinar la página activa
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <h2>🍰 CakeAdmin</h2>
        <div class="user-info">
            <div class="user-avatar">👤</div>
            <div class="user-details">
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_nombre']); ?></span>
                <span class="user-role-badge"><?php echo $_SESSION['user_rol']; ?></span>
            </div>
        </div>
    </div>
    
    <ul class="sidebar-menu">
        <li class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            <a href="dashboard.php">
                <span class="icon">📊</span>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="<?php echo $current_page == 'productos.php' ? 'active' : ''; ?>">
            <a href="productos.php">
                <span class="icon">🍰</span>
                <span>Productos</span>
            </a>
        </li>
        
        <li class="<?php echo $current_page == 'usuarios_lista.php' || $current_page == 'usuarios.php' ? 'active' : ''; ?>">
            <a href="usuarios_lista.php">
                <span class="icon">👥</span>
                <span>Usuarios</span>
            </a>
        </li>

        <li class="<?php echo $current_page == 'bitacora.php' ? 'active' : ''; ?>">
            <a href="bitacora.php">
                <span class="icon">📋</span>
                <span>Bitácora</span>
            </a>
        </li>

    </ul>
    
    <div class="sidebar-footer">
        <button onclick="cerrarSesion()" class="sidebar-logout">
            <span class="icon">🚪</span>
            <span>Cerrar Sesión</span>
        </button>
    </div>
</aside>

<!-- Botón para móvil -->
<button class="menu-toggle" onclick="toggleSidebar()">☰</button>
<div class="sidebar-overlay" onclick="closeSidebar()"></div>

<script>
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    if(sidebar) sidebar.classList.toggle('open');
    if(overlay) overlay.classList.toggle('active');
}

function closeSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    if(sidebar) sidebar.classList.remove('open');
    if(overlay) overlay.classList.remove('active');
}

function cerrarSesion() {
    if(confirm('¿Estás seguro de que deseas cerrar sesión?')) {
        fetch('../../../api/auth.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            credentials: 'same-origin',
            body: JSON.stringify({action: 'logout'})
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                localStorage.removeItem('carrito');
                window.location.href = '../../../index.php';
            }
        })
        .catch(error => {
            window.location.href = '../../../index.php';
        });
    }
}

// Cerrar sidebar al hacer clic fuera en móvil
document.addEventListener('click', function(event) {
    const sidebar = document.querySelector('.sidebar');
    const toggleBtn = document.querySelector('.menu-toggle');
    const overlay = document.querySelector('.sidebar-overlay');
    
    if (window.innerWidth <= 768) {
        if (sidebar && !sidebar.contains(event.target) && !toggleBtn?.contains(event.target)) {
            sidebar.classList.remove('open');
            if(overlay) overlay.classList.remove('active');
        }
    }
});

// Cerrar sidebar al redimensionar la ventana
window.addEventListener('resize', function() {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    if (window.innerWidth > 768 && sidebar) {
        sidebar.classList.remove('open');
        if(overlay) overlay.classList.remove('active');
    }
});
</script>