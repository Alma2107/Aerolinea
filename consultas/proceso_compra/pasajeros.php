<?php
session_start();
require_once '../../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ahora se guarda como un array: [1 => 'id_asiento_p1', 2 => 'id_asiento_p2']
    $_SESSION['id_asiento'] = $_POST['id_asiento']; 
}

// Consultas para el Historial Lateral (Vuelo y Plan)
$stmtV = $pdo->prepare("SELECT numero_vuelo, precio_base_vuelo FROM vuelos WHERE id_vuelo = ?");
$stmtV->execute([$_SESSION['id_vuelo']]);
$vuelo_sel = $stmtV->fetch(PDO::FETCH_ASSOC);

$stmtP = $pdo->prepare("SELECT nombre_plan, cargo_extra_plan FROM planes_tarifas WHERE id_plan = ?");
$stmtP->execute([$_SESSION['id_plan']]);
$plan_sel = $stmtP->fetch(PDO::FETCH_ASSOC);

// Calculamos el subtotal base (Vuelo + Plan) multiplicado por los pasajeros
$total_acumulado = ($vuelo_sel['precio_base_vuelo'] + $plan_sel['cargo_extra_plan']) * $_SESSION['pasajeros'];

$cantidad = $_SESSION['pasajeros'] ?? 1;
include_once '../../includes/header.php';
?>
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; max-width: 1200px; margin: 20px auto; padding: 0 20px;">
    
    <form action="pago.php" method="POST">
        <h2>Información de los Pasajeros</h2>
        <?php for($i = 1; $i <= $cantidad; $i++): ?>
            <div class="card" style="border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 6px; background:#fff;">
                <h3>Pasajero #<?=$i?></h3>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-top:10px;">
                    <input type="text" name="pasajero[<?=$i?>][nombre]" placeholder="Nombre" required style="padding:8px;">
                    <input type="text" name="pasajero[<?=$i?>][apellido]" placeholder="Apellido" required style="padding:8px;">
                    <select name="pasajero[<?=$i?>][tipo_doc]" required style="padding:8px;">
                        <option value="DNI">DNI</option>
                        <option value="Pasaporte">Pasaporte</option>
                    </select>
                    <input type="text" name="pasajero[<?=$i?>][doc]" placeholder="Documento" required style="padding:8px;">
                    <input type="date" name="pasajero[<?=$i?>][fnac]" required style="padding:8px;">
                </div>
                <div style="margin-top:10px;">
                    <label><input type="checkbox" name="pasajero[<?=$i?>][asistencia]" value="1"> Requiere asistencia especial</label>
                    <textarea name="pasajero[<?=$i?>][detalles_medicos]" placeholder="Detalles médicos si requiere asistencia..." style="width:100%; margin-top:5px; height:40px; resize:none;"></textarea>
                </div>
            </div>
        <?php endfor; ?>
        <button type="submit" style="background:#0056b3; color:#fff; padding:12px 25px; border:none; border-radius:4px; cursor:pointer; font-weight:bold; margin-top:10px;">Ir al Pago</button>
    </form>

    <div style="border: 1px solid #0056b3; padding: 20px; border-radius: 8px; background: #f4f8ff; height: fit-content; position: sticky; top: 20px;">
        <h3 style="color:#0056b3; border-bottom: 2px solid #0056b3; padding-bottom: 10px; margin-top:0;">Resumen de tu Viaje</h3>
        <p><strong>Vuelo:</strong> <?=$vuelo_sel['numero_vuelo']?></p>
        <p><strong>Tarifa:</strong> <?=$plan_sel['nombre_plan']?></p>
        <p><strong>Pasajeros:</strong> x<?=$cantidad?></p>
        
        <p style="margin-bottom:2px; font-weight:bold; margin-top:10px;">Asientos Asignados:</p>
        <ul style="margin:0; padding-left:20px; font-size:13px; color:#444;">
            <?php 
            if (!empty($_SESSION['id_asiento'])) {
                foreach ($_SESSION['id_asiento'] as $num_pasajero => $id_asiento_sel) {
                    $stmtA = $pdo->prepare("SELECT numero_asiento, cargo_extra FROM asientos_avion WHERE id_asiento_avion = ?");
                    $stmtA->execute([$id_asiento_sel]);
                    $asiento_sel = $stmtA->fetch(PDO::FETCH_ASSOC);
                    
                    if ($asiento_sel) {
                        $total_acumulado += $asiento_sel['cargo_extra'];
                        ?>
                        <li>Pasajero #<?=$num_pasajero?>: Asiento <strong><?=$asiento_sel['numero_asiento']?></strong> (+$<?=number_format($asiento_sel['cargo_extra'], 2)?>)</li>
                        <?php
                    }
                }
            }
            ?>
        </ul>
        
        <?php 
        // Sumar Equipajes al total acumulado
        if(!empty($_SESSION['equipajes'])) {
            foreach($_SESSION['equipajes'] as $id => $cant) {
                $stmtE = $pdo->prepare("SELECT precio_unitario FROM tipos_equipaje WHERE id_tipo_equipaje = ?");
                $stmtE->execute([$id]); 
                $total_acumulado += ($stmtE->fetchColumn() * $cant);
            }
        }
        // Sumar Servicios al total acumulado
        if(!empty($_SESSION['servicios'])) {
            foreach($_SESSION['servicios'] as $id_serv) {
                $stmtS = $pdo->prepare("SELECT precio_servicio FROM servicios_adicionales WHERE id_servicio = ?");
                $stmtS->execute([$id_serv]); 
                $total_acumulado += $stmtS->fetchColumn();
            }
        }
        ?>
        
        <hr style="border:0; border-top:1px dashed #ccc; margin-top:15px;">
        <h4 style="margin:10px 0; display:flex; justify-content:space-between;">
            <span>Total Acumulado:</span>
            <span style="color:green;">$<?=number_format($total_acumulado, 2)?></span>
        </h4>
    </div>
</div>
</body>
</html>