<?php
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$pedidoId = isset($_GET['pedido_id']) ? $_GET['pedido_id'] : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Compra - Pastelería</title>
    <link rel="stylesheet" href="../../../assets/css/confirmacion.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="confirmation-container">
        <div class="confirmation-card">
            <div class="confirmation-header">
                <div class="confirmation-icon">✅</div>
                <h1>¡Compra Exitosa!</h1>
                <p>Tu pedido ha sido registrado correctamente</p>
            </div>
            <div class="confirmation-body">
                <div class="ticket-actions">
                    <button onclick="enviarCorreo()" class="btn-ticket btn-email">
                        📧 Enviar ticket al correo
                    </button>
                    <button onclick="imprimirTicket()" class="btn-ticket btn-print">
                        🖨️ Imprimir ticket
                    </button>
                    <a href="../tienda.php" class="btn-ticket btn-tienda">
                        🍰 Seguir comprando
                    </a>
                </div>
                
                <div id="ticketContent" class="ticket-info">
                    <div class="loading">Cargando información del pedido...</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const pedido = JSON.parse(localStorage.getItem('ultimoPedido') || '{}');
        const pedidoId = <?php echo json_encode($pedidoId); ?>;
        let productosGlobal = [];

        async function cargarTicket() {
            try {
                const response = await fetch('../../../api/productos.php');
                productosGlobal = await response.json();
                mostrarTicket();
            } catch(error) {
                console.error('Error:', error);
                document.getElementById('ticketContent').innerHTML = '<p class="loading">Error al cargar el ticket</p>';
            }
        }

    function mostrarTicket() {
    if(!pedido.items || pedido.items.length === 0) {
        document.getElementById('ticketContent').innerHTML = '<p>No hay información del pedido</p>';
        return;
    }

    const fecha = new Date().toLocaleDateString('es-MX', {
        year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
    });
    
    const fechaEstimada = new Date();
    const dias = pedido.delivery_type === 'domicilio' ? 3 : 1;
    fechaEstimada.setDate(fechaEstimada.getDate() + dias);
    const fechaEstimadaStr = fechaEstimada.toLocaleDateString('es-MX', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
    });

    let productosHTML = '';
        for(let item of pedido.items) {
            const producto = productosGlobal.find(p => p.id == item.id);
            let imagenUrl = '';
            
            // Usar el nuevo endpoint servir_imagen.php
            if(producto && producto.tiene_imagen) {
                imagenUrl = `/eslava/pasteleria/api/servir_imagen.php?id=${producto.id}`;
            } else {
                // Placeholder con emoji
                imagenUrl = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 60 60"%3E%3Crect width="60" height="60" fill="%23f0f0f0"/%3E%3Ctext x="50%" y="50%" text-anchor="middle" dy=".3em" fill="%23999" font-size="30"%3E🍰%3C/text%3E%3C/svg%3E';
            }
            
            productosHTML += `
    <div class="producto-ticket" data-producto-id="${item.id}">
        <img src="${imagenUrl}" data-producto-id="${item.id}" alt="${escapeHtml(item.nombre)}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 10px;" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'60\' height=\'60\' viewBox=\'0 0 60 60\'%3E%3Crect width=\'60\' height=\'60\' fill=\'%23f0f0f0\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'%23999\' font-size=\'30\'%3E🍰%3C/text%3E%3C/svg%3E'">
        <div class="producto-info-ticket">
            <h4>${escapeHtml(item.nombre)}</h4>
            <p>Cantidad: ${item.cantidad} × $${parseFloat(item.precio).toFixed(2)}</p>
        </div>
        <div class="producto-subtotal">
            <strong>$${(item.precio * item.cantidad).toFixed(2)}</strong>
        </div>
    </div>
`;
    }

    const envioTexto = pedido.envio === 0 ? 'Gratis' : `$${parseFloat(pedido.envio).toFixed(2)}`;

    document.getElementById('ticketContent').innerHTML = `
        <div class="ticket-print-area">
            <div style="text-align: center; margin-bottom: 20px;">
                <h2>🍰 Pastelería</h2>
                <p>Dulces momentos desde 2020</p>
                <p>Av. Principal #123, Ciudad de México</p>
                <p>Tel: 55 1234 5678</p>
            </div>
            
            <h3>📋 COMPROBANTE DE COMPRA</h3>
            <div class="info-row">
                <div><strong>Pedido #:</strong> ${pedidoId}</div>
                <div><strong>Fecha:</strong> ${fecha}</div>
            </div>
            
            <h4>🛒 Productos</h4>
            ${productosHTML}
            
            <div class="ticket-totales">
                <p><strong>Subtotal:</strong> $${parseFloat(pedido.subtotal).toFixed(2)}</p>
                <p><strong>Envío:</strong> ${envioTexto}</p>
                <p class="total-grande"><strong>TOTAL:</strong> $${parseFloat(pedido.total).toFixed(2)}</p>
            </div>
            
            <div class="info-row">
                <div>
                    <strong>🚚 Entrega:</strong><br>
                    ${pedido.delivery_type === 'domicilio' ? '🏠 Envío a domicilio' : '🏪 Recoger en tienda'}
                </div>
                <div>
                    <strong>📅 Fecha estimada:</strong><br>
                    ${fechaEstimadaStr}
                </div>
            </div>
            
            <div class="info-row">
                <div>
                    <strong>📍 Dirección:</strong><br>
                    ${pedido.direccion || 'Recoger en tienda'}
                </div>
                <div>
                    <strong>💳 Pago:</strong><br>
                    ${pedido.payment_method?.toUpperCase()}
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px dashed #ccc; font-size: 10px; color: #999;">
                <p>¡Gracias por tu compra!</p>
                <p>Este comprobante es válido como ticket de compra</p>
            </div>
        </div>
    `;
}

        function imprimirTicket() {
    const contenido = document.getElementById('ticketContent').innerHTML;
    const baseUrl = window.location.protocol + '//' + window.location.host + '/eslava/pasteleria';
    
    const ventana = window.open('', '_blank');
    ventana.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Ticket de Compra - Pastelería</title>
            <meta charset="UTF-8">
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { 
                    font-family: 'Segoe UI', Arial, sans-serif; 
                    padding: 20px; 
                    background: white;
                }
                .ticket {
                    max-width: 800px;
                    margin: 0 auto;
                    background: white;
                }
                .producto-ticket {
                    display: flex;
                    align-items: center;
                    gap: 15px;
                    padding: 10px;
                    border-bottom: 1px solid #eee;
                }
                .producto-ticket img { 
                    width: 60px; 
                    height: 60px; 
                    object-fit: cover; 
                    border-radius: 8px;
                    background: #f5f5f5;
                }
                .producto-info-ticket { flex: 1; }
                .producto-info-ticket h4 { margin-bottom: 5px; color: #6B3E6B; }
                .ticket-totales { text-align: right; padding: 15px; margin-top: 15px; border-top: 1px solid #eee; }
                .total-grande { font-size: 1.2rem; font-weight: bold; color: #D4AF37; }
                .info-row { display: flex; justify-content: space-between; margin: 10px 0; flex-wrap: wrap; gap: 10px; }
                h3 { color: #6B3E6B; margin: 15px 0 10px; border-bottom: 2px solid #E8D5E8; padding-bottom: 5px; }
                h4 { color: #6B3E6B; margin: 10px 0; }
                .ticket-print-area { padding: 10px; }
                @media print {
                    body { padding: 0; }
                    button { display: none; }
                }
            </style>
        </head>
        <body>
            <div class="ticket">
                ${contenido}
            </div>
            <script>
                window.print();
                window.onafterprint = function() { window.close(); };
            <\/script>
        </body>
        </html>
    `);
    ventana.document.close();
}

        function descargarTicket() {
            const contenido = document.getElementById('ticketContent').innerHTML;
            const blob = new Blob([`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Ticket de Compra - Pastelería</title>
                    <meta charset="UTF-8">
                    <style>
                        * { margin: 0; padding: 0; box-sizing: border-box; }
                        body { font-family: Arial, sans-serif; padding: 20px; }
                        .producto-ticket { display: flex; gap: 15px; padding: 10px; border-bottom: 1px solid #eee; }
                        .producto-ticket img { width: 50px; height: 50px; }
                        .ticket-totales { text-align: right; margin-top: 15px; }
                        .total-grande { font-size: 1.2rem; font-weight: bold; color: #D4AF37; }
                        h3, h4 { color: #6B3E6B; }
                    </style>
                </head>
                <body>${contenido}</body>
                </html>
            `], {type: 'text/html'});
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `ticket_pedido_${pedidoId}.html`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            alert('✅ Ticket descargado como HTML');
        }

        async function enviarCorreo() {
            const btn = document.querySelector('.btn-email');
            const originalText = btn.innerHTML;
            btn.innerHTML = '⏳ Enviando...';
            btn.disabled = true;
            
            const ticketHTML = document.getElementById('ticketContent').innerHTML;
            
            try {
                const response = await fetch('../../../api/enviar_ticket.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    credentials: 'include',
                    body: JSON.stringify({
                        email: pedido.email,
                        nombre: pedido.nombre,
                        pedido_id: pedidoId,
                        ticket_html: ticketHTML
                    })
                });
                
                const result = await response.json();
                if(result.success) {
                    alert('✅ Ticket enviado a ' + pedido.email);
                } else {
                    alert('❌ Error: ' + result.message);
                }
            } catch(error) {
                alert('❌ Error al enviar el correo');
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }

        function escapeHtml(text) {
            if(!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        cargarTicket();
    </script>
</body>
</html>