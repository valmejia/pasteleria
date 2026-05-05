<?php
// Simular el ticket HTML
$ticketHTML = '
<div class="producto-ticket" data-producto-id="1">
    <img src="../../../api/imagen_producto.php?id=1">
    <div class="producto-info-ticket">
        <h4>Torta de Chocolate</h4>
    </div>
</div>
<div class="producto-ticket" data-producto-id="3">
    <img src="../../../api/imagen_producto.php?id=3">
    <div class="producto-info-ticket">
        <h4>Galletas de Mantequilla</h4>
    </div>
</div>
';

preg_match_all('/data-producto-id="(\d+)"/', $ticketHTML, $matches);
echo "<h1>IDs extraídos:</h1>";
echo "<pre>";
print_r($matches[1]);
echo "</pre>";
?>