<?php
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pastelería - Dulces Momentos</title>
    <link rel="stylesheet" href="assets/css/landing.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>

<div class="landing-container">
    <nav class="landing-nav">
        <div class="nav-container">
            <div class="logo">
                <h2>Pastelería</h2>
            </div>
            <div class="nav-links">
                <a href="#inicio">Inicio</a>
                <a href="#productos">Productos</a>
                <a href="#nosotros">Nosotros</a>
                <a href="#contacto">Contacto</a>
                <a href="src/pages/login.php" class="btn-login-nav">Iniciar Sesión</a>
                <a href="src/pages/signup.php" class="btn-signup-nav">Crear Cuenta</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="inicio" class="hero">
        <div class="hero-content">
            <h1>Dulces Momentos</h1>
            <p>Pastelería artesanal con ingredientes de la más alta calidad</p>
        </div>
    </section>

    <!-- Productos Destacados -->
    <section id="productos" class="featured-products">
        <div class="container">
            <h2>Productos Destacados</h2>
            <p class="section-subtitle">Los favoritos de nuestros clientes</p>
            <div class="products-grid" id="productosDestacados">
                <div class="loading">Cargando productos...</div>
            </div>
            <div class="view-more">
                <a href="src/pages/signup.php" class="btn-view-more">Regístrate para ver más productos 🎂</a>
            </div>
        </div>
    </section>

    <!-- Nosotros -->
    <section id="nosotros" class="about">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <h2>Sobre Nosotros</h2>
                    <p>Somos una pastelería artesanal que nació del amor por la repostería y los ingredientes de calidad.</p>
                    <p>Desde 2020, endulzamos los momentos especiales de nuestros clientes con creaciones únicas y deliciosas.</p>
                    <div class="features">
                        <div class="feature">
                            <span>🥐</span>
                            <h4>Ingredientes Naturales</h4>
                        </div>
                        <div class="feature">
                            <span>👩‍🍳</span>
                            <h4>Elaboración Artesanal</h4>
                        </div>
                        <div class="feature">
                            <span>🎂</span>
                            <h4>Pedidos Personalizados</h4>
                        </div>
                    </div>
                    <div class="about-cta">
                        <a href="src/pages/signup.php" class="btn-primary">Únete a nuestra comunidad</a>
                    </div>
                </div>
                <div class="about-image">
                    <img src="https://images.unsplash.com/photo-1550617931-e17a7b70dce2?w=500" alt="Pastelería">
                </div>
            </div>
        </div>
    </section>

    <!-- Contacto -->
    <section id="contacto" class="contact">
        <div class="container">
            <h2>Contáctanos</h2>
            <div class="contact-content">
                <div class="contact-info">
                    <div class="info-item">
                        <span>📍</span>
                        <p>Av. Principal #123, Ciudad</p>
                    </div>
                    <div class="info-item">
                        <span>📞</span>
                        <p>+52 (55) 1234-5678</p>
                    </div>
                    <div class="info-item">
                        <span>✉️</span>
                        <p>info@pasteleria.com</p>
                    </div>
                    <div class="info-item">
                        <span>⏰</span>
                        <p>Lun-Dom: 9am - 8pm</p>
                    </div>
                </div>
                <form id="contactForm" class="contact-form">
                    <input type="text" placeholder="Nombre" required>
                    <input type="email" placeholder="Email" required>
                    <textarea placeholder="Mensaje" rows="4" required></textarea>
                    <button type="submit" class="btn-primary">Enviar Mensaje</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <span>🍰</span>
                    <h3>Pastelería</h3>
                    <p>Dulces momentos desde 2020</p>
                </div>
                <div class="footer-links">
                    <h4>Enlaces Rápidos</h4>
                    <a href="#inicio">Inicio</a>
                    <a href="#productos">Productos</a>
                    <a href="#nosotros">Nosotros</a>
                    <a href="#contacto">Contacto</a>
                </div>
                <div class="footer-links">
                    <h4>Mi Cuenta</h4>
                    <a href="src/pages/login.php">Iniciar Sesión</a>
                    <a href="src/pages/signup.php">Crear Cuenta</a>
                </div>
                <div class="footer-social">
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
        </div>
    </footer>
</div>

<script>
    async function cargarProductosDestacados() {
        try {
            const response = await fetch('api/productos.php');
            const productos = await response.json();
            const productosPublicos = productos.filter(p => p.visibilidad === 'publico').slice(0, 6);
            
            const grid = document.getElementById('productosDestacados');
            if(productosPublicos.length === 0) {
                grid.innerHTML = '<p>Próximamente más productos...</p>';
                return;
            }
            
            grid.innerHTML = productosPublicos.map(producto => `
                <div class="product-card">
                    <img src="${producto.imagen || 'https://via.placeholder.com/300x200?text=Pastel'}" alt="${producto.nombre}">
                    <div class="product-info">
                        <h3>${producto.nombre}</h3>
                        <p>${producto.descripcion?.substring(0, 80)}...</p>
                        <div class="product-price">$${producto.precio}</div>
                        <a href="src/pages/signup.php" class="btn-order">Regístrate para pedir</a>
                    </div>
                </div>
            `).join('');
        } catch(error) {
            console.error('Error:', error);
            document.getElementById('productosDestacados').innerHTML = '<p>Error al cargar productos</p>';
        }
    }
    
    document.addEventListener('DOMContentLoaded', () => {
        cargarProductosDestacados();
        
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if(target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    });
    
    document.getElementById('contactForm')?.addEventListener('submit', (e) => {
        e.preventDefault();
        alert('Gracias por tu mensaje. Te contactaremos pronto.');
        e.target.reset();
    });
</script>

</body>
</html>