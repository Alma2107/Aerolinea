<?php
require_once '../../config/conexion.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['equipajes'] = [];
    // Procesamos y guardamos el equipaje de cada pasajero de forma organizada
    if (isset($_POST['equipaje'])) {
        foreach ($_POST['equipaje'] as $num_pasajero => $lista_cantidades) {
            foreach ($lista_cantidades as $id_tipo => $cantidad) {
                if ((int)$cantidad > 0) {
                    $_SESSION['equipajes'][$num_pasajero][$id_tipo] = (int)$cantidad;
                }
            }
        }
    }
}

// Consultas para el historial lateral
$stmtV = $pdo->prepare("SELECT numero_vuelo, precio_base_vuelo FROM vuelos WHERE id_vuelo = ?");
$stmtV->execute([$_SESSION['id_vuelo']]);
$vuelo_sel = $stmtV->fetch(PDO::FETCH_ASSOC);

$stmtP = $pdo->prepare("SELECT nombre_plan, cargo_extra_plan FROM planes_tarifas WHERE id_plan = ?");
$stmtP->execute([$_SESSION['id_plan']]);
$plan_sel = $stmtP->fetch(PDO::FETCH_ASSOC);

$cantidad_pasajeros = $_SESSION['pasajeros'] ?? 1;
$total_acumulado = ($vuelo_sel['precio_base_vuelo'] + $plan_sel['cargo_extra_plan']) * $cantidad_pasajeros;

$servicios = $pdo->query("SELECT id_servicio, nombre_servicio, descripcion, precio_servicio FROM servicios_adicionales")->fetchAll(PDO::FETCH_ASSOC);
include_once '../../includes/header.php';
?>
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; max-width: 1200px; margin: 20px auto; padding: 0 20px;">
    
    <form action="asientos.php" method="POST">
        
        <?php for($i = 1; $i <= $cantidad_pasajeros; $i++): ?>
            <div class="card" style="border: 1px solid #ddd; padding: 20px; border-radius: 8px; background:#fff; margin-bottom:20px;">
                <h3 style="margin-top:0; color:#0056b3;">👤 Pasajero #<?=$i?>: Servicios adicionales a bordo</h3>
                <p style="margin-bottom:15px; color:#666; font-size:13px;">Sumá extras opcionales exclusivos para este pasajero.</p>
                
                <div style="display: grid; grid-template-columns: 1fr; gap: 10px;">
                    <?php foreach($servicios as $s): ?>
                        <div style="border: 1px solid #eee; padding: 12px; border-radius: 4px; background:#fafafa;">
                            <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                                <input type="checkbox" name="servicios[<?=$i?>][]" value="<?=$s['id_servicio']?>"> 
                                <div>
                                    <strong><?=$s['nombre_servicio']?></strong> (+$<?=number_format($s['precio_servicio'], 2)?>)
                                    <p style="font-size: 11px; color:#666; margin:3px 0 0 0;"><?=$s['descripcion']?></p>
                                </div>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endfor; ?>

        <button type="submit" style="background:#0056b3; color:#fff; padding:12px 25px; border:none; border-radius:4px; cursor:pointer; font-weight:bold; margin-top:10px; font-size:15px;">Continuar a Elegir Asientos</button>
    </form>

    <div style="border: 1px solid #0056b3; padding: 20px; border-radius: 8px; background: #f4f8ff; height: fit-content; position: sticky; top: 20px;">
        <h3 style="color:#0056b3; border-bottom: 2px solid #0056b3; padding-bottom: 10px; margin-top:0;">Resumen de tu Viaje</h3>
        <p><strong>Vuelo:</strong> <?=$vuelo_sel['numero_vuelo']?></p>
        <p><strong>Tarifa:</strong> <?=$plan_sel['nombre_plan']?></p>
        <p><strong>Pasajeros:</strong> x<?=$cantidad_pasajeros?></p>
        
        <?php if(!empty($_SESSION['equipajes'])): ?>
            <p style="margin-bottom:2px; font-weight:bold; margin-top:10px;">Equipaje Extra Seleccionado:</p>
            <ul style="margin:0; padding-left:20px; font-size:13px; color:#444;">
                <?php foreach($_SESSION['equipajes'] as $num_p => $items): ?>
                    <?php foreach($items as $id => $cant): 
                        $stmtE = $pdo->prepare("SELECT nombre_tipo, precio_unitario FROM tipos_equipaje WHERE id_tipo_equipaje = ?");
                        $stmtE->execute([$id]);
                        $eq = $stmtE->fetch(PDO::FETCH_ASSOC);
                        $total_acumulado += ($eq['precio_unitario'] * $cant);
                    ?>
                        <li>Pasajero #<?=$num_p?>: <?=$eq['nombre_tipo']?> (x<?=$cant?>) +$<?=number_format(($eq['precio_unitario'] * $cant), 2)?></li>
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