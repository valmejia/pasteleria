<?php
session_start();

// Solo admin puede acceder
if(!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'admin') {
    header("Location: ../../../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Pastelería</title>
    <link rel="stylesheet" href="../../../assets/css/sidebar.css">
    <link rel="stylesheet" href="../../../assets/css/dashboard.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar component -->
        <?php include_once '../../components/sidebar.php'; ?>
        
        <!-- Overlay para móvil -->
        <div class="sidebar-overlay" onclick="closeSidebar()"></div>

        <main class="main-content">
            <!-- Top Navbar con búsqueda integrada -->
            <nav class="top-navbar">
            
                <!-- Barra de búsqueda -->
                <div class="navbar-search">
                    <div class="search-input-wrapper">
                        <span class="search-icon">🔍</span>
                        <input type="text" id="searchInput" placeholder="Buscar productos por nombre, SKU o categoría...">
                        <span class="clear-icon" id="clearSearchBtn" onclick="limpiarBusqueda()" style="display: none;">✕</span>
                    </div>
                    <button onclick="buscarProductos()" class="btn-search">Buscar</button>
                </div>
                
            
            </nav>

            <!-- Resultados de búsqueda -->
            <div id="searchResults" class="search-results-bar" style="display: none;">
                <div class="results-info">
                    <span>🔍</span>
                    <span>Mostrando <strong id="resultadosCount">0</strong> resultados para: <strong id="terminoBusqueda"></strong></span>
                </div>
                <button onclick="limpiarBusqueda()" class="clear-results-btn">✕ Limpiar búsqueda</button>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Total Productos</h3>
                        <div class="number" id="totalProductos">0</div>
                    </div>
                    <div class="stat-icon">🍰</div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Productos Activos</h3>
                        <div class="number" id="productosActivos">0</div>
                    </div>
                    <div class="stat-icon">👁️</div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Stock Total</h3>
                        <div class="number" id="stockTotal">0</div>
                    </div>
                    <div class="stat-icon">📦</div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Valor Inventario</h3>
                        <div class="number" id="valorInventario">$0</div>
                    </div>
                    <div class="stat-icon">💵</div>
                </div>
            </div>

            <!-- Products Table -->
            <div class="products-table-container">
                <div class="products-header">
                    <h3>Lista de Productos</h3>
                    <a href="productos.php" class="btn-add">
                        <span>➕</span> Agregar Producto
                    </a>
                </div>
                <div class="table-responsive">
                    <table id="productosTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Foto</th>
                                <th>SKU</th>
                                <th>Nombre</th>
                                <th>Categoría</th>
                                <th>Precio</th>
                                <th>Stock</th>
                                <th>Visibilidad</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="productosBody">
                            <tr><td colspan="9" class="loading">Cargando productos...</td</tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        let terminoBusquedaActual = '';
        
        document.addEventListener('DOMContentLoaded', function() {
            cargarProductos();
            
            var searchInput = document.getElementById('searchInput');
            var clearIcon = document.getElementById('clearSearchBtn');
            
            if(searchInput) {
                searchInput.addEventListener('input', function() {
                    if(clearIcon) {
                        clearIcon.style.display = this.value.length > 0 ? 'flex' : 'none';
                    }
                });
                
                searchInput.addEventListener('keypress', function(e) {
                    if(e.key === 'Enter') {
                        buscarProductos();
                    }
                });
            }
        });
        
        async function cargarProductos(search = '') {
            terminoBusquedaActual = search;
            
            try {
                var url = search ? '../../../api/productos.php?search=' + encodeURIComponent(search) : '../../../api/productos.php';
                var response = await fetch(url);
                var productos = await response.json();
                
                actualizarStats(productos);
                actualizarResultadosBusqueda(productos.length, search);
                
                var tbody = document.getElementById('productosBody');
                if(!tbody) return;
                
                if(productos.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="9" style="text-align: center;">No hay productos registrados</td></tr>';
                    return;
                }
                
                var html = '';
                for(var i = 0; i < productos.length; i++) {
                    var producto = productos[i];
                    var imagenUrl = 'https://via.placeholder.com/50x50?text=No+Image';
                    if(producto.tiene_imagen) {
                        imagenUrl = '../../../api/imagen_producto.php?id=' + producto.id;
                    }
                    
                    html += '<tr>';
                    html += '<td>' + producto.id + '</td>';
                    html += '<td><img src="' + imagenUrl + '" class="producto-imagen" onerror="this.src=\'https://via.placeholder.com/50x50?text=No+Image\'"></td>';
                    html += '<td><code>' + (producto.sku || '-') + '</code></td>';
                    html += '<td><strong>' + escapeHtml(producto.nombre) + '</strong><br><small style="color:#999;">' + (producto.descripcion ? producto.descripcion.substring(0, 40) : '') + (producto.descripcion && producto.descripcion.length > 40 ? '...' : '') + '</small></td>';
                    html += '<td>' + producto.categoria + '</td>';
                    html += '<td><strong>$' + producto.precio + '</strong></td>';
                    html += '<td><span class="badge ' + (producto.stock > 0 ? 'badge-stock' : 'badge-agotado') + '">' + (producto.stock > 0 ? producto.stock + ' uds' : 'Agotado') + '</span></td>';
                    html += '<td><span class="badge ' + (producto.visibilidad === 'publico' ? 'badge-publico' : 'badge-oculto') + '">' + (producto.visibilidad === 'publico' ? '👁️ Público' : '👻 Oculto') + '</span></td>';
                    html += '<td><a href="productos.php?id=' + producto.id + '" class="btn-edit">✏️ Editar</a> <button class="btn-delete" onclick="eliminarProducto(' + producto.id + ')">🗑️ Eliminar</button></td>';
                    html += '</tr>';
                }
                tbody.innerHTML = html;
                
            } catch(error) {
                console.error('Error:', error);
                var tbodyErr = document.getElementById('productosBody');
                if(tbodyErr) {
                    tbodyErr.innerHTML = '<tr><td colspan="9" style="text-align: center;">Error al cargar productos</td></tr>';
                }
            }
        }
        
        function escapeHtml(text) {
            if(!text) return '';
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        function actualizarStats(productos) {
            var total = productos.length;
            var activos = 0;
            var stockTotal = 0;
            var valorInventario = 0;
            
            for(var i = 0; i < productos.length; i++) {
                var p = productos[i];
                if(p.visibilidad === 'publico') activos++;
                stockTotal += p.stock;
                valorInventario += (p.precio * p.stock);
            }
            
            document.getElementById('totalProductos').textContent = total;
            document.getElementById('productosActivos').textContent = activos;
            document.getElementById('stockTotal').textContent = stockTotal;
            document.getElementById('valorInventario').textContent = '$' + valorInventario.toFixed(2);
        }
        
        function actualizarResultadosBusqueda(cantidad, search) {
            var searchResultsDiv = document.getElementById('searchResults');
            var resultadosCount = document.getElementById('resultadosCount');
            var terminoBusquedaSpan = document.getElementById('terminoBusqueda');
            
            if(search && search.trim() !== '') {
                searchResultsDiv.style.display = 'flex';
                resultadosCount.textContent = cantidad;
                terminoBusquedaSpan.textContent = search;
            } else {
                searchResultsDiv.style.display = 'none';
            }
        }
        
        function buscarProductos() {
            var search = document.getElementById('searchInput').value.trim();
            cargarProductos(search);
        }
        
        function limpiarBusqueda() {
            document.getElementById('searchInput').value = '';
            var clearIcon = document.getElementById('clearSearchBtn');
            if(clearIcon) clearIcon.style.display = 'none';
            cargarProductos('');
        }
        
        function eliminarProducto(id) {
            if(confirm('¿Estás seguro de eliminar este producto?')) {
                fetch('../../../api/productos.php?id=' + id, { method: 'DELETE' })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if(data.success) {
                        alert('✅ Producto eliminado');
                        cargarProductos(terminoBusquedaActual);
                    } else {
                        alert('❌ Error al eliminar');
                    }
                })
                .catch(function(error) {
                    alert('❌ Error al eliminar el producto');
                });
            }
        }
        
        function cerrarSesion() {
            if(confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                fetch('../../../api/auth.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({ action: 'logout' })
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if(data.success) {
                        localStorage.removeItem('carrito');
                        window.location.href = '../../../index.php';
                    }
                });
            }
        }
        
        function closeSidebar() {
            var sidebar = document.querySelector('.sidebar');
            var overlay = document.querySelector('.sidebar-overlay');
            if(sidebar) sidebar.classList.remove('open');
            if(overlay) overlay.classList.remove('active');
        }
    </script>
</body>
</html>