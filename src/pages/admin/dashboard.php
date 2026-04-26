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
    <link rel="stylesheet" href="../../../assets/css/dashboard.css">
</head>
<body>
    <div class="admin-layout">
        <main class="main-content">
            <!-- Top Navbar con búsqueda integrada -->
            <nav class="top-navbar">
                <div class="navbar-brand">
                    <span>🍰</span>
                    <h1>Pastelería</h1>
                </div>
                
                <div class="navbar-welcome">
                    <span>Bienvenido,</span>
                    <strong><?php echo htmlspecialchars($_SESSION['user_nombre']); ?></strong>
                </div>
                
                <!-- Barra de búsqueda con lupa y X dentro -->
                <div class="navbar-search">
                    <div class="search-input-wrapper">
                        <span class="search-icon">🔍</span>
                        <input type="text" id="searchInput" placeholder="Buscar productos por nombre, SKU o categoría...">
                        <span class="clear-icon" id="clearSearchBtn" onclick="limpiarBusqueda()" style="display: none;">✕</span>
                    </div>
                    <button onclick="buscarProductos()" class="btn-search">Buscar</button>
                </div>
                
                <div class="navbar-actions">
                    <button onclick="cerrarSesion()" class="btn-logout">🚪 Cerrar Sesión</button>
                </div>
            </nav>

            <!-- Resultados de búsqueda (se muestra solo cuando hay búsqueda activa) -->
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
        
        document.addEventListener('DOMContentLoaded', () => {
            cargarProductos();
            
            const searchInput = document.getElementById('searchInput');
            const clearIcon = document.getElementById('clearSearchBtn');
            
            // Mostrar/ocultar icono de limpiar según el contenido
            searchInput.addEventListener('input', function() {
                if(this.value.length > 0) {
                    clearIcon.style.display = 'flex';
                } else {
                    clearIcon.style.display = 'none';
                }
            });
            
            // Buscar al presionar Enter
            searchInput.addEventListener('keypress', function(e) {
                if(e.key === 'Enter') {
                    buscarProductos();
                }
            });
        });
        
        async function cargarProductos(search = '') {
            terminoBusquedaActual = search;
            
            try {
                const url = search ? `../../../api/productos.php?search=${encodeURIComponent(search)}` : '../../../api/productos.php';
                const response = await fetch(url);
                const productos = await response.json();
                
                actualizarStats(productos);
                actualizarResultadosBusqueda(productos.length, search);
                
                const tbody = document.getElementById('productosBody');
                if(productos.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="9" style="text-align: center;">No hay productos registrados</td</tr>';
                    return;
                }
                
                tbody.innerHTML = productos.map(producto => {
                    let imagenUrl = 'https://via.placeholder.com/50x50?text=No+Image';
                    if(producto.tiene_imagen) {
                        imagenUrl = `../../../api/imagen_producto.php?id=${producto.id}`;
                    }
                    
                    return `
                        <tr>
                            <td>${producto.id}</td>
                            <td><img src="${imagenUrl}" class="producto-imagen" onerror="this.src='https://via.placeholder.com/50x50?text=No+Image'"></td>
                            <td><code>${producto.sku || '-'}</code></td>
                            <td>
                                <strong>${escapeHtml(producto.nombre)}</strong><br>
                                <small style="color:#999;">${producto.descripcion?.substring(0, 40)}${producto.descripcion?.length > 40 ? '...' : ''}</small>
                            </td>
                            <td>${producto.categoria}</td>
                            <td><strong>$${producto.precio}</strong></td>
                            <td><span class="badge ${producto.stock > 0 ? 'badge-stock' : 'badge-agotado'}">${producto.stock > 0 ? producto.stock + ' uds' : 'Agotado'}</span></td>
                            <td><span class="badge ${producto.visibilidad === 'publico' ? 'badge-publico' : 'badge-oculto'}">${producto.visibilidad === 'publico' ? '👁️ Público' : '👻 Oculto'}</span></td>
                            <td>
                                <a href="productos.php?id=${producto.id}" class="btn-edit">✏️ Editar</a>
                                <button class="btn-delete" onclick="eliminarProducto(${producto.id})">🗑️ Eliminar</button>
                            </td>
                        </tr>
                    `;
                }).join('');
            } catch(error) {
                console.error('Error:', error);
                document.getElementById('productosBody').innerHTML = '<tr><td colspan="9" style="text-align: center;">Error al cargar productos</td</tr>';
            }
        }
        
        function escapeHtml(text) {
            if(!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        function actualizarStats(productos) {
            const total = productos.length;
            const activos = productos.filter(p => p.visibilidad === 'publico').length;
            const stockTotal = productos.reduce((sum, p) => sum + p.stock, 0);
            const valorInventario = productos.reduce((sum, p) => sum + (p.precio * p.stock), 0);
            
            document.getElementById('totalProductos').textContent = total;
            document.getElementById('productosActivos').textContent = activos;
            document.getElementById('stockTotal').textContent = stockTotal;
            document.getElementById('valorInventario').textContent = `$${valorInventario.toFixed(2)}`;
        }
        
        function actualizarResultadosBusqueda(cantidad, search) {
            const searchResultsDiv = document.getElementById('searchResults');
            const resultadosCount = document.getElementById('resultadosCount');
            const terminoBusquedaSpan = document.getElementById('terminoBusqueda');
            
            if(search && search.trim() !== '') {
                searchResultsDiv.style.display = 'flex';
                resultadosCount.textContent = cantidad;
                terminoBusquedaSpan.textContent = search;
            } else {
                searchResultsDiv.style.display = 'none';
            }
        }
        
        function buscarProductos() {
            const search = document.getElementById('searchInput').value.trim();
            cargarProductos(search);
        }
        
        function limpiarBusqueda() {
            document.getElementById('searchInput').value = '';
            document.getElementById('clearSearchBtn').style.display = 'none';
            cargarProductos('');
        }
        
        function eliminarProducto(id) {
            if(confirm('¿Estás seguro de eliminar este producto?')) {
                fetch(`../../../api/productos.php?id=${id}`, { method: 'DELETE' })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        alert('✅ Producto eliminado');
                        cargarProductos(terminoBusquedaActual);
                    } else {
                        alert('❌ Error al eliminar');
                    }
                })
                .catch(error => {
                    alert('❌ Error al eliminar el producto');
                });
            }
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
                    } else {
                        alert('Error al cerrar sesión');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    localStorage.removeItem('carrito');
                    window.location.href = '../../../index.php';
                });
            }
        }
    </script>
</body>
</html>