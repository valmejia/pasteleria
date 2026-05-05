<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'admin') {
    header("Location: ../../../index.php");
    exit();
}

require_once '../../../db/models/User.php';
$userModel = new User();

$search = isset($_GET['search']) ? $_GET['search'] : '';
$usuarios = $userModel->getAll($search);

// Estadísticas
$total = count($usuarios);
$admins = count(array_filter($usuarios, function($u) { return $u['rol'] == 'admin'; }));
$usuarios_normales = $total - $admins;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios - Pastelería</title>
    <link rel="stylesheet" href="../../../assets/css/sidebar.css">
    <link rel="stylesheet" href="../../../assets/css/dashboard.css">
    <link rel="stylesheet" href="../../../assets/css/usuarios.css">
</head>
<body>
    <div class="admin-layout">
        <?php include_once '../../components/sidebar.php'; ?>
        <div class="sidebar-overlay" onclick="closeSidebar()"></div>

        <main class="main-content">
            <div class="usuarios-container">
                <div class="page-header">
                    <h1>👥 Usuarios</h1>
                    <a href="usuarios.php" class="btn-add">
                        <span>➕</span> Agregar Usuario
                    </a>
                </div>

                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-info">
                            <h3>Total Usuarios</h3>
                            <div class="number"><?php echo $total; ?></div>
                        </div>
                        <div class="stat-icon">👥</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <h3>Administradores</h3>
                            <div class="number"><?php echo $admins; ?></div>
                        </div>
                        <div class="stat-icon">👑</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <h3>Usuarios</h3>
                            <div class="number"><?php echo $usuarios_normales; ?></div>
                        </div>
                        <div class="stat-icon">👤</div>
                    </div>
                </div>

                <!-- Barra de búsqueda -->
                <div class="search-bar">
                    <div class="search-input-wrapper">
                        <span class="search-icon">🔍</span>
                        <input type="text" id="searchInput" placeholder="Buscar usuarios por nombre o email..." value="<?php echo htmlspecialchars($search); ?>">
                        <span class="clear-icon" id="clearSearchBtn" onclick="limpiarBusqueda()" style="display: <?php echo $search ? 'flex' : 'none'; ?>;">✕</span>
                    </div>
                    <button onclick="buscarUsuarios()" class="btn-search">Buscar</button>
                </div>

                <!-- Tabla de usuarios -->
                <div class="table-container">
                    <div class="table-responsive">
                        <table id="usuariosTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Rol</th>
                                    <th>Fecha Registro</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(count($usuarios) > 0): ?>
                                    <?php foreach($usuarios as $u): ?>
                                    <tr>
                                        <td><?php echo $u['id']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($u['nombre']); ?></strong>
                                            <?php if($u['id'] == $_SESSION['user_id']): ?>
                                                <span class="badge-actual">(Tú)</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                                        <td>
                                            <span class="badge-rol <?php echo $u['rol']; ?>">
                                                <?php echo $u['rol'] == 'admin' ? '👑 Administrador' : '👤 Usuario'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($u['created_at'])); ?></td>
                                        <td class="acciones">
                                            <a href="usuarios.php?id=<?php echo $u['id']; ?>" class="btn-edit">✏️ Editar</a>
                                            <?php if($u['id'] != $_SESSION['user_id']): ?>
                                                <button class="btn-delete" onclick="eliminarUsuario(<?php echo $u['id']; ?>, '<?php echo htmlspecialchars($u['nombre']); ?>')">🗑️ Eliminar</button>
                                            <?php else: ?>
                                                <span class="text-muted">(No puedes eliminarte)</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="empty-row">No hay usuarios registrados</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function buscarUsuarios() {
            const search = document.getElementById('searchInput').value.trim();
            window.location.href = 'usuarios_lista.php?search=' + encodeURIComponent(search);
        }
        
        function limpiarBusqueda() {
            window.location.href = 'usuarios_lista.php';
        }
        
        function eliminarUsuario(id, nombre) {
            if(confirm('¿Estás seguro de eliminar al usuario "' + nombre + '"? Esta acción no se puede deshacer.')) {
                fetch('../../../api/usuarios_api.php', {
                    method: 'DELETE',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({id: id})
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        alert('✅ Usuario eliminado');
                        location.reload();
                    } else {
                        alert('❌ Error: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('❌ Error al eliminar el usuario');
                });
            }
        }
        
        document.getElementById('searchInput')?.addEventListener('keypress', function(e) {
            if(e.key === 'Enter') buscarUsuarios();
        });
        
        document.getElementById('searchInput')?.addEventListener('input', function() {
            const clearIcon = document.getElementById('clearSearchBtn');
            if(clearIcon) {
                clearIcon.style.display = this.value.length > 0 ? 'flex' : 'none';
            }
        });
        
        function closeSidebar() {
            document.querySelector('.sidebar')?.classList.remove('open');
            document.querySelector('.sidebar-overlay')?.classList.remove('active');
        }
    </script>
</body>
</html>