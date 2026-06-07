<?php
session_start();
require_once '../../config/conexion.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['datos_pasajeros'] = $_POST['pasajero'];
}

// Consultas Historial Final (Vuelo y Plan)
$stmtV = $pdo->prepare("SELECT numero_vuelo, precio_base_vuelo FROM vuelos WHERE id_vuelo = ?");
$stmtV->execute([$_SESSION['id_vuelo']]);
$vuelo_sel = $stmtV->fetch(PDO::FETCH_ASSOC);

$stmtP = $pdo->prepare("SELECT nombre_plan, cargo_extra_plan FROM planes_tarifas WHERE id_plan = ?");
$stmtP->execute([$_SESSION['id_plan']]);
$plan_sel = $stmtP->fetch(PDO::FETCH_ASSOC);

// Subtotal base multiplicado por la cantidad de pasajeros
$total_final = ($vuelo_sel['precio_base_vuelo'] + $plan_sel['cargo_extra_plan']) * $_SESSION['pasajeros'];

$metodos = $pdo->query("SELECT id_metodo_pago, nombre_metodo, banco_proveedor FROM metodos_pago")->fetchAll(PDO::FETCH_ASSOC);
include_once '../../includes/header.php';
?>
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; max-width: 1200px; margin: 20px auto; padding: 0 20px;">
    
    <form action="confirmar.php" method="POST">
        <div class="card" style="border: 1px solid #ddd; padding: 20px; border-radius: 8px; background:#fff;">
            <h2>Método de Pago</h2>
            <div style="margin: 15px 0; display:flex; flex-direction:column; gap:8px;">
                <?php foreach($metodos as $index => $m): ?>
                    <label style="cursor:pointer;">
                        <input type="radio" name="id_metodo_pago" value="<?=$m['id_metodo_pago']?>" <?=$index === 0 ? 'checked' : ''?>> 
                        <strong><?=$m['nombre_metodo']?></strong> — <span style="color:#555; font-size:13px;"><?=$m['banco_proveedor']?></span>
                    </label>
                <?php endforeach; ?>
            </div>
            <div style="display:flex; flex-direction:column; gap:10px; margin-top:15px;">
                <input type="text" placeholder="Número de Tarjeta" required style="padding:10px; border:1px solid #ccc; border-radius:4px;">
                <input type="text" placeholder="Nombre del Titular" required style="padding:10px; border:1px solid #ccc; border-radius:4px;">
            </div>
        </div>
        <button type="submit" style="background:#28a745; color:#fff; padding:12px 25px; border:none; border-radius:4px; cursor:pointer; font-weight:bold; margin-top:20px; font-size:16px;">Finalizar Reserva y Pagar</button>
    </form>

    <div style="border: 2px solid green; padding: 20px; border-radius: 8px; background: #f4fff4; height: fit-content; position: sticky; top: 20px;">
        <h3 style="color:green; border-bottom: 2px solid green; padding-bottom: 10px; margin-top:0;">Detalle Final de Pago</h3>
        <p style="font-size:14px;"><strong>Vuelo:</strong> <?=$vuelo_sel['numero_vuelo']?> ($<?=number_format($vuelo_sel['precio_base_vuelo'], 2)?> c/u)</p>
        <p style="font-size:14px;"><strong>Tarifa:</strong> <?=$plan_sel['nombre_plan']?> (+$<?=number_format($plan_sel['cargo_extra_plan'], 2)?> c/u)</p>
        <p style="font-size:14px;"><strong>Cantidad de Pasajeros:</strong> x<?=$_SESSION['pasajeros']?></p>
        
        <p style="margin-bottom:2px; font-weight:bold; margin-top:10px; font-size:14px;">Asientos Elegidos:</p>
        <ul style="margin:0; padding-left:20px; font-size:13px; color:#444;">
            <?php 
            if (!empty($_SESSION['id_asiento'])) {
                foreach ($_SESSION['id_asiento'] as $num_pasajero => $id_asiento_sel) {
                    $stmtA = $pdo->prepare("SELECT numero_asiento, cargo_extra FROM asientos_avion WHERE id_asiento_avion = ?");
                    $stmtA->execute([$id_asiento_sel]);
                    $asiento_sel = $stmtA->fetch(PDO::FETCH_ASSOC);
                    
                    if ($asiento_sel) {
                        $total_final += $asiento_sel['cargo_extra'];
                        ?>
                        <li>Pasajero #<?=$num_pasajero?>: Asiento <strong><?=$asiento_sel['numero_asiento']?></strong> (+$<?=number_format($asiento_sel['cargo_extra'], 2)?>)</li>
                        <?php
                    }
                }
            }
            ?>
        </ul>
        
        <?php 
        // Sumar Equipajes al total final
        if(!empty($_SESSION['equipajes'])) {
            foreach($_SESSION['equipajes'] as $id => $cant) {
                $stmtE = $pdo->prepare("SELECT precio_unitario FROM tipos_equipaje WHERE id_tipo_equipaje = ?");
                $stmtE->execute([$id]); 
                $total_final += ($stmtE->fetchColumn() * $cant);
            }
        }
        // Sumar Servicios al total final
        if(!empty($_SESSION['servicios'])) {
            foreach($_SESSION['servicios'] as $id_serv) {
                $stmtS = $pdo->prepare("SELECT precio_servicio FROM servicios_adicionales WHERE id_servicio = ?");
                $stmtS->execute([$id_serv]); 
                $total_final += $stmtS->fetchColumn();
            }
        }
        ?>
        
        <hr style="border:0; border-top:1px dashed green; margin-top:15px;">
        <h3 style="margin:10px 0; display:flex; justify-content:space-between; color:green;">
            <span>TOTAL A PAGAR:</span>
            <span>$<?=number_format($total_final, 2)?></span>
        </h3>
    </div>
</div>
</body>
</html>