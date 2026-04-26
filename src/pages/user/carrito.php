<?php
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Carrito - Pastelería</title>
    <link rel="stylesheet" href="../../../assets/css/carrito.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <a href="../user/tienda.php">
                    <span>🍰</span>
                    <h2>Pastelería</h2>
                </a>
            </div>
            <div class="nav-actions">
                <button class="cart-btn active" onclick="window.location.href='carrito.php'">
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
                        <a href="../tienda.php">🏠 Seguir comprando</a>
                        <a href="mis-pedidos.php">📦 Mis Pedidos</a>
                        <button onclick="cerrarSesion()" class="dropdown-logout">🚪 Cerrar Sesión</button>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="cart-container">
        <div class="cart-header">
            <h1>🛒 Mi Carrito</h1>
            <p>Revisa tus productos antes de finalizar la compra</p>
        </div>

        <div id="cartContent">
            <div class="loading">Cargando carrito...</div>
        </div>
    </div>

    <script>
        let carrito = [];
        let productosGlobal = [];

        async function cargarProductos() {
            try {
                const response = await fetch('../../../api/productos.php');
                productosGlobal = await response.json();
                cargarCarrito();
            } catch(error) {
                console.error('Error al cargar productos:', error);
                document.getElementById('cartContent').innerHTML = '<div class="loading">Error al cargar productos</div>';
            }
        }

        function cargarCarrito() {
            carrito = JSON.parse(localStorage.getItem('carrito') || '[]');
            actualizarContadorCarrito();
            mostrarCarrito();
        }

        function mostrarCarrito() {
            const container = document.getElementById('cartContent');
            
            if(carrito.length === 0) {
                container.innerHTML = `
                    <div class="cart-empty">
                        <div class="empty-icon">🛒</div>
                        <h2>Tu carrito está vacío</h2>
                        <p>¡Explora nuestra tienda y encuentra los mejores postres!</p>
                        <a href="../tienda.php" class="btn-continuar-comprando">🍰 Continuar comprando</a>
                    </div>
                `;
                return;
            }

            // Enriquecer los items del carrito con datos actualizados de la BD
            const productosCarrito = carrito.map(item => {
                const producto = productosGlobal.find(p => p.id === item.id);
                if(producto) {
                    item.nombre = producto.nombre;
                    item.precio = parseFloat(producto.precio);
                    item.stock = producto.stock;
                    item.imagen_url = producto.imagen_url;
                    item.tiene_imagen = producto.tiene_imagen;
                    item.categoria = producto.categoria;
                }
                return item;
            });

            let subtotal = productosCarrito.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
            let envio = subtotal > 500 ? 0 : 89;
            let iva = subtotal * 0.16;
            let total = subtotal + envio + iva;

            container.innerHTML = `
                <div class="cart-grid">
                    <div class="cart-items">
                        <div class="cart-items-header">
                            <h3>Productos (${carrito.length})</h3>
                            <div class="header-labels">
                                <span>Precio</span>
                                <span>Cantidad</span>
                                <span>Subtotal</span>
                            </div>
                        </div>
                        <div class="items-list">
                            ${productosCarrito.map((item, index) => {
                                let imagenUrl = 'https://via.placeholder.com/80x80?text=Pastel';
                                if(item.tiene_imagen) {
                                    imagenUrl = `../../../api/imagen_producto.php?id=${item.id}`;
                                } else if(item.imagen_url) {
                                    imagenUrl = item.imagen_url;
                                }
                                
                                return `
                                    <div class="cart-item" data-index="${index}">
                                        <div class="item-info">
                                            <img src="${imagenUrl}" alt="${item.nombre}" onerror="this.src='https://via.placeholder.com/80x80?text=Producto'">
                                            <div class="item-details">
                                                <h4>${item.nombre}</h4>
                                                <p class="item-categoria">${item.categoria || 'Pastelería'}</p>
                                                <p class="item-stock">📦 Stock: ${item.stock} unidades</p>
                                                <button class="btn-remove" onclick="eliminarDelCarrito(${index})">
                                                    🗑️ Eliminar
                                                </button>
                                            </div>
                                        </div>
                                        <div class="item-price">$${item.precio.toFixed(2)}</div>
                                        <div class="item-quantity">
                                            <button onclick="cambiarCantidad(${index}, -1)" ${item.cantidad <= 1 ? 'disabled' : ''}>-</button>
                                            <span>${item.cantidad}</span>
                                            <button onclick="cambiarCantidad(${index}, 1)" ${item.cantidad >= item.stock ? 'disabled' : ''}>+</button>
                                        </div>
                                        <div class="item-subtotal">$${(item.precio * item.cantidad).toFixed(2)}</div>
                                    </div>
                                `;
                            }).join('')}
                        </div>
                        <div class="cart-actions">
                            <button onclick="vaciarCarrito()" class="btn-clear">🗑️ Vaciar carrito</button>
                            <a href="../user/tienda.php" class="btn-continuar">➕ Seguir comprando</a>
                        </div>
                    </div>
                    
                    <div class="cart-summary">
                        <h3>Resumen del pedido</h3>
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span>$${subtotal.toFixed(2)}</span>
                        </div>
                        <div class="summary-row">
                            <span>Envío</span>
                            <span>${envio === 0 ? 'GRATIS' : '$' + envio.toFixed(2)}</span>
                        </div>
                        <div class="summary-row">
                            <span>IVA (16%)</span>
                            <span>$${iva.toFixed(2)}</span>
                        </div>
                        ${subtotal > 500 ? '<div class="summary-row free-shipping">🎉 Envío gratis por compras mayores a $500</div>' : ''}
                        <div class="summary-row total">
                            <span>Total</span>
                            <span>$${total.toFixed(2)}</span>
                        </div>
                        <button onclick="procederPago()" class="btn-checkout">✅ Proceder al pago</button>
                        <p class="payment-info">🔒 Pago seguro | 📦 Envío a todo México</p>
                    </div>
                </div>
            `;
        }

        function cambiarCantidad(index, cambio) {
            const item = carrito[index];
            const nuevaCantidad = item.cantidad + cambio;
            
            if(nuevaCantidad > 0 && nuevaCantidad <= item.stock) {
                item.cantidad = nuevaCantidad;
                localStorage.setItem('carrito', JSON.stringify(carrito));
                mostrarCarrito();
                actualizarContadorCarrito();
                mostrarNotificacion(`Cantidad actualizada: ${item.cantidad}`, 'success');
            } else if(nuevaCantidad > item.stock) {
                mostrarNotificacion('No hay suficiente stock disponible', 'error');
            }
        }

        function eliminarDelCarrito(index) {
            const producto = carrito[index];
            carrito.splice(index, 1);
            localStorage.setItem('carrito', JSON.stringify(carrito));
            mostrarCarrito();
            actualizarContadorCarrito();
            mostrarNotificacion(`${producto.nombre} eliminado del carrito`, 'success');
        }

        function vaciarCarrito() {
            if(confirm('¿Estás seguro de que deseas vaciar el carrito?')) {
                carrito = [];
                localStorage.setItem('carrito', JSON.stringify(carrito));
                mostrarCarrito();
                actualizarContadorCarrito();
                mostrarNotificacion('Carrito vaciado', 'success');
            }
        }

        function procederPago() {
            if(carrito.length === 0) {
                mostrarNotificacion('Tu carrito está vacío', 'error');
                return;
            }
            
            const subtotal = carrito.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
            const envio = subtotal > 500 ? 0 : 89;
            const iva = subtotal * 0.16;
            const total = subtotal + envio + iva;
            
            const pedido = {
                items: carrito,
                subtotal: subtotal,
                envio: envio,
                iva: iva,
                total: total,
                fecha: new Date().toISOString()
            };
            
            localStorage.setItem('pedidoActual', JSON.stringify(pedido));
            window.location.href = 'checkout.php';
        }

        function actualizarContadorCarrito() {
            const total = carrito.reduce((sum, item) => sum + item.cantidad, 0);
            const cartCount = document.getElementById('cartCount');
            if(cartCount) cartCount.textContent = total;
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

        function toggleUserMenu() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('show');
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

        window.onclick = function(event) {
            const dropdown = document.getElementById('userDropdown');
            if(!event.target.matches('.user-btn') && !event.target.closest('.user-btn')) {
                dropdown.classList.remove('show');
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            cargarProductos();
        });
    </script>
</body>
</html>