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
    <title>Confirmación - Pastelería</title>
    <link rel="stylesheet" href="../../../assets/css/checkout.css">
    <style>
        .confirmation-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 0 20px;
        }
        .confirmation-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .confirmation-icon {
            font-size: 4rem;
            margin-bottom: 20px;
        }
        .confirmation-card h1 {
            color: #6B3E6B;
            margin-bottom: 10px;
        }
        .confirmation-card > p {
            color: #666;
            margin-bottom: 30px;
        }
        .confirmation-details {
            background: #f9f5f9;
            border-radius: 15px;
            padding: 20px;
            text-align: left;
            margin: 20px 0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #E8D5E8;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-row strong {
            color: #6B3E6B;
        }
        .detail-row .total {
            font-size: 1.2rem;
            font-weight: bold;
            color: #D4AF37;
        }
        .ticket-info {
            background: #E8D5E8;
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
            font-size: 0.85rem;
            text-align: center;
        }
        .confirmation-actions {
            display: flex;
            gap: 15px;
            margin-top: 25px;
            justify-content: center;
        }
        .btn-primary, .btn-secondary {
            padding: 12px 24px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
            display: inline-block;
        }
        .btn-primary {
            background: linear-gradient(135deg, #D4AF37 0%, #C5A059 100%);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(212, 175, 55, 0.3);
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <div class="confirmation-card">
            <div class="confirmation-icon">✅</div>
            <h1>¡Compra exitosa!</h1>
            <p>Tu pedido ha sido registrado correctamente</p>
            
            <div class="confirmation-details" id="confirmationDetails">
                <div class="detail-row">
                    <strong>Cargando...</strong>
                    <span></span>
                </div>
            </div>
            
            <div class="confirmation-actions">
                <a href="../user/tienda.php" class="btn-primary">🍰 Seguir comprando</a>
                <button onclick="window.print()" class="btn-secondary">🖨️ Imprimir ticket</button>
            </div>
        </div>
    </div>

    <script>
        const pedido = JSON.parse(localStorage.getItem('ultimoPedido') || '{}');
        const pedidoId = <?php echo json_encode($pedidoId); ?>;
        
        function fechaEstimada() {
            const fecha = new Date();
            const dias = pedido.delivery_type === 'domicilio' ? 3 : 1;
            fecha.setDate(fecha.getDate() + dias);
            return fecha.toLocaleDateString('es-MX', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        }
        
        function formatearFecha() {
            const fecha = new Date();
            return fecha.toLocaleDateString('es-MX', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
        
        function getMetodoEntrega() {
            if(pedido.delivery_type === 'domicilio') return '🚚 Envío a domicilio';
            return '🏪 Recoger en tienda';
        }
        
        document.getElementById('confirmationDetails').innerHTML = `
            <div class="detail-row">
                <strong>📋 Número de pedido:</strong>
                <span>#${pedidoId || 'N/A'}</span>
            </div>
            <div class="detail-row">
                <strong>📅 Fecha:</strong>
                <span>${formatearFecha()}</span>
            </div>
            <div class="detail-row">
                <strong>🚚 Método de entrega:</strong>
                <span>${getMetodoEntrega()}</span>
            </div>
            <div class="detail-row">
                <strong>💳 Método de pago:</strong>
                <span>${pedido.payment_method?.toUpperCase() || 'N/A'}</span>
            </div>
            <div class="detail-row">
                <strong>📦 Fecha estimada de llegada:</strong>
                <span>📅 ${fechaEstimada()}</span>
            </div>
            <div class="detail-row">
                <strong>💰 Total pagado:</strong>
                <span class="total">$${(pedido.total || 0).toFixed(2)}</span>
            </div>
            <div class="detail-row">
                <strong>📍 Dirección:</strong>
                <span>${pedido.direccion || 'Recoger en tienda'}</span>
            </div>
            <div class="ticket-info">
                <p>📧 Se ha enviado un ticket de compra a tu correo electrónico: <strong>${pedido.email || 'No disponible'}</strong></p>
                <p>🔔 Guarda este número de pedido para cualquier consulta</p>
                <p>📞 Contacto: 55 1234 5678</p>
            </div>
        `;
        
        // Limpiar el pedido temporal después de mostrar
        setTimeout(() => {
            localStorage.removeItem('ultimoPedido');
        }, 5000);
    </script>
</body>
</html>