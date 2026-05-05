<?php
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once '../../../db/config/database.php';

// Obtener datos del usuario
$database = new Database();
$conn = $database->getConnection();
$stmt = $conn->prepare("SELECT nombre, email FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Pastelería</title>
    <link rel="stylesheet" href="../../../assets/css/checkout.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
</head>
<body>
    <div class="checkout-container">
        <div class="checkout-header">
            <a href="../tienda.php" class="back-btn">← Seguir comprando</a>
            <h1>🍰 Finalizar Compra</h1>
        </div>

        <div class="checkout-grid">
            <!-- Resumen del pedido -->
            <div class="order-summary">
                <h2>Resumen del pedido</h2>
                <div id="orderItems" class="order-items">
                    <div class="loading">Cargando productos...</div>
                </div>
                <div id="orderTotals" class="order-totals"></div>
            </div>

            <!-- Formulario de checkout -->
            <div class="checkout-form">
                <form id="checkoutForm">
                    <!-- Información de envío -->
                    <div class="form-section">
                        <h3>📦 Información de envío</h3>
                        <div class="form-group">
                            <label>Nombre completo</label>
                            <input type="text" id="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" id="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Teléfono</label>
                            <input type="tel" id="telefono" placeholder="55 1234 5678" required>
                        </div>
                    </div>

                    <!-- Tipo de entrega -->
                    <div class="form-section">
                        <h3>🚚 Tipo de entrega</h3>
                        <div class="radio-group">
                            <label class="radio-option">
                                <input type="radio" name="delivery_type" value="domicilio" checked>
                                <span>🏠 Envío a domicilio</span>
                                <small>Costo: $89 MXN</small>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="delivery_type" value="pickup">
                                <span>🏪 Recoger en tienda</span>
                                <small>Sin costo - Av. Principal #123</small>
                            </label>
                        </div>
                        <div id="direccionField" class="form-group">
                            <label>Dirección de envío</label>
                            <textarea id="direccion" rows="2" placeholder="Calle, número, colonia, ciudad, CP"></textarea>
                        </div>
                    </div>

                    <!-- Método de pago -->
                    <div class="form-section">
                        <h3>💳 Método de pago</h3>
                        <div class="payment-methods">
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="tarjeta" checked>
                                <div class="payment-card">
                                    <span>💳</span>
                                    <div>
                                        <strong>Tarjeta de crédito/débito</strong>
                                        <small>Visa, Mastercard, American Express</small>
                                    </div>
                                </div>
                            </label>
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="oxxo">
                                <div class="payment-card">
                                    <span>🏪</span>
                                    <div>
                                        <strong>Pago en OXXO</strong>
                                        <small>Genera código de barras</small>
                                    </div>
                                </div>
                            </label>
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="transferencia">
                                <div class="payment-card">
                                    <span>🏦</span>
                                    <div>
                                        <strong>Transferencia bancaria</strong>
                                        <small>BBVA, Banamex, Santander</small>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <!-- Datos de tarjeta -->
                        <div id="tarjetaFields" class="payment-fields">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Número de tarjeta</label>
                                    <input type="text" id="tarjeta_numero" placeholder="1234 5678 9012 3456" maxlength="19">
                                </div>
                                <div class="form-group">
                                    <label>Fecha expiración</label>
                                    <input type="text" id="tarjeta_fecha" placeholder="MM/AA">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>CVV</label>
                                    <input type="text" id="tarjeta_cvv" placeholder="123" maxlength="4">
                                </div>
                                <div class="form-group">
                                    <label>Nombre titular</label>
                                    <input type="text" id="tarjeta_titular" placeholder="Como aparece en tarjeta">
                                </div>
                            </div>
                        </div>

                        <!-- Datos de transferencia -->
                        <div id="transferenciaFields" class="payment-fields" style="display:none;">
                            <div class="bank-info">
                                <h4>Datos bancarios</h4>
                                <p><strong>Banco:</strong> BBVA México</p>
                                <p><strong>Cuenta:</strong> 1234 5678 9012 3456</p>
                                <p><strong>CLABE:</strong> 012 345 678 901234567</p>
                                <p><strong>Beneficiario:</strong> Pastelería Dulces Momentos S.A. de C.V.</p>
                                <p><strong>Referencia:</strong> Usa tu número de pedido</p>
                            </div>
                        </div>

                        <!-- Código de barras para OXXO -->
                        <div id="oxxoFields" class="payment-fields" style="display:none;">
                            <div class="barcode-card">
                                <div class="barcode-header">
                                    <span>📷</span>
                                    <h3>CÓDIGO DE BARRAS PARA PAGO</h3>
                                </div>
                                <div id="codigoBarras" class="barcode-display"></div>
                                <div class="barcode-ref-container">
                                    <div class="ref-title">📌 CÓDIGO DE REFERENCIA</div>
                                    <div class="ref-code" id="oxxoCode"></div>
                                    <div class="ref-number" id="oxxoNumber"></div>
                                </div>
                                <div class="barcode-amount-container">
                                    <div class="amount-label">💰 MONTO A PAGAR</div>
                                    <div class="amount-value" id="montoOxxo">$0.00 MXN</div>
                                </div>
                                <div class="barcode-instructions-container">
                                    <div class="instructions-title">📋 Instrucciones de pago en OXXO:</div>
                                    <ol class="instructions-list">
                                        <li>Acude a cualquier tienda OXXO</li>
                                        <li>Indica que deseas pagar en efectivo</li>
                                        <li>Muestra este código de barras o el número de referencia</li>
                                        <li>Realiza el pago y guarda tu ticket</li>
                                    </ol>
                                </div>
                                <div class="barcode-timer-container" id="timerOxxo">⏳ Tiempo restante para pagar: 23:59:59</div>
                                <button type="button" onclick="generarCodigoOxxo()" class="btn-regenerar">🔄 Regenerar código</button>
                            </div>
                        </div>

                        <button type="submit" class="btn-confirmar">✅ Confirmar compra</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function escapeHtml(text) {
    if(!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
    console.log('Script iniciado');
    
    let carrito = [];
    let productosGlobal = [];
    let subtotal = 0;
    let envio = 89;
    let total = 0;

 async function cargarDatos() {
    console.log('Cargando datos...');
    try {
        const response = await fetch('../../../api/productos.php');
        const productos = await response.json();
        productosGlobal = productos;
        
        // Debug: Ver qué información tiene el primer producto
        if(productos.length > 0) {
            console.log('Primer producto:', productos[0]);
            console.log('tiene_imagen:', productos[0].tiene_imagen);
        }
        
        const carritoStorage = localStorage.getItem('carrito');
        
        if(!carritoStorage || carritoStorage === '[]') {
            window.location.href = '../tienda.php';
            return;
        }
        
        carrito = JSON.parse(carritoStorage);
        if(carrito.length === 0) {
            window.location.href = '../tienda.php';
            return;
        }
        
        // Enriquecer carrito con datos de productos
        carrito = carrito.map(item => {
            const producto = productosGlobal.find(p => p.id == item.id);
            if(producto) {
                return {
                    ...item,
                    nombre: producto.nombre,
                    precio: parseFloat(producto.precio),
                    stock: producto.stock,
                    tiene_imagen: producto.tiene_imagen  // ← IMPORTANTE: guardar este valor
                };
            }
            return item;
        });
        
        console.log('Carrito enriquecido:', carrito);
        
        calcularTotales();
        mostrarResumen();
    } catch(error) {
        console.error('Error al cargar datos:', error);
        document.getElementById('orderItems').innerHTML = '<div class="error">Error al cargar el carrito</div>';
    }
}

    function calcularTotales() {
        subtotal = carrito.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
        total = subtotal + envio;
        console.log('Totales - Subtotal:', subtotal, 'Envío:', envio, 'Total:', total);
    }


function mostrarResumen() {
    const orderItems = document.getElementById('orderItems');
    if(orderItems && carrito.length > 0) {
        orderItems.innerHTML = carrito.map(item => {
            // Usar la MISMA URL que funciona en el navegador
            let imagenUrl = '';
            if(item.tiene_imagen) {
                // Usar ruta ABSOLUTA desde la raíz (la misma que funciona)
                imagenUrl = `/eslava/pasteleria/api/imagen_producto.php?id=${item.id}`;
            } else {
                // Placeholder interno (no depende de internet)
                imagenUrl = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50"%3E%3Crect width="50" height="50" fill="%23f0f0f0"/%3E%3Ctext x="50%" y="50%" text-anchor="middle" dy=".3em" fill="%23999" font-size="10"%3EProducto%3C/text%3E%3C/svg%3E';
            }
            
            return `
                <div class="order-item">
                    <div class="order-item-img">
                        <img src="${imagenUrl}" 
                             alt="${escapeHtml(item.nombre)}" 
                             style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;"
                             onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'50\' height=\'50\' viewBox=\'0 0 50 50\'%3E%3Crect width=\'50\' height=\'50\' fill=\'%23f0f0f0\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'%23999\' font-size=\'10\'%3EProducto%3C/text%3E%3C/svg%3E'">
                    </div>
                    <div class="order-item-info">
                        <span class="order-item-name">${escapeHtml(item.nombre)}</span>
                        <span class="order-item-qty">x${item.cantidad}</span>
                    </div>
                    <span class="order-item-price">$${(item.precio * item.cantidad).toFixed(2)}</span>
                </div>
            `;
        }).join('');
    } else if(orderItems) {
        orderItems.innerHTML = '<div class="loading">No hay productos en el carrito</div>';
    }

    const orderTotals = document.getElementById('orderTotals');
    if(orderTotals) {
        orderTotals.innerHTML = `
            <div class="totals-row">
                <span>Subtotal</span>
                <span>$${subtotal.toFixed(2)}</span>
            </div>
            <div class="totals-row">
                <span>Envío</span>
                <span>${envio === 0 ? 'Gratis' : '$' + envio.toFixed(2)}</span>
            </div>
            <div class="totals-row total">
                <span>TOTAL</span>
                <span>$${total.toFixed(2)}</span>
            </div>
        `;
    }
}

// Función auxiliar para escapar HTML
function escapeHtml(text) {
    if(!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

    // Métodos de pago
    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.getElementById('tarjetaFields').style.display = 'none';
            document.getElementById('transferenciaFields').style.display = 'none';
            document.getElementById('oxxoFields').style.display = 'none';
            
            if(this.value === 'tarjeta') {
                document.getElementById('tarjetaFields').style.display = 'block';
            } else if(this.value === 'transferencia') {
                document.getElementById('transferenciaFields').style.display = 'block';
            } else if(this.value === 'oxxo') {
                document.getElementById('oxxoFields').style.display = 'block';
                setTimeout(() => generarCodigoOxxo(), 100);
            }
        });
    });

    // Tipo de entrega
    document.querySelectorAll('input[name="delivery_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const direccionField = document.getElementById('direccionField');
            if(this.value === 'domicilio') {
                direccionField.style.display = 'block';
                envio = 89;
            } else {
                direccionField.style.display = 'none';
                envio = 0;
            }
            calcularTotales();
            mostrarResumen();
            
            const oxxoSelected = document.querySelector('input[name="payment_method"]:checked')?.value === 'oxxo';
            if(oxxoSelected) {
                generarCodigoOxxo();
            }
        });
    });

    function generarCodigoOxxo() {
        console.log('Generando código OXXO, Total:', total);
        
        if(total === 0) {
            calcularTotales();
        }
        
        const fecha = new Date();
        const anio = fecha.getFullYear().toString().slice(-2);
        const mes = (fecha.getMonth() + 1).toString().padStart(2, '0');
        const dia = fecha.getDate().toString().padStart(2, '0');
        const hora = fecha.getHours().toString().padStart(2, '0');
        const minuto = fecha.getMinutes().toString().padStart(2, '0');
        const segundo = fecha.getSeconds().toString().padStart(2, '0');
        const milisegundo = fecha.getMilliseconds().toString().padStart(3, '0');
        const aleatorio1 = Math.floor(Math.random() * 9999).toString().padStart(4, '0');
        const aleatorio2 = Math.floor(Math.random() * 999).toString().padStart(3, '0');
        
        let referencia = anio + mes + dia + hora + minuto + segundo + milisegundo + aleatorio1 + aleatorio2;
        if(referencia.length < 24) {
            referencia = referencia.padEnd(24, '0');
        }
        
        const referenciaFormateada = referencia.match(/.{1,4}/g).join(' ');
        
        document.getElementById('oxxoCode').innerHTML = `<strong>Código de barras:</strong> ${referenciaFormateada}`;
        document.getElementById('oxxoNumber').textContent = referencia;
        document.getElementById('montoOxxo').textContent = `$${total.toFixed(2)} MXN`;
        
        const container = document.getElementById('codigoBarras');
        if(container) {
            container.innerHTML = '';
            
            const canvas = document.createElement('canvas');
            canvas.width = 750;
            canvas.height = 90;
            canvas.style.width = '100%';
            canvas.style.height = 'auto';
            canvas.style.background = 'white';
            
            const ctx = canvas.getContext('2d');
            ctx.fillStyle = 'white';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            
            const codigo = referencia;
            const startX = 30;
            const endX = canvas.width - 30;
            const barHeight = 50;
            let x = startX;
            
            const espacioDisponible = endX - startX;
            const totalDigitos = codigo.length;
            const paso = espacioDisponible / totalDigitos;
            const digitosPosiciones = [];
            
            for(let i = 0; i < codigo.length; i++) {
                const digito = parseInt(codigo[i]);
                let anchoBarra = 0.8 + (digito / 8);
                anchoBarra = Math.min(anchoBarra, 2);
                const centroX = x + (anchoBarra / 2);
                digitosPosiciones.push({ digito: codigo[i], x: centroX });
                ctx.fillStyle = '#000';
                ctx.fillRect(x, 10, anchoBarra, barHeight);
                x += paso;
            }
            
            ctx.fillStyle = '#000';
            ctx.font = '8px monospace';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'top';
            
            for(let i = 0; i < digitosPosiciones.length; i++) {
                const pos = digitosPosiciones[i];
                ctx.fillText(pos.digito, pos.x, barHeight + 5);
            }
            
            ctx.font = 'bold 9px monospace';
            ctx.fillStyle = '#000';
            ctx.textAlign = 'center';
            ctx.fillText(referencia, canvas.width / 2, barHeight + 22);
            
            container.appendChild(canvas);
        }
        
        console.log('Código OXXO generado:', referencia);
        iniciarTemporizador();
    }

    function iniciarTemporizador() {
        const timerDiv = document.getElementById('timerOxxo');
        if(!timerDiv) return;
        
        if(window.timerInterval) clearInterval(window.timerInterval);
        
        const horaFin = new Date();
        horaFin.setHours(horaFin.getHours() + 24);
        
        function actualizarTimer() {
            const ahora = new Date();
            const diff = horaFin - ahora;
            
            if(diff <= 0) {
                timerDiv.innerHTML = '⏰ El tiempo para pagar ha expirado. Por favor genera un nuevo código.';
                timerDiv.style.background = '#f8d7da';
                timerDiv.style.color = '#721c24';
                if(window.timerInterval) clearInterval(window.timerInterval);
                return;
            }
            
            const horas = Math.floor(diff / (1000 * 60 * 60));
            const minutos = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const segundos = Math.floor((diff % (1000 * 60)) / 1000);
            timerDiv.innerHTML = `⏳ Tiempo restante para pagar: ${horas.toString().padStart(2, '0')}:${minutos.toString().padStart(2, '0')}:${segundos.toString().padStart(2, '0')}`;
        }
        
        actualizarTimer();
        window.timerInterval = setInterval(actualizarTimer, 1000);
    }

    // Evento submit del formulario
    const checkoutForm = document.getElementById('checkoutForm');
    if(checkoutForm) {
        checkoutForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const nombre = document.getElementById('nombre').value;
            const email = document.getElementById('email').value;
            const telefono = document.getElementById('telefono').value;
            const deliveryType = document.querySelector('input[name="delivery_type"]:checked').value;
            const direccion = deliveryType === 'domicilio' ? document.getElementById('direccion').value : 'Recoger en tienda';
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
            
            if(!nombre || !email || !telefono) {
                alert('Por favor completa todos los campos');
                return;
            }
            
            if(deliveryType === 'domicilio' && !direccion) {
                alert('Por favor ingresa tu dirección de envío');
                return;
            }
            
            const btn = document.querySelector('.btn-confirmar');
            const btnText = btn.innerHTML;
            btn.innerHTML = '⏳ Procesando...';
            btn.disabled = true;
            
            const pedido = {
                usuario_id: <?php echo $_SESSION['user_id']; ?>,
                items: carrito.map(item => ({
                    id: item.id,
                    nombre: item.nombre,
                    cantidad: item.cantidad,
                    precio: item.precio
                })),
                subtotal: subtotal,
                envio: envio,
                total: total,
                delivery_type: deliveryType,
                direccion: direccion,
                payment_method: paymentMethod,
                nombre: nombre,
                email: email,
                telefono: telefono
            };
            
            try {
                const response = await fetch('../../../api/procesar_pedido.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    credentials: 'include',
                    body: JSON.stringify(pedido)
                });
                
                const result = await response.json();
                console.log('Respuesta del servidor:', result);
                
                if(result.success) {
                    localStorage.removeItem('carrito');
                    localStorage.setItem('ultimoPedido', JSON.stringify(pedido));
                    window.location.href = `confirmacion.php?pedido_id=${result.pedido_id}`;
                } else {
                    alert('Error: ' + result.message);
                    btn.innerHTML = btnText;
                    btn.disabled = false;
                }
            } catch(error) {
                console.error('Error:', error);
                alert('Error al procesar el pedido: ' + error.message);
                btn.innerHTML = btnText;
                btn.disabled = false;
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        cargarDatos();
    });
    </script>
</body>
</html>