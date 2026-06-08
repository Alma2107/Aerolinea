<?php
require_once '../../config/conexion.php';
session_start();

// 1. CAPTURAR DATOS SI VIENEN POR POST, O MANTENER LOS EXISTENTES EN LA SESIÓN
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id_vuelo'])) $_SESSION['id_vuelo'] = $_POST['id_vuelo'];
    if (isset($_POST['id_plan'])) $_SESSION['id_plan'] = $_POST['id_plan'];
}

// Valores de respaldo (fallbacks) por si no hay nada en la sesión (ej: reiniciaste XAMPP)
$id_vuelo_actual = $_SESSION['id_vuelo'] ?? 1;
$id_plan_actual  = $_SESSION['id_plan'] ?? 1;

// 2. CONSULTAS BLINDADAS PARA EL PANEL DE HISTORIAL
$stmtV = $pdo->prepare("SELECT numero_vuelo, precio_base_vuelo FROM vuelos WHERE id_vuelo = ?");
$stmtV->execute([$id_vuelo_actual]);
$vuelo_sel = $stmtV->fetch(PDO::FETCH_ASSOC);

// Si la consulta no devolvió nada, creamos un arreglo de respaldo para evitar Warnings
if (!$vuelo_sel) {
    $vuelo_sel = ['numero_vuelo' => 'No asignado', 'precio_base_vuelo' => 0.00];
}

$stmtP = $pdo->prepare("SELECT nombre_plan, cargo_extra_plan FROM planes_tarifas WHERE id_plan = ?");
$stmtP->execute([$id_plan_actual]);
$plan_sel = $stmtP->fetch(PDO::FETCH_ASSOC);

// Si el plan no existe, creamos un arreglo de respaldo
if (!$plan_sel) {
    $plan_sel = ['nombre_plan' => 'Estándar', 'cargo_extra_plan' => 0.00];
}

$cantidad_pasajeros = $_SESSION['pasajeros'] ?? 1;
$subtotal_inicial = ((float)$vuelo_sel['precio_base_vuelo'] + (float)$plan_sel['cargo_extra_plan']) * $cantidad_pasajeros;

$equipajes_disponibles = $pdo->query("SELECT id_tipo_equipaje, nombre_tipo, descripcion, precio_unitario FROM tipos_equipaje")->fetchAll(PDO::FETCH_ASSOC);
include_once '../../includes/header.php';
?>
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; max-width: 1200px; margin: 20px auto; padding: 0 20px; font-family: sans-serif;">
    
    <form action="servicios.php" method="POST">
        
        <?php for($i = 1; $i <= $cantidad_pasajeros; $i++): ?>
            <div class="card" style="border: 1px solid #ddd; padding: 20px; border-radius: 8px; background:#fff; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <h3 style="margin-top:0; color:#0056b3;">👤 Pasajero #<?=$i?>: Equipaje adicional</h3>
                <p style="color:green; margin-bottom:15px; font-size:13px;">🎒 Bolso de mano bajo el asiento incluido en tu plan <strong><?=$plan_sel['nombre_plan']?></strong>.</p>
                
                <div style="display:grid; grid-template-columns: 1fr; gap:15px;">
                    <?php foreach($equipajes_disponibles as $e): ?>
                        <div style="border: 1px solid #ddd; padding: 15px; border-radius: 6px; display:flex; justify-content:space-between; align-items:center; background:#fafafa;">
                            <div>
                                <h4 style="margin:0;"><?=$e['nombre_tipo']?></h4>
                                <p style="font-size: 12px; color: #555; margin:5px 0 0 0;"><?=$e['descripcion']?></p>
                                <p style="margin:5px 0 0 0; color:#0056b3;"><strong>$<?=number_format($e['precio_unitario'], 2)?> cada uno</strong></p>
                            </div>
                            <input type="number" name="equipaje[<?=$i?>][<?=$e['id_tipo_equipaje']?>]" value="0" min="0" max="5" style="width: 60px; padding: 8px; text-align:center;">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endfor; ?>

        <button type="submit" style="background:#0056b3; color:#fff; padding:12px 25px; border:none; border-radius:4px; cursor:pointer; font-weight:bold; margin-top:10px; font-size:15px;">Continuar a Servicios</button>
    </form>

    <div style="border: 1px solid #0056b3; padding: 20px; border-radius: 8px; background: #f4f8ff; height: fit-content; position: sticky; top: 20px;">
        <h3 style="color:#0056b3; border-bottom: 2px solid #0056b3; padding-bottom: 10px; margin-top:0;">Resumen de tu Viaje</h3>
        <p><strong>Vuelo:</strong> <?=$vuelo_sel['numero_vuelo']?> ($<?=number_format($vuelo_sel['precio_base_vuelo'], 2)?> c/u)</p>
        <p><strong>Tarifa:</strong> <?=$plan_sel['nombre_plan']?> (+$<?=number_format($plan_sel['cargo_extra_plan'], 2)?> c/u)</p>
        <p><strong>Pasajeros:</strong> x<?=$cantidad_pasajeros?></p>
        <hr style="border:0; border-top:1px dashed #ccc;">
        <h4 style="margin:10px 0; display:flex; justify-content:space-between;">
            <span>Total Acumulado:</span>
            <span style="color:green;">$<?=number_format($subtotal_inicial, 2)?></span>
        </h4>
    </div>
</div>
</body>
</html>