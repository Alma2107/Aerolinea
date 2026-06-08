<?php
session_start();
require_once '../../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CAPTURA NUEVA: Ahora recibimos el string del asiento desde el mapa (ej: "1A", "14B")
    if (isset($_POST['asiento_seleccionado'])) {
        $_SESSION['asiento_seleccionado'] = $_POST['asiento_seleccionado']; 
    }
}

// Consultas para el Historial Lateral (Vuelo y Plan)
$stmtV = $pdo->prepare("SELECT numero_vuelo, precio_base_vuelo FROM vuelos WHERE id_vuelo = ?");
$stmtV->execute([$_SESSION['id_vuelo'] ?? 1]);
$vuelo_sel = $stmtV->fetch(PDO::FETCH_ASSOC) ?: ['numero_vuelo' => 'No definido', 'precio_base_vuelo' => 0.00];

$stmtP = $pdo->prepare("SELECT nombre_plan, cargo_extra_plan FROM planes_tarifas WHERE id_plan = ?");
$stmtP->execute([$_SESSION['id_plan'] ?? 1]);
$plan_sel = $stmtP->fetch(PDO::FETCH_ASSOC) ?: ['nombre_plan' => 'Estándar', 'cargo_extra_plan' => 0.00];

// Calculamos el subtotal base (Vuelo + Plan) multiplicado por los pasajeros
$cantidad = $_SESSION['pasajeros'] ?? 1;
$total_acumulado = ((float)$vuelo_sel['precio_base_vuelo'] + (float)$plan_sel['cargo_extra_plan']) * $cantidad;

include_once '../../includes/header.php';
?>
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; max-width: 1200px; margin: 20px auto; padding: 0 20px; font-family: sans-serif;">
    
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
            // NUEVA LÓGICA DINÁMICA: No lee la BD, calcula el precio interpretando la fila del asiento string
            if (!empty($_SESSION['asiento_seleccionado'])) {
                foreach ($_SESSION['asiento_seleccionado'] as $num_pasajero => $asiento_texto) {
                    
                    // Extraemos los dígitos del asiento (de "14B" extrae 14)
                    preg_match('/(\d+)/', $asiento_texto, $matches);
                    $fila = (int)($matches[1] ?? 1);
                    
                    // Aplicamos el mismo tarifario del mapa de asientos
                    $cargo_extra_asiento = 0.00;
                    if ($fila == 1) {
                        $cargo_extra_asiento = 5000.00;
                    } elseif ($fila >= 2 && $fila <= 5) {
                        $cargo_extra_asiento = 12000.00;
                    } elseif ($fila == 6 || $fila == 7) {
                        $cargo_extra_asiento = 3000.00;
                    } elseif ($fila == 12 || $fila == 13) {
                        $cargo_extra_asiento = 4500.00;
                    }
                    
                    $total_acumulado += $cargo_extra_asiento;
                    ?>
                    <li>Pasajero #<?=$num_pasajero?>: Asiento <strong><?=$asiento_texto?></strong> (+$<?=number_format($cargo_extra_asiento, 2)?>)</li>
                    <?php
                }
            }
            ?>
        </ul>
        
        <?php if(!empty($_SESSION['equipajes'])): ?>
            <p style="margin-bottom:2px; font-weight:bold; margin-top:10px;">Equipaje Extra:</p>
            <ul style="margin:0; padding-left:20px; font-size:13px; color:#444;">
                <?php foreach($_SESSION['equipajes'] as $num_p => $items): ?>
                    <?php foreach($items as $id => $cant): 
                        $stmtE = $pdo->prepare("SELECT nombre_tipo, precio_unitario FROM tipos_equipaje WHERE id_tipo_equipaje = ?");
                        $stmtE->execute([$id]);
                        $eq = $stmtE->fetch(PDO::FETCH_ASSOC);
                        if ($eq) {
                            $total_acumulado += ($eq['precio_unitario'] * $cant);
                        }
                    ?>
                        <li>Pasajero #<?=$num_p?>: <?=$eq['nombre_tipo'] ?? 'Equipaje'?> (x<?=$cant?>) +$<?=number_format((($eq['precio_unitario'] ?? 0) * $cant), 2)?></li>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if(!empty($_SESSION['servicios'])): ?>
            <p style="margin-bottom:2px; font-weight:bold; margin-top:10px;">Servicios adicionales:</p>
            <ul style="margin:0; padding-left:20px; font-size:13px; color:#444;">
                <?php foreach($_SESSION['servicios'] as $num_p => $servicios_p): ?>
                    <?php foreach($servicios_p as $id_serv): 
                        $stmtS = $pdo->prepare("SELECT nombre_servicio, precio_servicio FROM servicios_adicionales WHERE id_servicio = ?");
                        $stmtS->execute([$id_serv]);
                        $srv = $stmtS->fetch();
                        if ($srv) {
                            $total_acumulado += $srv['precio_servicio'];
                        }
                    ?>
                        <li>Pasajero #<?=$num_p?>: <?=$srv['nombre_servicio'] ?? 'Servicio'?> +$<?=number_format(($srv['precio_servicio'] ?? 0), 2)?></li>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        
        <hr style="border:0; border-top:1px dashed #ccc; margin-top:15px;">
        <h4 style="margin:10px 0; display:flex; justify-content:space-between;">
            <span>Total Acumulado:</span>
            <span style="color:green;">$<?=number_format($total_acumulado, 2)?></span>
        </h4>
    </div>
</div>
</body>
</html>