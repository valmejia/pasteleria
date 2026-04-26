// Funciones globales
function showSection(sectionId) {
    // Ocultar todas las secciones
    document.querySelectorAll('.section').forEach(section => {
        section.classList.remove('active');
    });
    
    // Mostrar la sección seleccionada
    document.getElementById(sectionId).classList.add('active');
    
    // Cargar datos según la sección
    if(sectionId === 'ventas') {
        cargarProductos();
    } else if(sectionId === 'productos') {
        cargarTablaProductos();
    } else if(sectionId === 'reportes') {
        cargarReportes();
    }
}

function cerrarSesion() {
    if(confirm('¿Estás seguro de que deseas cerrar sesión?')) {
        fetch('api/auth.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({action: 'logout'})
        }).then(() => {
            window.location.href = 'pages/login.php';
        });
    }
}

// Cargar productos para el punto de venta
async function cargarProductos() {
    try {
        const response = await fetch('api/productos.php');
        const productos = await response.json();
        
        const productosList = document.getElementById('productosList');
        if(productosList) {
            productosList.innerHTML = productos.map(producto => `
                <div class="producto-card">
                    <h3>${producto.nombre}</h3>
                    <p class="precio">$${producto.precio}</p>
                    <p class="stock">Stock: ${producto.stock}</p>
                    <button onclick="agregarAlCarrito(${producto.id}, '${producto.nombre}', ${producto.precio}, ${producto.stock})" 
                            class="btn-small">
                        Agregar al Carrito
                    </button>
                </div>
            `).join('');
        }
    } catch(error) {
        console.error('Error:', error);
    }
}

// Inicializar al cargar la página
document.addEventListener('DOMContentLoaded', () => {
    if(document.getElementById('ventas')) {
        cargarProductos();
    }
});