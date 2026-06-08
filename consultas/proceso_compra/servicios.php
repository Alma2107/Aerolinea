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

// Controladores de respaldo primarios (Detección de tramo inicial)
$id_vuelo_actual = $_SESSION['id_vuelo_ida'] ?? $_SESSION['id_vuelo'] ?? 1;
$id_plan_actual  = $_SESSION['id_plan'] ?? 1;
$cantidad_pasajeros = $_SESSION['pasajeros'] ?? 1;

// Si no está definido el vuelo genérico de control, le asignamos el de ida
if (!isset($_SESSION['id_vuelo'])) {
    $_SESSION['id_vuelo'] = $id_vuelo_actual;
}

// Consultas seguras para el historial lateral
$stmtV = $pdo->prepare("SELECT numero_vuelo, precio_base_vuelo FROM vuelos WHERE id_vuelo = ?");
$stmtV->execute([$id_vuelo_actual]);
$vuelo_sel = $stmtV->fetch(PDO::FETCH_ASSOC);

if (!$vuelo_sel) {
    $vuelo_sel = ['numero_vuelo' => 'No definido', 'precio_base_vuelo' => 0.00];
}

$stmtP = $pdo->prepare("SELECT nombre_plan, cargo_extra_plan FROM planes_tarifas WHERE id_plan = ?");
$stmtP->execute([$id_plan_actual]);
$plan_sel = $stmtP->fetch(PDO::FETCH_ASSOC);

if (!$plan_sel) {
    $plan_sel = ['nombre_plan' => 'Estándar', 'cargo_extra_plan' => 0.00];
}

// Suma base de vuelos y planes contratados en la primera pantalla
$total_acumulado = ((float)$vuelo_sel['precio_base_vuelo'] + (float)$plan_sel['cargo_extra_plan']) * $cantidad_pasajeros;

// Si existe vuelo de vuelta, sumamos su base al total inicial
if (!empty($_SESSION['id_vuelo_vuelta']) && $_SESSION['id_vuelo_vuelta'] > 0) {
    $stmtVV = $pdo->prepare("SELECT precio_base_vuelo FROM vuelos WHERE id_vuelo = ?");
    $stmtVV->execute([$_SESSION['id_vuelo_vuelta']]);
    $vuelo_vuelta = $stmtVV->fetch(PDO::FETCH_ASSOC);
    if ($vuelo_vuelta) {
        $total_acumulado += ((float)$vuelo_vuelta['precio_base_vuelo'] * $cantidad_pasajeros);
    }
}

$servicios = $pdo->query("SELECT id_servicio, nombre_servicio, descripcion, precio_servicio FROM servicios_adicionales")->fetchAll(PDO::FETCH_ASSOC);
include_once '../../includes/header.php';
?>

<link rel="stylesheet" href="css/proceso_compra/servicios.css">

<div class="contenedor-servicios">
    
    <form action="asientos.php" method="POST" id="form-servicios">
        
        <?php for($i = 1; $i <= $cantidad_pasajeros; $i++): ?>
            <div class="card">
                <h3>👤 Pasajero #<?=$i?>: Servicios adicionales a bordo</h3>
                <p class="subtitulo-card">Sumá extras opcionales exclusivos para este pasajero.</p>
                
                <div class="grid-servicios">
                    <?php foreach($servicios as $s): ?>
                        <div class="item-servicio">
                            <label class="label-servicio">
                                <input type="checkbox" name="servicios[<?=$i?>][]" value="<?=$s['id_servicio']?>" data-precio="<?=$s['precio_servicio']?>" data-nombre="P#<?=$i?> - <?=$s['nombre_servicio']?>" class="check-servicio"> 
                                <div>
                                    <strong><?=$s['nombre_servicio']?></strong> (+$<?=number_format($s['precio_servicio'], 2)?>)
                                    <p><?=$s['descripcion']?></p>
                                </div>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endfor; ?>

        <button type="submit" class="btn-continuar">Continuar a Elegir Asientos</button>
    </form>

    <div class="sidebar-resumen">
        <h3>Resumen de tu Viaje</h3>
        <p><strong>Vuelo principal:</strong> <?=$vuelo_sel['numero_vuelo']?></p>
        <p><strong>Tarifa base:</strong> <?=$plan_sel['nombre_plan']?></p>
        <p><strong>Pasajeros:</strong> x<?=$cantidad_pasajeros?></p>
        
        <?php 
        $subtotal_equipajes = 0;
        if(!empty($_SESSION['equipajes'])): 
        ?>
            <p class="titulo-seccion-resumen">Equipaje Extra Seleccionado:</p>
            <ul class="lista-resumen lista-equipaje-resumen">
                <?php foreach($_SESSION['equipajes'] as $num_p => $items): ?>
                    <?php foreach($items as $id => $cant): 
                        $stmtE = $pdo->prepare("SELECT nombre_tipo, precio_unitario FROM tipos_equipaje WHERE id_tipo_equipaje = ?");
                        $stmtE->execute([$id]);
                        $eq = $stmtE->fetch(PDO::FETCH_ASSOC);
                        
                        if(!$eq) continue;
                        $monto_equipaje = ((float)$eq['precio_unitario'] * (int)$cant);
                        $subtotal_equipajes += $monto_equipaje;
                    ?>
                        <li>Pasajero #<?=$num_p?>: <?=$eq['nombre_tipo']?> (x<?=$cant?>) +$<?=number_format($monto_equipaje, 2)?></li>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </ul>
        <?php 
        endif; 
        $total_con_equipaje = $total_acumulado + $subtotal_equipajes;
        ?>
        
        <div id="contenedor-servicios-dinamico" class="bloque-dinamico">
            <p class="titulo-seccion-resumen">Servicios de a bordo:</p>
            <ul id="lista-servicios-vista" class="lista-resumen"></ul>
        </div>
        
        <hr class="separador">
        <h4 class="total-contenedor">
            <span>Total Acumulado:</span>
            <span class="total-precio" id="total-vista" data-base="<?=$total_con_equipaje?>">$<?=number_format($total_con_equipaje, 2)?></span>
        </h4>
    </div>
</div>

<script src="js/proceso_compra/servicios.js"></script>
</body>
</html>