<?php
require_once '../../config/conexion.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['id_vuelo'] = $_POST['id_vuelo'];
    $_SESSION['id_plan'] = $_POST['id_plan'];
}

// Consultas para el panel de historial
$stmtV = $pdo->prepare("SELECT numero_vuelo, precio_base_vuelo FROM vuelos WHERE id_vuelo = ?");
$stmtV->execute([$_SESSION['id_vuelo']]);
$vuelo_sel = $stmtV->fetch(PDO::FETCH_ASSOC);

$stmtP = $pdo->prepare("SELECT nombre_plan, cargo_extra_plan FROM planes_tarifas WHERE id_plan = ?");
$stmtP->execute([$_SESSION['id_plan']]);
$plan_sel = $stmtP->fetch(PDO::FETCH_ASSOC);

$subtotal_inicial = ($vuelo_sel['precio_base_vuelo'] + $plan_sel['cargo_extra_plan']) * $_SESSION['pasajeros'];

$equipajes_disponibles = $pdo->query("SELECT id_tipo_equipaje, nombre_tipo, descripcion, precio_unitario FROM tipos_equipaje")->fetchAll(PDO::FETCH_ASSOC);
include_once '../../includes/header.php';
?>
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; max-width: 1200px; margin: 20px auto; padding: 0 20px;">
    
    <form action="servicios.php" method="POST">
        <div class="card" style="border: 1px solid #ddd; padding: 20px; border-radius: 8px; background:#fff;">
            <h2>¿Llevás equipaje adicional en bodega?</h2>
            <p style="color:green; margin-bottom:15px; font-size:13px;">🎒 Bolso de mano bajo el asiento incluido en tu plan <?=$plan_sel['nombre_plan']?>.</p>
            
            <div style="display:grid; grid-template-columns: 1fr; gap:15px;">
                <?php foreach($equipajes_disponibles as $e): ?>
                    <div style="border: 1px solid #ddd; padding: 15px; border-radius: 6px; display:flex; justify-content:space-between; align-items:center; background:#fafafa;">
                        <div>
                            <h4 style="margin:0;"><?=$e['nombre_tipo']?></h4>
                            <p style="font-size: 12px; color: #555; margin:5px 0 0 0;"><?=$e['descripcion']?></p>
                            <p style="margin:5px 0 0 0; color:#0056b3;"><strong>$<?=number_format($e['precio_unitario'], 2)?> cada uno</strong></p>
                        </div>
                        <input type="number" name="equipaje[<?=$e['id_tipo_equipaje']?>]" value="0" min="0" max="5" style="width: 60px; padding: 8px; text-align:center;">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <button type="submit" style="background:#0056b3; color:#fff; padding:12px 25px; border:none; border-radius:4px; cursor:pointer; font-weight:bold; margin-top:20px;">Continuar a Servicios</button>
    </form>

    <div style="border: 1px solid #0056b3; padding: 20px; border-radius: 8px; background: #f4f8ff; height: fit-content; position: sticky; top: 20px;">
        <h3 style="color:#0056b3; border-bottom: 2px solid #0056b3; padding-bottom: 10px; margin-top:0;">Resumen de tu Viaje</h3>
        <p><strong>Vuelo:</strong> <?=$vuelo_sel['numero_vuelo']?> ($<?=number_format($vuelo_sel['precio_base_vuelo'], 2)?> c/u)</p>
        <p><strong>Tarifa:</strong> <?=$plan_sel['nombre_plan']?> (+$<?=number_format($plan_sel['cargo_extra_plan'], 2)?> c/u)</p>
        <p><strong>Pasajeros:</strong> <?=$_SESSION['pasajeros']?></p>
        <hr style="border:0; border-top:1px dashed #ccc;">
        <h4 style="margin:10px 0; display:flex; justify-content:space-between;">
            <span>Total Acumulado:</span>
            <span style="color:green;">$<?=number_format($subtotal_inicial, 2)?></span>
        </h4>
    </div>
</div>
</body>
</html>