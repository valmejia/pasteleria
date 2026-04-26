<?php
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$isAdmin = ($_SESSION['user_rol'] === 'admin');
$isUser = ($_SESSION['user_rol'] === 'usuario');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda Online - Pastelería</title>
    <link rel="stylesheet" href="../../../assets/css/tienda.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <a href="tienda.php">
                    <span>🍰</span>
                    <h2>Pastelería</h2>
                </a>
            </div>
            
            <div class="nav-search">
                <div class="search-wrapper">
                    <span class="search-icon">🔍</span>
                    <input type="text" id="searchInput" placeholder="Buscar productos...">
                    <span class="clear-search" id="clearSearch" onclick="limpiarBusqueda()" style="display: none;">✕</span>
                </div>
                <button onclick="buscarProductos()" class="btn-search-nav">Buscar</button>
            </div>
            
            <div class="nav-actions">
                <button class="cart-btn" onclick="verCarrito()">
                    🛒 <span id="cartCount" class="cart-count">0</span>
                </button>
                <div class="user-menu">
                    <button class="user-btn" onclick="toggleUserMenu()">
                        👤 <span><?php echo htmlspecialchars($_SESSION['user_nombre']); ?></span>
                    </button>
                    <div class="user-dropdown" id="userDropdown">
                        <div class="user-info">
                            <strong><?php echo htmlspecialchars($_SESSION['user_nombre']); ?></strong>
                            <small><?php echo $_SESSION['user_rol']; ?></small>
                        </div>
                        <hr>
                        <a href="perfil.php">👤 Mi Perfil</a>
                        <a href="mis-pedidos.php">📦 Mis Pedidos</a>
                        <button onclick="cerrarSesion()" class="dropdown-logout">🚪 Cerrar Sesión</button>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Banner -->
    <section class="hero-banner">
        <div class="hero-content">
            <h1>🍰 Dulces Momentos</h1>
            <p>Los mejores postres artesanales, hechos con amor</p>
            <a href="#productos" class="hero-btn">Ver Productos</a>
        </div>
    </section>

    <!-- Categorías -->
    <section class="categorias">
        <div class="section-header">
            <h2>Categorías</h2>
            <p>Explora por categorías</p>
        </div>
        <div class="categorias-grid" id="categoriasGrid">
            <div class="categoria-card" onclick="filtrarPorCategoria('')">
                <span>🍰</span>
                <h3>Todos</h3>
            </div>
            <div class="categoria-card" onclick="filtrarPorCategoria('pasteles')">
                <span>🍰</span>
                <h3>Pasteles</h3>
            </div>
            <div class="categoria-card" onclick="filtrarPorCategoria('galletas')">
                <span>🍪</span>
                <h3>Galletas</h3>
            </div>
            <div class="categoria-card" onclick="filtrarPorCategoria('cupcakes')">
                <span>🧁</span>
                <h3>Cupcakes</h3>
            </div>
            <div class="categoria-card" onclick="filtrarPorCategoria('postres_especiales')">
                <span>🎂</span>
                <h3>Postres Especiales</h3>
            </div>
            <div class="categoria-card" onclick="filtrarPorCategoria('panaderia_dulce')">
                <span>🥐</span>
                <h3>Panadería Dulce</h3>
            </div>
        </div>
    </section>

    <!-- Productos Destacados -->
    <section class="productos-destacados">
        <div class="section-header">
            <h2>✨ Productos Destacados</h2>
            <p>Los favoritos de nuestros clientes</p>
        </div>
        <div id="productosDestacados" class="productos-grid"></div>
    </section>

    <!-- Todos los Productos -->
    <section id="productos" class="todos-productos">
        <div class="section-header">
            <h2>🍰 Todos los Productos</h2>
            <p>Descubre nuestra variedad de productos</p>
        </div>
        
        <!-- Filtros -->
        <div class="filtros">
            <div class="filtro-group">
                <label>Ordenar por:</label>
                <select id="ordenarSelect" onchange="ordenarProductos()">
                    <option value="relevancia">Relevancia</option>
                    <option value="precio_asc">Precio: menor a mayor</option>
                    <option value="precio_desc">Precio: mayor a menor</option>
                    <option value="nombre_asc">Nombre: A-Z</option>
                </select>
            </div>
            <div class="filtro-group">
                <label>Mostrar:</label>
                <select id="mostrarSelect" onchange="cambiarPagina(1)">
                    <option value="12">12 productos</option>
                    <option value="24">24 productos</option>
                    <option value="48">48 productos</option>
                </select>
            </div>
        </div>
        
        <div id="todosProductos" class="productos-grid"></div>
        <div id="paginacion" class="paginacion"></div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-section">
                <h3>🍰 Pastelería</h3>
                <p>Dulces momentos desde 2020</p>
            </div>
            <div class="footer-section">
                <h4>Enlaces Rápidos</h4>
                <a href="tienda.php">Inicio</a>
                <a href="#productos">Productos</a>
                <a href="#">Contacto</a>
            </div>
            <div class="footer-section">
                <h4>Contacto</h4>
                <p>📞 +52 (55) 1234-5678</p>
                <p>✉️ info@pasteleria.com</p>
            </div>
            <div class="footer-section">
                <h4>Síguenos</h4>
                <div class="social-icons">
                    <a href="#">📘</a>
                    <a href="#">📸</a>
                    <a href="#">🐦</a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 Pastelería - Todos los derechos reservados</p>
        </div>
    </footer>

    <!-- Modal de Producto -->
    <div id="productoModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="cerrarModal()">&times;</span>
            <div id="modalBody"></div>
        </div>
    </div>

    <script>
        let productosGlobal = [];
        let productosFiltrados = [];
        let paginaActual = 1;
        let productosPorPagina = 12;
        let categoriaActual = '';
        let terminoBusqueda = '';

        // Actualizar contador al cargar la página
        function actualizarContadorGlobal() {
            const carritoStorage = JSON.parse(localStorage.getItem('carrito') || '[]');
            const total = carritoStorage.reduce((sum, item) => sum + item.cantidad, 0);
            const cartCount = document.getElementById('cartCount');
            if(cartCount) cartCount.textContent = total;
        }
        
        // Cargar productos al inicio
        document.addEventListener('DOMContentLoaded', () => {
            cargarTodosProductos();
            
            const searchInput = document.getElementById('searchInput');
            const clearSearch = document.getElementById('clearSearch');
            
            if(searchInput) {
                searchInput.addEventListener('input', function() {
                    clearSearch.style.display = this.value.length > 0 ? 'flex' : 'none';
                });
                
                searchInput.addEventListener('keypress', function(e) {
                    if(e.key === 'Enter') buscarProductos();
                });
            }
        });
        
        async function cargarTodosProductos() {
            try {
                const response = await fetch('../../../api/productos.php');
                const productos = await response.json();
                console.log('Productos cargados:', productos);
                
                // Filtrar solo productos públicos
                productosGlobal = productos.filter(p => p.visibilidad === 'publico');
                console.log('Productos públicos:', productosGlobal.length);
                
                aplicarFiltrosYOrden();
                cargarProductosDestacados();
            } catch(error) {
                console.error('Error al cargar productos:', error);
                document.getElementById('todosProductos').innerHTML = '<div class="error">Error al cargar productos</div>';
            }
        }
        
        async function cargarProductosDestacados() {
            try {
                const destacados = productosGlobal.slice(0, 4);
                const container = document.getElementById('productosDestacados');
                if(container) {
                    container.innerHTML = destacados.map(producto => crearTarjetaProducto(producto)).join('');
                }
            } catch(error) {
                console.error('Error:', error);
            }
        }
        
        function crearTarjetaProducto(producto) {
            // Construir URL de imagen correctamente
            let imagenUrl = 'https://via.placeholder.com/300x200?text=Pasteleria';
            if(producto.tiene_imagen) {
                imagenUrl = `../../../api/imagen_producto.php?id=${producto.id}`;
            } else if(producto.imagen && producto.imagen.startsWith('http')) {
                imagenUrl = producto.imagen;
            } else if(producto.imagen_url) {
                imagenUrl = producto.imagen_url;
            }
            
            return `
                <div class="producto-card" onclick="verProducto(${producto.id})">
                    <div class="producto-img">
                        <img src="${imagenUrl}" alt="${producto.nombre}" onerror="this.src='https://via.placeholder.com/300x200?text=Producto'">
                        ${producto.stock < 5 && producto.stock > 0 ? '<span class="stock-bajo">¡Últimas unidades!</span>' : ''}
                        ${producto.stock === 0 ? '<span class="agotado">Agotado</span>' : ''}
                    </div>
                    <div class="producto-info">
                        <span class="categoria">${producto.categoria}</span>
                        <h3>${producto.nombre}</h3>
                        <div class="precio">$${parseFloat(producto.precio).toFixed(2)} <span class="iva">IVA incl.</span></div>
                        <div class="stock-info">📦 ${producto.stock} disponibles</div>
                        <button class="btn-comprar" onclick="event.stopPropagation(); agregarAlCarrito(${producto.id})">
                            🛒 Agregar al carrito
                        </button>
                    </div>
                </div>
            `;
        }
        
        function aplicarFiltrosYOrden() {
            let productos = [...productosGlobal];
            
            // Filtrar por categoría
            if(categoriaActual) {
                productos = productos.filter(p => p.categoria === categoriaActual);
            }
            
            // Filtrar por búsqueda
            if(terminoBusqueda) {
                productos = productos.filter(p => 
                    p.nombre.toLowerCase().includes(terminoBusqueda) || 
                    (p.descripcion && p.descripcion.toLowerCase().includes(terminoBusqueda)) ||
                    (p.sku && p.sku.toLowerCase().includes(terminoBusqueda))
                );
            }
            
            // Ordenar
            const orden = document.getElementById('ordenarSelect')?.value || 'relevancia';
            switch(orden) {
                case 'precio_asc':
                    productos.sort((a, b) => parseFloat(a.precio) - parseFloat(b.precio));
                    break;
                case 'precio_desc':
                    productos.sort((a, b) => parseFloat(b.precio) - parseFloat(a.precio));
                    break;
                case 'nombre_asc':
                    productos.sort((a, b) => a.nombre.localeCompare(b.nombre));
                    break;
                default:
                    productos.sort((a, b) => b.id - a.id);
            }
            
            productosFiltrados = productos;
            paginaActual = 1;
            mostrarProductosPaginados();
        }
        
        function mostrarProductosPaginados() {
            const mostrarSelect = document.getElementById('mostrarSelect');
            if(mostrarSelect) {
                productosPorPagina = parseInt(mostrarSelect.value);
            }
            
            const inicio = (paginaActual - 1) * productosPorPagina;
            const fin = inicio + productosPorPagina;
            const productosPagina = productosFiltrados.slice(inicio, fin);
            
            const container = document.getElementById('todosProductos');
            if(productosPagina.length === 0) {
                container.innerHTML = '<div class="no-resultados">No se encontraron productos</div>';
            } else {
                container.innerHTML = productosPagina.map(producto => crearTarjetaProducto(producto)).join('');
            }
            
            mostrarPaginacion();
        }
        
        function mostrarPaginacion() {
            const totalPaginas = Math.ceil(productosFiltrados.length / productosPorPagina);
            const paginacionDiv = document.getElementById('paginacion');
            
            if(totalPaginas <= 1) {
                paginacionDiv.innerHTML = '';
                return;
            }
            
            let html = '<div class="paginacion-buttons">';
            html += `<button onclick="cambiarPagina(${paginaActual - 1})" ${paginaActual === 1 ? 'disabled' : ''}>◀ Anterior</button>`;
            
            for(let i = 1; i <= totalPaginas; i++) {
                if(i === paginaActual) {
                    html += `<button class="active">${i}</button>`;
                } else if(Math.abs(i - paginaActual) <= 2) {
                    html += `<button onclick="cambiarPagina(${i})">${i}</button>`;
                } else if(i === totalPaginas || i === 1) {
                    html += `<button onclick="cambiarPagina(${i})">${i}</button>`;
                } else if(Math.abs(i - paginaActual) === 3) {
                    html += `<span>...</span>`;
                }
            }
            
            html += `<button onclick="cambiarPagina(${paginaActual + 1})" ${paginaActual === totalPaginas ? 'disabled' : ''}>Siguiente ▶</button>`;
            html += '</div>';
            paginacionDiv.innerHTML = html;
        }
        
        function cambiarPagina(pagina) {
            const totalPaginas = Math.ceil(productosFiltrados.length / productosPorPagina);
            if(pagina >= 1 && pagina <= totalPaginas) {
                paginaActual = pagina;
                mostrarProductosPaginados();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }
        
        function ordenarProductos() {
            aplicarFiltrosYOrden();
        }
        
        function filtrarPorCategoria(categoria) {
            categoriaActual = categoria;
            terminoBusqueda = '';
            document.getElementById('searchInput').value = '';
            document.getElementById('clearSearch').style.display = 'none';
            aplicarFiltrosYOrden();
        }
        
        function buscarProductos() {
            terminoBusqueda = document.getElementById('searchInput').value.toLowerCase().trim();
            categoriaActual = '';
            aplicarFiltrosYOrden();
        }
        
        function limpiarBusqueda() {
            document.getElementById('searchInput').value = '';
            document.getElementById('clearSearch').style.display = 'none';
            terminoBusqueda = '';
            categoriaActual = '';
            aplicarFiltrosYOrden();
        }
        
        async function verProducto(id) {
            try {
                const response = await fetch(`../../api/productos.php?id=${id}`);
                const producto = await response.json();
                
                let imagenUrl = 'https://via.placeholder.com/500x400?text=Pasteleria';
                if(producto.tiene_imagen) {
                    imagenUrl = `../../api/imagen_producto.php?id=${producto.id}`;
                } else if(producto.imagen && producto.imagen.startsWith('http')) {
                    imagenUrl = producto.imagen;
                }
                
                const modalBody = document.getElementById('modalBody');
                modalBody.innerHTML = `
                    <div class="modal-producto">
                        <div class="modal-img">
                            <img src="${imagenUrl}" alt="${producto.nombre}" onerror="this.src='https://via.placeholder.com/500x400?text=Producto'">
                        </div>
                        <div class="modal-info">
                            <span class="categoria-badge">${producto.categoria}</span>
                            <h2>${producto.nombre}</h2>
                            <div class="modal-precio">$${parseFloat(producto.precio).toFixed(2)} <span>MXN</span></div>
                            <div class="modal-stock ${producto.stock < 5 ? 'bajo' : ''}">
                                📦 Stock disponible: ${producto.stock} unidades
                                ${producto.stock < 5 && producto.stock > 0 ? '<span class="alerta"> ¡Últimas unidades!</span>' : ''}
                                ${producto.stock === 0 ? '<span class="alerta"> Producto agotado</span>' : ''}
                            </div>
                            <div class="modal-descripcion">
                                <h4>Descripción</h4>
                                <p>${producto.descripcion || 'Delicioso producto artesanal, elaborado con ingredientes de la más alta calidad.'}</p>
                            </div>
                            <div class="modal-caracteristicas">
                                <h4>Características</h4>
                                <ul>
                                    <li><strong>📦 SKU:</strong> ${producto.sku || 'N/A'}</li>
                                    <li><strong>🍰 Porción:</strong> ${producto.porcion_tamano || 'Individual'}</li>
                                    <li><strong>⏰ Tiempo preparación:</strong> ${producto.tiempo_preparacion || '1 día'}</li>
                                    <li><strong>🥜 Alérgenos:</strong> ${producto.alergenos || 'Ninguno'}</li>
                                    <li><strong>✨ Personalizable:</strong> ${producto.personalizable ? 'Sí' : 'No'}</li>
                                    <li><strong>📅 Temporada:</strong> ${producto.temporada_edicion || 'Todo el año'}</li>
                                </ul>
                            </div>
                            ${producto.stock > 0 ? `
                                <button class="btn-comprar-modal" onclick="agregarAlCarrito(${producto.id}); cerrarModal();">
                                    🛒 Agregar al carrito
                                </button>
                            ` : `
                                <button class="btn-comprar-modal" disabled style="opacity:0.5; cursor:not-allowed;">
                                    ❌ Producto agotado
                                </button>
                            `}
                        </div>
                    </div>
                `;
                
                document.getElementById('productoModal').style.display = 'block';
                document.body.style.overflow = 'hidden';
            } catch(error) {
                console.error('Error:', error);
            }
        }
        
        function cerrarModal() {
            document.getElementById('productoModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
        let carrito = JSON.parse(localStorage.getItem('carrito') || '[]');
        
        function actualizarContadorCarrito() {
            const total = carrito.reduce((sum, item) => sum + item.cantidad, 0);
            const cartCount = document.getElementById('cartCount');
            if(cartCount) cartCount.textContent = total;
            localStorage.setItem('carrito', JSON.stringify(carrito));
        }
        
        function agregarAlCarrito(id) {
            const producto = productosGlobal.find(p => p.id === id);
            if(!producto) return;
            
            const existente = carrito.find(item => item.id === id);
            if(existente) {
                if(existente.cantidad < producto.stock) {
                    existente.cantidad++;
                } else {
                    mostrarNotificacion('No hay suficiente stock disponible', 'error');
                    return;
                }
            } else {
                carrito.push({
                    id: producto.id,
                    nombre: producto.nombre,
                    precio: parseFloat(producto.precio),
                    cantidad: 1,
                    imagen: producto.imagen
                });
            }
            
            actualizarContadorCarrito();
            mostrarNotificacion(`${producto.nombre} agregado al carrito`, 'success');
        }
        
        function mostrarNotificacion(mensaje, tipo) {
            const notificacion = document.createElement('div');
            notificacion.className = `notificacion ${tipo}`;
            notificacion.innerHTML = `<span>${tipo === 'success' ? '✅' : '❌'}</span> ${mensaje}`;
            document.body.appendChild(notificacion);
            
            setTimeout(() => {
                notificacion.remove();
            }, 3000);
        }
        
        function verCarrito() {
            window.location.href = './carrito.php';
        }
        
        function toggleUserMenu() {
            const dropdown = document.getElementById('userDropdown');
            if(dropdown) dropdown.classList.toggle('show');
        }
        
        function cerrarSesion() {
            if(confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                // Mostrar loading
                const btn = event.target;
                const originalText = btn.innerHTML;
                btn.innerHTML = '⏳ Cerrando...';
                btn.disabled = true;
                
                fetch('../../../api/auth.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    credentials: 'same-origin',  // Importante para mantener la sesión
                    body: JSON.stringify({action: 'logout'})
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        // Limpiar carrito del localStorage
                        localStorage.removeItem('carrito');
                        // Redirigir al index
                        window.location.href = '../../../index.php';
                    } else {
                        alert('Error al cerrar sesión');
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Si falla el fetch, redirigir de todas formas
                    localStorage.removeItem('carrito');
                    window.location.href = '../../../index.php';
                });
            }
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('productoModal');
            if(event.target === modal) cerrarModal();
            
            const dropdown = document.getElementById('userDropdown');
            if(dropdown && !event.target.matches('.user-btn') && !event.target.closest('.user-btn')) {
                dropdown.classList.remove('show');
            }
        }
        
        actualizarContadorCarrito();
    </script>
</body>
</html>