<?php
require_once '../../config/conexion.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['id_vuelo'] = $_POST['id_vuelo'];
    $_SESSION['id_plan'] = $_POST['id_plan'];
}

$equipajes_disponibles = $pdo->query("SELECT id_tipo_equipaje, nombre_tipo, descripcion, precio_unitario FROM tipos_equipaje")->fetchAll(PDO::FETCH_ASSOC);
include_once '../../includes/header.php';
?>
<form action="servicios.php" method="POST">
    <div class="booking-layout">
        <div class="main-content">
            <div class="card">
                <h2>¿Llevás equipaje adicional?</h2>
                <p style="color:green; margin-bottom:15px;">Bolso o mochila de mano (bajo el asiento) ya incluida en tu tarifa base.</p>
                
                <div class="grid-equipaje">
                    <?php foreach($equipajes_disponibles as $e): ?>
                        <div class="equipaje-card" style="border: 1px solid #ddd; padding: 15px; margin-bottom: 10px; border-radius: 6px;">
                            <h4><?=$e['nombre_tipo']?></h4>
                            <p style="font-size: 12px; color: #555;"><?=$e['descripcion']?></p>
                            <p><strong>$<?=number_format($e['precio_unitario'], 2)?> c/u</strong></p>
                            <label>Cantidad: 
                                <input type="number" name="equipaje[<?=$e['id_tipo_equipaje']?>]" value="0" min="0" max="5" style="width: 60px; padding: 5px;">
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <button type="submit" class="btn-next">Continuar a Servicios</button>
        </div>
    </div>
</form>
</body>
</html>