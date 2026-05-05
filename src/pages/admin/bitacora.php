<?php
session_start();

// Solo admin puede acceder
if(!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'admin') {
    header("Location: ../../../index.php");
    exit();
}

require_once '../../../db/config/database.php';

$database = new Database();
$conn = $database->getConnection();

// Paginación
$pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$porPagina = 20;
$offset = ($pagina - 1) * $porPagina;

// Filtros
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$filtro_usuario = isset($_GET['usuario']) ? $_GET['usuario'] : '';
$filtro_fecha = isset($_GET['fecha']) ? $_GET['fecha'] : '';

// Construir consulta con filtros
$where = [];
$params = [];

if($filtro_estado && $filtro_estado != '') {
    $where[] = "b.estado = :estado";
    $params[':estado'] = $filtro_estado;
}

if($filtro_usuario && $filtro_usuario != '') {
    $where[] = "(b.usuario_email LIKE :usuario OR b.usuario_nombre LIKE :usuario)";
    $params[':usuario'] = "%{$filtro_usuario}%";
}

if($filtro_fecha && $filtro_fecha != '') {
    $where[] = "DATE(b.fecha) = :fecha";
    $params[':fecha'] = $filtro_fecha;
}

$whereSQL = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

// Consulta principal
$query = "SELECT b.*, 
          DATE_FORMAT(b.fecha, '%d/%m/%Y') as fecha_formateada,
          TIME_FORMAT(b.fecha, '%H:%i:%s') as hora_formateada
          FROM bitacora_sesiones b
          {$whereSQL}
          ORDER BY b.fecha DESC
          LIMIT {$offset}, {$porPagina}";

$stmt = $conn->prepare($query);
foreach($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Contar total para paginación
$countQuery = "SELECT COUNT(*) as total FROM bitacora_sesiones b {$whereSQL}";
$stmtCount = $conn->prepare($countQuery);
foreach($params as $key => $value) {
    $stmtCount->bindValue($key, $value);
}
$stmtCount->execute();
$total = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
$totalPaginas = ceil($total / $porPagina);

// Estadísticas
$statsQuery = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN estado = 'exitoso' THEN 1 ELSE 0 END) as exitosos,
                SUM(CASE WHEN estado = 'fallido' THEN 1 ELSE 0 END) as fallidos
               FROM bitacora_sesiones";
$statsStmt = $conn->query($statsQuery);
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bitácora de Sesiones - Pastelería</title>
    <link rel="stylesheet" href="../../../assets/css/sidebar.css">
    <link rel="stylesheet" href="../../../assets/css/dashboard.css">
    <link rel="stylesheet" href="../../../assets/css/bitacora.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar component -->
        <?php include_once '../../components/sidebar.php'; ?>
        <div class="sidebar-overlay" onclick="closeSidebar()"></div>

        <main class="main-content">

            <div class="bitacora-container">
                <h1>📋 Bitácora de Sesiones</h1>
                <p>Registro de todos los intentos de inicio de sesión</p>

                <!-- Tarjetas de estadísticas -->
                <div class="stats-cards">
                    <div class="stat-card-bitacora">
                        <h3>Total de intentos</h3>
                        <div class="number"><?php echo number_format($stats['total']); ?></div>
                    </div>
                    <div class="stat-card-bitacora exitoso">
                        <h3>Inicios exitosos</h3>
                        <div class="number"><?php echo number_format($stats['exitosos']); ?></div>
                    </div>
                    <div class="stat-card-bitacora fallido">
                        <h3>Intentos fallidos</h3>
                        <div class="number"><?php echo number_format($stats['fallidos']); ?></div>
                    </div>
                    <div class="stat-card-bitacora">
                        <h3>Tasa de éxito</h3>
                        <div class="number">
                            <?php 
                            $tasa = $stats['total'] > 0 ? round(($stats['exitosos'] / $stats['total']) * 100) : 0;
                            echo $tasa . '%';
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <form method="GET" class="filtros">
                    <div class="filtro-group">
                        <label>Estado</label>
                        <select name="estado">
                            <option value="">Todos</option>
                            <option value="exitoso" <?php echo $filtro_estado == 'exitoso' ? 'selected' : ''; ?>>✅ Exitoso</option>
                            <option value="fallido" <?php echo $filtro_estado == 'fallido' ? 'selected' : ''; ?>>❌ Fallido</option>
                        </select>
                    </div>
                    <div class="filtro-group">
                        <label>Usuario</label>
                        <input type="text" name="usuario" value="<?php echo htmlspecialchars($filtro_usuario); ?>" placeholder="Nombre o email...">
                    </div>
                    <div class="filtro-group">
                        <label>Fecha</label>
                        <input type="date" name="fecha" value="<?php echo $filtro_fecha; ?>">
                    </div>
                    <div class="filtro-group">
                        <button type="submit" class="btn-filtrar">🔍 Filtrar</button>
                    </div>
                    <div class="filtro-group">
                        <a href="bitacora.php" class="btn-limpiar">🗑️ Limpiar</a>
                    </div>
                </form>

                <!-- Tabla de registros -->
                <div class="tabla-bitacora">
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Usuario</th>
                                    <th>Email</th>
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                    <th>Estado</th>
                                    <th>Mensaje</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(count($registros) > 0): ?>
                                    <?php foreach($registros as $row): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['usuario_nombre'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($row['usuario_email'] ?? '-'); ?></td>
                                        <td><?php echo $row['fecha_formateada']; ?></td>
                                        <td><?php echo $row['hora_formateada']; ?></td>
                                        <td>
                                            <span class="estado-badge estado-<?php echo $row['estado']; ?>">
                                                <?php echo $row['estado'] == 'exitoso' ? '✅ Exitoso' : '❌ Fallido'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['mensaje'] ?? '-'); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr class="empty-row">
                                        <td colspan="8" style="text-align: center; padding: 40px;">
                                            No hay registros en la bitácora
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Paginación -->
                <?php if($totalPaginas > 1): ?>
                <div class="paginacion">
                    <?php if($pagina > 1): ?>
                        <a href="?pagina=<?php echo $pagina-1; ?>&estado=<?php echo urlencode($filtro_estado); ?>&usuario=<?php echo urlencode($filtro_usuario); ?>&fecha=<?php echo urlencode($filtro_fecha); ?>">← Anterior</a>
                    <?php endif; ?>
                    
                    <?php for($i = 1; $i <= $totalPaginas; $i++): ?>
                        <?php if($i == $pagina): ?>
                            <span class="active"><?php echo $i; ?></span>
                        <?php elseif($i <= 5 || $i > $totalPaginas - 2 || ($i >= $pagina - 2 && $i <= $pagina + 2)): ?>
                            <a href="?pagina=<?php echo $i; ?>&estado=<?php echo urlencode($filtro_estado); ?>&usuario=<?php echo urlencode($filtro_usuario); ?>&fecha=<?php echo urlencode($filtro_fecha); ?>"><?php echo $i; ?></a>
                        <?php elseif($i == 6 && $pagina > 5): ?>
                            <span>...</span>
                        <?php elseif($i == $totalPaginas - 2 && $pagina < $totalPaginas - 3): ?>
                            <span>...</span>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if($pagina < $totalPaginas): ?>
                        <a href="?pagina=<?php echo $pagina+1; ?>&estado=<?php echo urlencode($filtro_estado); ?>&usuario=<?php echo urlencode($filtro_usuario); ?>&fecha=<?php echo urlencode($filtro_fecha); ?>">Siguiente →</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
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
                });
            }
        }
        
        function closeSidebar() {
            document.querySelector('.sidebar')?.classList.remove('open');
            document.querySelector('.sidebar-overlay')?.classList.remove('active');
        }
    </script>
</body>
</html>